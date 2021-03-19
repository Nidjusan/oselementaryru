<?php

class wpForoPMsTemplate
{
    private function show_report_form(){ ?>
        <form id="wpfpm-report" data-title="<?php echo esc_attr( wpforo_phrase('Report to forum administrators', false) ) ?>">
            <input type="hidden" id="wpfpm-report-userid">
            <textarea id="wpfpm-report-message" required placeholder="<?php wpforo_phrase('Write message') ?>"></textarea>
            <input id="wpfpm-report-send" type="button" value="<?php wpforo_phrase('Send Report') ?>"/>
        </form>
        <?php
    }

    public function show_form( $mode = 'default' ){
        if( !is_user_logged_in() ) return;
        $this->show_report_form();
        if( !WPF_PM()->user->can_add_pm() ){
            echo '<div class="wpfpm-form-wrapper wpfpm-form-blocked"><i class="fas fa-ban" aria-hidden="true"></i> ';
            echo WPF()->notice->get_notices();
            echo '</div>';
            return;
        }
	    $textareaid = uniqid('wpforo_pm_body_');
        ?>
        <div class="wpfpm-form-wrapper">
            <div id="wpf-post-create" class="wpf-post-create">
                <form name="post" action="" enctype="multipart/form-data" method="POST" class="editor" data-textareaid="<?php echo $textareaid ?>">
                    <?php if( $mode == 'new' ): ?>
                        <div class="wpfpm-form-field">
                            <input id="wpfpm-title" required name="wpforopm[title]" class="wpf-subject" autocomplete="off" placeholder="<?php wpforo_phrase('Conversation Title') ?>" type="text" autofocus>
                        </div>
                        <div class="wpfpm-udatalist">
                            <input id="wpfpm-users" required name="wpforopm[users]" class="wpf-subject" autocomplete="off" placeholder="<?php wpforo_phrase('Conversation Members') ?>" type="text">
                            <ul id="wpfpm-users-datalist"></ul>
                        </div>
                    <?php endif; ?>

                    <input type="hidden" name="wpforopm_submit" value="1"/>
                    <?php wp_nonce_field( 'wpforo_verify_form', 'wpforo_form' ); ?>
                    <?php
                    $settings = WPF()->tpl->editor_buttons();
                    $settings['textarea_name'] = 'wpforopm[message]';
                    wp_editor( '', $textareaid, $settings );
                    ?>
                    <div class="wpf-extra-fields">
                        <?php do_action('wpforopm_form_bottom'); ?>&nbsp;&nbsp;
                        <div class="wpf-clear"></div>
                    </div>
                    <input id="formbutton" type="submit" name="wpforopm_submit" class="button button-primary forum_submit" value="<?php wpforo_phrase('Send') ?>">
                    <div class="wpf-clear"></div>
                </form>
            </div>
        </div>
        <?php
    }

    public function show_add_users_form(){ ?>
        <div class="wpfpm-add-user">
            <div class="wpfpm-udatalist">
                <input id="wpfpm-users" required name="wpforopm[users]" class="wpf-subject" autocomplete="off"
                       title="<?php wpforo_phrase('Comma separated user nickname, e.g: @john,@alex,') ?>"
                       placeholder="<?php wpforo_phrase('User Names') ?>" type="text">
                <div class="wpfpm-add-user-go"><i class="fas fa-check-circle" aria-hidden="true"></i></div>
                <ul id="wpfpm-users-datalist"></ul>
            </div>
        </div>
        <?php
    }

    public function show_users_datalist($userids){
        if( empty($userids) ) return;

        foreach( $userids as $userid ){
            if( $user = WPF()->member->get_member($userid) ){ ?>
                <li id="wpfpmu-<?php echo $userid ?>" tabindex="<?php echo $userid ?>" class="wpfpm-datalist-user" data-nicename="@<?php echo $user['user_nicename'] ?>" title="<?php echo wpforo_user_dname($user) ?>">
                    <div class="wpfpm-uli-avatar"><?php echo WPF()->member->get_avatar($userid, 'width="22" height="22"'); ?></div>
                    <div class="wpfpm-uli-info">
                        <span class="wpfpm-uli-dname"><?php wpforo_text( wpforo_user_dname($user), 20 ) ?></span>
                        <span>   (@<?php echo $user['user_nicename'] ?>)</span>
                    </div>
                </li>
                <?php
            }
        }

    }

