<?php

/**
 * @package WP Encryption
 *
 * @author     Go Web Smarty
 * @copyright  Copyright (C) 2019-2020, Go Web Smarty
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3
 * @link       https://gowebsmarty.com
 * @since      Class available since Release 5.1.0
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
class WPLE_Trait
{
    /**
     * Progress & error indicator
     *
     * @since 4.4.0 
     * @return void
     */
    public static function wple_progress_bar( $yellow = 0 )
    {
        $stage1 = $stage2 = $stage3 = $stage4 = '';
        $progress = get_option( 'wple_error' );
        
        if ( FALSE === $progress ) {
            //still waiting first run
        } else {
            
            if ( $progress == 0 ) {
                //success
                $stage1 = $stage2 = $stage3 = 'prog-1';
            } else {
                
                if ( $progress == 1 || $progress == 400 || $progress == 429 ) {
                    //failed on first step
                    $stage1 = 'prog-0';
                } else {
                    
                    if ( $progress == 2 ) {
                        $stage1 = 'prog-1';
                        $stage2 = 'prog-0';
                    } else {
                        
                        if ( $progress == 3 ) {
                            $stage1 = $stage2 = 'prog-1';
                            $stage3 = 'prog-0';
                        } else {
                            
                            if ( $progress == 4 ) {
                                $stage1 = $stage2 = $stage3 = 'prog-1';
                                $stage4 = 'prog-0';
                            } else {
                                if ( $progress == 5 ) {
                                    $stage1 = $stage2 = $stage3 = 'prog-1';
                                }
                            }
                        
                        }
                    
                    }
                
                }
            
            }
        
        }
        
        if ( FALSE !== ($cmp = get_option( 'wple_complete' )) && $cmp ) {
            $stage1 = $stage2 = $stage3 = $stage4 = 'prog-1';
        }
        $out = '<ul class="wple-progress">
      <li class="' . $stage1 . '"><a href="?page=wp_encryption&restart=1" class="wple-tooltip" data-tippy="' . esc_attr__( "Click to re-start from beginning", 'wp-letsencrypt-ssl' ) . '"><span>1</span>&nbsp;' . esc_html__( 'Registration', 'wp-letsencrypt-ssl' ) . '</a></li>
      <li class="' . $stage2 . '"><span>2</span>&nbsp;' . esc_html__( 'Domain Verification', 'wp-letsencrypt-ssl' ) . '</li>
      <!--<li class="' . $stage3 . '"><span>3</span>&nbsp;' . esc_html__( 'Certificate Generated', 'wp-letsencrypt-ssl' ) . '</li>-->
      <li class="' . $stage4 . ' onprocess' . esc_attr( $yellow ) . '"><span>3</span>&nbsp;' . esc_html__( 'Install SSL Certificate', 'wp-letsencrypt-ssl' ) . '</li>';
        $out .= '</ul>';
        return $out;
    }
    
    public static function wple_get_acmename( $nonwwwdomain, $identifier )
    {
        $dmn = $nonwwwdomain;
        
        if ( FALSE !== ($slashpos = stripos( $dmn, '/' )) ) {
            $pdomain = substr( $dmn, 0, $slashpos );
        } else {
            $pdomain = $dmn;
        }
        
        $parts = explode( '.', $dmn );
        $subdomain = '';
        $acmedomain = str_ireplace( $pdomain, '', $identifier );
        if ( count( $parts ) > 2 && strlen( $parts[0] ) >= 3 ) {
            $subdomain = $parts[0] . '.';
        }
        
        if ( count( $parts ) > 3 ) {
            //double nested subdomain
            $subdomain = '';
            $acmedomain = $identifier;
        }
        
        $acme = '_acme-challenge.' . esc_html( $acmedomain ) . $subdomain;
        if ( count( $parts ) <= 3 ) {
            $acme = substr( $acme, 0, -1 );
        }
        return $acme;
    }
    
    public static function wple_Is_SubDomain( $syt )
    {
        $parts = explode( '.', $syt );
        
        if ( count( $parts ) > 2 && strlen( $parts[0] ) >= 3 && strlen( $parts[1] ) > 2 ) {
            return true;
            //probably subdomain
        }
        
        return false;
    }
    
    /**
     * FAQ & Videos
     *
     * @param [type] $html
     * @return void
     * @since 5.2.2
     */
    public static function wple_headernav( &$html )
    {
        if ( !wple_fs()->is_plan( 'firewall', true ) ) {
            
            if ( FALSE == ($fstage = get_option( 'wple_firewall_stage' )) || $fstage != 6 ) {
                $html .= '<ul id="wple-nav">
        <li><a href="' . admin_url( '/admin.php?page=wp_encryption_log' ) . '"><span class="dashicons dashicons-admin-tools"></span> ' . esc_html__( 'Debug Log', 'wp-letsencrypt-ssl' ) . '</a></li>
        <li><a href="' . admin_url( '/admin.php?page=wp_encryption_faq' ) . '"><span class="dashicons dashicons-editor-help"></span> ' . esc_html__( 'FAQ', 'wp-letsencrypt-ssl' ) . '</a></li>
        <li><a href="' . admin_url( '/admin.php?page=wp_encryption_howto_videos' ) . '"><span class="dashicons dashicons-video-alt3"></span> ' . esc_html__( 'Videos', 'wp-letsencrypt-ssl' ) . '</a></li>';
                $html .= '<li><a href="https://wordpress.org/support/plugin/wp-letsencrypt-ssl/" target="_blank" rel="nofollow"><span class="dashicons dashicons-sos"></span> ' . esc_html__( 'Free Support', 'wp-letsencrypt-ssl' ) . '</a></li>';
                //if (!wple_fs()->is__premium_only()) {
                //$html .= '<li><a href="https://wpencryption.com/cdn-firewall/" target="_blank"><span class="dashicons dashicons-superhero" style="font-size: 26px; width: 26px; line-height: 21px !important; margin-left: -5px;"></span> Speed Up Your Site <span class="dashicons dashicons-editor-help wple-tooltip bottom" data-tippy="Sky rocket your WordPress site performance with Fastest Content Delivery Network + Premium Sectigo SSL + Secure Firewall"></span></a></li>';
                //}
                $html .= '</ul>';
            }
        
        }
    }
    
    /**
     * Debug logger
     *
     * @param string $msg
     * @param string $type
     * @param string $mode
     * @param boolean $redirect
     * @return void
     * 
     * @since 5.2.4
     */
    public static function wple_logger(
        $msg = '',
        $type = 'success',
        $mode = 'a',
        $redirect = false
    )
    {
        $handle = fopen( WPLE_DEBUGGER . 'debug.log', $mode );
        if ( $type == 'error' ) {
            $msg = '<span class="error"><b>' . esc_html__( 'ERROR', 'wp-letsencrypt-ssl' ) . ':</b> ' . wp_kses_post( $msg ) . '</span>';
        }
        fwrite( $handle, wp_kses_post( $msg ) . "\n" );
        fclose( $handle );
        
        if ( $redirect ) {
            if ( FALSE != ($dlog = get_option( 'wple_send_usage' )) && $dlog ) {
                SELF::wple_send_log_data();
            }
            wp_redirect( admin_url( '/admin.php?page=wp_encryption&error=1' ), 302 );
            die;
        }
    
    }
    
    public static function wple_send_log_data()
    {
        $readlog = file_get_contents( WPLE_DEBUGGER . 'debug.log' );
        $handle = curl_init();
        $srvr = array(
            'challenge_folder_exists' => file_exists( ABSPATH . '.well-known/acme-challenge' ),
            'certificate_exists'      => file_exists( ABSPATH . 'keys/certificate.crt' ),
            'server_software'         => $_SERVER['SERVER_SOFTWARE'],
            'http_host'               => $_SERVER['HTTP_HOST'],
            'pro'                     => ( wple_fs()->is__premium_only() ? 'PRO' : 'FREE' ),
        );
        $curlopts = array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_POST           => 1,
            CURLOPT_URL            => 'https://gowebsmarty.in/?catchwple=1',
            CURLOPT_HEADER         => false,
            CURLOPT_POSTFIELDS     => array(
            'response' => $readlog,
            'server'   => json_encode( $srvr ),
        ),
        );
        curl_setopt_array( $handle, $curlopts );
        curl_exec( $handle );
        curl_close( $handle );
    }
    
    /**
     * Send reverter code on force HTTPS
     *
     * @since 3.3.0
     * @source le-admin.php
     * @since 5.2.4
     * @param string $revertcode
     * @return void
     */
    public static function wple_send_reverter_secret( $revertcode )
    {
        // $to = get_bloginfo('admin_email');
        // $sub = esc_html__('You have successfully forced HTTPS on your site', 'wp-letsencrypt-ssl');
        // $header = array('Content-Type: text/html; charset=UTF-8');
        // $rcode = sanitize_text_field($revertcode);
        // $body = SELF::wple_kses(__("HTTPS have been strictly forced on your site now!. In rare cases, this may cause issue / make the site un-accessible <b>IF</b> you dont have valid SSL certificate installed for your WordPress site. Kindly save the below <b>Secret code</b> to revert back to HTTP in such a case.", 'wp-letsencrypt-ssl')) . "
        //   <br><br>
        //   <strong>$rcode</strong><br><br>" .
        //   SELF::wple_kses(__("Opening the revert url will <b>IMMEDIATELY</b> turn back your site to HTTP protocol & revert back all the force SSL changes made by WP Encryption in one go!. Please follow instructions given at https://wordpress.org/support/topic/locked-out-unable-to-access-site-after-forcing-https-2/", 'wp-letsencrypt-ssl')) . "<br>
        //   <br>
        //   " . esc_html__("Revert url format", 'wp-letsencrypt-ssl') . ": http://yourdomainname.com/?reverthttps=SECRETCODE<br>
        //   " . esc_html__("Example:", 'wp-letsencrypt-ssl') . " http://wpencryption.com/?reverthttps=wple43643sg5qaw<br>
        //   <br>
        //   " . esc_html__("We have spent several hours to craft this plugin to perfectness. Please take a moment to rate us with 5 stars", 'wp-letsencrypt-ssl') . " - https://wordpress.org/support/plugin/wp-letsencrypt-ssl/reviews/#new-post
        //   <br />";
        // wp_mail($to, $sub, $body, $header);
    }
    
    /**
     * Escape html but retain bold
     *
     * @since 3.3.3
     * @source le-admin.php
     * @since 5.2.4
     * @param string $translated
     * @param string $additional Additional allowed html tags
     * @return void
     */
    public static function wple_kses( $translated, $additional = '' )
    {
        $allowed = array(
            'strong' => array(),
            'b'      => array(),
        );
        if ( $additional == 'a' ) {
            $allowed['a'] = array(
                'href'   => array(),
                'rel'    => array(),
                'target' => array(),
                'title'  => array(),
            );
        }
        return wp_kses( $translated, $allowed );
    }
    
    public static function wple_verify_ssl( $domain )
    {
        $streamContext = stream_context_create( [
            'ssl' => [
            'verify_peer' => true,
        ],
        ] );
        $errorDescription = $errorNumber = '';
        $client = @stream_socket_client(
            "ssl://{$domain}:443",
            $errorNumber,
            $errorDescription,
            30,
            STREAM_CLIENT_CONNECT,
            $streamContext
        );
        return $client;
    }
    
    public static function compose_htaccess_rules( $spmode = false )
    {
        $rule = "\n" . "# BEGIN WP_Encryption_Force_SSL\n";
        $rule .= "<IfModule mod_rewrite.c>" . "\n";
        $rule .= "RewriteEngine on" . "\n";
        $rule .= "RewriteCond %{HTTPS} !=on [NC]" . "\n";
        
        if ( isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' ) {
            $rule .= "RewriteCond %{HTTP:X-Forwarded-Proto} !https" . "\n";
        } elseif ( isset( $_SERVER['HTTP_X_PROTO'] ) && $_SERVER['HTTP_X_PROTO'] == 'SSL' ) {
            $rule .= "RewriteCond %{HTTP:X-Proto} !SSL" . "\n";
        } elseif ( isset( $_SERVER['HTTP_X_FORWARDED_SSL'] ) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on' ) {
            $rule .= "RewriteCond %{HTTP:X-Forwarded-SSL} !on" . "\n";
        } elseif ( isset( $_SERVER['HTTP_X_FORWARDED_SSL'] ) && $_SERVER['HTTP_X_FORWARDED_SSL'] == '1' ) {
            $rule .= "RewriteCond %{HTTP:X-Forwarded-SSL} !=1" . "\n";
        } elseif ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) || $spmode ) {
            $rule .= "RewriteCond %{HTTP:X-Forwarded-FOR} ^\$" . "\n";
        } elseif ( isset( $_SERVER['HTTP_CF_VISITOR'] ) && $_SERVER['HTTP_CF_VISITOR'] == 'https' ) {
            $rule .= "RewriteCond %{HTTP:CF-Visitor} '" . '"scheme":"http"' . "'" . "\n";
        } elseif ( isset( $_SERVER['SERVER_PORT'] ) && '443' == $_SERVER['SERVER_PORT'] ) {
            $rule .= "RewriteCond %{SERVER_PORT} !443" . "\n";
        } elseif ( isset( $_SERVER['HTTP_CLOUDFRONT_FORWARDED_PROTO'] ) && $_SERVER['HTTP_CLOUDFRONT_FORWARDED_PROTO'] == 'https' ) {
            $rule .= "RewriteCond %{HTTP:CloudFront-Forwarded-Proto} !https" . "\n";
        } elseif ( isset( $_ENV['HTTPS'] ) && 'on' == $_ENV['HTTPS'] ) {
            $rule .= "RewriteCond %{ENV:HTTPS} !=on" . "\n";
        }
        
        
        if ( is_multisite() ) {
            global  $wp_version ;
            $sites = ( $wp_version >= 4.6 ? get_sites() : wp_get_sites() );
            foreach ( $sites as $domn ) {
                $domain = str_ireplace( array( "http://", "https://", "www." ), array( "", "", "" ), $domn->domain );
                if ( FALSE != ($spos = stripos( $domain, '/' )) ) {
                    $domain = substr( $domain, 0, $spos );
                }
                $www = 'www.' . $domain;
                $rule .= "RewriteCond %{HTTP_HOST} ^" . preg_quote( $domain, "/" ) . " [OR]" . "\n";
                $rule .= "RewriteCond %{HTTP_HOST} ^" . preg_quote( $www, "/" ) . " [OR]" . "\n";
            }
            if ( count( $sites ) > 0 ) {
                $rule = strrev( implode( "", explode( strrev( "[OR]" ), strrev( $rule ), 2 ) ) );
            }
        }
        
        $rule .= "RewriteCond %{REQUEST_URI} !^/\\.well-known/acme-challenge/" . "\n";
        $rule .= "RewriteRule ^(.*)\$ https://%{HTTP_HOST}/\$1 [R=301,L]" . "\n";
        $rule .= "</IfModule>" . "\n";
        $rule .= "# END WP_Encryption_Force_SSL" . "\n";
        $finalrule = preg_replace( "/\n+/", "\n", $rule );
        return $finalrule;
    }
    
    /**
     * Get root domain
     *
     * @since 5.3.5
     * @return void
     */
    public static function get_root_domain()
    {
        $currentdomain = esc_html( str_ireplace( array( 'http://', 'https://' ), array( '', '' ), site_url() ) );
        $slashpos = stripos( $currentdomain, '/' );
        if ( FALSE !== $slashpos ) {
            //subdir installation
            $currentdomain = substr( $currentdomain, 0, $slashpos );
        }
        return $currentdomain;
    }

}