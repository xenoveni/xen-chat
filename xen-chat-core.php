<?php
/*
	Plugin Name: Xen Chat
	Version: 3.1.1
	Description: Fully-featured chat plugin for WordPress. It requires no server, supports multiple channels, bad words filtering, themes, appearance settings, filters, bans and more.
	Author: Xenophon Venios
	Author URI: https://xenophonvenios.com
	Text Domain: xen-chat
*/

define('Xen_CHAT_VERSION', '3.1.1');

require_once(dirname(__FILE__).'/src/XenChatContainer.php');
XenChatContainer::load('XenChatInstaller');
XenChatContainer::load('XenChatOptions');

if (XenChatOptions::getInstance()->isOptionEnabled('enabled_debug', false)) {
	error_reporting(E_ALL);
	ini_set("display_errors", 1);
}

if (is_admin()) {
	// installer:
	register_activation_hook(__FILE__, array('XenChatInstaller', 'activate'));
	register_deactivation_hook(__FILE__, array('XenChatInstaller', 'deactivate'));
	register_uninstall_hook(__FILE__, array('XenChatInstaller', 'uninstall'));

    /** @var XenChatSettings $settings */
	$settings = XenChatContainer::get('XenChatSettings');
    // initialize plugin settings page:
	$settings->initialize();

	add_action('admin_enqueue_scripts', function() {
		wp_enqueue_media();
	});
}

// register action that detects when WordPress user logs in / logs out:
function Xen_chat_after_setup_theme_action() {
    /** @var XenChatUserService $userService */
	$userService = XenChatContainer::get('services/user/XenChatUserService');
	$userService->switchUser();
}
add_action('after_setup_theme', 'Xen_chat_after_setup_theme_action');

// register CSS file in HEAD section:
function Xen_chat_register_common_css() {
	$pluginBaseURL = plugin_dir_url(__FILE__);
	wp_enqueue_style('Xen_chat_libs', $pluginBaseURL.'assets/css/Xen-chat-libs.min.css?v='.Xen_CHAT_VERSION);
	wp_enqueue_style('Xen_chat_core', $pluginBaseURL.'assets/css/Xen-chat.min.css?v='.Xen_CHAT_VERSION);
}
add_action('wp_enqueue_scripts', 'Xen_chat_register_common_css');

// register chat shortcode:
function Xen_chat_shortcode($atts) {
	/** @var XenChat $XenChat */
	$XenChat = XenChatContainer::get('XenChat');
	$html = $XenChat->getRenderedShortcode($atts);
	$XenChat->registerResources();
    return $html;
}
add_shortcode('Xen-chat', 'Xen_chat_shortcode');

// register chat channel stats shortcode:
function Xen_chat_channel_stats_shortcode($atts) {
	$XenChatStatsShortcode = XenChatContainer::get('XenChatStatsShortcode');
	return $XenChatStatsShortcode->getRenderedChannelStatsShortcode($atts);
}
add_shortcode('Xen-chat-channel-stats', 'Xen_chat_channel_stats_shortcode');

// chat function:
function Xen_chat($channel = null) {
	$XenChat = XenChatContainer::get('XenChat');
	echo $XenChat->getRenderedChat(!is_array($channel) ? array($channel) : $channel);
	$XenChat->registerResources();
}

// register chat widget:
function Xen_chat_widget() {
	XenChatContainer::get('XenChatWidget');
	register_widget("XenChatWidget");
}
add_action('widgets_init', 'Xen_chat_widget');

// register action that auto-removes images generate by the chat (the additional thumbnail):
function Xen_chat_action_delete_attachment($attachmentId) {
	/** @var XenChatImagesService $XenChatImagesService */
	$XenChatImagesService = XenChatContainer::get('services/XenChatImagesService');
	$XenChatImagesService->removeRelatedImages($attachmentId);
}
add_action('delete_attachment', 'Xen_chat_action_delete_attachment');

// Endpoints fo AJAX requests:
function Xen_chat_endpoint_messages() {
	/** @var XenChatMessagesEndpoint $XenChatEndpoints */
	$XenChatEndpoints = XenChatContainer::get('endpoints/XenChatMessagesEndpoint');
	$XenChatEndpoints->messagesEndpoint();
}
add_action("wp_ajax_nopriv_Xen_chat_messages_endpoint", 'Xen_chat_endpoint_messages');
add_action("wp_ajax_Xen_chat_messages_endpoint", 'Xen_chat_endpoint_messages');