    //-START- Folder TOOLS
    private function get_folder_tools($folder){
        $tools = array(
//			'pin' => array('ico' => 'fas fa-thumbtack', 'title' => wpforo_phrase('Pin to top', false), 'val' => wpforo_phrase('Pin', false)),
//			'unpin' => array('ico' => 'fas fa-thumbtack', 'title' => wpforo_phrase('Unpin from top', false), 'val' => wpforo_phrase('Unpin', false)),
            'notification-off' => array('ico' => 'fas fa-bell', 'title' => wpforo_phrase('Turn off email notification', false), 'val' => wpforo_phrase('Turn off', false)),
            'notification-on' => array('ico' => 'fas fa-bell-slash', 'title' => wpforo_phrase('Turn on email notification', false), 'val' => wpforo_phrase('Turn on', false)),
            'hide' => array('ico' => 'fas fa-eye-slash', 'title' => wpforo_phrase('Hide from this list', false), 'val' => wpforo_phrase('Hide', false)),
            'delete' => array('ico' => 'fas fa-trash-alt', 'title' => wpforo_phrase('Delete this conversation with all messages', false), 'val' => wpforo_phrase('Delete', false)),
        );

        if( !WPF_PM()->user->is_participant($folder) ) unset($tools['hide']);
        if( !WPF_PM()->user->is_owner($folder) ) unset($tools['delete']);

        $html = '';
        if( !empty($tools) ){
            foreach( $tools as $key => $tool ){
                extract($tool);

                switch ($key){
                    case 'notification-off':
                        $attr = ( !WPF_PM()->folder->is_email_notification_on_for_user($folder) ? ' style="display: none;" ' : '' );
                        break;
                    case 'notification-on':
                        $attr = ( WPF_PM()->folder->is_email_notification_on_for_user($folder) ? ' disabled style="display: none;" ' : ' disabled ' );
                        break;
                    default:
                        $attr = '';
                }

                $html .= '<li '.$attr.' class="wpfpm-uli-cog-'.$key.'" title="'.$title.'"><i class="'.$ico.'"></i>'.$val.'</li>';
            }
        }

        return $html;
    }

    private function show_folder_tools($folder){
        echo $this->get_folder_tools($folder);
    }

    public function show_folder_cog($folder){ ?>
        <div class="wpfpm-uli-cog" tabindex="<?php echo $folder['folderid'] ?>">
            <i title="<?php wpforo_phrase('Settings') ?>" class="fas fa-cog" aria-hidden="true"></i>
            <ul class="wpfpm-uli-cog-tooltip">
                <?php $this->show_folder_tools($folder); ?>
            </ul>
        </div>
        <?php
    }
    //-END- Folder TOOLS

    //-START- User TOOLS
    public function get_user_tools($userid){
        $tools = array(
            'report' => array('ico' => 'fas fa-exclamation-triangle', 'title' => wpforo_phrase('Report Spam', false), 'val' => wpforo_phrase('Report', false)),
            'block' => array('ico' => 'fas fa-ban', 'title' => wpforo_phrase('Block User', false), 'val' => wpforo_phrase('Block', false)),
            'unblock' => array('ico' => 'fas fa-ban', 'title' => wpforo_phrase('Unblock User', false), 'val' => wpforo_phrase('Unblock', false)),
            'delete' => array('ico' => 'fas fa-user-times', 'title' => wpforo_phrase('Remove this user from conversation', false), 'val' => wpforo_phrase('Remove', false)),
        );

        if( WPF_PM()->folder->is_single_conversation() && WPF()->current_object['pm_folder']['user_count'] < 3 ){
            if( WPF_PM()->user->is_blocked($userid) ){
                unset($tools['block']);
            }else{
                unset($tools['unblock']);
            }
        }else{
            unset($tools['block'], $tools['unblock']);
        }

        if( $userid == WPF()->current_userid ) unset($tools['report'],$tools['block']);

        $html = '';
        if( !empty($tools) ){
            foreach( $tools as $key => $tool ){
                extract($tool);
                $html .= '<li class="wpfpm-uli-cog-'.$key.'" title="'.$title.'"><i class="'.$ico.'"></i>'.$val.'</li>';
            }
        }

        return $html;
    }

