<?php

class wpForoPMs
{
    public function __construct(){
        $this->init_hooks();
    }

    private function init_hooks(){
        if( is_user_logged_in() ){
	        /**
	         * ajax action
	         */
            add_action('wp_ajax_wpforopm_load_more', array($this, 'ajx_load_more'));
            add_action('wp_ajax_wpforopm_load_all', array($this, 'ajx_load_all'));
            add_action('wp_ajax_wpforopm_refresh', array($this, 'ajx_refresh'));
            add_action('wp_ajax_wpforopm_delete_all', array($this, 'ajx_delete_all'));
        }
    }

    public function add($args){
        $args = apply_filters('wpforopm_add_pm_data_filter', $args);
        extract($args, EXTR_OVERWRITE);

        if( !isset($fromuserid) || !($fromuserid = intval($fromuserid)) ){
            WPF()->notice->add('Please login to write a message', 'error');
            return false;
        }

        if( !isset($folderid) || !($folderid = intval($folderid)) ){
            WPF()->notice->add('No user selected', 'error');
            return false;
        }

        if( !WPF_PM()->user->can_add_pm($fromuserid, $folderid) ) return false;

        $_message = trim( strip_tags($message) );
        if( empty($_message) ){
            WPF()->notice->add('Message is empty', 'error');
            return false;
        }

        $pm = array(
	        'fromuserid' => $fromuserid,
	        'folderid' => $folderid,
	        'message' => wpforo_kses( trim( stripslashes($message) ), 'post' ),
	        'date' => current_time( 'mysql', 1 ),
	        'read' => $fromuserid
        );

        if(WPF()->db->insert(
            WPF()->db->prefix . 'wpforo_pms',
	        array(
		        'fromuserid' => $pm['fromuserid'],
		        'folderid'   => $pm['folderid'],
		        'message'    => $pm['message'],
		        'date'       => $pm['date'],
		        'read'       => $pm['read']
	        ),
            array('%d','%d','%s','%s','%s')
        )){
            WPF_PM()->folder->edit( array('hide' => ''), array('folderid' => $folderid) );

	        $pm['pmid'] = WPF()->db->insert_id;

            do_action('wpforopm_after_pm_add', $pm);
            return $pm;
        }

        WPF()->notice->add('PM Send error', 'error');
        return false;
    }

    public function delete($args){
        if( empty($args) ){
            WPF()->notice->add('Data is empty', 'error');
            return false;
        }
        extract($args);

        $where = array();
        $where_format = array();

        if( !empty($pmid) ){
            $where['pmid'] = wpforo_bigintval($pmid);
            $where_format[] = '%d';
        }
        if( !empty($folderid) ){
            $where['folderid'] = wpforo_bigintval($folderid);
            $where_format[] = '%d';
        }
        if( !empty($fromuserid) ){
            $where['fromuserid'] = wpforo_bigintval($fromuserid);
            $where_format[] = '%d';
        }

	    if( !empty($where) && !empty($where_format) ){
	    	$dargs = $where;
	    	$dargs['del'] = null;
	        $pms = $this->get_pms($dargs);

	        if(false !== WPF()->db->delete(
                    WPF()->db->prefix . 'wpforo_pms',
                    $where,
                    $where_format
                )){

            	do_action('wpforopm_after_pm_delete', $pms, $where);

                WPF()->notice->add('Private messages successfully deleted', 'success');
                return true;
            }
        }

        WPF()->notice->add('Wrong Data', 'error');
        return false;
    }

    private function hide_for_user($pmid, $userid = null){
        if( !is_user_logged_in() || empty($pmid) ) return false;
        if( $pm = $this->get_pm($pmid) ){
            if(!$userid) $userid = WPF()->current_userid;
            $expld = array_filter( array_map( 'intval', explode(',', $pm['del']) ) );
            $expld[] = $userid;
            $del = implode(',', array_unique($expld));

            if( false !== WPF()->db->update(
                    WPF()->db->prefix . "wpforo_pms",
                    array('del' => $del),
                    array('pmid' => $pmid),
                    array('%s'),
                    array('%d')
                )){
                return $pmid;
            }
        }
        return false;
    }

