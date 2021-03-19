<?php

class wpForoPMsUsers
{
    public function __construct(){
        $this->init_hooks();
    }

    private function init_hooks(){
        if( is_user_logged_in() ){
            add_action('wp_ajax_wpforopm_block_user', array($this, 'ajx_block'));
            add_action('wp_ajax_wpforopm_unblock_user', array($this, 'ajx_unblock'));
            add_action('wp_ajax_wpforopm_report', array($this, 'ajx_report'));
            add_action('wp_ajax_wpforopm_search_contact', array($this, 'ajx_search'));

            add_action('wpforo_after_delete_user', array($this, 'after_delete_user'), 10, 2);
        }
    }

    public function after_delete_user($userid, $reassign){
        if( $folders = WPF_PM()->folder->get_folders( array('current_userid' => $userid) ) ){
            foreach ($folders as $folder){
                if( $userids = WPF_PM()->folder->get_userids($folder, false) ){
                    if( in_array($userid, $userids) ){
                        if( count($userids) < 3 ){
                            WPF_PM()->pm->delete(array('folderid' => $folder['folderid']));
                            WPF_PM()->folder->delete($folder['folderid']);
                        }else{
                            WPF_PM()->pm->delete(array('fromuserid' => $userid));
                            WPF_PM()->folder->delete_users($userid, $folder['folderid']);
                        }
                    }
                }
            }
        }

    }

    public function get_userids_by_user_nicenames($nicenames){
        if( empty($nicenames) ) return array();

        $nicenames = explode(',', trim(stripslashes($nicenames), ','));
        $filtered_nicenames = array();
        foreach( $nicenames as $nicename ) $filtered_nicenames[] = esc_sql( esc_html( trim( trim($nicename), '@' ) ) );
        $nicename_in = implode("','", $filtered_nicenames);

        $sql = "SELECT `ID` FROM `".WPF()->db->users."` WHERE `user_nicename` IN('$nicename_in')";
        return WPF()->db->get_col($sql);
    }

    public function is_blocked($check_userid, $userid = NULL){
        if( !$userid ) $userid = WPF()->current_userid;

        $blocked_userids = get_user_meta($userid, 'wpforopm_blocked_userids', true);
        $expld = array_filter( array_map( 'intval', explode(',', $blocked_userids) ) );

        if( in_array($check_userid, $expld) ) return true;

        return false;
    }

    public function is_participant($folder = NULL, $userid = NULL){
        if(!$userid) $userid = WPF()->current_userid;
        if(!$folder) $folder = WPF()->current_object['pm_folder'];
        if( is_numeric($folder) ) $folder = WPF_PM()->folder->get_folder($folder, $userid);
        $userids = array_values(array_filter(explode(',', $folder['userids'] )));
        return in_array($userid, $userids);
    }

    public function is_owner($folder = NULL, $userid = NULL){
        if(!$userid) $userid = WPF()->current_userid;
        if(!$folder) $folder = WPF()->current_object['pm_folder'];
        if( is_numeric($folder) ) $folder = WPF_PM()->folder->get_folder($folder, $userid);
        $userids = array_values(array_filter(explode(',', $folder['userids'] )));
        return $userids[0] == $userid;
    }

    public function can_add_pm($fromuserid = NULL, $folderid = NULL){
        //Make sure user is logged in
        if( !is_user_logged_in() ) return false;
        if( !$fromuserid ){
            $fromuserid = WPF()->current_userid;
            $fromuser = WPF()->current_user;
        }else{
            $fromuser = WPF()->member->get_member($fromuserid);
        }
        if( !$folderid ){
            $folderid = WPF()->current_object['pm_folderid'];
            $folder = WPF()->current_object['pm_folder'];
        }else{
            $folder = WPF_PM()->folder->get_folder($folderid);
        }

        //Allow Admins and Moderators to pass limitations
        if( wpforo_current_user_is('admin') || wpforo_current_user_is('moderator') ){ //pass limits
        }else{
            if( !empty($folder) && WPF_PM()->folder->is_single_conversation($folder) && $folder['user_count'] < 3 ){
                $folder_userids = WPF_PM()->folder->get_userids($folder);
                if( !empty($folder_userids[0]) && $this->is_blocked($fromuserid, $folder_userids[0]) ){
                    WPF()->notice->add('You have been blocked by this user.', 'error');
                    return false;
                }
            }
            if( !WPF_PM()->pm->get_pms_count_for_user($fromuserid) && $fromuser['posts'] < WPF_PM()->options['min_num_posts'] ){
                WPF()->notice->add('You are not allowed to write a message yet.', 'error');
                return false;
            }
            if( WPF_PM()->options['max_num_contacts_per_day'] && WPF_PM()->pm->get_num_contacts_per_day($fromuserid) > WPF_PM()->options['max_num_contacts_per_day'] ){
                WPF()->notice->add('The number of contacts per day is exceeded.', 'error');
                return false;
            }
            if( WPF_PM()->options['max_num_pms_per_day'] && $folderid && WPF_PM()->pm->get_user_personal_pm_count_per_day($fromuserid, $folderid) > WPF_PM()->options['max_num_pms_per_day'] ){
                WPF()->notice->add('The maximum number of messages for this contact is exceeded.', 'error');
                return false;
            }
        }
        return true;
    }