    private function show_user_tools($userid){
        echo $this->get_user_tools($userid);
    }

    private function show_user_cog($userid){ ?>
        <div class="wpfpm-uli-cog" tabindex="<?php echo $userid ?>">
            <i title="<?php wpforo_phrase('Settings') ?>" class="fas fa-cog" aria-hidden="true"></i>
            <ul class="wpfpm-uli-cog-tooltip">
                <?php $this->show_user_tools($userid); ?>
            </ul>
        </div>
        <?php
    }
    //-END- User TOOLS

    public function get_pm_note($full_msg = false, $userid = NULL){
        $note = '';
        if( !$userid ) $userid = WPF()->current_userid;
        if( !$unread_pms_count = WPF_PM()->pm->get_unread_pms_count(NULL, $userid) ) return $note;
        $new_message = ( $unread_pms_count > 1 ) ? wpforo_phrase('You have %d new messages', false) : wpforo_phrase('You have %d new message', false);
	    if($full_msg){
		    $note = '<span class="wpforo-pm-note">' . sprintf($new_message, $unread_pms_count) . '</span>';
	    }else{
		    $note = '<span class="wpfbg-5 wpfcl-3 wpforo-pm-note" title="' . sprintf($new_message, $unread_pms_count) . '">'.$unread_pms_count.'</span>';
	    }

	    $last_pm_user = WPF_PM()->folder->get_folders( array( 'row_count' => 1, 'hide' => true ) );
	    $note = '<a href="'. WPF_PM()->get_conversation_url($last_pm_user[0]) .'" style="display: inline;">' . $note . '</a>';

        return $note;
    }

    public function show_pm_note($full_msg = false, $userid = NULL){
        echo $this->get_pm_note($full_msg, $userid);
    }

    private function show_blocked_user_indicator($userid){
        $display = 'none';
        if( WPF_PM()->user->is_blocked($userid) ) $display = 'block';
        echo '<i class="fas fa-ban wpfpm_avatar_ban" style="display: '.$display.'" title="'.wpforo_phrase('Blocked', false).'"></i>';
    }

    public function show_conversation_users($userids = array()){
        if( empty($userids) && empty(WPF()->current_object['pm_folder_userids']) ) return;
        if( empty($userids) ) $userids = WPF()->current_object['pm_folder_userids'];
        $userids = array_values(array_diff($userids, array(WPF()->current_userid)));
        $userids[] = WPF()->current_userid;
        $user_count = count($userids);
        ?>

        <div id="wpfpm-ul-users-tooltip-wrap" class="wpfpm-convr-user">
            <i class="fas fa-users" aria-hidden="true"></i>
            <ul class="wpfpm-ul-users-tooltip">
                <?php if( WPF_PM()->options['max_num_users_per_folder'] <= 1 || $user_count < WPF_PM()->options['max_num_users_per_folder'] ) : ?>
                    <li class="wpfpm-uli-users wpfpm-add-user-button"
                        title="<?php wpforo_phrase('Add new people to this conversation') ?>">
                        <div class="wpfpm-uli-avatar">
                            <div class="wpfpm-add wpfpm-people-add"><i class="fas fa-plus" aria-hidden="true"></i></div>
                        </div>
                        <div class="wpfpm-uli-info">
                            <span class="wpfpm-uli-dname"><?php wpforo_phrase('Add People') ?></span>
                        </div>
                    </li>
                <?php endif ?>
                <?php foreach( $userids as $userid ) :
                    if( !$user = WPF()->member->get_member($userid) ) continue; ?>
                    <li id="wpfpmu-<?php echo $userid ?>" class="wpfpm-uli-users"
                        title="<?php wpforo_user_dname($user) ?>">
                        <div class="wpfpm-uli-avatar">
                            <?php echo WPF()->member->get_avatar($userid, 'width="22" height="22"'); ?>
                            <?php if( WPF()->current_object['pm_folder']['user_count'] < 3 ) $this->show_blocked_user_indicator($userid) ?>
                        </div>
                        <div class="wpfpm-uli-info">
			            	<span class="wpfpm-uli-dname">
			            		<?php wpforo_text(wpforo_user_dname($user), 18); ?>
                                <?php if( $userid == WPF()->current_userid ) echo '  <span style="color:#dd0000;">(' . wpforo_phrase('You', FALSE) . ')</span>'; ?>
			            	</span>
                        </div>
                        <?php $this->show_user_cog($userid); ?>
                    </li>
                <?php endforeach ?>
            </ul>
            <?php echo (!empty($user_count) && $user_count > 2) ? '<div class="wpfpm-members-count" title="' . $user_count . ' ' . wpforo_phrase('members', FALSE) . '">' . $user_count . '</div>' : ''; ?>
        </div>

        <?php $h3 = WPF_PM()->folder->make_title(); ?>
        <h3 class="wpfpm-convr-title" title="<?php echo $h3 ?>"><?php wpforo_text(esc_html($h3), 30) ?></h3>
        <?php
    }

