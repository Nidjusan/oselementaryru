<?php

class wpForoPMsFolders
{
    public function __construct(){
        $this->init_hooks();
    }

    private function init_hooks(){
        if( is_user_logged_in() ){
            add_action('wp_ajax_wpforopm_load_more_folders', array($this, 'ajx_show_list'));
            add_action('wp_ajax_wpforopm_add_users', array($this, 'ajx_add_users'));
            add_action('wp_ajax_wpforopm_delete_users', array($this, 'ajx_delete_users'));
            add_action('wp_ajax_wpforopm_delete_folder', array($this, 'ajx_delete'));
            add_action('wp_ajax_wpforopm_hide_folder', array($this, 'ajx_hide'));
            add_action('wp_ajax_wpforopm_folder_email_notification_on', array($this, 'ajx_email_notification_on'));
            add_action('wp_ajax_wpforopm_folder_email_notification_off', array($this, 'ajx_email_notification_off'));
        }
    }

    public function add($args){
        if( !is_user_logged_in() ) return false;
        extract($args);

        if( empty($userids) ) return false;
        if( empty($current_userid) ) $current_userid = WPF()->current_userid;
        $userids = (array) $userids;
        array_unshift($userids, $current_userid);

        if(WPF_PM()->options['max_num_users_per_folder'] > 1)
            $userids = array_slice($userids, 0, WPF_PM()->options['max_num_users_per_folder']);

        $userids = array_map('wpforo_bigintval', $userids);
        $hide = array_diff($userids, (array) $current_userid);
        $hide = implode(',', $hide);
        $user_count = count($userids);
        $userids = implode(',', $userids);

        $data = array(
            'userids' => $userids,
            'hide' => $hide
        );
        $format = array('%s','%s');

        if( !empty($name) ){
            $data['name'] = trim( stripslashes( esc_html($name) ) );
            $format[] = '%s';
        }

        if( !empty($img) ){
            $data['img'] = trim( stripslashes($img) );
            $format[] = '%s';
        }

        if( !empty($user_count) ){
            $data['user_count'] = trim( stripslashes($user_count) );
            $format[] = '%d';
        }

        if(WPF()->db->insert(
            WPF()->db->prefix . "wpforo_pmfolders",
            $data,
            $format
        )){
            return WPF()->db->insert_id;
        }

        return false;
    }

    public function edit($data, $where){
        if( empty($data) || empty($where) || !is_user_logged_in() ) return false;

        $field_formats = array(
            'folderid' => '%d',
            'name' => '%s',
            'img' => '%s',
            'userids' => '%s',
            'hide' => '%s',
            'user_count' => '%d',
            'pintotop' => '%s',
            'exclude_sendmail' => '%s'
        );

        if( false !== WPF()->db->update(
                WPF()->db->prefix . "wpforo_pmfolders",
                $data,
                $where,
                wpforo_array_ordered_intersect_key($field_formats, $data),
                wpforo_array_ordered_intersect_key($field_formats, $where)
            )){
            return true;
        }

        return false;
    }

    public function delete($folderid){
        if( empty($folderid) ){
            WPF()->notice->add('Data is empty', 'error');
            return false;
        }

        if(false !== WPF()->db->delete(
                WPF()->db->prefix . 'wpforo_pmfolders',
                array('folderid' => wpforo_bigintval($folderid)),
                array('%d')
            )){
            WPF()->notice->add('Conversation successfully deleted', 'success');
            return $folderid;
        }

        WPF()->notice->add('Wrong Data', 'error');
        return false;
    }

    private function add_users($userids, $folderid = NULL){
        if( empty($userids) || !is_user_logged_in() ) return false;
        if( !$folderid ){
            $folderid = WPF()->current_object['pm_folderid'];
            $folder = WPF()->current_object['pm_folder'];
        }else{
            $folder = $this->get_folder($folderid);
        }

        //Make sure user is logged in and folder exists
        if( empty($folder) ) return false;

        //Make sure user is a participant of this conversation
        if( !WPF_PM()->user->is_participant($folder) ){
            WPF()->notice->add('Error: you are not allowed to do this action (not participant)', 'error');
            return FALSE;
        }

        $folder_userids = $this->get_userids($folder, false);
        $folder_userids = array_merge($folder_userids, (array) $userids);
        $folder_userids = array_unique($folder_userids);

        if(WPF_PM()->options['max_num_users_per_folder'] > 1)
            $folder_userids = array_slice($folder_userids, 0, WPF_PM()->options['max_num_users_per_folder']);

        $user_count = count($folder_userids);
        $userids = implode(',', $folder_userids);
        if( false !== $this->edit( array('userids' => $userids, 'user_count' => $user_count), array('folderid' => $folderid) ) )
            return $folder_userids;
        return false;
    }