    //-START- ajax call functions
    public function ajx_block($userid = NULL){

        if( !$userid ) $userid = WPF()->current_userid;
        $return = array('stat' => 0);

        //Make sure user is a participant of this conversation | Try to get Folder ID
        if( !is_user_logged_in() || !$this->is_participant() ){
            WPF()->notice->add('Error: you are not allowed to do this action (not participant)', 'error');
            $return['notice'] = WPF()->notice->get_notices();
            echo json_encode($return);
            exit();
        }

        if( empty($_POST['userid']) ){
            WPF()->notice->add('Error: user not selected', 'error');
            $return['notice'] = WPF()->notice->get_notices();
            echo json_encode($return);
            exit();
        }

        $blocked_userids = get_user_meta($userid, 'wpforopm_blocked_userids', true);

        $expld = array_filter( array_map( 'intval', explode(',', $blocked_userids) ) );
        $expld[] = intval($_POST['userid']);
        $blocked_userids = implode(',', array_unique($expld));

        update_user_meta($userid, 'wpforopm_blocked_userids', $blocked_userids);

        $return['stat'] = 1;
        $return['html'] = WPF_PM()->tpl->get_user_tools($_POST['userid']);

        WPF()->notice->add('User successfully blocked', 'success');
        $return['notice'] = WPF()->notice->get_notices();
        echo json_encode($return);
        exit();
    }

    public function ajx_unblock($userid = NULL){

        if( !$userid ) $userid = WPF()->current_userid;
        $return = array('stat' => 0);

        //Make sure user is a participant of this conversation | Try to get Folder ID
        if( !is_user_logged_in() || !$this->is_participant() ){
            WPF()->notice->add('Error: you are not allowed to do this action (not participant)', 'error');
            $return['notice'] = WPF()->notice->get_notices();
            echo json_encode($return);
            exit();
        }

        if( empty($_POST['userid']) ){
            WPF()->notice->add('Error: user not selected', 'error');
            $return['notice'] = WPF()->notice->get_notices();
            echo json_encode($return);
            exit();
        }

        $blocked_userids = get_user_meta($userid, 'wpforopm_blocked_userids', true);

        $expld = array_filter( array_map( 'intval', explode(',', $blocked_userids) ) );
        $expld = array_diff( $expld, array( intval($_POST['userid']) ) );
        $blocked_userids = implode(',', array_unique($expld));

        update_user_meta($userid, 'wpforopm_blocked_userids', $blocked_userids);

        $return['stat'] = 1;
        $return['html'] = WPF_PM()->tpl->get_user_tools($_POST['userid']);

        WPF()->notice->add('User successfully unblocked', 'success');
        $return['notice'] = WPF()->notice->get_notices();
        echo json_encode($return);
        exit();
    }