    public function show_current_folder_info(){
        if ( empty(WPF()->current_object['pm_folder']) ) return;

        $uli_avatar = '<div class="wpfpm-add"><i class="fas fa-users" aria-hidden="true"></i></div>';
        $uli_dname = '-';
        $last_active = '';

        $pm_folder = WPF()->current_object['pm_folder'];
        if( WPF_PM()->folder->is_single_conversation($pm_folder) ){
            if( $folder_userids = WPF_PM()->folder->get_userids($pm_folder) ){
                $member = WPF()->member->get_member($folder_userids[0]);
                $uli_avatar = WPF()->member->get_avatar($member['userid'], 'width="48" height="48"');
                $uli_dname = wpforo_text( wpforo_user_dname($member), 18, false );
                $last_active =  $this->get_last_online_time($member);
            }
        }else{
            $uli_dname = wpforo_text( esc_html($pm_folder['name']), 18, false );
        }
        ?>

        <div class="wpfpm-uli-avatar"><?php echo $uli_avatar ?></div>
        <div class="wpfpm-cfi-top">
            <div class="wpfpm-cfi-dname"><?php echo $uli_dname ?></div>
            <?php echo $last_active; ?>
        </div>

        <?php
    }

    public function show_pms($pms, $reverse = true){
        if( empty($pms) ) return;

        if($reverse) $pms = array_reverse($pms);
        $displayed_whrs = array();
        $prev_userid = 0;
        foreach($pms as $pm) :
            extract($pm, EXTR_OVERWRITE);

            $abbr_title = '';
            $whr = WPF_PM()->tools->get_human_time($date, 'abbr_title');
            if(!in_array($whr, $displayed_whrs)) : ?>
                <li class="whr"><abbr><?php echo $whr ?></abbr></li>
                <?php $displayed_whrs[] = $whr;
                $abbr_title = $whr;
            endif;

            if( $fromuserid == WPF()->current_userid ){
                extract(WPF()->current_user, EXTR_OVERWRITE);
            }else{
                extract(WPF()->member->get_member($fromuserid));
            }
            $echo_udetails = ( $prev_userid == $ID ? false : true );
            ?>
            <li id="wpfpmid-<?php echo $pmid ?>" class="wpfpm <?php echo ( $fromuserid == WPF()->current_userid ? 'wpfpm-me' : 'wpfpm-other' ) ?>" title="<?php echo $abbr_title ?>">
                <div class="wpfpm-inner">
                    <div class="wpfpm-details">
                        <?php if($echo_udetails) : ?>
                            <div class="wpfpm-avatar">
                                <a href="<?php echo WPF()->member->get_profile_url($ID) ?>"><?php echo WPF()->member->get_avatar($ID, 'height="28" width="28"'); ?></a>
                            </div>
                            <div class="wpfpm-dname">
                                <a href="<?php echo WPF()->member->get_profile_url($ID) ?>">
                                    <strong><a href="<?php echo WPF()->member->get_profile_url($ID) ?>"><?php echo $display_name ?></a></strong>
                                </a>
                            </div>
                        <?php endif ?>
                    </div>
                    <div class="wpfpm-msg">
                        <div class="wpfpm-msg-inner <?php echo ( $echo_udetails ? ' wpfpm-msg-before' : '' ) ?>"><?php wpforopm_content($message) ?></div>
                        <div class="wpfpm-date">
                            <abbr title="<?php echo $abbr_title ?>"><?php echo WPF_PM()->tools->get_human_time($date, 'pm') ?></abbr>
                        </div>
                    </div>
                </div>
            </li>
            <?php $prev_userid = $ID;
        endforeach;
    }

