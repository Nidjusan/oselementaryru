<?php

/**
 * @package WP Encryption
 *
 * @author     Go Web Smarty
 * @copyright  Copyright (C) 2019-2020, Go Web Smarty
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3
 * @link       https://gowebsmarty.com
 * @since      Class available since Release 1.0.0
 *
 *
 *   This program is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 */
/**
 * Autoloader
 * 
 * @since 5.1.1
 */
require_once plugin_dir_path(__DIR__) . 'vendor/autoload.php';
/**
 * require all the lib files for generating certs
 */

use  WPLEClient\LEFunctions;
use  WPLEClient\LEConnector;
use  WPLEClient\LEAccount;
use  WPLEClient\LEAuthorization;
use  WPLEClient\LEClient;
use  WPLEClient\LEOrder;

require_once WPLE_DIR . 'classes/le-trait.php';
/**
 * WPLE_Core class
 * Responsible for handling account registration, certificate generation & install certs on cPanel
 * 
 * @since 1.0.0  
 */
class WPLE_Core
{
  protected  $email;
  protected  $date;
  protected  $basedomain;
  protected  $domains;
  protected  $mdomain = false;
  protected  $rootdomain;
  protected  $client;
  protected  $order;
  protected  $pendings;
  protected  $wcard = false;
  protected  $dnss = false;
  protected  $iscron = false;
  protected  $noscriptresponse = false;
  protected  $disablespmode = false;
  /**
   * construct all params & proceed with cert generation
   *
   * @since 1.0.0
   * @param array $opts
   * @param boolean $gen
   */
  public function __construct(
    $opts = array(),
    $gen = true,
    $wc = false,
    $dnsverify = false
  ) {

    if (!empty($opts)) {
      $this->email = sanitize_email($opts['email']);
      $this->date = $opts['date'];
      $optss = $opts;
    } else {
      $optss = get_option('wple_opts');
      $this->email = (isset($optss['email']) ? sanitize_email($optss['email']) : '');
      $this->date = (isset($optss['date']) ? $optss['date'] : '');
    }

    if (isset($optss['type']) && $optss['type'] == 'wildcard') {
      $wc = true;
    }
    $siteurl = site_url();
    if (isset($optss['subdir'])) {
      $siteurl = sanitize_text_field($optss['domain']);
    }
    $this->rootdomain = str_ireplace(array('http://', 'https://', 'www.'), array('', '', ''), $siteurl);
    $this->basedomain = str_ireplace(array('http://', 'https://'), array('', ''), $siteurl);
    $this->domains = array($this->basedomain);
    //include both www & non-www

    if (isset($optss['include_www']) && $optss['include_www'] == 1) {
      $this->basedomain = $this->rootdomain;
      $this->domains = array($this->rootdomain, 'www.' . $this->rootdomain);
    }

    if ($dnsverify) {
      //manual dns verify
      $this->dnss = true;
    }
    if (get_option('wple_disable_spmode') == 'true') {
      $this->disablespmode = true;
    }
    if ($gen) {
      $this->wple_generate_verify_ssl();
    }
  }

  /**
   * group all different steps into one function & clear debug.log intially.
   *
   * @since 1.0.0
   * @return void
   */
  public function wple_generate_verify_ssl()
  {
    delete_option('wple_complete');
    $init = (int) get_option('wple_have_cpanel');
    //since 4.7

    if (!isset($_GET['wpleauto'])) {
      update_option('wple_http_valid', 0);

      if (isset($_POST['wple_send_usage'])) {
        update_option('wple_send_usage', 1);
      } else {
        update_option('wple_send_usage', 0);
      }

      $PRO = (wple_fs()->can_use_premium_code__premium_only() ? 'PRO' : '');
      $PRO .= ($this->wcard ? ' WILDCARD SSL ' : ' SINGLE DOMAIN SSL ');
      $PRO .= $init;
      $this->wple_log('<b>' . WPLE_VERSION . ' ' . $PRO . ' - ' . esc_html(site_url()) . '</b>', 'success', 'w');
    }

    $this->wple_create_client();
    $this->wple_generate_order();
    $this->wple_verify_pending_orders();
    $this->wple_generate_certs();
    if (FALSE != ($dlog = get_option('wple_send_usage')) && $dlog) {
      $this->wple_send_usage_data();
    }
  }

