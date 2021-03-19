<?php

/**
 * @package WP Encryption
 *
 * @author     Go Web Smarty
 * @copyright  Copyright (C) 2019-2020, Go Web Smarty. All Rights Reserved.
 * @link       https://gowebsmarty.com
 * @since      Class available since Release 5.1.7
 *
 */

/**
 * Mixed content scanner
 *
 * @since 5.1.7
 */
class WPLE_Scanner
{

  public function __construct()
  {
    add_action('wp_ajax_wple_start_scanner', [$this, 'wple_cspro']);
    add_action('wp_ajax_wple_clearreport', [$this, 'wple_clear_report']);
    add_action('plugins_loaded', [$this, 'wple_process_reports'], 99999);
    add_action('wp_ajax_wple_get_scanreports', [$this, 'wple_get_mxresults']);
  }

  public function wple_clear_report()
  {
    if (!wp_verify_nonce($_POST['nc'], 'wplemixedscanner')) {
      http_response_code(403);
      exit('Unauthorized');
    }

    if (is_writable(ABSPATH . '.htaccess')) {
      $htaccess = file_get_contents(ABSPATH . '.htaccess');
      $group = "/#\\s?BEGIN\\s?WP_ENCRYPTION_SCANNER.*?#\\s?END\\s?WP_ENCRYPTION_SCANNER/s";

      if (preg_match($group, $htaccess)) {
        $modhtaccess = preg_replace($group, "", $htaccess);
        file_put_contents(ABSPATH . '.htaccess', $modhtaccess, LOCK_EX);
      }
    }

    exit();
  }

  public function wple_process_reports()
  {
    if (isset($_GET['wpencryption'])) {
      session_start();

      //http_response_code(204); // HTTP 204 No Content

      if ($_SERVER['HTTP_REFERER'] != site_url('/', 'https')) exit('UNAUTHORIZED');

      if (!isset($_SESSION['mxkey']) || $_SESSION['mxkey'] !== $_GET['mxnonce']) {
        exit('Unauthorized');
      }

      $json_data = file_get_contents('php://input');

      if ($json_data = json_decode($json_data)) {
        if (!empty($json_data)) {
          foreach ($json_data as $obj) {
            $reportArray = array();
            foreach ($obj as $key => $val) {
              $reportArray[str_ireplace('-', '_', $key)] = $val;
            }
          }

          $jsn = json_encode($reportArray) . '|';
          $_SESSION['wple_mx_reports'] .= $jsn;
        }
      }

      session_write_close();
      exit();
    }
  }

  public function wple_cspro()
  {

    if (!wp_verify_nonce($_POST['nc'], 'wplemixedscanner')) {
      http_response_code(403);
      exit('Unauthorized');
    }

    if (!file_exists(ABSPATH . '.htaccess') || !is_writable(ABSPATH . '.htaccess')) {
      echo "fail";
      exit();
    }

    $basedomain = str_ireplace(array('http://', 'https://'), array('', ''), site_url());

    $client = WPLE_Trait::wple_verify_ssl($basedomain);

    if (!$client) {
      echo 'nossl';
      exit();
    }
    session_start();
    $_SESSION['wple_mx_reports'] = '';

    $mxnonce = wp_create_nonce('wplemxscan');
    $_SESSION['mxkey'] = $mxnonce;
    $reporter = site_url('/?wpencryption=1&mxnonce=' . $mxnonce, 'https');

    $rule = '<IfModule mod_headers.c>' . "\n" . '
    #<If "%{QUERY_STRING} ^wpencryption">' . "\n" . '
      <FilesMatch "\.(php|html)$">' . "\n" . '
        #Header set Report-To \'{"max_age": 1800, "group": "wpencryption", "endpoints": [{"url": "https://scanner.wpencryption.com"}]}\'' . "\n" . '
        Header set Content-Security-Policy-Report-Only "default-src \'unsafe-inline\' \'unsafe-eval\' https: data:; report-uri ' . $reporter . ';"' . "\n" . '    
      </FilesMatch>' . "\n" . '
    #</If>' . "\n" . '
    </IfModule>';

    insert_with_markers(ABSPATH . '.htaccess', 'WP_ENCRYPTION_SCANNER', $rule);

    echo "true";
    session_write_close();
    exit();
  }

  public function wple_get_mxresults()
  {
    if (!current_user_can('manage_options')) {
      exit('unauthorized');
    }
    session_start();
    header("Content-type: application/json");

    $results = $_SESSION['wple_mx_reports'];

    if (FALSE == $results || $results == '') {
      echo json_encode(array());
      delete_option('wple_mixed_issues');
      exit();
    }

    update_option('wple_mixed_issues', true);

    $results = substr($results, 0, -1);

    $results = explode('|', $results);

    $final = array();
    foreach ($results as $res) {
      $final[] = json_decode($res, true);
    }

    echo json_encode($final);
    session_destroy();
    session_write_close();
    exit();
  }
}
