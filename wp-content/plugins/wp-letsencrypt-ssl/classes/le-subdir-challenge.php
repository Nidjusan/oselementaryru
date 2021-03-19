<?php

/**
 * @package WP Encryption
 *
 * @author     Go Web Smarty
 * @copyright  Copyright (C) 2019-2020, Go Web Smarty
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3
 * @link       https://gowebsmarty.com
 * @since      Class available since Release 4.7.0
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
require_once WPLE_DIR . 'classes/le-trait.php';
/**
 * Sub-directory http challenge
 *
 * @since 4.7.0
 */
class WPLE_Subdir_Challenge_Helper
{
    public static function show_challenges( $opts )
    {
        if ( !isset( $opts['challenge_files'] ) && !isset( $opts['dns_challenges'] ) ) {
            return esc_html__( 'Could not retrieve domain verification challenges. Please go back and try again.', 'wp-letsencrypt-ssl' );
        }
        $output = '<h2>' . esc_html__( 'Please verify your domain ownership by completing one of the below challenges', 'wp-letsencrypt-ssl' ) . ':</h2>';
        $output .= WPLE_Trait::wple_progress_bar();
        $output .= '<div class="subdir-challenges-block">    
    <div class="subdir-http-challenge manualchallenge">' . SELF::HTTP_challenges_block( $opts['challenge_files'] ) . '</div>
    <div class="subdir-dns-challenge manualchallenge">' . SELF::DNS_challenges_block( $opts['dns_challenges'] ) . '</div>
    </div>
    <div id="wple-error-popper">    
      <div class="wple-flex">
        <img src="' . WPLE_URL . 'admin/assets/loader.png" class="wple-loader"/>
        <div class="wple-error">Error</div>
      </div>
    </div>';
        $havecPanel = ( FALSE !== get_option( 'wple_have_cpanel' ) ? get_option( 'wple_have_cpanel' ) : 0 );
        // if (!wple_fs()->can_use_premium_code__premium_only() && FALSE == get_option('wple_no_pricing')) {
        //   if (!$havecPanel) {
        //     $output .= '<div class="wple-error-firewall">
        //     <div>
        //       <img src="' . WPLE_URL . 'admin/assets/firewall-shield-firewall.png"/>
        //     </div>
        //     <div class="wple-upgrade-features">
        //       <span><b>Instant</b><br>Firewall Setup</span>
        //       <span><b>Premium</b><br>Sectigo SSL</span>
        //       <span><b>Most Secure</b><br>Firewall</span>
        //       <span><b>Accelerate</b><br>Site with CDN</span>
        //       <a href="https://wpencryption.com/cdn-firewall/?utm_campaign=wpencryption&utm_source=wordpress&utm_medium=gocdn" target="_blank">Learn More <span class="dashicons dashicons-external"></span></a>
        //     </div>
        //   </div>';
        //   } else {
        $upgradeurl = admin_url( '/admin.php?page=wp_encryption-pricing&checkout=true&plan_id=8210&plan_name=pro&billing_cycle=lifetime&pricing_id=7965&currency=usd' );
        if ( !$havecPanel ) {
            $upgradeurl = admin_url( '/admin.php?page=wp_encryption-pricing&checkout=true&plan_id=8210&plan_name=pro&billing_cycle=annual&pricing_id=7965&currency=usd' );
        }
        $output .= '<div class="wple-error-firewall">
        <div>
          <img src="' . WPLE_URL . 'admin/assets/firewall-shield-pro.png"/>
        </div>
        <div class="wple-upgrade-features">
          <span><b>Automatic</b><br>Domain Verification</span>
          <span><b>Wildcard</b><br>SSL Support</span>
          <span><b>Automatic</b><br>SSL Installation</span>
          <span><b>Automatic</b><br>SSL Renewal</span>          
          <!--<span><b>Automatic</b><br>Content Delivery Network</span>
          <span><b>Website</b><br>Security</span>-->          
          <a href="' . $upgradeurl . '">UPGRADE</a>
        </div>
      </div>';
        // }
        return $output;
    }
    