  /**
   * create ACMEv2 client
   *
   * @since 1.0.0
   * @return void
   */
  protected function wple_create_client()
  {
    try {
      $keydir = ABSPATH . 'keys/';
      $this->client = new LEClient(
        $this->email,
        false,
        LEClient::LOG_STATUS,
        $keydir
      );
    } catch (Exception $e) {
      update_option('wple_error', 1);
      $this->wple_log(
        "CREATE_CLIENT:" . $e,
        'error',
        'w',
        true
      );
    }
    ///echo '<pre>'; print_r( $client->getAccount() ); echo '</pre>';
  }

  /**
   * Generate order with ACMEv2 client for given domain
   *
   * @since 1.0.0
   * @return void
   */
  protected function wple_generate_order()
  {
    try {
      $this->order = $this->client->getOrCreateOrder($this->basedomain, $this->domains);
    } catch (Exception $e) {
      $this->wple_log(
        "CREATE_ORDER:" . $e,
        'error',
        'w',
        true
      );
    }
  }

  /**
   * Get all pendings orders which need domain verification
   *
   * @since 1.0.0
   * @return void
   */
  protected function wple_get_pendings($dns = false)
  {
    $chtype = LEOrder::CHALLENGE_TYPE_HTTP;
    $http = 1;

    if ($this->dnss || $dns) {
      $chtype = LEOrder::CHALLENGE_TYPE_DNS;
      $http = 0;
    }

    try {
      $this->pendings = $this->order->getPendingAuthorizations($chtype);

      if (!empty($this->pendings) && $http == 1) {
        $opts = get_option('wple_opts');
        $opts['challenge_files'] = array();
        foreach ($this->pendings as $chlng) {
          $opts['challenge_files'][] = array(
            'file'  => sanitize_text_field(trim($chlng['filename'])),
            'value' => sanitize_text_field(trim($chlng['content'])),
          );
        }
        update_option('wple_opts', $opts);
      }
    } catch (Exception $e) {
      $this->wple_log(
        'GET_PENDING_AUTHS:' . $e,
        'error',
        'w',
        true
      );
    }
  }

  /**
   * verify all the challenges via HTTP
   *
   * @since 1.0.0
   * @return void
   */
  protected function wple_verify_pending_orders($forcehttpverify = false, $forcednsverify = false, $is_cron = false)
  {
    $this->iscron = $is_cron;
    // $this->order->deactivateOrderAuthorization($this->basedomain);
    // $this->order->revokeCertificate();
    // exit();
    if (isset($_GET['wpleauto'])) {

      if ($_GET['wpleauto'] == 'http') {
        $forcehttpverify = true;
      } else {
        $forcednsverify = true;
      }
    }

    if (!$this->order->allAuthorizationsValid()) {
      //since 4.7
      $this->wple_override_subdir_logic();

      if ($this->wcard) { } else {
        $this->wple_single_ssl_verify($forcehttpverify, $forcednsverify);
      }
    }
  }

  /**
   * Finalize and get certificates
   *
   * @since 1.0.0
   * @return void
   */
  public function wple_generate_certs($rectify = true)
  {

    if ($this->order->allAuthorizationsValid()) {
      // Finalize the order

      if (!$this->order->isFinalized()) {
        $this->wple_log(esc_html__('Finalizing the order', 'wp-letsencrypt-ssl'), 'success', 'a');
        $this->order->finalizeOrder();
      }

      // get the certificate.

      if ($this->order->isFinalized()) {
        $this->wple_log(esc_html__('Getting SSL certificates', 'wp-letsencrypt-ssl'), 'success', 'a');
        $this->order->getCertificate();
      }

      //since 5.3.5
      $this->wple_email_cert_files();
      $cert = ABSPATH . 'keys/certificate.crt';

      if (file_exists($cert)) {
        $this->wple_save_expiry_date();
        $sslgenerated = "<h2>" . esc_html__('SSL Certificate generated successfully', 'wp-letsencrypt-ssl') . "!</h2>";
        $this->wple_log($sslgenerated, 'success', 'a');
        if (FALSE != ($dlog = get_option('wple_send_usage')) && $dlog) {
          $this->wple_send_usage_data();
        }
        wp_redirect(admin_url('/admin.php?page=wp_encryption&success=1'), 302);
        exit;
      }
    } else {
      update_option('wple_error', 2);
      $this->wple_log(
        '<h2>' . esc_html__('There are some pending verifications. If new DNS records were added, please run this installation again after 5-10mins', 'wp-letsencrypt-ssl') . '</h2>',
        'success',
        'a',
        false
      );
      $this->wple_http_not_possible();
      $this->wple_log(
        '',
        'success',
        'a',
        true
      );
    }
  }

