<?php
	// Exit if accessed directly
	if( !defined( 'ABSPATH' ) ) exit;
?>
    
<div class="wpforo-messages-content wpfbg-9">

	<?php if( !WPF()->perm->usergroup_can('vwpm') ) : ?>
		
		<div class="wpfbg-7" style="flex-grow: 1; border: #E6E6E6 1px solid; margin-top:3px;">
			<div style="border: 1px dotted #91cf89; display: block; font-size: 14px; text-align: center; padding: 5px 10px; margin: 10px auto; width: auto; color: #000; background-color: #F5F5F5;">
				<?php wpforo_phrase('Permission denied') ?>
			</div>
		</div>
		
    <?php else :
		$no_more = ( count($pm_folders) < WPF_PM()->options['folders_per_load'] ? 1 : 0 );
        ?>

        <div class="wpfpm-left">
			<ul id="wpfpm-users-list" data-paged="1" data-nomore="<?php echo $no_more ?>">
				<li class="wpfpm-uli wpfpm-add-new-conversation <?php echo ( !WPF()->current_object['pm_folderid'] ? ' wpfpm-uli-active' : '' ) ?>" title="<?php wpforo_phrase('New Conversation') ?>">
                    <a href="<?php echo WPF_PM()->get_new_conversation_url() ?>">
                    	<div class="wpfpm-uli-wrap">
                            <div class="wpfpm-uli-avatar">
                            	<div class="wpfpm-add"><i class="fas fa-plus" aria-hidden="true"></i></div>
                            </div>
                            <div class="wpfpm-uli-details">
                                <span class="wpfpm-add-text"><?php wpforo_phrase('New Conversation') ?></span>
                            </div>
                        </div>
                    </a>
                </li>
                <?php WPF_PM()->tpl->show_folders_list($pm_folders) ?>
            </ul>
            
    	</div>
    	<div class="wpfpm-main">
            <?php if( WPF()->current_object['pm_folderid'] ): ?>
                <div id="wpfpm-uli-users-wrap" class="wpfpm-toolbar">
                    <button id="wpfpm_left_toggle_button" type="button"><i class="fas fa-list-ul" aria-hidden="true"></i></button>
                    <?php WPF_PM()->tpl->show_add_users_form() ?>
                    <div class="tollbar-top-info"><?php WPF_PM()->tpl->show_conversation_users() ?></div>
                    <div class="wpfpm-current-folder-info"><?php WPF_PM()->tpl->show_current_folder_info() ?></div>
                    <ul id="wpfpm-tools">
                        <li id="wpfpm-tool-write" title="<?php wpforo_phrase('Write PM') ?>"><i class="fas fa-pencil-alt" aria-hidden="true"></i></li>
                        <li id="wpfpm-tool-refresh" title="<?php wpforo_phrase('Refresh') ?>"><i class="fas fa-sync" aria-hidden="true"></i></li>
                        <li id="wpfpm-tool-load-all" title="<?php wpforo_phrase('Load all Messages') ?>"><i class="fas fa-arrow-circle-down" aria-hidden="true"></i></li>

                        <?php if( WPF_PM()->options['email_notification'] ) : ?>

                            <li id="wpfpm-tool-notification-off" <?php echo ( !WPF_PM()->folder->is_email_notification_on_for_user() ? 'style="display: none;"' : '' ) ?> title="<?php wpforo_phrase('Turn off email notification') ?>"><i class="fas fa-bell" aria-hidden="true"></i></li>
                            <li id="wpfpm-tool-notification-on" disabled <?php echo ( WPF_PM()->folder->is_email_notification_on_for_user() ? 'style="display: none;"' : '' ) ?> title="<?php wpforo_phrase('Turn on email notification') ?>"><i class="fas fa-bell-slash" aria-hidden="true"></i></li>

                        <?php endif; ?>

                        <li id="wpfpm-tool-delete-all" title="<?php wpforo_phrase('Delete all Messages') ?>"><i class="fas fa-trash-alt" aria-hidden="true"></i></li>
                    </ul>
                </div>
                <div class="wpfpm-main-inner">
                    <ul id="wpfpm-wrap" class="wpfpm-content">
                        <?php if(!empty($pm_datas)) : ?>
                            <?php WPF_PM()->tpl->show_pms($pm_datas); ?>
                        <?php else : ?>
                            <li id="wpf-no-discussion" class="whr"><abbr><?php wpforo_phrase('no discussion') ?></abbr></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <?php WPF_PM()->tpl->show_form('default'); ?>
            <?php else: ?>
            	<h3 id="wpfpm_new_conv_form_title">
                    <button id="wpfpm_left_toggle_button" type="button"><i class="fas fa-list-ul" aria-hidden="true"></i></button>
                    <?php wpforo_phrase('Create New Private Conversation') ?>
                </h3>
				<?php WPF_PM()->tpl->show_form('new'); ?>
            <?php endif; ?>
    	</div>
    <?php endif ?>
</div>