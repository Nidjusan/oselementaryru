<?php
	// Exit if accessed directly
	if( !defined( 'ABSPATH' ) ) exit;

$wpe_args = array(
	'teeny'          => false,
	'media_buttons'  => true,
	'textarea_rows'  => '8',
	'tinymce'        => true,
	'quicktags'      => false,
	'default_editor' => 'tinymce',
    'textarea_name'  => 'wpforopm_options[new_pm_notification_email_message]'
);
?>
<style>
	.wpf-addon-header{ padding:10px 10px 5px 10px; text-align:right; border-bottom:1px solid #ddd; max-width:240px; margin:0 0 20px auto; font-weight:normal;}
	.wpf-addon-table{width:100%; border:none; padding:0; float:left; margin:0 auto; box-sizing:border-box;}
	.wpf-addon-table tr:nth-child(2n+1) { background: #f9f9f9 none repeat scroll 0 0; }
	.wpf-addon-table td, .wpf-addon-table th{ padding:10px 15px; text-align:left; vertical-align:top;}
	.wpf-addon-table label{ font-size:14px;}
    .wpf-email-shortcodes {font-weight: normal;font-size: 12px;color: #666666;list-style: disc;margin-left: 20px;}
    .wpf-email-shortcodes li {padding: 0; margin: 0; line-height: 18px;}
</style>
<h3 class="wpf-addon-header"><?php _e('wpForo Private Messages', 'wpforo_pm') ?></h3>

<form method="POST" >
	<?php wp_nonce_field( 'wpforo-pm-settings' ); ?>
	<table class="wpf-addon-table">
		<tr>
			<th style="width:50%;"><label for="folders_per_load"><?php _e('Number of Conversations per load', 'wpforo_pm') ?></label></th>
			<td><input id="folders_per_load" min="15" name="wpforopm_options[folders_per_load]" value="<?php echo WPF_PM()->options['folders_per_load'] ?>" class="wpf-field-small" type="number"></td>
		</tr>
		<tr>
			<th style="width:50%;"><label for="pms_per_load"><?php _e('Number of Private Messages per load', 'wpforo_pm') ?></label></th>
			<td><input id="pms_per_load" min="15" name="wpforopm_options[pms_per_load]" value="<?php echo WPF_PM()->options['pms_per_load'] ?>" class="wpf-field-small" type="number"></td>
		</tr>
		<tr>
			<th>
				<label for="min_num_posts"><?php _e('Min number of forum posts user must have', 'wpforo_pm') ?></label>
				<p class="wpf-info"><?php _e('This is the first level of spam message protection.', 'wpforo_pm') ?></p>
			</th>
			<td>
				<input id="min_num_posts" min="0" name="wpforopm_options[min_num_posts]" value="<?php echo WPF_PM()->options['min_num_posts'] ?>" class="wpf-field-small" type="number">
				<p class="wpf-info"><?php _e('set 0 to remove this limit', 'wpforo_pm') ?></p>
			</td>
		</tr>
		<tr>
			<th>
				<label for="max_num_contacts_per_day"><?php _e('Maximum number of contacts per day', 'wpforo_pm') ?></label>
				<p class="wpf-info">
					<?php _e('Limits number of contacts you can send a message during one day.', 'wpforo_pm') ?> <br/>
					<?php _e('This option is designed for all Usergroups, except admin and moderator Usergroups.', 'wpforo_pm') ?>
				</p>
			</th>
			<td>
				<input id="max_num_contacts_per_day" min="0" name="wpforopm_options[max_num_contacts_per_day]" value="<?php echo WPF_PM()->options['max_num_contacts_per_day'] ?>" class="wpf-field-small" type="number">
				<p class="wpf-info"><?php _e('set 0 to remove this limit', 'wpforo_pm') ?></p>
			</td>
		</tr>
		<tr>
			<th>
				<label for="max_num_pms_per_day"><?php _e('Max number of personal messages per user during one day', 'wpforo_pm') ?></label>
			</th>
			<td>
				<input id="max_num_pms_per_day" min="0" name="wpforopm_options[max_num_pms_per_day]" value="<?php echo WPF_PM()->options['max_num_pms_per_day'] ?>" class="wpf-field-small" type="number">
				<p class="wpf-info"><?php _e('set 0 to remove this limit', 'wpforo_pm') ?></p>
			</td>
		</tr>
		<tr>
			<th>
				<label for="max_num_users_per_folder"><?php _e('Max number of conversation participants', 'wpforo_pm') ?></label>
			</th>
			<td>
				<input id="max_num_users_per_folder" min="0" name="wpforopm_options[max_num_users_per_folder]" value="<?php echo WPF_PM()->options['max_num_users_per_folder'] ?>" class="wpf-field-small" type="number">
				<p class="wpf-info"><?php _e('set (0 or 1) to remove this limit', 'wpforo_pm') ?></p>
			</td>
		</tr>
		<tr>
			<th>
				<label for="allow_external_url"><?php _e('Allow external URL and link in messages', 'wpforo_pm') ?></label>
			</th>
			<td>
				<div class="wpf-switch-field">
                    <input type="radio" value="1" name="wpforopm_options[allow_external_url]" id="wpf_allow_external_url_1" <?php wpfo_check(WPF_PM()->options['allow_external_url'], 1); ?>><label for="wpf_allow_external_url_1"><?php _e('Enable', 'wpforo_pm'); ?></label> &nbsp;
                    <input type="radio" value="0" name="wpforopm_options[allow_external_url]" id="wpf_allow_external_url_0" <?php wpfo_check(WPF_PM()->options['allow_external_url'], 0); ?>><label for="wpf_allow_external_url_0"><?php _e('Disable', 'wpforo_pm'); ?></label>
                </div>
			</td>
		</tr>
		<tr>
			<th>
				<label for="allow_external_img_url"><?php _e('Allow Image URLs in messages', 'wpforo_pm') ?></label>
			</th>
			<td>
				<div class="wpf-switch-field">
                    <input type="radio" value="1" name="wpforopm_options[allow_external_img_url]" id="wpf_allow_external_img_url_1" <?php wpfo_check(WPF_PM()->options['allow_external_img_url'], 1); ?>><label for="wpf_allow_external_img_url_1"><?php _e('Enable', 'wpforo_pm'); ?></label> &nbsp;
                    <input type="radio" value="0" name="wpforopm_options[allow_external_img_url]" id="wpf_allow_external_img_url_0" <?php wpfo_check(WPF_PM()->options['allow_external_img_url'], 0); ?>><label for="wpf_allow_external_img_url_0"><?php _e('Disable', 'wpforo_pm'); ?></label>
                </div>
			</td>
		</tr>
		<tr>
			<th>
				<label for="allow_embedded_content"><?php _e('Allow embedded content in messages', 'wpforo_pm') ?></label>
			</th>
			<td>
				<div class="wpf-switch-field">
                    <input type="radio" value="1" name="wpforopm_options[allow_embedded_content]" id="wpf_allow_embedded_content_1" <?php wpfo_check(WPF_PM()->options['allow_embedded_content'], 1); ?>><label for="wpf_allow_embedded_content_1"><?php _e('Enable', 'wpforo_pm'); ?></label> &nbsp;
                    <input type="radio" value="0" name="wpforopm_options[allow_embedded_content]" id="wpf_allow_embedded_content_0" <?php wpfo_check(WPF_PM()->options['allow_embedded_content'], 0); ?>><label for="wpf_allow_embedded_content_0"><?php _e('Disable', 'wpforo_pm'); ?></label>
                </div>
			</td>
		</tr>
        <tr><td colspan="2"><hr/></td></tr>
        <tr>
			<th>
				<label for="email_notification"><?php _e('Email Notification', 'wpforo_pm') ?></label>
			</th>
			<td>
				<div class="wpf-switch-field">
                    <input type="radio" value="1" name="wpforopm_options[email_notification]" id="wpf_email_notification_1" <?php wpfo_check(WPF_PM()->options['email_notification'], 1); ?>><label for="wpf_email_notification_1"><?php _e('Enable', 'wpforo_pm'); ?></label> &nbsp;
                    <input type="radio" value="0" name="wpforopm_options[email_notification]" id="wpf_email_notification_0" <?php wpfo_check(WPF_PM()->options['email_notification'], 0); ?>><label for="wpf_email_notification_0"><?php _e('Disable', 'wpforo_pm'); ?></label>
                </div>
			</td>
		</tr>
        <tr>
            <th><label for="new_note_email_sbj"><?php _e('New "private message" notification email subject', 'wpforo_pm'); ?>:</label></th>
            <td><input id="new_note_email_sbj" style="width:98%" name="wpforopm_options[new_pm_notification_email_subject]" type="text"  value="<?php wpfo(WPF_PM()->options['new_pm_notification_email_subject']); ?>" required></td>
        </tr>
        <tr>
            <th>
                <label for="new_note_email"><?php _e('New "private message" notification email message', 'wpforo_pm'); ?>:</label>
                <ul class="wpf-email-shortcodes">
                    <li>[conversation] - <?php _e('Conversation link', 'wpforo_pm') ?></li>
                    <li>[msg] - <?php _e('Message content', 'wpforo_pm') ?></li>
                </ul>
            </th>
            <td><?php wp_editor( wp_unslash( WPF_PM()->options['new_pm_notification_email_message'] ), 'new_note_email', $wpe_args ); ?></td>
        </tr>
		<tr>
			<td colspan="2" style="text-align:right;"><input type="submit" class="button button-primary" value="<?php _e('Update Options', 'wpforo_pm') ?>"></td>
		</tr>
	</table>
</form>