    public function delete_users($userids, $folderid = NULL){
        if( empty($userids) || !is_user_logged_in() ) return false;

        if( !$folderid ){
            $folderid = WPF()->current_object['pm_folderid'];
            $folder = WPF()->current_object['pm_folder'];
        }else{
            $folder = $this->get_folder($folderid);
        }

        //Make sure user is logged in and folder exists
        if( empty($folder) ) return false;

        //Make sure user is a participant of this conversation
        if( !( WPF_PM()->user->is_participant($folder) || current_user_can('remove_users') ) ){
            WPF()->notice->add('Error: you are not allowed to do this action (not participant)', 'error');
            return FALSE;
        }

        $folder_userids = $this->get_userids($folder, false);
        $folder_userids = array_unique( array_diff($folder_userids, (array) $userids) );
        $user_count = count($folder_userids);
        $userids = implode(',', $folder_userids);
        if( false !== $this->edit( array('userids' => $userids, 'user_count' => $user_count), array('folderid' => $folderid) ) )
            return $folder_userids;
        return false;
    }

    public function show_for_user($folderid, $userids = NULL){
        //Make sure user is logged in
        if( !$folderid || !is_user_logged_in() ) return false;

        if( !$userids ) $userids = WPF()->current_userid;

        //Make sure user is a participant of this conversation
        if( !WPF_PM()->user->is_participant($folderid) ){
            WPF()->notice->add('Error: you are not allowed to do this action (not participant)', 'error');
            return FALSE;
        }

        $folder = $this->get_folder($folderid);
        $hide = $folder['hide'];

        $expld = array_filter( array_map( 'wpforo_bigintval', explode(',', $hide) ) );
        $userids = array_filter( array_map( 'wpforo_bigintval', (array) $userids ) );
        $expld = array_diff( $expld, $userids );
        $hide = implode(',', array_unique($expld));

        return $this->edit( array('hide' => $hide), array('folderid' => $folderid) );
    }

    private function hide_for_user($folderid, $userids = NULL){
        //Make sure user is logged in
        if( !$folderid || !is_user_logged_in() ) return false;

        if( !$userids ) $userids = WPF()->current_userid;

        //Make sure user is a participant of this conversation
        if( !WPF_PM()->user->is_participant($folderid) ){
            WPF()->notice->add('Error: you are not allowed to do this action (not participant)', 'error');
            return FALSE;
        }

        $folder = $this->get_folder($folderid);
        $hide = $folder['hide'];

        $expld = array_filter( array_map( 'wpforo_bigintval', explode(',', $hide) ) );
        $userids = array_filter( array_map( 'wpforo_bigintval', (array) $userids ) );
        $expld = array_merge($expld, $userids);
        $hide = implode(',', array_unique($expld));

        if( false !== $this->edit( array('hide' => $hide), array('folderid' => $folderid) ) ){
            WPF()->notice->add('Successfully Hided', 'success');
            return true;
        }

        WPF()->notice->add('Wrong Data', 'error');
        return false;
    }

    private function email_notification_on_for_user($folderid, $userid = null){
        //Make sure user is logged in
        if( !$folderid || !is_user_logged_in() ) return false;

        if( !$userid ) $userid = WPF()->current_userid;

        //Make sure user is a participant of this conversation
        if( !WPF_PM()->user->is_participant($folderid) ){
            WPF()->notice->add('Error: you are not allowed to do this action (not participant)', 'error');
            return FALSE;
        }

        $folder = $this->get_folder($folderid);
        $exclude_sendmail = $folder['exclude_sendmail'];

        $expld = array_filter( array_map( 'wpforo_bigintval', explode(',', $exclude_sendmail) ) );
        $expld = array_diff( $expld, array( wpforo_bigintval($userid) ) );
        $exclude_sendmail = implode(',', array_unique($expld));

        return $this->edit( array('exclude_sendmail' => $exclude_sendmail), array('folderid' => $folderid) );
    }