    public static function HTTP_challenges_block( $challenges )
    {
        if ( empty($challenges) ) {
            return;
        }
        $list = '<h3>' . esc_html__( 'HTTP Challenges', 'wp-letsencrypt-ssl' ) . '</h3>
    <span class="manual-verify-vid">
    <a href="https://youtu.be/GVnEQU9XWG0" target="_blank" class="videolink"><span class="dashicons dashicons-video-alt"></span> ' . esc_html__( 'Video Tutorial', 'wp-letsencrypt-ssl' ) . '</a>
    </span>
    <p><b>Step 1:</b> ' . esc_html__( 'Download HTTP challenge files below', 'wp-letsencrypt-ssl' ) . '</p>';
        $nc = wp_create_nonce( 'subdir_ch' );
        $filesExpected = '';
        $bareDomain = str_ireplace( array( 'https://', 'http://' ), array( '', '' ), site_url() );
        if ( FALSE !== ($slashpos = stripos( $bareDomain, '/' )) ) {
            $bareDomain = substr( $bareDomain, 0, $slashpos );
        }
        for ( $i = 0 ;  $i < count( $challenges ) ;  $i++ ) {
            $j = $i + 1;
            $list .= '<a href="?page=wp_encryption&subdir_chfile=' . $j . '&nc=' . $nc . '"><span class="dashicons dashicons-download"></span>&nbsp;' . esc_html__( 'Download File', 'wp-letsencrypt-ssl' ) . ' ' . $j . '</a><br />';
            $filesExpected .= '<div class="wple-http-manual-verify verify-' . esc_attr( $i ) . '"><a href="http://' . trailingslashit( esc_html( $bareDomain ) ) . '.well-known/acme-challenge/' . esc_html( $challenges[$i]['file'] ) . '" target="_blank">' . $j . '. ' . esc_html__( 'Verification File', 'wp-letsencrypt-ssl' ) . '&nbsp;<span class="dashicons dashicons-external"></span></a></div>';
        }
        $list .= '
    <p><b>Step 2:</b> ' . esc_html__( 'Open FTP or File Manager on your hosting panel', 'wp-letsencrypt-ssl' ) . '</p>
    <p><b>Step 3:</b> ' . sprintf(
            __( 'Navigate to your %sdomain%s / %ssub-domain%s folder. Create %s.well-known%s folder and create %sacme-challenge%s folder inside .well-known folder if not already created.', 'wp-letsencrypt-ssl' ),
            '<b>',
            '</b>',
            '<b>',
            '</b>',
            '<b>',
            '</b>',
            '<b>',
            '</b>'
        ) . '</p>
    <p><b>Step 4:</b> ' . esc_html__( 'Upload the above downloaded challenge files into acme-challenge folder', 'wp-letsencrypt-ssl' ) . '</p>

    <div class="wple-http-accessible">
    <p>' . esc_html__( 'Uploaded files should be publicly viewable at', 'wp-letsencrypt-ssl' ) . ':</p>
    ' . $filesExpected . '
    </div>
    
