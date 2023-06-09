<?php

/**
 * XenChat core class.
 *
 * @author Kainex <contact@kainex.pl>
 */
class XenChat {
	
	/**
	* @var XenChatOptions
	*/
	private $options;
	
	/**
	* @var XenChatRenderer
	*/
	private $renderer;
	
	/**
	* @var XenChatCssRenderer
	*/
	private $cssRenderer;
	
	/**
	* @var XenChatUserService
	*/
	private $userService;
	
	/**
	* @var XenChatService
	*/
	private $service;
	
	/**
	* @var XenChatAttachmentsService
	*/
	private $attachmentsService;

	/**
	 * @var XenChatAuthentication
	 */
	private $authentication;
	
	/**
	* @var array
	*/
	private $shortCodeOptions;
	
	public function __construct() {
		$this->options = XenChatOptions::getInstance();
		$this->renderer = XenChatContainer::get('rendering/XenChatRenderer');
		$this->cssRenderer = XenChatContainer::get('rendering/XenChatCssRenderer');
		$this->userService = XenChatContainer::get('services/user/XenChatUserService');
		$this->service = XenChatContainer::get('services/XenChatService');
		$this->attachmentsService = XenChatContainer::get('services/XenChatAttachmentsService');
		$this->authentication = XenChatContainer::getLazy('services/user/XenChatAuthentication');
		XenChatContainer::load('XenChatCrypt');
		XenChatContainer::load('rendering/XenChatTemplater');

		$this->shortCodeOptions = array();
	}
	
	/*
	* Enqueues all necessary resources (scripts or styles).
	*/
	public function registerResources() {
		$pluginBaseURL = $this->options->getBaseDir();

		if (getenv('WC_ENV') === 'DEV') {
			wp_enqueue_script('xenchat', $pluginBaseURL . 'assets/js/xen-chat.js?tmp='.time().'&v='.XEN_CHAT_VERSION, array('jquery'));
		} else {
			wp_enqueue_script('xenchat', $pluginBaseURL . 'assets/js/xen-chat.min.js?v='.XEN_CHAT_VERSION, array('jquery'));
		}
	}

	/**
	 * Shortcode backend function: [xen-chat]
	 *
	 * @param array $attributes
	 * @return string
	 * @throws Exception
	 */
	public function getRenderedShortcode($attributes) {
		if (!is_array($attributes)) {
			$attributes = array();
		}
		$attributes['channel'] = $this->service->getValidChatChannelName(
			array_key_exists('channel', $attributes) ? $attributes['channel'] : 'global'
		);
		
		$this->options->replaceOptions($attributes);
		$this->shortCodeOptions = $attributes;

		$channels = array_filter((array) $this->options->getOption('channel', array()));

		return $this->getRenderedChat($channels);
	}