  /**
   * Save expiry date of cert dynamically by parsing the cert
   *
   * @since 1.0.0
   * @return void
   */
  public function wple_save_expiry_date()
  {
    $certfile = ABSPATH . 'keys/certificate.crt';

    if (file_exists($certfile)) {
      $opts = get_option('wple_opts');
      $opts['expiry'] = '';
      try {
        $this->wple_getRemainingDays($certfile, $opts);
      } catch (Exception $e) {
        update_option('wple_opts', $opts);
        //echo $e;
        //exit();
      }
    }
  }

  /**
   * Utility functions
   * 
   * @since 1.0.0 
   */
  public function wple_parseCertificate($cert_pem)
  {
    // if (false === ($ret = openssl_x509_read(file_get_contents($cert_pem)))) {
    //   throw new Exception('Could not load certificate: ' . $cert_pem . ' (' . $this->get_openssl_error() . ')');
    // }
    if (!is_array($ret = openssl_x509_parse(file_get_contents($cert_pem), true))) {
      throw new Exception('Could not parse certificate');
    }
    return $ret;
  }

  public function wple_getRemainingDays($cert_pem, $opts)
  {
    $ret = $this->wple_parseCertificate($cert_pem);
    $expiry = date('d-m-Y', $ret['validTo_time_t']);
    $opts['expiry'] = $expiry;
    if (isset($opts['expiry'])) {
      wp_clear_scheduled_hook('wple_ssl_reminder_notice');
    }
    if ($opts['expiry'] != '') {
      wp_schedule_single_event(strtotime('-10 day', strtotime($opts['expiry'])), 'wple_ssl_reminder_notice');
    }
    update_option('wple_opts', $opts);
    update_option('wple_show_review', 1);
    do_action('cert_expiry_updated');
  }

  public function wple_log(
    $msg = '',
    $type = 'success',
    $mode = 'a',
    $redirect = false
  ) {
    $handle = fopen(WPLE_DEBUGGER . 'debug.log', $mode);
    if ($type == 'error') {
      $msg = '<span class="error"><b>' . esc_html__('ERROR', 'wp-letsencrypt-ssl') . ':</b> ' . wp_kses_post($msg) . '</span>';
    }
    fwrite($handle, wp_kses_post($msg) . "\n");
    fclose($handle);

    if ($redirect) {
      if (FALSE != ($dlog = get_option('wple_send_usage')) && $dlog) {
        $this->wple_send_usage_data();
      }
      wp_redirect(admin_url('/admin.php?page=wp_encryption&error=1'), 302);
      die;
    }
  }

  /**
   * Collect usage data to improve plugin
   *
   * @since 2.1.0
   * @return void
   */
  public function wple_send_usage_data()
  {
    $readlog = file_get_contents(WPLE_DEBUGGER . 'debug.log');
    $handle = curl_init();
    $srvr = array(
      'challenge_folder_exists' => file_exists(ABSPATH . '.well-known/acme-challenge'),
      'certificate_exists'      => file_exists(ABSPATH . 'keys/certificate.crt'),
      'server_software'         => $_SERVER['SERVER_SOFTWARE'],
      'http_host'               => $_SERVER['HTTP_HOST'],
      'pro'                     => (wple_fs()->is__premium_only() ? 'PRO' : 'FREE'),
    );
    $curlopts = array(
      CURLOPT_RETURNTRANSFER => 1,
      CURLOPT_POST           => 1,
      CURLOPT_URL            => 'https://gowebsmarty.in/?catchwple=1',
      CURLOPT_HEADER         => false,
      CURLOPT_POSTFIELDS     => array(
        'response' => $readlog,
        'server'   => json_encode($srvr),
      ),
    );
    curl_setopt_array($handle, $curlopts);
    curl_exec($handle);
    curl_close($handle);
  }