    ' . wp_nonce_field(
            'verifyhttprecords',
            'checkhttp',
            false,
            false
        ) . '
    <button id="verify-subhttp" class="subdir_verify"><span class="dashicons dashicons-update stable"></span>&nbsp;' . esc_html__( 'Verify HTTP Challenges', 'wp-letsencrypt-ssl' ) . '</button>

    <div class="http-notvalid">' . esc_html__( 'Could not verify HTTP challenges. Please check whether HTTP challenge files uploaded to acme-challenge folder is publicly accessible.', 'wp-letsencrypt-ssl' ) . ' ' . esc_html__( 'Some hosts purposefully block BOT access to acme-challenge folder, please try DNS based verification.', 'wp-letsencrypt-ssl' );
        if ( FALSE !== ($havecp = get_option( 'wple_have_cpanel' )) && $havecp && !wple_fs()->can_use_premium_code__premium_only() ) {
            $list .= ' Upgrade to <b>PRO</b> version for fully automatic domain verification.';
        }
        $list .= '</div>';
        if ( FALSE != ($httpvalid = get_option( 'wple_http_valid' )) && $httpvalid ) {
            $list .= '<div class="wple-no-http">' . esc_html__( 'HTTP verification not possible on your site as your hosting server blocks bot access. Please proceed with DNS verification.', 'wp-letsencrypt-ssl' ) . '</div>';
        }
        return $list;
    }
    
    public static function DNS_challenges_block( $challenges )
    {
        $list = '<h3>' . esc_html__( 'DNS Challenges', 'wp-letsencrypt-ssl' ) . '</h3>
    <span class="manual-verify-vid">
    <a href="https://youtu.be/BBQL69PDDrk" target="_blank" class="videolink"><span class="dashicons dashicons-video-alt"></span> ' . esc_html__( 'Video Tutorial', 'wp-letsencrypt-ssl' ) . '</a>
    </span>
    <p><b>Step 1:</b> ' . esc_html__( 'Go to domain DNS manager of your primary domain. Add below TXT records using add TXT record option.', 'wp-letsencrypt-ssl' ) . '</p>';
        $dmn = str_ireplace( array( 'https://', 'http://', 'www.' ), '', site_url() );
        for ( $i = 0 ;  $i < count( $challenges ) ;  $i++ ) {
            
            if ( FALSE !== ($slashpos = stripos( $dmn, '/' )) ) {
                $pdomain = substr( $dmn, 0, $slashpos );
            } else {
                $pdomain = $dmn;
            }
            
            $parts = explode( '.', $dmn );
            $subdomain = '';
            $domain_code = explode( '||', $challenges[$i] );
            $acmedomain = str_ireplace( $pdomain, '', $domain_code[0] );
            if ( count( $parts ) > 2 && strlen( $parts[0] ) >= 3 && strlen( $parts[1] ) > 3 ) {
                $subdomain = $parts[0] . '.';
            }
            
            if ( count( $parts ) > 3 ) {
                //double nested subdomain
                $subdomain = '';
                $acmedomain = $domain_code[0];
            }
            
            $acme = '_acme-challenge.' . esc_html( $acmedomain ) . $subdomain;
            if ( count( $parts ) <= 3 ) {
                $acme = substr( $acme, 0, -1 );
            }
            $list .= '<div class="subdns-item">
      ' . esc_html__( 'Name', 'wp-letsencrypt-ssl' ) . ': <b>' . $acme . '</b><br>
      ' . esc_html__( 'TTL', 'wp-letsencrypt-ssl' ) . ': <b>60</b> or ' . sprintf( __( '%sLowest%s possible value', 'wp-letsencrypt-ssl' ), '<b>', '</b>' ) . '<br>
      ' . esc_html__( 'Value', 'wp-letsencrypt-ssl' ) . ': <b>' . esc_html( $domain_code[1] ) . '</b>
      </div>';
        }
        $list .= '
    <p><b>Step 2:</b> ' . esc_html__( 'Please wait 5-10 Minutes for newly added DNS to propagate and then verify DNS using below button', 'wp-letsencrypt-ssl' ) . '.</p>

    ' . wp_nonce_field(
            'verifydnsrecords',
            'checkdns',
            false,
            false
        ) . '
    <button id="verify-subdns" class="subdir_verify"><span class="dashicons dashicons-update stable"></span>&nbsp;' . esc_html__( 'Verify DNS Challenges', 'wp-letsencrypt-ssl' ) . '</button>

    <div class="dns-notvalid">' . esc_html__( 'Could not verify DNS records. Please check whether you have added above DNS records perfectly or try again after 5 minutes if you added DNS records just now.', 'wp-letsencrypt-ssl' );
        if ( FALSE !== ($havecp = get_option( 'wple_have_cpanel' )) && $havecp && !wple_fs()->can_use_premium_code__premium_only() ) {
            $list .= ' Upgrade to <b>PRO</b> version for fully automatic domain verification.';
        }
        $list .= '</div>';
        return $list;
    }
    
    public static function download_challenge_files()
    {
        
        if ( isset( $_GET['subdir_chfile'] ) ) {
            if ( !wp_verify_nonce( $_GET['nc'], 'subdir_ch' ) ) {
                die( 'Unauthorized request. Please try again.' );
            }
            $opts = get_option( 'wple_opts' );
            
            if ( isset( $opts['challenge_files'] ) && !empty($opts['challenge_files']) ) {
                $req = intval( $_GET['subdir_chfile'] ) - 1;
                $ch = $opts['challenge_files'][$req];
                if ( !isset( $ch ) ) {
                    wp_die( 'Requested challenge file not exists. Please go back and try again.' );
                }
                SELF::compose_challenge_files( $ch['file'], $ch['value'] );
            } else {
                wp_die( 'HTTP challenge files not ready. Please go back and try again.' );
            }
        
        }
    
    }
    
    private static function compose_challenge_files( $name, $content )
    {
        $file = sanitize_file_name( $name );
        file_put_contents( $file, sanitize_text_field( $content ) );
        header( 'Content-Description: File Transfer' );
        header( 'Content-Type: text/plain; charset=UTF-8' );
        header( 'Content-Length: ' . filesize( $file ) );
        header( 'Content-Disposition: attachment; filename=' . basename( $file ) );
        readfile( $file );
        exit;
    }

}