    private function email_notification_off_for_user($folderid, $userid = null){
        //Make sure user is logged in
        if( !$folderid || !is_user_logged_in() ) return false;

        if( !$userid ) $userid = WPF()->current_userid;

        //Make sure user is a participant of this conversation
        if( !WPF_PM()->user->is_participant($folderid) ){
            WPF()->notice->add('Error: you are not allowed to do this action (not participant)', 'error');
            return FALSE;
        }

        $folder = $this->get_folder($folderid);
        $exclude_sendmail = $folder['exclude_sendmail'];

        $expld = array_filter( array_map( 'wpforo_bigintval', explode(',', $exclude_sendmail) ) );
        $expld[] = wpforo_bigintval($userid);
        $exclude_sendmail = implode(',', array_unique($expld));

        return $this->edit( array('exclude_sendmail' => $exclude_sendmail), array('folderid' => $folderid) );
    }

    public function get_folder($folderid, $userid = NULL){
        if( empty($folderid) ) return false;
        $sql = "SELECT * FROM `".WPF()->db->prefix."wpforo_pmfolders` WHERE `folderid` = %d";
        $sql = WPF()->db->prepare($sql, $folderid);
        if($userid){
            $sql .= " AND FIND_IN_SET(%d, IFNULL(`userids`, ''))";
            $sql = WPF()->db->prepare($sql, $userid);
        }
        return WPF()->db->get_row( $sql, ARRAY_A );
    }

	public function get_folders( $args ) {
		$default = array(
			'current_userid'   => null,
			'current_folderid' => null,
			'userids'          => array(),
			'userids_include'  => array(),
			'userids_exclude'  => array(),
			'orderby'          => 'pm.`pmid`', // order by `field`
			'order'            => 'DESC', // ASC DESC
			'offset'           => null, // OFFSET
			'row_count'        => null, // ROW COUNT
			'hide'             => false
		);

		$args = wpforo_parse_args( $args, $default );

		if ( ! $args['current_userid'] ) {
			$args['current_userid'] = WPF()->current_userid;
		}
		$current_userid = wpforo_bigintval( $args['current_userid'] );

		$userids         = wpforo_parse_args( array_map( 'wpforo_bigintval', $args['userids'] ) );
		$userids_include = wpforo_parse_args( array_map( 'wpforo_bigintval', $args['userids_include'] ) );
		$userids_exclude = wpforo_parse_args( array_map( 'wpforo_bigintval', $args['userids_exclude'] ) );

		$sql    = "SELECT f.* FROM `" . WPF()->db->prefix . "wpforo_pmfolders` f
				LEFT JOIN `" . WPF()->db->prefix . "wpforo_pms` pm 
					ON pm.`folderid` = f.folderid 
						AND pm.`pmid` = (SELECT MAX(p2.`pmid`) FROM `" . WPF()->db->prefix . "wpforo_pms` p2 WHERE p2.`folderid` = f.`folderid`)";
		$wheres = array();
		if ( ! $args['hide'] && $current_userid ) {
			$wheres[] = " NOT FIND_IN_SET($current_userid, IFNULL(f.`hide`, '')) ";
		}
		if ( $current_userid ) {
			$wheres[] = " FIND_IN_SET($current_userid, IFNULL(f.`userids`, '')) ";
		}

		if ( ! empty( $userids ) ) {
			foreach ( $userids as $userid ) {
				$wheres[] = " FIND_IN_SET($userid, IFNULL(f.`userids`, '')) ";
			}
			$wheres[] = " f.`user_count` = " . count( $userids );
		}

		if ( ! empty( $userids_include ) ) {
			foreach ( $userids_include as $userid ) {
				$wheres[] = " FIND_IN_SET($userid, IFNULL(f.`userids`, '')) ";
			}
		}

		if ( ! empty( $userids_exclude ) ) {
			foreach ( $userids_exclude as $userid ) {
				$wheres[] = " NOT FIND_IN_SET($userid, IFNULL(f.`userids`, '')) ";
			}
		}

		if ( ! empty( $wheres ) ) {
			$sql .= " WHERE " . implode( " AND ", $wheres );
		}

		$sql .= " ORDER BY " . ( $args['current_folderid'] ? "{$args['current_folderid']} = f.`folderid` DESC, " : "" ) . " 
				NOT NOT FIND_IN_SET($current_userid, IFNULL(pm.`read`, '')) ASC,
				{$args['orderby']} {$args['order']}";

		if ( $args['row_count'] ) {
			$sql .= " LIMIT " . intval( $args['offset'] ) . "," . intval( $args['row_count'] );
		}

		$folders = WPF()->db->get_results( $sql, ARRAY_A );
		return apply_filters( 'wpforopm_get_folders', $folders );
	}