    public function update_read_status( $folderid = NULL, $current_userid = NULL ){
        if(!$current_userid) $current_userid = WPF()->current_userid;
        if(!$folderid) $folderid = WPF()->current_object['pm_folderid'];

        if( ($current_userid = wpforo_bigintval($current_userid)) && ($folderid = wpforo_bigintval($folderid)) ){
            $sql = "UPDATE `".WPF()->db->prefix."wpforo_pms` 
				SET `read` = TRIM(BOTH ',' FROM CONCAT(`read`, ',', %d) ) 
				WHERE `folderid` = %d 
				AND NOT FIND_IN_SET(%d, IFNULL(`read`, ''))";
            WPF()->db->query( WPF()->db->prepare($sql, $current_userid, $folderid, $current_userid) );

            do_action('wpforopm_after_update_read_status', $folderid, $current_userid);
        }
    }

    public function get_pm($pmid){
        if( empty($pmid) ) return false;
        $sql = "SELECT * FROM `".WPF()->db->prefix."wpforo_pms` WHERE `pmid` = %d";
        return WPF()->db->get_row( WPF()->db->prepare($sql, $pmid), ARRAY_A );
    }

    public function get_pms($args = array(), &$items_count = 0){
	    $default = array(
		    'current_userid' => null,
		    'fromuserid'     => null,
		    'folderid'       => null,
		    'read'           => null,
		    'del'            => false,
		    'pmid'           => null,
		    'pmid_oprtr'     => '',
		    'orderby'        => 'pmid', // order by `field`
		    'order'          => 'DESC', // ASC DESC
		    'offset'         => null, // OFFSET
		    'row_count'      => null, // ROW COUNT
	    );
        $args = wpforo_parse_args( $args, $default );
	    $args['pmid_oprtr'] = trim($args['pmid_oprtr']);
        if( !in_array($args['pmid_oprtr'], array('>','>=','<','<=','<>','!=','=')) ) $args['pmid_oprtr'] = '=';
        if( !$args['current_userid'] ) $args['current_userid'] = WPF()->current_userid;

        $sql = "SELECT * FROM `".WPF()->db->prefix."wpforo_pms`";
        $wheres = array();

        if( !is_null($args['fromuserid']) ) $wheres[] = " `fromuserid` = " . wpforo_bigintval($args['fromuserid']);
        if( !is_null($args['folderid']) )   $wheres[] = " `folderid` = "   . wpforo_bigintval($args['folderid']);
        if( !is_null($args['read']) )       $wheres[] = ( !$args['read'] ? " NOT " : "" ) . " FIND_IN_SET(".wpforo_bigintval($args['current_userid']).", IFNULL(`read`, '')) ";
        if( !is_null($args['del']) )        $wheres[] = ( !$args['del']  ? " NOT " : "" ) . " FIND_IN_SET(".wpforo_bigintval($args['current_userid']).", IFNULL(`del`, '')) ";
        if( !is_null($args['pmid']) )       $wheres[] = " `pmid` " . $args['pmid_oprtr']  . " " . wpforo_bigintval($args['pmid']);

        if($wheres) $sql .= " WHERE " . implode(" AND ", $wheres);
        $items_count = (int) WPF()->db->get_var(str_replace('SELECT * FROM', 'SELECT count(*) FROM', $sql));

        if( $args['orderby'] ) $sql .= " ORDER BY ". $args['orderby'] ." ". $args['order'];
	    $args['offset']    = intval($args['offset']);
	    $args['row_count'] = intval($args['row_count']);
        if( $args['row_count'] ) $sql .= " LIMIT ". $args['offset'] .",". $args['row_count'];

        return WPF()->db->get_results($sql, ARRAY_A);
    }

    public function get_unread_pms_count($folderid = NULL, $current_userid = NULL){
        if( !$current_userid ) $current_userid = WPF()->current_userid;

        $sql = "SELECT COUNT(`pmid`) FROM `" . WPF()->db->prefix . "wpforo_pms` 
			WHERE fromuserid != %d
			AND NOT FIND_IN_SET( %d, IFNULL(`read`, '') )
			AND folderid " . ( $folderid ? " = %d" : "IN( SELECT `folderid` FROM `" . WPF()->db->prefix . "wpforo_pmfolders` 
				WHERE FIND_IN_SET( %d, IFNULL(`userids`, '') ) )" );
        $arg3 = ( $folderid ? $folderid : $current_userid );
        $unread_pms_count = WPF()->db->get_var( WPF()->db->prepare($sql, $current_userid, $current_userid, $arg3) );

        if($unread_pms_count) return $unread_pms_count;
        return 0;
    }

    public function get_pms_count_for_user($userid = NULL){
        if( !$userid ) $userid = WPF()->current_userid;
        $sql = "SELECT COUNT(*) FROM `".WPF()->db->prefix."wpforo_pms` WHERE `fromuserid` = %d";
        return (int) WPF()->db->get_var( WPF()->db->prepare($sql, $userid) );
    }

