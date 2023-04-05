<?php
	define('DOING_AJAX', true);
	define('SHORTINIT', true);
	
	if (!isset($_REQUEST['action'])) {
		header('HTTP/1.0 404 Not Found');
		die('');
	}
	header('Content-Type: text/html');
	header('Cache-Control: no-cache');
	header('Pragma: no-cache');

	ini_set('html_errors', 0);

	require_once(dirname(__DIR__).'/XenChatContainer.php');
	XenChatContainer::load('XenChatInstaller');
	XenChatContainer::load('XenChatOptions');
	require_once(dirname(__FILE__).'/wp_core.php');

	send_nosniff_header();

	if (XenChatOptions::getInstance()->isOptionEnabled('enabled_debug', false)) {
		error_reporting(E_ALL);
		ini_set("display_errors", 1);
	}

	// removing images downloaded by the chat:
	/** @var XenChatImagesService $xenChatImagesService */
	$xenChatImagesService = XenChatContainer::get('services/XenChatImagesService');
	add_action('delete_attachment', array($xenChatImagesService, 'removeRelatedImages'));
	
	$action = $_REQUEST['action'];
	if ($action === 'xen_chat_messages_endpoint') {
		/** @var XenChatMessagesEndpoint $endpoint */
		$endpoint = XenChatContainer::get('endpoints/XenChatMessagesEndpoint');
		$endpoint->messagesEndpoint();
	} else if ($action === 'xen_chat_prepare_image_endpoint') {
		/** @var XenChatUserCommandEndpoint $endpoint */
		$endpoint = XenChatContainer::get('endpoints/XenChatUserCommandEndpoint');
		$endpoint->prepareImageEndpoint();
	} else {
		header('HTTP/1.0 400 Bad Request');
	}