    public function make_title( $folder = array() ){
        $title = '-';
        if( empty($folder) && empty(WPF()->current_object['pm_folder']) ) return $title;
        if(empty($folder)) $folder = WPF()->current_object['pm_folder'];
        if( $this->is_single_conversation($folder) ){
            if ( $userids = $this->get_userids($folder) ) {
                if( $member = WPF()->member->get_member($userids[0]) ){
                    $title = wpforo_text(wpforo_user_dname($member), 18, false);
                }
            }
        }else{
            $title = wpforo_text( esc_html($folder['name']), 18, false );
        }
        return $title;
    }

    public function get_userids($folder = array(), $exclude_current = true){
        if( empty($folder) && empty(WPF()->current_object['pm_folder']) ) return array();
        if(empty($folder)) $folder = WPF()->current_object['pm_folder'];
        $userids = array_unique(array_filter(array_map('wpforo_bigintval',explode(',', $folder['userids'] ))));
        if($exclude_current) $userids = array_diff($userids, array(WPF()->current_userid));
        return array_values(array_filter( $userids ));
    }

    public function get_exclude_sendmail($folder = array()){
        if( empty($folder) && empty(WPF()->current_object['pm_folder']) ) return array();
        if(empty($folder)) $folder = WPF()->current_object['pm_folder'];
        $exclude_sendmail = array_unique(array_filter(array_map('wpforo_bigintval',explode(',', $folder['exclude_sendmail'] ))));
        return array_values(array_filter( $exclude_sendmail ));
    }

    public function is_single_conversation($folder = NULL){
        if(!$folder) $folder = WPF()->current_object['pm_folder'];
//		return wpforo_bigintval($folder['user_count']) < 3;
        return empty($folder['name']);
    }

    public function is_email_notification_on_for_user($folder = array(), $userid = NULL){
        if( empty($folder) && empty(WPF()->current_object['pm_folder']) ) return FALSE;
        if(empty($folder)) $folder = WPF()->current_object['pm_folder'];
        if(empty($userid)) $userid = WPF()->current_userid;
        $exclude_sendmail_userids = $this->get_exclude_sendmail($folder);
        if( in_array($userid, $exclude_sendmail_userids) ) return FALSE;
        return TRUE;
    }

    //-START- ajax call functions
	public function ajx_show_list() {
		$return = array( 'stat' => 0, 'html' => '', 'no_more' => 0 );

		$paged = intval( wpfval($_POST, 'paged') );
		$offset = ($paged - 1) * WPF_PM()->options['folders_per_load'];
		$args = array(
			'current_folderid' => WPF()->current_object['pm_folderid'],
			'row_count'        => WPF_PM()->options['folders_per_load'],
			'offset'           => $offset
		);
		if ( $pm_folders = $this->get_folders( $args ) ) {
			ob_start();
			WPF_PM()->tpl->show_folders_list( $pm_folders );
			$return['html'] = ob_get_clean();
			$return['stat'] = 1;
			if( count($pm_folders) < WPF_PM()->options['folders_per_load'] ) $return['no_more'] = 1;
		}else{
			$return['no_more'] = 1;
		}

		$return['notice'] = WPF()->notice->get_notices();
		echo json_encode( $return );
		exit();
	}

    public function ajx_add_users(){
        $return = array('stat' => 0);

        //Make sure user is a participant of this conversation
        if( !is_user_logged_in() || !WPF_PM()->user->is_participant() ){
            WPF()->notice->add('Error: you are not allowed to do this action (not participant)', 'error');
            $return['notice'] = WPF()->notice->get_notices();
            echo json_encode($return);
            exit();
        }

        if( empty($_POST['wpfpm_users']) ){
            WPF()->notice->add('Error: user not selected', 'error');
            $return['notice'] = WPF()->notice->get_notices();
            echo json_encode($return);
            exit();
        }

        $userids = WPF_PM()->user->get_userids_by_user_nicenames($_POST['wpfpm_users']);
        if( false !== $userids = $this->add_users($userids) ){
            $return['stat'] = 1;

            ob_start();
            WPF_PM()->tpl->show_conversation_users($userids);
            $return['html'] = ob_get_clean();

            WPF()->notice->add('Users successfully added to Conversation', 'success');
        }else{
            WPF()->notice->add('Users not added', 'error');
        }

        $return['notice'] = WPF()->notice->get_notices();
        echo json_encode($return);
        exit();
    }