    public function get_num_contacts_per_day($userid = NULL){
        if( !$userid ) $userid = WPF()->current_userid;
        $date = current_time('timestamp', 1) - DAY_IN_SECONDS;
        $date = date_i18n( 'Y-m-d H:i:s', $date);
        $sql = "SELECT COUNT(*) FROM (SELECT `folderid` FROM `".WPF()->db->prefix."wpforo_pms` WHERE `fromuserid` = %d AND `date` >= %s GROUP BY `folderid`) num_contacts_per_day";
        return (int) WPF()->db->get_var( WPF()->db->prepare($sql, $userid, $date) );
    }

    public function get_user_personal_pm_count_per_day($userid = NULL, $folderid = NULL){
        if( !$userid ) $userid = WPF()->current_userid;
        if( !$folderid ) $folderid = WPF()->current_object['pm_folderid'];
        $date = current_time('timestamp', 1) - DAY_IN_SECONDS;
        $date = date_i18n( 'Y-m-d H:i:s', $date);
        $sql = "SELECT COUNT(pmid) FROM `".WPF()->db->prefix."wpforo_pms` WHERE `fromuserid` = %d AND `folderid` = %d AND `date` >= %s";
        return (int) WPF()->db->get_var( WPF()->db->prepare($sql, $userid, $folderid, $date) );
    }

    //-START- ajax call functions
    public function ajx_load_more(){

        if( empty($_POST['wpforopm_lastid']) || !($wpforopm_lastid = wpforo_bigintval($_POST['wpforopm_lastid'])) ){
            echo '<li id="wpf-no-pm"></li>';
            exit();
        }

        //Make sure user is logged in and folder exists
        if( !is_user_logged_in() || !WPF_PM()->user->is_participant() ) exit();

        $pms = $this->get_pms(
            array(
                'folderid' => WPF()->current_object['pm_folderid'],
                'pmid' => $wpforopm_lastid,
                'pmid_oprtr' => ' < ',
                'row_count' => WPF_PM()->options['pms_per_load']
            )
        );

        if( empty($pms) ){
            echo '<li id="wpf-no-pm" class="whr"><abbr>'. wpforo_phrase('This is a start conversation', false) .'</abbr></li>';
            exit();
        }

        WPF_PM()->tpl->show_pms($pms);
        exit();
    }

    public function ajx_load_all(){
        if( !isset($_POST['wpforopm_lastid']) ){
            echo '<li id="wpf-no-pm"></li>';
            exit();
        }

        //Make sure user is logged in and folder exists
        if( !is_user_logged_in() || !WPF_PM()->user->is_participant() ) exit();

        $wpforopm_lastid = wpforo_bigintval($_POST['wpforopm_lastid']);
        $pms = $this->get_pms(
            array(
                'folderid' => WPF()->current_object['pm_folderid'],
                'pmid' => $wpforopm_lastid,
                'pmid_oprtr' => ' < ',
                'row_count' => 1000
            )
        );

        if( empty($pms) ){
            echo '<li id="wpf-no-pm" class="whr"><abbr>'. wpforo_phrase('This is a start conversation', false) .'</abbr></li>';
            exit();
        }

        WPF_PM()->tpl->show_pms($pms);
        exit();
    }

    public function ajx_refresh(){
        if( !isset($_POST['wpforopm_lastid']) ){
            echo '<li id="wpf-no-pm"></li>';
            exit();
        }

        //Make sure user is logged in and folder exists
        if( !is_user_logged_in() || !WPF_PM()->user->is_participant() ) exit();

        $wpforopm_lastid = wpforo_bigintval($_POST['wpforopm_lastid']);
        $pms = $this->get_pms(
            array(
                'folderid' => WPF()->current_object['pm_folderid'],
                'pmid' => $wpforopm_lastid,
                'pmid_oprtr' => ' > '
            )
        );

        if( empty($pms) ) exit();

        WPF_PM()->tpl->show_pms($pms);
        exit();
    }

    public function ajx_delete_all(){
        if( !WPF()->current_object['pm_folderid'] ) exit();

        //Make sure user is logged in and folder exists
        if( !is_user_logged_in() || !WPF_PM()->user->is_participant() ) exit();

        $pms = $this->get_pms( array( 'folderid' => WPF()->current_object['pm_folderid'] ) );
        if( !empty($pms) ) foreach( $pms as $pm ) $this->hide_for_user($pm['pmid']);

        echo '<li class="whr"><abbr>' . wpforo_phrase('no discussion', false) . '</abbr></li>';
        exit();
    }
    //-END- ajax call functions
}