function Xen_chat_endpoint_past_messages() {
	/** @var XenChatMessagesEndpoint $XenChatEndpoints */
	$XenChatEndpoints = XenChatContainer::get('endpoints/XenChatMessagesEndpoint');
	$XenChatEndpoints->pastMessagesEndpoint();
}
add_action("wp_ajax_nopriv_Xen_chat_past_messages_endpoint", 'Xen_chat_endpoint_past_messages');
add_action("wp_ajax_Xen_chat_past_messages_endpoint", 'Xen_chat_endpoint_past_messages');

function Xen_chat_endpoint_message() {
	/** @var XenChatMessageEndpoint $XenChatEndpoints */
	$XenChatEndpoints = XenChatContainer::get('endpoints/XenChatMessageEndpoint');
	$XenChatEndpoints->messageEndpoint();
}
add_action("wp_ajax_nopriv_Xen_chat_message_endpoint", 'Xen_chat_endpoint_message');
add_action("wp_ajax_Xen_chat_message_endpoint", 'Xen_chat_endpoint_message');

function Xen_chat_endpoint_maintenance() {
	/** @var XenChatMaintenanceEndpoint $endpoint */
	$endpoint = XenChatContainer::get('endpoints/XenChatMaintenanceEndpoint');
	$endpoint->maintenanceEndpoint();
}
add_action("wp_ajax_nopriv_Xen_chat_maintenance_endpoint", 'Xen_chat_endpoint_maintenance');
add_action("wp_ajax_Xen_chat_maintenance_endpoint", 'Xen_chat_endpoint_maintenance');

function Xen_chat_endpoint_settings() {
	$XenChatEndpoints = XenChatContainer::get('endpoints/XenChatEndpoints');
	$XenChatEndpoints->settingsEndpoint();
}
add_action("wp_ajax_nopriv_Xen_chat_settings_endpoint", 'Xen_chat_endpoint_settings');
add_action("wp_ajax_Xen_chat_settings_endpoint", 'Xen_chat_endpoint_settings');

function Xen_chat_endpoint_prepare_image() {
	/** @var XenChatUserCommandEndpoint $endpoint */
	$endpoint = XenChatContainer::get('endpoints/XenChatUserCommandEndpoint');
	$endpoint->prepareImageEndpoint();
}
add_action("wp_ajax_nopriv_Xen_chat_prepare_image_endpoint", 'Xen_chat_endpoint_prepare_image');
add_action("wp_ajax_Xen_chat_prepare_image_endpoint", 'Xen_chat_endpoint_prepare_image');

function Xen_chat_endpoint_user_command() {
	/** @var XenChatUserCommandEndpoint $endpoint */
	$endpoint = XenChatContainer::get('endpoints/XenChatUserCommandEndpoint');
	$endpoint->userCommandEndpoint();
}
add_action("wp_ajax_nopriv_Xen_chat_user_command_endpoint", 'Xen_chat_endpoint_user_command');
add_action("wp_ajax_Xen_chat_user_command_endpoint", 'Xen_chat_endpoint_user_command');

function Xen_chat_endpoint_auth() {
	/** @var XenChatAuthEndpoint $endpoint */
	$endpoint = XenChatContainer::get('endpoints/XenChatAuthEndpoint');
	$endpoint->authEndpoint();
}
add_action("wp_ajax_nopriv_Xen_chat_auth_endpoint", 'Xen_chat_endpoint_auth');
add_action("wp_ajax_Xen_chat_auth_endpoint", 'Xen_chat_endpoint_auth');

function Xen_chat_profile_update($userId, $oldUserData) {
	/** @var XenChatUserService $XenChatUserService */
	$XenChatUserService = XenChatContainer::get('services/user/XenChatUserService');
	$XenChatUserService->onWpUserProfileUpdate($userId, $oldUserData);
}
add_action("profile_update", 'Xen_chat_profile_update', 10, 2);

function Xen_chat_load_plugin_textdomain() {
	load_plugin_textdomain('Xen-chat', false, basename(dirname(__FILE__)).'/languages/');
}
add_action('plugins_loaded', 'Xen_chat_load_plugin_textdomain');

function Xen_chat_elementor($widgetsManager) {
	/** @var XenChatElementor $XenChatElementor */
	$XenChatElementor = XenChatContainer::get('integrations/elementor/XenChatElementor');
	$XenChatElementor->register($widgetsManager);
}
add_action('elementor/widgets/register', 'Xen_chat_elementor');