  /**
   * Show DNS records for domain verification
   *
   * @since 2.2.0
   * @return void
   */
  private function reloop_get_dns($return = false)
  {
    $site = str_ireplace('www.', '', $this->basedomain);
    $vrfy = '';
    $this->wple_get_pendings(true);
    $dns_records = array();
    foreach ($this->pendings as $challenge) {

      if ($challenge['type'] == 'dns-01' && stripos($challenge['identifier'], $site) !== FALSE) {
        $vrfy .= 'Name: <b>_acme-challenge.' . $site . '</b> or <b>_acme-challenge</b>
          TTL: <b>60</b> or <b>Lowest</b> possible value
          Type: <b>TXT</b>
          Value: <b>' . esc_html($challenge['DNSDigest']) . '</b><br>
          ';
        $dns_records[] = esc_html($challenge['DNSDigest']);
      }
    }
    if ($return) {
      return $dns_records;
    }
    $this->wple_log($vrfy, 'success', 'a');
  }

  /**
   * Deploy challenge files
   *
   * @since 3.2.0
   * @param array $challenge
   * @return void
   */
  private function wple_deploy_challenge_files($acmefile, $challenge)
  {
    $fpath = ABSPATH . '.well-known/acme-challenge/';
    if (!file_exists($fpath)) {
      mkdir($fpath, 0775, true);
    }
    $this->wple_log(esc_html__('Creating HTTP challenge file', 'wp-letsencrypt-ssl') . ' ' . $acmefile, 'success', 'a');
    file_put_contents($fpath . $challenge['filename'], trim($challenge['content']));
  }

  /**
   * Retrieve file content
   *
   * @since 3.2.0
   * @param string $acmefile
   * @return void
   */
  private function wple_get_file_response($acmefile)
  {
    $args = array(
      'sslverify' => false,
    );
    $remoteget = wp_remote_get($acmefile, $args);

    if (is_wp_error($remoteget)) {
      $rsponse = 'error';
    } else {
      $rsponse = trim(wp_remote_retrieve_body($remoteget));
    }

    return $rsponse;
  }

  /**
   * Save HTTP + DNS challenges for later use
   *
   * @since 4.6.0
   * @return void
   */
  private function wple_save_all_challenges($dnsonly = false)
  {
    $opts = (FALSE === get_option('wple_opts') ? array() : get_option('wple_opts'));
    //DNS
    $chtype = LEOrder::CHALLENGE_TYPE_DNS;
    try {
      $dns_challenges = $this->order->getPendingAuthorizations($chtype);

      if (!empty($dns_challenges)) {
        $opts['dns_challenges'] = array();
        foreach ($dns_challenges as $challenge) {

          if ($challenge['type'] == 'dns-01' && stripos($challenge['identifier'], $this->rootdomain) !== FALSE) {
            $identifier = $challenge['identifier'];
            $opts['dns_challenges'][] = sanitize_text_field($identifier) . '||' . sanitize_text_field($challenge['DNSDigest']);
          }
        }
      }
    } catch (Exception $e) {
      $this->wple_log(
        'Unable to store DNS challenges:' . $e,
        'error',
        'w',
        true
      );
    }

    if ($opts['type'] != 'wildcard') {
      //HTTP
      $chtype = LEOrder::CHALLENGE_TYPE_HTTP;
      try {
        $httppendings = $this->order->getPendingAuthorizations($chtype);

        if (!empty($httppendings)) {
          $opts['challenge_files'] = array();
          foreach ($httppendings as $chlng) {
            $opts['challenge_files'][] = array(
              'file'  => sanitize_text_field(trim($chlng['filename'])),
              'value' => sanitize_text_field(trim($chlng['content'])),
            );
          }
        }
      } catch (Exception $e) {
        $this->wple_log(
          'Unable to store HTTP challenges:' . $e,
          'error',
          'w',
          true
        );
      }
    }

    update_option('wple_opts', $opts);
  }

  /**
   * Detect sub-dir site & act accordingly
   * Manual verification for subdir site
   *
   * @since 4.7.0
   * @return void
   */
  private function wple_override_subdir_logic()
  {
    $opts = get_option('wple_opts');

    if (isset($opts['subdir']) && !isset($_GET['wpleauto'])) {
      update_option('wple_error', 2);
      $this->wple_log('Cleaning & re-generating challenges', 'success', 'a');
      if (isset($opts['challenge_files'])) {
        unset($opts['challenge_files']);
      }
      if (isset($opts['dns_challenges'])) {
        unset($opts['dns_challenges']);
      }
      $this->wple_save_all_challenges();
      wp_redirect(admin_url('/admin.php?page=wp_encryption&subdir=1'), 302);
      exit;
    }
  }