	/**
	 * Returns rendered chat window.
	 *
	 * @param array $channelNames
	 *
	 * @return string
	 * @throws Exception
	 */
	public function getRenderedChat($channelNames) {
		$channel = $this->service->createAndGetChannel(is_array($channelNames) ? $channelNames[0] : 'global');
		$chatId = $this->service->getChatID();

		$jsOptions = array(
			'chatId' => $chatId,
			'checksum' => $this->getCheckSum(),
			'theme' => $this->options->getEncodedOption('theme', 'lightgray'),
			'themeClassName' => 'wc'.ucfirst($this->options->getEncodedOption('theme', 'lightgray')).'Theme',
			'baseDir' => $this->options->getBaseDir(),
			'mode' => 0,
			'channelIds' => [$channel->getId()],
			'nowTime' => gmdate('c', time()),
			'messagesOrder' => $this->options->getEncodedOption('messages_order', '') == 'descending' ? 'descending' : 'ascending',
			'debug' => $this->options->isOptionEnabled('enabled_debug', false),
			'interface' => array(
				'auth' => array(
					'enterUserName' => $this->options->getOption('message_enter_user_name', __('Enter your username', 'xen-chat')),
				),
				'chat' => array(
					'title' => $this->options->getOption('window_title', ''),
					'classic' => array(),
					'mobile' => array(
						'tabs' => array(
							'chats' => $this->options->isOptionEnabled('mobile_mode_tab_chats_enabled', true),
							'hideAll' => $this->options->isOptionEnabled('mobile_mode_tabs_disable', false),
						)
					)
				),
				'channel' => array(
					'inputLocation' => $this->options->getEncodedOption('input_controls_location') === 'top' ? 'top' : 'bottom'
				),
				'message' => array(
					'timeMode' => $this->options->getEncodedOption('messages_time_mode', 'elapsed'),
					'dateFormat' => trim($this->options->getEncodedOption('messages_date_format')),
					'timeFormat' => trim($this->options->getEncodedOption('messages_time_format')),
					'senderMode' => $this->options->getIntegerOption('link_wp_user_name', 0),
					'links' => $this->options->isOptionEnabled('allow_post_links', true),
					'attachments' => $this->options->isOptionEnabled('enable_attachments_uploader', true),
					'images' => $this->options->isOptionEnabled('allow_post_images', true),
					'yt' => $this->options->isOptionEnabled('enable_youtube', true),
					'ytWidth' => $this->options->getIntegerOption('youtube_width', 186),
					'ytHeight' => $this->options->getIntegerOption('youtube_height', 105),
					'tt' => $this->options->isOptionEnabled('enable_twitter_hashtags', true)
				),
				'input' => array(
					'userName' => $this->options->isOptionEnabled('show_user_name'),
					'submit' => $this->options->isOptionEnabled('show_message_submit_button', true),
					'multiline' => $this->options->isOptionEnabled('multiline_support'),
					'multilineEasy' => $this->options->isOptionEnabled('multiline_easy_mode', false),
					'maxLength' => $this->options->getIntegerOption('message_max_length', 100),
					'emoticons' => array(
						'enabled' => $this->options->isOptionEnabled('show_emoticon_insert_button', true),
						'set' => $this->options->getIntegerOption('emoticons_enabled', 1),
						'size' => $this->options->getIntegerOption('emoticons_size', 32),
						'baseURL' => $this->options->getEmoticonsBaseURL(),
					),
					'images' => array(
						'enabled' => $this->options->isOptionEnabled('enable_images_uploader', true),
						'sizeLimit' => $this->options->getIntegerOption('images_size_limit', 3145728),
					),
					'attachments' => array(
						'enabled' => $this->options->isOptionEnabled('enable_attachments_uploader', true),
						'extensionsList' => $this->attachmentsService->getAllowedExtensionsList(),
						'validFileFormats' => $this->attachmentsService->getAllowedFormats(),
						'sizeLimit' => $this->attachmentsService->getSizeLimit()
					),
				),
				'customization' => array(
					'userNameLengthLimit' => $this->options->getIntegerOption('user_name_length_limit', 25),
				),
				'browser' => array(
					'enabled' => $this->options->isOptionEnabled('show_users', true),
					'searchSubChannels' => $this->options->isOptionEnabled('show_users_list_search_box', true),
					'location' => $this->options->getEncodedOption('browser_location') === 'left' ? 'left' : 'right',
					'status' => $this->options->isOptionEnabled('show_users_online_offline_mark', true),
				),
				'counter' => array(
					'onlineUsers' => $this->options->isOptionEnabled('show_users_counter', false)
				)
			),
			'engines' => array(
				'ajax' => array(
					'apiEndpointBase' => $this->getEndpointBase(),
					'apiMessagesEndpointBase' => $this->getMessagesEndpointBase(),
					'apiWPEndpointBase' => $this->getWPEndpointBase(),
					'refresh' => intval($this->options->getEncodedOption('messages_refresh_time', 3000)),
				)
			),
			'rights' => array(
				'receiveMessages' => !$this->options->isOptionEnabled('write_only', false), // TODO: review
			),

			'notifications' => array(
				'newMessage' => array(
					'title' => $this->options->isOptionEnabled('enable_title_notifications'),
					'sound' => $this->options->getEncodedOption('sound_notification'),
				),
				'userLeft' => array(
					'sound' => $this->options->getEncodedOption('leave_sound_notification'),
					'browserHighlight' => $this->options->isOptionEnabled('enable_leave_notification', true),
				),
				'userJoined' => array(
					'sound' => $this->options->getEncodedOption('join_sound_notification'),
					'browserHighlight' => $this->options->isOptionEnabled('enable_join_notification', true),
				),
				'mentioned' => array(
					'sound' => $this->options->getEncodedOption('mentioning_sound_notification'),
				)
			),

			'i18n' => array(
				'loadingChat' => $this->options->getOption('message_loading_chat', __('Loading the chat ...', 'xen-chat')),
				'loading' => $this->options->getOption('message_loading', __('Loading ...', 'xen-chat')),
				'sending' => $this->options->getOption('message_sending', __('Sending ...', 'xen-chat')),
				'send' => $this->options->getOption('message_submit_button_caption', __('Send', 'xen-chat')),
				'hint' => $this->options->getOption('hint_message'),
				'customize' => $this->options->getOption('message_customize', __('Customize', 'xen-chat')),
				'secAgo' => $this->options->getOption('message_sec_ago', __('sec. ago', 'xen-chat')),
				'minAgo' => $this->options->getOption('message_min_ago', __('min. ago', 'xen-chat')),
				'yesterday' => $this->options->getOption('message_yesterday', __('yesterday', 'xen-chat')),
				'insertIntoMessage' => $this->options->getOption('message_insert_into_message', __('Insert into message', 'xen-chat')),
				'users' => $this->options->getOption('message_users', __('Users', 'xen-chat')),
				'channels' => $this->options->getOption('message_channels', __('Channels', 'xen-chat')),
				'channel' => $this->options->getOption('message_channel', __('Channel', 'xen-chat')),
				'recent' => $this->options->getOption('message_recent', __('Recent', 'xen-chat')),
				'chats' => $this->options->getOption('message_chats', __('Chats', 'xen-chat')),
				'noChannels' => $this->options->getOption('message_no_channels', __('No channels open.', 'xen-chat')),
				'enterUserName' => $this->options->getOption('message_enter_user_name', __('Enter your username', 'xen-chat')),
				'logIn' => $this->options->getOption('message_login', __('Log in', 'xen-chat')),
				'onlineUsers' => $this->options->getOption('message_online_users', __('Online users', 'xen-chat')),
			)
		);
		
		$templater = new XenChatTemplater($this->options->getPluginBaseDir());
		$templater->setTemplateFile('/templates/main-react.tpl');

		$data = array(
			'chatId' => $chatId,
			'title' => $this->options->getOption('window_title', ''),
			'themeClassName' => 'wc'.ucfirst($this->options->getEncodedOption('theme', 'lightgray')).'Theme',
			'loading' => $this->options->getEncodedOption('message_loading_chat', __('Loading the chat ...', 'xen-chat')),
			'classicMode' => true,
			'baseDir' => $this->options->getBaseDir(),
			'jsOptionsEncoded' => htmlspecialchars(json_encode($jsOptions), ENT_QUOTES, 'UTF-8'),
			'cssDefinitions' => $this->cssRenderer->getCssDefinition($chatId),
			'customCssDefinitions' => $this->cssRenderer->getCustomCssDefinition()
		);

		return $templater->render($data);
	}

    /**
     * @return string
     */
    private function getCheckSum() {
		$checkSumData = is_array($this->shortCodeOptions) ? $this->shortCodeOptions : array();

        return base64_encode(XenChatCrypt::encryptToString(serialize($checkSumData)));
    }

    /**
     * @return string
     */
	private function getEndpointBase() {
		$endpointBase = get_site_url().'/wp-admin/admin-ajax.php';
		if (in_array($this->options->getEncodedOption('ajax_engine', null), array('lightweight', 'ultralightweight'))) {
			$endpointBase = plugin_dir_url(__FILE__).'endpoints/';
		}
		
		return $endpointBase;
	}

	/**
	 * @return string
	 */
	private function getMessagesEndpointBase() {
		if ($this->options->getEncodedOption('ajax_engine', null) === 'ultralightweight') {
			$endpointBase = plugin_dir_url(__FILE__).'endpoints/ultra/index.php';
		} else {
			$endpointBase = $this->getEndpointBase();
		}
		return $endpointBase;
	}

 	/**
     * @return string
     */
	private function getWPEndpointBase() {
		return get_site_url().'/wp-admin/admin-ajax.php';
	}
}