    public function ajx_report(){
        if(!is_user_logged_in()) return;

        if( !isset($_POST['reportmsg']) || !$_POST['reportmsg'] || !isset($_POST['userid']) || !$_POST['userid'] ){
            WPF()->notice->add('Error: please insert some text to report.', 'error');
            echo json_encode( WPF()->notice->get_notices() );
            exit();
        }

        ############### Sending Email  ##################
        $report_text = substr($_POST['reportmsg'], 0, 1000);
        $userid = intval($_POST['userid']);
        $reporter = '<a href="'.WPF()->current_user['profile_url'].'">'.(WPF()->current_user['display_name'] ? WPF()->current_user['display_name'] : urldecode(WPF()->current_user['user_nicename'])).'</a>';
        $reportmsg = wpforo_kses($report_text, 'email');
        $profile_url = '<a target="_blank" href="'. esc_attr(WPF_PM()->get_conversation_url($userid)).'">' . wpforo_phrase('Profile link', false) . '&raquo;</a>';

        $subject = WPF()->sbscrb->options['report_email_subject'];
        $message = WPF()->sbscrb->options['report_email_message'];

        $from_tags = array("[reporter]", "[message]", "[post_url]");
        $to_words   = array(sanitize_text_field($reporter), $reportmsg, $profile_url);

        $subject = str_replace($from_tags, $to_words, $subject);
        $message = str_replace($from_tags, $to_words, $message);

        $admin_email = get_option( 'admin_email' );
        $admin_emails = WPF()->sbscrb->options['admin_emails'];
        $admin_emails = trim($admin_emails);
        $admin_emails = explode(',', $admin_emails);
        $admin_emails = array_map('sanitize_email', $admin_emails);
        $admin_email = (isset($admin_emails[0]) && $admin_emails[0]) ? $admin_emails[0] : $admin_email;
        $headers = wpforo_admin_mail_headers();

        add_filter( 'wp_mail_content_type', 'wpforo_set_html_content_type' );
        if( wp_mail( $admin_email, $subject, $message, $headers ) ){
            remove_filter( 'wp_mail_content_type', 'wpforo_set_html_content_type' );
        }else{
            remove_filter( 'wp_mail_content_type', 'wpforo_set_html_content_type' );
            WPF()->notice->add('Can\'t send report email', 'error');
            echo json_encode( WPF()->notice->get_notices() );
            exit();
        }

        ############### Sending Email end  ##############
        WPF()->notice->add('Message has been sent', 'success');
        echo json_encode( WPF()->notice->get_notices() );
        exit();
    }

    public function ajx_search(){
        if(!is_user_logged_in()) exit();
        if( empty($_POST['wpfpm_needle']) ) exit();

        $userids = $this->search( array('needle' => $_POST['wpfpm_needle'], 'groupid' => WPF_PM()->allowed_groupids), array('user_nicename', 'title', 'display_name'), 7 );
        $diff_userids = ( !empty( WPF()->current_object['pm_folder_userids'] ) ? WPF()->current_object['pm_folder_userids'] : array(WPF()->current_userid) );
        $userids = array_diff($userids, $diff_userids);
        WPF_PM()->tpl->show_users_datalist($userids);
        exit();
    }
    //-END- ajax call functions

    //core member search duplicate
    function search($args, $fields = array(), $limit = NULL){
        if(!$args) return array();

        $wheres_and = array();
        if( is_array($args) && !empty($args['needle']) ){
            $needle = $args['needle'];
            unset($args['needle']);
            if( !empty($args) ){
                foreach ( $args as $key => $value ){
                    if(!$value) continue;
                    if( is_array($value) ){
                        $value = array_values($value);
                        $callback = ( is_numeric($value[0]) ? 'wpforo_bigintval' : 'trim' );
                        $value = array_map($callback, $value);
                        $wheres_and[] = "`$key` IN(".implode(',', $value).")";
                    }else{
                        $type = ( is_numeric($value) ? '%d' : '%s' );
                        $wheres_and[] = WPF()->db->prepare("`$key` = $type", $value);
                    }
                }
            }
        }else{
            $needle = $args;
        }

        $needle = sanitize_text_field($needle);
        if(empty($fields)){
            $fields = array(
                'title',
                'user_nicename',
                'user_email',
                'signature'
            );
        }

        $sql = "SELECT `ID` FROM `".WPF()->db->users."` u 
            INNER JOIN `".WPF()->tables->profiles."` p ON p.`userid` = u.`ID`";
        $wheres = array();

        foreach($fields as $field){
            $field = sanitize_text_field($field);
            $wheres[] = "`".esc_sql($field)."` LIKE '%" . esc_sql($needle) ."%'";
        }

        if(!empty($wheres)){
            $sql .= " WHERE (" . implode(' OR ', $wheres) . ")";
            if( !empty($wheres_and) ) $sql .= " AND " . implode(' AND ', $wheres_and);

            if($limit) $sql .= " LIMIT " . intval($limit);
            return WPF()->db->get_col($sql);
        }else{
            return array();
        }

    }
}