  /**
   * DNS only verification for http noscript
   *
   * @since 5.0.7
   * @return void
   */
  public function wple_http_not_possible()
  {

    if (FALSE != ($httpvalid = get_option('wple_http_valid')) && $httpvalid) {
      $this->wple_save_all_challenges();
      wp_redirect(admin_url('/admin.php?page=wp_encryption&subdir=1'), 302);
      exit;
    }
  }

  protected function wple_single_ssl_verify($forcehttpverify, $forcednsverify)
  {

    if ($forcednsverify) {
      //dns verify
      $this->wple_get_pendings(true);
    } else {
      $this->wple_get_pendings();
    }

    if (!empty($this->pendings)) {
      foreach ($this->pendings as $challenge) {

        if ($challenge['type'] == 'dns-01' && stripos($challenge['identifier'], $this->rootdomain) !== FALSE) {
          $this->order->verifyPendingOrderAuthorization($challenge['identifier'], LEOrder::CHALLENGE_TYPE_DNS);
        } else {

          if ($challenge['type'] == 'http-01' && stripos($challenge['identifier'], $this->rootdomain) !== FALSE) {
            ///if (!$this->dnss && !$forcednsverify) {
            $acmefile = "http://" . $challenge['identifier'] . "/.well-known/acme-challenge/" . $challenge['filename'];

            $rsponse = $this->wple_get_file_response($acmefile);

            if ($rsponse != trim($challenge['content']) && !isset($_GET['wpleauto'])) {
              update_option('wple_error', 2);
              $this->wple_log(esc_html__("Offering manual verification procedure.", 'wp-letsencrypt-ssl') . " \n", 'success', 'a');
              if (FALSE != ($dlog = get_option('wple_send_usage')) && $dlog) {
                $this->wple_send_usage_data();
              }
              $this->wple_save_all_challenges();
              wp_redirect(admin_url('/admin.php?page=wp_encryption&subdir=1'), 302);
              exit;
            }

            $this->order->verifyPendingOrderAuthorization($challenge['identifier'], LEOrder::CHALLENGE_TYPE_HTTP);
            ///}
          }
        }
      }
    }
  }

  protected function wple_goto_manual_challenges()
  {
    $this->wple_save_all_challenges();
    wp_redirect(admin_url('/admin.php?page=wp_encryption&subdir=1'), 302);
    exit;
  }

  /**
   * simple debug log message
   *
   * @since 5.2.6
   * @return void
   */
  private function wple_nocpanel_notice($renewal = false)
  {

    if ($renewal == false) {
      WPLE_Trait::wple_logger("Awaiting SSL installation for Non-cPanel site and SSL validation\n", "success");
      wp_redirect(admin_url('/admin.php?page=wp_encryption&nocpanel=1'), 302);
      exit;
    }
  }

  /**
   * Email SSL certs as attachment
   *
   * @since 5.3.5
   * @return void
   */
  private function wple_email_cert_files()
  {

    if (class_exists('ZipArchive')) {
      $opts = get_option('wple_opts');

      if (get_option('wple_email_certs') == 'true') {
        $zip = new ZipArchive();
        $zip->open(ABSPATH . 'keys/certificates.zip', ZipArchive::CREATE);
        $certificate = ABSPATH . 'keys/certificate.crt';
        $zip->addFile($certificate, 'certificate.crt');
        $pemfile = ABSPATH . 'keys/private.pem';
        $zip->addFile($pemfile, 'private.pem');
        $cabundle = ABSPATH . 'keys/cabundle.crt';
        $zip->addFile($cabundle, 'cabundle.crt');
        $zip->close();
        $to = sanitize_email($opts['email']);
        $subject = esc_html__('WP Encryption - Please find new SSL certs attached', 'wp-letsencrypt-ssl');
        $headers = array('Content-Type: text/html; charset=UTF-8');
        $body = '<h2>' . esc_html__('Your SSL certs have been generated / auto renewed today!', 'wp-letsencrypt-ssl') . '</h2>';
        $body .= '<p>' . esc_html__('New SSL cert files have been attached to this email as per your preferences.', 'wp-letsencrypt-ssl') . ' ' . esc_html__('These certificates expires on', 'wp-letsencrypt-ssl') . ' <b>' . esc_html($opts['expiry']) . '</b></p>';
        wp_mail(
          $to,
          $subject,
          $body,
          $headers,
          array(ABSPATH . 'keys/certificates.zip')
        );
      }
    }
  }
}
