<?php 
/*
* Plugin Name: wpForo Private Messages
* Plugin URI: https://wpforo.com
* Description: Provides a safe way to communicate directly with other members. Messages are private and can only be viewed by conversation participants
* Author: gVectors Team (A. Chakhoyan and R. Hovhannisyan)
* Author URI: https://gvectors.com/
* Version: 1.3.0
* Text Domain: wpforo_pm
* Domain Path: /languages
*/

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;
if( !defined( 'WPFOROPM_VERSION' ) ) define('WPFOROPM_VERSION', '1.3.0');

define('WPFOROPM_DIR', rtrim( str_replace( '//', '/', dirname(__FILE__)), '/'));
define('WPFOROPM_URL', rtrim( plugins_url( '', __FILE__ ), '/'));
define('WPFOROPM_FOLDER', rtrim( plugin_basename(dirname(__FILE__)), '/'));
define('WPFOROPM_BASENAME', plugin_basename( __FILE__ ));

function wpforo_pm_load_plugin_textdomain() { load_plugin_textdomain( 'wpforo_pm', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' ); }
add_action( 'plugins_loaded', 'wpforo_pm_load_plugin_textdomain' );

if( !class_exists('wpForoPMsMain') ){
    class wpForoPMsMain{
        private static $_instance = NULL;

        public $default;
        public $options;
        public $user;
        public $folder;
        public $pm;
        public $tools;
        public $tpl;
        public $allowed_groupids = array();

        public static function instance(){
            if ( is_null(self::$_instance) ) self::$_instance = new self();
            return self::$_instance;
        }

        private function includes(){
            include(WPFOROPM_DIR . '/includes/class-users.php');
            include(WPFOROPM_DIR . '/includes/class-folders.php');
            include(WPFOROPM_DIR . '/includes/class-pms.php');
            include(WPFOROPM_DIR . '/includes/class-tools.php');
            include(WPFOROPM_DIR . '/includes/class-template.php');
            include(WPFOROPM_DIR . "/includes/gvt-api-manager.php");
        }

        private function __construct(){
            $this->includes();
            $this->init_defaults();
            $this->init_options();
            $this->init_allowed_groupids();
            $this->init_hooks();

            $this->user =   new wpForoPMsUsers();
            $this->folder = new wpForoPMsFolders();
            $this->pm =     new wpForoPMs();
            $this->tools =  new wpForoPMsTools();
            $this->tpl =    new wpForoPMsTemplate();

            new GVT_API_Manager(__FILE__, 'wpforo-settings&tab=plugins&subtab=private-messages','wpforo_settings_page_top');
        }

        private function init_defaults(){
            $this->default = new stdClass;

	        $this->default->options = array(
		        'folders_per_load'                  => 15,
		        'pms_per_load'                      => 15,
		        'min_num_posts'                     => 5,
		        'max_num_contacts_per_day'          => 20,
		        'max_num_pms_per_day'               => 100,
		        'max_num_users_per_folder'          => 0,
		        'allow_external_url'                => 1,
		        'allow_external_img_url'            => 1,
		        'allow_embedded_content'            => 1,
		        'email_notification'                => 1,
		        'new_pm_notification_email_subject' => 'New Private Message',
		        'new_pm_notification_email_message' => 'Hello!<br>
             You have received new Private Message.
             <br><br>
             <strong>[conversation]</strong>
             <blockquote>[msg]</blockquote>'
	        );
        }

        private function init_options(){
            $this->options = get_wpf_option('wpforopm_options', $this->default->options);
        }

        private function init_allowed_groupids(){
            if( $groups = WPF()->usergroup->get_usergroups() ){
                foreach ($groups as $group){
//                    if( !$group['visible'] ) continue;
                    $cans = unserialize($group['cans']);
                    if( $cans['vwpm'] ) $this->allowed_groupids[] = $group['groupid'];
                }
            }
        }

        private function init_hooks(){
            add_filter('wpforo_plugins_tabs_array_filter', array($this, 'add_options_tab'));
            add_filter('wpforo_plugins_option_files_array_filter', array($this, 'add_options_file_path'));
            add_filter('wpforo_menu_array_filter', array($this, 'add_main_nav_menu'), 10, 1);
            add_filter('wpforo_member_menu_filter', array($this, 'add_member_menu'), 10, 2);
            add_action('wpforo_profile_data_item', array($this, 'add_message_button'));
            add_action('wpforo_member_info_buttons', array($this, 'add_member_info_button'));
	        add_action('wpforopm_form_bottom', array(WPF()->tpl, 'add_default_attach_input'));
	        add_filter('wpforo_profile_header_obj', array($this, 'change_profile_header_to_current'));
	        add_filter('wpforo_member_templates_filter', array($this, 'add_template'), 10, 1);
	        add_filter('is_wpforo_attach_page_templates', array($this, 'add_template_to_is_wpforo_attach_page'));
	        add_filter('wpforo_can_attach', array($this, 'can_attach'));
	        add_filter('wpforo_permissions_forum_can', array($this, 'forum_can'), 10, 2);
	        add_filter('wpforo_member_error_filter', array($this, 'add_error'), 10, 1);
	        add_action('wpforo_actions', array($this, 'do_actions'), 10, 0);
	        add_action('wpforopm_after_pm_add', array($this, 'send_notification_mail'));
	        add_action('wpforopm_after_pm_add', array($this, 'after_pm_add'));
	        add_action('wpforopm_after_pm_delete', array($this, 'after_pm_delete'));
	        add_action('wpforopm_after_update_read_status', array($this, 'after_update_read_status'), 10, 2);
	        add_filter('wpforo_register_actions', array($this, 'register_notification_actions'));
	        add_filter('wpforo_before_init_current_object', array($this, 'add_current_object'), 10, 2);
	        add_action('wp_enqueue_scripts', array($this, 'css_js_enqueue'));
	        add_filter('wpforo_dynamic_css_filter', array($this, 'add_dynamic_css'), 10, 2);
//	        add_action('wpforo_before_search_toggle', function(){WPF_PM()->tpl->show_pm_note();});
	        add_filter('wpforopm_add_pm_data_filter', array($this, 'add_default_attachment'));
        }

        public function add_options_tab($tabs){
            $tabs['private-messages'] = 'Private Messages';
            return $tabs;
        }

        public function add_options_file_path($option_files){
            $option_files['private-messages'] = WPFOROPM_DIR . "/includes/options.php";
            return $option_files;
        }

        public function add_main_nav_menu($menu){
            if(is_user_logged_in() && WPF()->perm->usergroup_can('vwpm')){
                $menu['wpforo-profile-messages'] = array(
                    'href' => $this->get_conversation_url(WPF()->current_userid),
                    'label' => wpforo_phrase('messages', false),
                    'attr' => ( WPF()->current_object['template'] == 'messages' ? ' class="wpforo-active"' : '' ),
                    'submenues' => array()
                );
            }
            return $menu;
        }

        public function add_member_menu($menu, $userid){
            if(isset(WPF()->current_userid) && isset(WPF()->current_object['userid'])){
                if( WPF()->perm->usergroup_can('vwpm') && WPF()->current_userid == $userid ) {
                    $menu['messages'] = 'fas fa-envelope';
                }
            }
            return $menu;
        }

        public function add_message_button( $current_object ){
            if( isset(WPF()->current_userid) && isset($current_object['userid']) && in_array($current_object['user']['groupid'], $this->allowed_groupids) ){
                if( WPF()->current_userid == $current_object['userid'] || $current_object['template'] == 'messages' ) return;
                if( WPF()->perm->usergroup_can('vwpm') ) {
                    $url = $this->get_conversation_url($current_object['userid']);
                    echo '<div class="wpfpm-message-div"><a href="' . esc_url($url) . '" class="wpfpm-message-button wpf-button">'.wpforo_phrase('Send a Message', false).'</a></div>';
                }
            }
        }

        public function add_member_info_button( $member ){
            if( isset(WPF()->current_userid) && isset($member['userid']) && in_array($member['groupid'], $this->allowed_groupids) ){
                $phrase = ( WPF()->current_userid == $member['userid'] ) ? wpforo_phrase('Messages', false) : wpforo_phrase('Send a Message', false);
                if( WPF()->perm->usergroup_can('vwpm') ) {
                    echo ' <a class="wpf-member-profile-button" title="' . esc_attr($phrase) . '" href="'. esc_url($this->get_conversation_url($member['userid'])) . '">
                        <i class="far fa-envelope"></i>
                     </a>';
                }
            }
        }

        public function change_profile_header_to_current($user){
            if( WPF()->current_object['template'] == 'messages' ) return WPF()->current_user;
            return $user;
        }

        public function add_template($templates){
            $templates['messages'] = WPFOROPM_DIR . '/includes/template.php';
            return $templates;
        }

        public function add_template_to_is_wpforo_attach_page($templates){
        	$templates[] = 'messages';
        	return $templates;
        }

        public function can_attach($can_attach){
        	if( WPF()->current_object['template'] == 'messages' ) $can_attach = true;
            return $can_attach;
        }

        public function forum_can($forum_can, $do){
        	if( $do == 'va' && WPF()->current_object['template'] == 'messages' ) $forum_can = 1;
            return $forum_can;
        }

        public function add_error($error){
            if( WPF()->current_object['template'] == 'messages' && empty(WPF()->current_object['pm_folderid']) ){
                $error = wpforo_phrase('You have no one to talk.', false) . '<br/><a href="'. wpforo_home_url(WPF()->tpl->slugs['members']) . '">' . wpforo_phrase('Please add a new contact', false) . '</a>';
            }

            return $error;
        }

        public function get_new_conversation_url(){
            return  wpforo_home_url('messages');
        }

        public function get_conversation_url($arg = array()){
            if( empty($arg) ) $userid = WPF()->current_userid;
            if( !is_array($arg) ) $userid = $arg;
            if( isset($userid) ) return WPF()->member->get_profile_url($userid, 'messages');

            if( !empty($arg['folderid']) )
                return trim(WPF()->member->get_profile_url(WPF()->current_userid, 'messages'), '/') . "/" . $arg['folderid'] ;

            return  WPF()->member->get_profile_url(WPF()->current_userid);
        }

        public function do_actions(){
//            if( WPF()->current_object['template'] != 'messages' && $pm_note = $this->tpl->get_pm_note(true) ) WPF()->notice->add($pm_note);
            //Make sure we're in Dashboard and Post is submitted
	        if ( wpforo_is_admin() && isset( $_POST['wpforopm_options'] ) && is_array($_POST['wpforopm_options']) ) {
		        //Validate wp_nonce field and referrer
		        check_admin_referer( 'wpforo-pm-settings' );
		        //Check Admin Permission
		        if ( ! current_user_can( 'administrator' ) ) return;
		        //Do actions
		        if ( wpfval($_POST, 'wpforopm_options', 'folders_per_load') < 15 ) {
			        $_POST['wpforopm_options']['folders_per_load'] = 15;
		        }
		        if ( wpfval($_POST, 'wpforopm_options', 'pms_per_load') < 15 ) {
			        $_POST['wpforopm_options']['pms_per_load'] = 15;
		        }
		        update_option( "wpforopm_options", $_POST['wpforopm_options'] );
		        WPF()->notice->add( 'Options Saved', 'success' );
		        wp_redirect( wpforo_get_request_uri() );
		        exit();
	        }

            if(WPF()->current_object['template'] === 'messages'){
                if(isset($_POST['wpforopm_submit'])){

                    //Referrer verification
                    //Validate wp_nonce field
                    //Remote submit detection
                    wpforo_verify_form();

                    //Check Permissions
                    if( !$this->user->can_add_pm() ){
                        WPF()->notice->add('Permission Denied', 'error');
                        wp_redirect( wpforo_get_request_uri() );
                        exit();
                    }

                    if( empty($_POST['wpforopm']['title']) && !empty(WPF()->current_object['pm_folderid']) ){
                        $args = array();
                        $args['message'] = $_POST['wpforopm']['message'];
                        $args['fromuserid'] = WPF()->current_userid;
                        $args['folderid'] = WPF()->current_object['pm_folderid'];

                        $this->pm->add($args);
                        wp_redirect( wpforo_get_request_uri() );
                        exit();
                    }else{
                        $userids = $this->user->get_userids_by_user_nicenames($_POST['wpforopm']['users']);
                        $args = array(
                            'name' => $_POST['wpforopm']['title'],
                            'userids' => $userids
                        );

                        if( $pm_folderid = $this->folder->add($args) ){
                            $args = array();
                            $args['message'] = $_POST['wpforopm']['message'];
                            $args['fromuserid'] = WPF()->current_userid;
                            $args['folderid'] = $pm_folderid;

                            WPF()->notice->clear();
                            $this->pm->add($args);
                            wp_redirect( $this->get_conversation_url( array('folderid' => $pm_folderid) ) );
                            exit();
                        }
                    }
                }

                if(!empty(WPF()->current_object['pm_datas']) && !wpforo_is_ajax()) $this->pm->update_read_status();
            }
        }

	    public function add_default_attachment( $args ) {
		    if ( WPF()->current_userid && ! empty( $_FILES['attachfile'] ) && ! empty( $_FILES['attachfile']['name'] ) ) {
			    if ( $default_attach = wpforo_move_uploded_default_attach( 'attachfile' ) ) {
				    $args['message'] .= $default_attach;
			    }
		    }

		    return $args;
	    }

        public function send_notification_mail($args){
            if( !$this->options['email_notification'] ) return false;

            if( $args['folderid'] == WPF()->current_object['pm_folderid'] ){
                $folder = WPF()->current_object['pm_folder'];
                $userids = $this->folder->get_userids();
                $exclude_sendmail = $this->folder->get_exclude_sendmail();
            }else{
                $folder = $this->folder->get_folder($args['folderid']);
                $userids = $this->folder->get_userids($folder);
                $exclude_sendmail = $this->folder->get_exclude_sendmail($folder);
            }

            $userids = array_diff($userids, $exclude_sendmail);

            if( !empty($userids) ){
                $folder_title = $this->folder->make_title($folder);
                $subject = $this->options['new_pm_notification_email_subject'];
                $message = $this->options['new_pm_notification_email_message'];
                $from_tags = array("[conversation]", "[msg]");
                $to_words   = array('<strong>' . sanitize_text_field($folder_title) . '</strong>', '<br><br> '. $args['message'] .' <a href="' . esc_url($this->get_conversation_url($folder)) . '"> ' . wpforo_phrase('Conversation Link', false) . ' >></a>');
                $subject = stripslashes(str_replace($from_tags, $to_words, $subject));
                $message = stripslashes(str_replace($from_tags, $to_words, $message));
                $message = wpforo_kses($message, 'email');
                $headers = wpforo_mail_headers();

                add_filter( 'wp_mail_content_type', 'wpforo_set_html_content_type' );

                foreach ($userids as $userid){
                    if( WPF()->member->is_online($userid) ) continue;
                    $user = get_userdata($userid);
                    wp_mail( $user->user_email, sanitize_text_field($subject), $message, $headers );
                }

                remove_filter( 'wp_mail_content_type', 'wpforo_set_html_content_type' );
                return TRUE;
            }

            //WPF()->notice->add('Can\'t send notification email', 'error');
            return false;
        }

	    /**
	     * action after pm add
	     *
	     * @param array $pm
	     */
	    public function after_pm_add($pm) {
		    $userids = $this->folder->get_userids($this->folder->get_folder($pm['folderid']));
		    foreach ( $userids as $userid ){
			    $args = array(
				    'itemid'    => $pm['pmid'],
				    'userid'    => $userid,
				    'content'   => $pm['message'],
				    'permalink' => $this->get_conversation_url( $pm ),
			    );
			    WPF()->activity->add_notification('new_pm', $args);
		    }
	    }

	    public function after_pm_delete($pms) {
		    if( $pms ){
		    	foreach ($pms as $pm){
				    $args = array(
					    'type' => 'new_pm',
					    'itemid' => $pm['pmid'],
					    'itemtype' => 'alert'
				    );
				    WPF()->activity->delete_notification( $args );
			    }
		    }
	    }

	    public function after_update_read_status( $folderid, $current_userid ) {
		    if( $pms = $this->pm->get_pms(array('folderid' => $folderid)) ){
			    $args = array(
				    'type'     => 'new_pm',
				    'itemtype' => 'alert',
				    'userid'   => $current_userid
			    );
			    foreach ( $pms as $pm ){
				    $args['itemid'] = $pm['pmid'];
				    WPF()->activity->delete_notification( $args );
			    }
		    }
	    }

	    public function register_notification_actions($actions){
		    $actions['new_pm'] = array(
			    'title'       => wpforo_phrase( 'New Message', false ),
			    'icon'        => '<i class="far fa-envelope"></i>',
			    'description' => wpforo_phrase( 'New message from %1$s, %2$s', false ),
			    'before'      => '<li class="wpf-new_pm">',
			    'after'       => '</li>',
		    );
		    return $actions;
	    }

        public function add_current_object($current_object, $wpf_url_parse){
            if(in_array('messages', $wpf_url_parse)){
                if( !is_user_logged_in() ){
                    wp_redirect( wpforo_login_url() );
                    exit();
                }

                $current_object['userid'] = WPF()->current_userid;

                $wpf_url_parse = array_values(array_reverse(array_diff($wpf_url_parse, array('messages'))));

                $current_object['template'] = 'messages';
                $current_object['pm_folderid'] = 0;
                $current_object['pm_folder'] = array();
                $current_object['pm_folders'] = array();
                $current_object['pm_datas'] = array();

                if( count($wpf_url_parse) > 1 ){
                    if( $pm_folder = $this->folder->get_folder( wpforo_bigintval($wpf_url_parse[1]), WPF()->current_userid ) ){
                        $current_object['pm_folder'] = $pm_folder;
                        $current_object['pm_folderid'] = $pm_folder['folderid'];
                    }else{
                        wp_redirect( $this->get_new_conversation_url() );
                        exit();
                    }
                }elseif( !empty($wpf_url_parse[0]) ){
                    $user =  WPF()->member->get_member($wpf_url_parse[0]);

                    if( !isset($user['userid']) || $user['userid'] == WPF()->current_userid ){
                        $last_pm_folder = $this->folder->get_folders( array('row_count' => 1) );
                        if( empty($last_pm_folder[0]) ){
                            wp_redirect( $this->get_new_conversation_url() );
                            exit();
                        }
                        wp_redirect( $this->get_conversation_url($last_pm_folder[0]) );
                        exit();
                    }else{
                        $pm_folder = $this->folder->get_folders( array( 'userids' => array(WPF()->current_userid, $user['userid']), 'hide' => true, 'row_count' => 1 ) );
                        if( !empty($pm_folder[0]) ){
                            wp_redirect( $this->get_conversation_url($pm_folder[0]) );
                            exit();
                        }else{
                            $pm_folderid = $this->folder->add( array('userids' => $user['userid']) );
                            wp_redirect( $this->get_conversation_url( array('folderid' => $pm_folderid) ) );
                            exit();
                        }
                    }
                }

                $current_object['pm_folders'] = $this->folder->get_folders( array('current_folderid' => $current_object['pm_folderid'], 'row_count' => WPF_PM()->options['folders_per_load']) );
                if( $current_object['pm_folderid'] ){
                    $current_object['pm_datas'] = $this->pm->get_pms(
                        array(
                            'folderid' => $current_object['pm_folderid'],
                            'row_count' => $this->options['pms_per_load']
                        )
                    );

                    $current_object['pm_folder_userids'] = $this->folder->get_userids($current_object['pm_folder']);

                    $this->folder->show_for_user($current_object['pm_folderid']);
                }
            }

            return $current_object;
        }

        public function css_js_enqueue(){
            if( !is_wpforo_page() ) return;
            $templates = array('messages', 'profile', 'account', 'activity', 'subscriptions', 'members');
            if( in_array( WPF()->current_object['template'], $templates) ){
                wp_register_style('wpfpm-style', WPFOROPM_URL . '/assets/css/style.css', false, WPFOROPM_VERSION );
                wp_enqueue_style('wpfpm-style');
                if (is_rtl()) {
                    wp_register_style('wpfpm-style-rtl', WPFOROPM_URL . '/assets/css/style-rtl.css', false, WPFOROPM_VERSION );
                    wp_enqueue_style('wpfpm-style-rtl');
                }
            }
            if( WPF()->current_object['template'] == 'messages' ){
                if( is_user_logged_in() ){
                    wp_register_script('wpfpm', WPFOROPM_URL . '/assets/js/pm.js', array('jquery','wpforo-frontend-js'), WPFOROPM_VERSION );
                    wp_enqueue_script('wpfpm');
                }
            }
        }

        public function add_dynamic_css($css, $COLORS){
            extract($COLORS);
            $css .= '
                #wpforo-wrap #wpf-pm-load-more{color: '. $WPFCOLOR_14 .';}
                #wpforo-wrap .wpforo-messages-content .whr{ border-top: 1px solid '. $WPFCOLOR_8 .' !important; color: '. $WPFCOLOR_7 .'; }
                #wpforo-wrap .wpforo-messages-content .whr abbr{ background-color: '. $WPFCOLOR_1 .'; }
                #wpforo-wrap .wpfpm-msg-inner{border-color: '. $WPFCOLOR_8 .'; background-color: '. $WPFCOLOR_8 .'; color:'. $WPFCOLOR_3 .'}
                #wpforo-wrap .wpfpm-msg-before:before{border-top-color: '. $WPFCOLOR_8 .';}
                #wpforo-wrap .wpfpm-me .wpfpm-msg-inner{ background-color: '. $WPFCOLOR_9 .'; border-color: '. $WPFCOLOR_9 .'; color: '. $WPFCOLOR_3 .'; }
                #wpforo-wrap .wpfpm-me .wpfpm-msg-before:before{border-top-color: '. $WPFCOLOR_9 .';}
                #wpforo-wrap .wpforo-messages-content .wpfpm-left{border-color: '. $WPFCOLOR_8 .' }
                #wpforo-wrap .wpforo-messages-content .wpfpm-main{background-color: '. $WPFCOLOR_1 .';}
                #wpforo-wrap .wpforo-messages-content .wpfpm-main .wpfpm-form-wrapper{ border-top: '. $WPFCOLOR_12 .' 1px dotted;}
                #wpforo-wrap .wpfpm-uli{ background-color: '. $WPFCOLOR_9 .'; border-top-color: '. $WPFCOLOR_8 .'; border-bottom-color: '. $WPFCOLOR_1 .';}
                #wpforo-wrap .wpfpm-uli-active, #wpforo-wrap .wpfpm-uli:hover{background-color: '. $WPFCOLOR_12 .';}
                #wpforo-wrap .wpfpm-uli-active a, .wpfpm-uli:hover a, .wpfpm-uli a:hover{color: '. $WPFCOLOR_1 .' !important;}
                #wpforo-wrap .wpfpm-content{ background-color: '. $WPFCOLOR_1 .' !important; }
                #wpforo-wrap .wpforo-messages-content *::-webkit-scrollbar-track { -webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.3); }
                #wpforo-wrap .wpforo-messages-content *::-webkit-scrollbar-thumb { background: '. $WPFCOLOR_3 .'; -webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.5); }
                #wpforo-wrap .wpforo-messages-content *::-webkit-scrollbar-thumb:window-inactive { background: '. $WPFCOLOR_3 .'; }
                #wpforo-wrap .wpforo-profile-wrap .wpfpm-toolbar{ background-color: '. $WPFCOLOR_9 .'; color: '. $WPFCOLOR_3 .'; }
                #wpforo-wrap .wpforo-profile-wrap .wpfpm-toolbar #wpfpm-tools li a{ color: '. $WPFCOLOR_3 .'; }
                #wpforo-wrap .wpforo-profile-wrap #wpfpm-ul-users-tooltip-wrap:hover, 
                #wpforo-wrap .wpforo-profile-wrap #wpfpm_left_toggle_button:hover,
                #wpforo-wrap .wpforo-profile-wrap .wpfpm-toolbar #wpfpm-tools li:hover a, 
                #wpforo-wrap .wpforo-profile-wrap .wpfpm-toolbar #wpfpm-tools li:hover,
                #wpforo-wrap .wpfpm-add-user:hover, #wpforo-wrap .wpfpm-add-user .wpfpm-add-user-go{ color: '. $WPFCOLOR_12 .'; }
                #wpforo-wrap .wpfpm-uli-cog-tooltip li:hover{ background-color: '. $WPFCOLOR_14 .'; color: white; }
                #wpforo-wrap #wpfpm-uli-users-wrap .wpfpm-ul-users-tooltip li:hover{ background-color: '. $WPFCOLOR_14 .'; color: white; }
                #wpforo-wrap #wpfpm-users-datalist > li.wpfpm-datalist-user:hover, #wpforo-wrap #wpfpm-users-datalist > li.wpfpm-datalist-user:focus{ background-color: '. $WPFCOLOR_14 .'; color: white; }
                #wpforo-wrap .wpfpm-uli-avatar .wpfpm-add{ border-color: '. $WPFCOLOR_12 .'; background: '. $WPFCOLOR_1 .'; color: '. $WPFCOLOR_12 .'; }
                #wpforo-wrap .wpfpm-uli-has-message{border-bottom: 2px solid '. $WPFCOLOR_20 .' !important;}
                #wpforo-wrap .wpfpm-uli-unread-count{background-color: '. $WPFCOLOR_20 .'; color: '. $WPFCOLOR_1 .';}
                #wpforo-wrap .wpfpm_avatar_ban{color:'. $WPFCOLOR_20 .';}
                #wpforo-wrap .wpfpm-form-blocked{border-bottom: 1px solid '. $WPFCOLOR_20 .';color: '. $WPFCOLOR_20 .';}
                #wpforo-wrap .wpforo-messages-content{border:'. $WPFCOLOR_8 .' 1px solid;}
                #wpforo-wrap.wpf-dark .wpforo-profile-wrap .wpfpm-toolbar, #wpforo-wrap.wpf-dark #wpfpm_left_toggle_button{color:'. $WPFCOLOR_10 .';}
                #wpforo-wrap.wpf-dark .wpfpm-msg-inner, #wpforo-wrap.wpf-dark .wpfpm-msg-inner p{color:'. $WPFCOLOR_10 .';}
                #wpforo-wrap.wpf-dark #wpf-post-create{border:none;}
                #wpforo-wrap.wpf-dark .wpfpm-uli-avatar .wpfpm-add{background:'. $WPFCOLOR_10 .';}
                #wpforo-wrap.wpf-dark .wpfpm-uli-details{color:'. $WPFCOLOR_10 .';}
                #wpforo-wrap.wpf-dark .wpforo-messages-content *::-webkit-scrollbar-thumb,
                #wpforo-wrap.wpf-dark .wpforo-messages-content *::-webkit-scrollbar-thumb:window-inactive { background: '. $WPFCOLOR_14 .'; }
            ';
            return $css;
        }
    }

    if ( !function_exists('WPF_PM') ){
        function WPF_PM(){
            return wpForoPMsMain::instance();
        }
    }

    include(WPFOROPM_DIR . "/includes/functions.php");
}