    public function ajx_delete_users(){
        $return = array('stat' => 0);

        //Make sure user is a participant of this conversation | Try to get Folder ID
        if( !is_user_logged_in() || !WPF_PM()->user->is_participant() ){
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

        if( false !== $userids = $this->delete_users($_POST['userid']) ){
            $return['stat'] = 1;

            ob_start();
            WPF_PM()->tpl->show_conversation_users($userids);
            $return['html'] = ob_get_clean();

            WPF()->notice->add('User successfully removed', 'success');
            if( $_POST['userid'] == WPF()->current_userid ) $return['location'] = WPF_PM()->get_conversation_url();
        }else{
            WPF()->notice->add('User not removed', 'error');
        }

        $return['notice'] = WPF()->notice->get_notices();
        echo json_encode($return);
        exit();
    }

    public function ajx_delete(){
        $return = array('stat' => 0);
        $folderid = $_POST['wpfpm_folderid'];

        if( empty($folderid) ){
            WPF()->notice->add('Error: Conversation not selected', 'error');
            $return['notice'] = WPF()->notice->get_notices();
            echo json_encode($return);
            exit();
        }

        //Make sure user is a owner of this conversation
        if( !is_user_logged_in() || !WPF_PM()->user->is_owner($folderid) ){
            WPF()->notice->add('Error: you are not allowed to do this action (not owner)', 'error');
            $return['notice'] = WPF()->notice->get_notices();
            echo json_encode($return);
            exit();
        }

        if( false !== WPF_PM()->pm->delete(array('folderid' => $folderid)) &&
            false !== $this->delete($folderid)
        ){
            $return['stat'] = 1;
            if( $folderid == WPF()->current_object['pm_folderid'] ) $return['location'] = WPF_PM()->get_conversation_url();
        }

        $return['notice'] = WPF()->notice->get_notices();
        echo json_encode($return);
        exit();
    }

    public function ajx_hide(){

        $return = array('stat' => 0);
        $folderid = $_POST['wpfpm_folderid'];

        if( empty($folderid) ){
            WPF()->notice->add('Error: no conversation selected', 'error');
            $return['notice'] = WPF()->notice->get_notices();
            echo json_encode($return);
            exit();
        }

        if( is_user_logged_in() && $this->hide_for_user($folderid) ){
            $return['stat'] = 1;
            if( $_POST['wpfpm_folderid'] == WPF()->current_object['pm_folderid'] ) $return['location'] = WPF_PM()->get_conversation_url();
        }

        $return['notice'] = WPF()->notice->get_notices();
        echo json_encode($return);
        exit();
    }

    public function ajx_email_notification_on(){
        $return = array('stat' => 0);
        $folderid = ($_POST['wpfpm_folderid'] != 0 ? $_POST['wpfpm_folderid'] : WPF()->current_object['pm_folderid'] );

        if( empty($folderid) ){
            WPF()->notice->add('Error: no conversation selected', 'error');
            $return['notice'] = WPF()->notice->get_notices();
            echo json_encode($return);
            exit();
        }

        if( is_user_logged_in() && $this->email_notification_on_for_user($folderid) ){
            $return['stat'] = 1;
            $return['folderid'] = $folderid;
            $return['is_current'] = ( $folderid == WPF()->current_object['pm_folderid'] ? 1 : 0 );
        }

        $return['notice'] = WPF()->notice->get_notices();
        echo json_encode($return);
        exit();
    }

    public function ajx_email_notification_off(){
        $return = array('stat' => 0);
        $folderid = ($_POST['wpfpm_folderid'] != 0 ? $_POST['wpfpm_folderid'] : WPF()->current_object['pm_folderid'] );

        if( empty($folderid) ){
            WPF()->notice->add('Error: no conversation selected', 'error');
            $return['notice'] = WPF()->notice->get_notices();
            echo json_encode($return);
            exit();
        }

        if( is_user_logged_in() && $this->email_notification_off_for_user($folderid) ){
            $return['stat'] = 1;
            $return['folderid'] = $folderid;
            $return['is_current'] = ( $folderid == WPF()->current_object['pm_folderid'] ? 1 : 0 );
        }

        $return['notice'] = WPF()->notice->get_notices();
        echo json_encode($return);
        exit();
    }
    //-END- ajax call functions
}