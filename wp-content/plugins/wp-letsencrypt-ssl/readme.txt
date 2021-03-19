=== WP Encryption - One Click Free SSL Certificate & SSL / HTTPS Redirect to fix Insecure Content ===
Contributors: gowebsmarty, gwsharsha
Tags: ssl,https,ssl certificate,free ssl,mixed content,force ssl,https redirect,insecure content,enable ssl,security,force ssl,lets encrypt,install ssl,enable https
Requires at least: 4.2
License: GPL3
Tested up to: 5.7
Requires PHP: 5.4
Stable tag: 5.3.15

Lifetime SSL Solution - Install free SSL certificate and enable SSL / HTTPS sitewide, fixing insecure content & mixed content issues easily. Force SSL instantly & download SSL certificate.

== Description ==

Generate free Let's Encrypt SSL certificate for your WordPress site and force SSL/HTTPS sitewide, fixing insecure content & mixed content issues easily.

Secure your WordPress site with SSL certificate provided by [Let's Encrypt®](https://letsencrypt.com). [WP Encryption](https://wpencryption.com/?utm_source=wordpress&utm_medium=description&utm_campaign=wpencryption) plugin registers your site, verifies your domain, generates SSL certificate for your site in simple mouse clicks without the need of any technical knowledge. 

A typical SSL installation without WP Encryption would require you to generate CSR, prove domain ownership, provide your bussiness data and deal with many more technical tasks!.

== 200k+ Downloads Worldwide -- 280k+ SSL certificates generated ==

https://youtu.be/aKvvVlAlZ14

= REQUIREMENTS =
PHP 5.4 & tested upto PHP 8.0, Linux hosting, OpenSSL, CURL, allow_url_fopen should be enabled.

== FREE Features ==
1. Manual domain verification

2. Manual SSL installation (Download generated SSL certificates with a click of button and Follow very simple video tutorial to install SSL certificate on your cPanel)

3. Manual SSL renewal (SSL certificates expire in 90 days. Make sure to renew it before expiry date to avoid insecure warning on site)

4. Force HTTPS + Redirect loop fix for Cloudflare, StackPath, Load balancers and reverse proxies.

5. (NEW) [Mixed content scanner](https://wpencryption.com/mixed-content-scanner/) (Run a mixed content scan for frontend or backend admin pages to detect which insecure contents are causing browser padlock to not show - Mixed content scanner shown for supported servers only).

(Optional) Running WordPress on a specialized VPS/Dedicated server without cPanel? You can download the generated SSL certificate files easily via "Download SSL Certificates" page and install it on your server by modifying server config file via SSH access as explained in our [DOCS](https://wpencryption.com/docs/). 

== PRO Features Worth Upgrading ==

https://youtu.be/jrkFwFH7r6o

1. Automatic domain verification

2. Automatic SSL installation

3. Automatic SSL renewal (Auto renews SSL certificate 30 days prior to expiry date)

4. Wildcard SSL support - Install Wildcard SSL certificate for your primary domain that covers ALL sub-domains. Automatic DNS based domain verification for Wildcard SSL installation (DNS should be managed by cPanel or Godaddy)

5. Multisite + Mapped domains support - Supports SSL installation for domains mapped with MU domain mapping plugin

6. Automatic Content Delivery Network(CDN) to boost your site performance (Annual Plan Only)

7. Blocks SQL injection, XSS, Shellshock, Remote File Inclusion, Apache Structs Exploits, Local File Inclusion attacks.

8. Blocks common web application vulnerabilities and common WordPress attacks.

9. Blocks invalid user agents, unknown user agents, CSRF, Convicted bot traffic, Spam & abuse, Probing & forced browsing, Brute force attacks.

10. Top notch one to one priority support - Live Chat, Email, Premium Support Forum

[BUY PREMIUM](https://wpencryption.com/?utm_source=wordpress&utm_medium=premiumfeatures&utm_campaign=wpencryption)

== Switch to HTTPS in seconds ==

* Secure browser padlock in minutes.

* Free domain validated (DV) certificates are provided by Let's Encrypt (A non profit Global certificate Authority).

* SSL encryption ensures protection against man-in-middle attacks by securely encrypting the data transfer between client and your server.

== Why does My WordPress site need SSL? ==
1. SEO Benefit: Major search engines like Google ranks SSL enabled sites higher compared to non SSL sites. Thus bringing more organic traffic for your site.

2. Data Encryption: Data transmission between server and visitor are securely encrypted on a SSL site thus avoiding any data hijacks in-between the transmission(Ex: personal information, credit card information).

3. Trust: Google chrome shows non-SSL sites as 'insecure', bringing a feel of insecurity in website visitors.

4. Authentic: HTTPS green padlock represents symbol of trust, authenticity and security.

= Translations =

Many thanks to the generous efforts of our translators:

* Korean (ko_KR) - [the Korean translation team](https://translate.wordpress.org/locale/ko/default/wp-plugins/wp-letsencrypt-ssl/)
* Swedish (sv_SE) - [the Swedish translation team](https://translate.wordpress.org/locale/sv/default/wp-plugins/wp-letsencrypt-ssl/)
* Spanish (es_ES) - [the Spanish translation team](https://translate.wordpress.org/locale/es/default/wp-plugins/wp-letsencrypt-ssl/)
* Spanish (es_VE) - [the Venezuelan translation team](https://translate.wordpress.org/locale/es-ve/default/wp-plugins/wp-letsencrypt-ssl/)
* Spanish (es_MX) - [the Mexican translation team](https://translate.wordpress.org/locale/es-mx/default/wp-plugins/wp-letsencrypt-ssl/)
* French (fr_FR) - [the French translation team](https://translate.wordpress.org/locale/fr/default/wp-plugins/wp-letsencrypt-ssl/)

If you would like to translate to your language, [Feel free to sign up and start translating!](https://translate.wordpress.org/projects/wp-plugins/wp-letsencrypt-ssl/)

= Get Involved =

* Rate Plugin – If you find this plugin useful, please leave a [positive review](https://wordpress.org/support/plugin/wp-letsencrypt-ssl/reviews/). Your reviews are our biggest motivation for further development of plugin.
* Submit a Bug – If you find any issue, please submit a bug via support forum.

== Installation ==	
1. Make a backup of your website and database
2. Download the plugin
3. Upload the plugin to the wp-content/plugins directory,
4. Go to “plugins” in your WordPress admin, then click activate.
5. You will now see WP Encryption option on your left navigation bar. Click on it and follow the step by step guide.

== Frequently Asked Questions ==

= Does installing the plugin will instantly turn my site https? =
Installing SSL certificate is a server side process and not as straight forward as installing a ready widget and using it instantly. You will have to follow some simple steps to install SSL for your WordPress site. Our plugin acts like a tool to generate and install SSL for your WordPress site. On FREE version of plugin - You should manually go through the SSL certificate installation process following the simple video tutorial. Whereas, the SSL certificates are easily generated by our plugin by running a simple SSL generation form.

= I already have SSL certificate installed, how to activate HTTPS? =
If you already have SSL certificate installed, You can use WP Encryption plugin purely for HTTPS redirection & SSL enforcing purpose. All you need to do is enable "Force HTTPS" feature in this plugin.

= Need help with SSL installation for non cPanel site =
Some helpful tutorials for non cPanel based SSL installations can be found on our [DOCS](https://wpencryption.com/docs/).

= How to install SSL for both www & non-www version of my domain? =
First of all, Please make sure you can access your site with and without www. Otherwise you will be not able to complete domain verification for both www & non-www together. If both are accessible, You will see **"Generate SSL for both www & non-www"** option on SSL install form. Otherwise, this option will be hidden.

= Images/Fonts not loading on HTTPS site after SSL certificate installation - Insecure Content / Mixed Content issue? =
Images on your site might be loading over http:// protocol, please enable "Force HTTPS via WordPress" feature of WP Encryption. If you have Elementor page builder installed, please go to Elementor > Tools > Replace URL and replace your http:// site url with https://. Make sure you have SSL certificates installed and browser padlock shows certificate as valid before forcing these https measures. If you have too many mixed content errors because of http:// resources loaded in your css, js or external links, We recommend using "Really Simple SSL" plugin along with WP Encryption.

= How do I renew SSL certificate =
You can click on STEP 1 in progress bar or Renew SSL button (which will be enabled during last 30 days of SSL expiry date) and follow the same initial process of SSL certificate generation to renew the certificates.

= What if I failed to renew the SSL certificate =
Your site will start showing as insecure on all major browsers thus forcing the visitors to not visit insecure site.

= Should I go through domain verification again during SSL renewal process =
Completed domain verifications are valid only for 30 days, so you will need to complete domain verification again when renewing SSL certificate in 90 days.

= Do you support Wildcard SSL? =
Wildcard SSL support is included with PRO version

= Do you support SAN SSL certificate =
SAN SSL certificates are not supported at this moment

= Receiving "Too many requests" error =
Let's Encrypt API has rate limits so please try after few hours if you receive this error.

= Do I need any technical knowledge to use this plugin =
Downloading and installing the generated SSL certificates on cPanel is very easy and can be done without any technical knowledge.

= ERR_SSL_PROTOCOL_ERROR =
VirtualHost for port 443 (in case of apache server), Server{} listening at port 443 (in case of nginx server) might be not defined in your server config file.

= SSL Certificates renewed but new certs not showing in frontend =
This might happen for non cPanel sites, all you need to do is reboot the server instance once.

= How to revert back to HTTP in case of force HTTPS failure? =
Please follow the revert back instructions given in [support thread - Forced SSL via Htaccess](https://wordpress.org/support/topic/locked-out-after-force-ssl-via-htaccess-method/) and [support thread - Forced SSL via WordPress](https://wordpress.org/support/topic/locked-out-unable-to-access-site-after-forcing-https-2/) accordingly.

= I am getting some errors during SSL installation =
Feel free to open a ticket in this plugin support form and we will try our best to resolve your issue.

= Should I configure anything for auto renewal of SSL certificates to work after upgrading to PRO version? =
You don't need to configure anything. Once after you upgrade to PRO version and activate PRO plugin on your site, the auto renewal of SSL certificates will start working in background according to 60 days schedule i.e., 30 days prior to SSL certificate expiry date.

== Disclaimer ==

Security is an important subject regarding SSL/TLS certificates, of course. It is obvious that your private key, stored on your web server, should never be accessible from the web. When the plugin created the keys directory for the first time, it will store a .htaccess file in this directory, denying all visitors. Always make sure yourself your keys aren't accessible from the web! We are in no way responsible if your private keys go public. If this does happen, the easiest solution is to check folder permissions on your server and make sure public access is forbidden for root folders. Next, create a new certificate.

== Screenshots ==
1. Generate and Install SSL certificate while Agreeing to TOS
2. SSL certificate generation successful message
3. Change your WordPress & Site url to HTTPS://
4. Force HTTPS throughout entire site
5. Mixed Content Scanner to identify insecure contents on HTTPS site

== Changelog ==

= 5.3.13 =
* Fixed wpleauto

= 5.3.12 =
* Ajaxified SSL notices
* UI Improved
* Improved SSL alerts for PRO

= 5.3.11 =
* Improved navigation
* Bug fix for DNS verification of SSL

= 5.3.10 =
* PRO - cPanel login check fix
* PRO - Minor bug fix

= 5.3.9 =
* Minor pricing changes
* Added support link

= 5.3.8 =
* PRO - Improved SPmode flow & cpanel backup method

= 5.3.6 =
* Ability to activate the plugin network wide
* PRO - Activate license network wide
* Minor bug fixes

= 5.3.5 =
* PRO - More precise DNS verification
* PRO - Bug fixes
* FREE - Get SSL certs emailed as attachment but enabling the option in "Download SSL certificates" page.

= 5.3.4 =
* SP mode redirect loop fix
* Cleaner plugin deactivation

= 5.3.3 =
* Double check auto renewal of SSL
* Bypass SSL verify peer
* Styling fixes + asset updates
* Privacy enabled youtube videos

= 5.3.1 =
* Added contact form
* Reduced plugin size
* Updated links
* SSL renewal reminder email
* removed BF banner

= 5.3.0 =
* Certificate chain fix - Please update
* FAQs updated

= 5.2.13 =
* SSL Leaf Signature issue fix

= 5.2.10 =
* Bug fixes for Premium SSL setup
* User flow fixes for SP Mode

= 5.2.4 =
* Optimized code
* minor bug fixes
* force generate SSL for www & non-www
* spmode related fixes

= 5.2.2 =
* SDK update
* minor link fixes

= 5.2.0 =
* SP mode for annual PRO users
* Faq & Videos moved to nav
* Bug Fix related to memory exhaust

= 5.1.11 =
* User flow improvements
* Improved error catching
* Improved instructions
* PLEASE UPDATE

= 5.1.8 =
* Identify mixed content issues
* minor fixes

= 5.1.6 =
* Fixed a bug with Manual DNS verification

= 5.1.5 =
* PRO - Fixed major bug related to Wildcard SSL - Please update

= 5.1.0 =
* Fixed - Minor bugs
* Improved - Cluster free SSL generate interface
* Improved - Complete user interface design
* Improved - Sub pages instead of confusing tabs
* Added - retain SSL stage
* Added - Force SSL improvements
* Added - Checkbox to generate SSL for both www & non-www domain
* PRO - Improved DNS automation
* PRO - Improved error handling
* PRO - Added important notifications

= 5.0.9 =
* Fixed - Download SSL tab not showing after success

= 5.0.8 =
* Fixed - DNS verification feature for http verification failures of noscript

= 5.0.7 =
* Added - Attempt http verification before offering manual verification options

= 5.0.6 =
* Improved - Domain verification interface
* Fixed - minor bug
* Fixed - Cron handling

= 5.0.4 =
* Added - SSL support for cPanel users with shell_exec function disabled
* PRO only release

= 5.0.0 =
* NEW - Upon various Non-cPanel user requests, Introducing FIREWALL plan for Non-cPanel sites
* PRO - New instant firewall setup wizard
* Improved - More cleaner admin interface
* Improved - Admin css, overall coding
* Added - Force HTTPS, FAQ, SSL videos as sub pages
* Fixed - minor php error