    private function get_last_online_time($user = array()){
        if(!$user) $user = ( !empty(WPF()->current_object['user']) ? WPF()->current_object['user'] : array() ) ;
        if(!$user) return '';

        $abbr_title = WPF_PM()->tools->get_human_time($user['online_time'], 'abbr_title');
        $human_time = WPF_PM()->tools->get_human_time($user['online_time'], 'pm');

        return sprintf('<span title="%s">(%s)</span>', $abbr_title, sprintf(wpforo_phrase('last activity: %s', FALSE), $human_time) );
    }

    private function show_last_online_time($user = array()){
        echo $this->get_last_online_time($user);
    }

	public function show_folders_list( $pm_folders ) {
		if ( ! empty( $pm_folders ) ) :
			foreach ( $pm_folders as $pm_folder ) :
				if ( WPF_PM()->folder->is_single_conversation( $pm_folder ) ) {
					$folder_userids = WPF_PM()->folder->get_userids( $pm_folder );
					$user = WPF()->member->get_member( $folder_userids[0] );

					$uli_avatar = WPF()->member->get_avatar( $user['userid'], 'width="48" height="48"' );
					$uli_dname  = wpforo_text( wpforo_user_dname($user), 18, false );
				} else {
					$uli_avatar = '<div class="wpfpm-add"><i class="fas fa-users" aria-hidden="true"></i></div>';
					$uli_dname  = wpforo_text( esc_html( $pm_folder['name'] ), 18, false );
				}

				$last_human_time  = '';
				$abbr_title       = '';
				$unread_pms_count = WPF_PM()->pm->get_unread_pms_count( $pm_folder['folderid'] );
				$last_pm          = WPF_PM()->pm->get_pms( array(
					"folderid"  => $pm_folder['folderid'],
					'row_count' => 1
				) );
				if ( ! empty( $last_pm ) ) {
					$last_human_time = WPF_PM()->tools->get_human_time( $last_pm[0]['date'], 'last_human_time' );
					$abbr_title      = WPF_PM()->tools->get_human_time( $last_pm[0]['date'], 'abbr_title' );
				} ?>
                <li id="wpfpmf-<?php echo $pm_folder['folderid'] ?>"
                    class="wpfpm-uli <?php echo( $pm_folder['folderid'] == WPF()->current_object['pm_folderid'] ? ' wpfpm-uli-active' : ( $unread_pms_count != 0 ? ' wpfpm-uli-has-message' : '' ) ) ?>">
                    <a href="<?php echo WPF_PM()->get_conversation_url( $pm_folder ) ?>">
                        <div class="wpfpm-uli-wrap">
                            <div class="wpfpm-uli-avatar"><?php echo $uli_avatar ?></div>
                            <div class="wpfpm-uli-details">
                                <div class="wpfpm-uli-top">
                                    <span class="wpfpm-uli-dname"><?php echo $uli_dname ?></span>
                                    <abbr class="wpfpm-uli-date"
                                          title="<?php echo $abbr_title ?>"><?php echo $last_human_time ?></abbr>
                                </div>
                                <div class="wpfpm-uli-middle">
                                    <div class="wpfpm-uli-lastmsg">
										<?php $lastmod_text = ( ! empty( $last_pm ) ? ( WPF()->current_userid == $last_pm[0]['fromuserid'] ? wpforo_phrase( 'You', false ) . ": " . $last_pm[0]['message'] : $last_pm[0]['message'] ) : '' ); ?>
                                        <span><?php wpforo_text( $lastmod_text, 19 ) ?></span>
                                    </div>
                                </div>
								<?php if ( ! ( $pm_folder['folderid'] == WPF()->current_object['pm_folderid'] || $unread_pms_count == 0 ) ) : ?>
                                    <abbr class="wpfpm-uli-unread"><?php wpforo_phrase( 'unread' ) ?> <span
                                                class="wpfpm-uli-unread-count"><?php echo $unread_pms_count; ?></span></abbr>
								<?php endif ?>
                            </div>
							<?php WPF_PM()->tpl->show_folder_cog( $pm_folder ); ?>
                        </div>
                    </a>
                </li>
			<?php endforeach;
		endif;
	}
}