<?php

XenChatContainer::load('exceptions/XenChatUnauthorizedAccessException');

/**
 * Xen Chat base endpoints class
 *
 * @author Kainex <contact@kainex.pl>
 */
class XenChatEndpoint {

	/**
	 * @var XenChatClientSide
	 */
	protected $clientSide;

	/**
	 * @var XenChatMessagesDAO
	 */
	protected $messagesDAO;

	/**
	 * @var XenChatChannelsDAO
	 */
	protected $channelsDAO;

	/**
	 * @var XenChatUsersDAO
	 */
	protected $usersDAO;

	/**
	 * @var XenChatUserSettingsDAO
	 */
	protected $userSettingsDAO;

	/**
	 * @var XenChatChannelUsersDAO
	 */
	protected $channelUsersDAO;

	/**
	 * @var XenChatBansDAO
	 */
	protected $bansDAO;

	/**
	 * @var XenChatActions
	 */
	protected $actions;

	/**
	 * @var XenChatRenderer
	 */
	protected $renderer;

	/**
	 * @var XenChatBansService
	 */
	protected $bansService;

	/**
	 * @var XenChatKicksService
	 */
	protected $kicksService;

	/**
	 * @var XenChatMessagesService
	 */
	protected $messagesService;

	/**
	 * @var XenChatUserService
	 */
	protected $userService;

	/**
	 * @var XenChatService
	 */
	protected $service;

	/**
	 * @var XenChatChannelsService
	 */
	protected $channelsService;

	/**
	 * @var XenChatAuthentication
	 */
	protected $authentication;

	/**
	 * @var XenChatUserEvents
	 */
	protected $userEvents;

	/**
	 * @var XenChatAuthorization
	 */
	protected $authorization;

	/**
	 * @var XenChatHttpRequestService
	 */
	protected $httpRequestService;

	/**
	 * @var XenChatOptions
	 */
	protected $options;

	private $arePostSlashesStripped = false;

	public function __construct() {
		$this->options = XenChatOptions::getInstance();

		$this->authentication = XenChatContainer::getLazy('services/user/XenChatAuthentication');
		$this->userEvents = XenChatContainer::getLazy('services/user/XenChatUserEvents');
		$this->authorization = XenChatContainer::getLazy('services/user/XenChatAuthorization');
		$this->messagesDAO = XenChatContainer::get('dao/XenChatMessagesDAO');
		$this->usersDAO = XenChatContainer::getLazy('dao/user/XenChatUsersDAO');
		$this->userSettingsDAO = XenChatContainer::getLazy('dao/user/XenChatUserSettingsDAO');
		$this->channelUsersDAO = XenChatContainer::getLazy('dao/XenChatChannelUsersDAO');
		$this->actions = XenChatContainer::getLazy('services/user/XenChatActions');
		$this->channelsDAO = XenChatContainer::getLazy('dao/XenChatChannelsDAO');
		$this->bansDAO = XenChatContainer::getLazy('dao/XenChatBansDAO');
		$this->renderer = XenChatContainer::getLazy('rendering/XenChatRenderer');
		$this->bansService = XenChatContainer::getLazy('services/XenChatBansService');
		$this->kicksService = XenChatContainer::getLazy('services/XenChatKicksService');
		$this->messagesService = XenChatContainer::getLazy('services/XenChatMessagesService');
		$this->userService = XenChatContainer::getLazy('services/user/XenChatUserService');
		$this->service = XenChatContainer::getLazy('services/XenChatService');
		$this->channelsService = XenChatContainer::getLazy('services/XenChatChannelsService');
		$this->httpRequestService = XenChatContainer::getLazy('services/XenChatHttpRequestService');
		$this->clientSide = XenChatContainer::getLazy('services/client-side/XenChatClientSide');

		XenChatContainer::load('XenChatCrypt');
		XenChatContainer::load('services/user/XenChatUserService');
		XenChatContainer::load('services/XenChatChannelsService');
	}

	/**
	 * @param XenChatMessage $message
	 * @param $channelId
	 * @param $channelName
	 * @param array $attributes
	 * @return array
	 */
	protected function toPlainMessage($message, $channelId, $attributes = array()) {
		$textColorAffectedParts = (array)$this->options->getOption("text_color_parts", array('message', 'messageUserName'));
		$classes = '';
		$wpUser = $this->usersDAO->getWpUserByID($message->getWordPressUserId());
		if ($this->options->isOptionEnabled('css_classes_for_user_roles', false)) {
			$classes = $this->userService->getCssClassesForUserRoles($message->getUser(), $wpUser);
		}

		$messagePlain = array(
			'id' => $this->clientSide->encryptMessageId($message->getId()),
			'own' => $message->getUserId() === $this->authentication->getUserIdOrNull(),
			'text' => $message->getText(),
			'channel' => array(
				'id' => $channelId,
				'name' => $message->getChannelName(),
				'type' => 'public',
				'readOnly' => false
			),
			'color' => in_array('message', $textColorAffectedParts) ? $this->userService->getUserTextColor($message->getUser()) : null,
			'cssClasses' => $classes,
			'timeUTC' => gmdate('c', $message->getTime()),
			'sortKey' => $message->getTime().$message->getId(),
			'sender' => $this->getMessageSender($message, $wpUser)
		);

		$messagePlain = array_merge($messagePlain, $attributes);

		return $messagePlain;
	}

	private function getMessageSender($message, $wpUser) {
		$textColorAffectedParts = (array) $this->options->getOption("text_color_parts", array('message', 'messageUserName'));

		return array(
			'id' => $this->clientSide->encryptUserId($message->getUserId()),
			'name' => $message->getUserName(),
			'source' => $wpUser !== null ? 'w' : 'a',
			'current' => $this->authentication->getUser()->getId() == $message->getUserId(),
			'color' => in_array('messageUserName', $textColorAffectedParts) ? $this->userService->getUserTextColor($message->getUser()) : null,
			'profileUrl' => $this->options->getIntegerOption('link_wp_user_name', 0) === 1 ? $this->userService->getUserProfileLink($message->getUser(), $message->getUserName(), $message->getWordPressUserId()) : null,
			'avatarUrl' => $this->options->isOptionEnabled('show_avatars', true) ? $this->userService->getUserAvatarFromMessage($message) : null
		);
	}

	protected function getPostParam($name, $default = null) {
		if (!$this->arePostSlashesStripped) {
			$_POST = stripslashes_deep($_POST);
			$this->arePostSlashesStripped = true;
		}

		return array_key_exists($name, $_POST) ? $_POST[$name] : $default;
	}

	protected function getGetParam($name, $default = null) {
		return array_key_exists($name, $_GET) ? $_GET[$name] : $default;
	}

	protected function getParam($name, $default = null) {
		$getParam = $this->getGetParam($name);
		if ($getParam === null) {
			return $this->getPostParam($name, $default);
		}

		return $getParam;
	}

	/**
	 * @param array $params
	 * @throws Exception
	 */
	protected function checkGetParams($params) {
		foreach ($params as $param) {
			if ($this->getGetParam($param) === null) {
				throw new Exception('Required parameters are missing');
			}
		}
	}

	/**
	 * @param array $params
	 * @throws Exception
	 */
	protected function checkPostParams($params) {
		foreach ($params as $param) {
			if ($this->getPostParam($param) === null) {
				throw new Exception('Required parameters are missing');
			}
		}
	}

	/**
	 * Checks if user is authenticated.
	 *
	 * @throws XenChatUnauthorizedAccessException
	 */
	protected function checkUserAuthentication() {
		if (!$this->authentication->isAuthenticated()) {
			throw new XenChatUnauthorizedAccessException('Not authenticated');
		}
	}

	protected function confirmUserAuthenticationOrEndRequest() {
		if (!$this->authentication->isAuthenticated()) {
			$this->sendBadRequestStatus();
			die('{ }');
		}
	}

	/**
	 * @throws XenChatUnauthorizedAccessException
	 */
	protected function checkUserAuthorization() {
		if ($this->service->isChatRestrictedForAnonymousUsers()) {
			throw new XenChatUnauthorizedAccessException('Access denied');
		}
		if ($this->service->isChatRestrictedForCurrentUserRole()) {
			throw new XenChatUnauthorizedAccessException('Access denied');
		}
		if ($this->service->isChatRestrictedToCurrentUser()) {
			throw new XenChatUnauthorizedAccessException('Access denied');
		}
	}

	/**
	 * @throws XenChatUnauthorizedAccessException
	 */
	protected function checkIpNotKicked() {
		if (isset($_SERVER['REMOTE_ADDR']) && $this->kicksService->isIpAddressKicked($_SERVER['REMOTE_ADDR'])) {
			throw new XenChatUnauthorizedAccessException($this->options->getOption('message_error_12', __('You are blocked from using the chat', 'xen-chat')));
		}
	}

	/**
	 * @throws XenChatUnauthorizedAccessException
	 */
	protected function checkUserWriteAuthorization() {
		if (!$this->userService->isSendingMessagesAllowed()) {
			throw new XenChatUnauthorizedAccessException('No write permission');
		}
	}

	/**
	 * @throws Exception
	 */
	protected function checkChatOpen() {
		if (!$this->service->isChatOpen()) {
			throw new Exception($this->options->getEncodedOption('message_error_5', 'The chat is closed now'));
		}
	}

	/**
	 * @param XenChatChannel $channel
	 * @throws Exception
	 */
	protected function checkChannel($channel) {
		if ($channel === null) {
			throw new Exception('Channel does not exist');
		}
	}

	/**
	 * @param XenChatChannel $channel
	 * @throws XenChatUnauthorizedAccessException
	 * @throws Exception
	 */
	protected function checkChannelAuthorization($channel) {
		if (!$this->authorization->isUserAuthorizedForChannel($channel)) {
			throw new XenChatUnauthorizedAccessException('Not authorized in this channel');
		}
	}

	protected function generateCheckSum() {
		$checksum = $this->getParam('checksum');
		if ($checksum !== null) {
			$decoded = unserialize(XenChatCrypt::decryptFromString(base64_decode($checksum)));
			if (is_array($decoded)) {
				$decoded['ts'] = time();

				return base64_encode(XenChatCrypt::encryptToString(serialize($decoded)));
			}
		}
		return null;
	}

	protected function verifyCheckSum() {
		$checksum = $this->getParam('checksum');

		if ($checksum !== null) {
			$decoded = unserialize(XenChatCrypt::decryptFromString(base64_decode($checksum)));
			if (is_array($decoded)) {
				$timestamp = array_key_exists('ts', $decoded) ? $decoded['ts'] : time();
				$validityTime = $this->options->getIntegerOption('ajax_validity_time', 1440) * 60;
				if ($timestamp + $validityTime < time()) {
					$this->sendNotFoundStatus();
					die();
				}

				$this->options->replaceOptions($decoded);
			}
		}
	}

	protected function verifyXhrRequest() {
		if (!$this->options->isOptionEnabled('enabled_xhr_check', true)) {
			return true;
		}
		if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
			return true;
		} else {
			$this->sendNotFoundStatus();
			die();
		}
	}

	protected function checkUserRight($rightName) {
		if (!$this->usersDAO->hasCurrentWpUserRight($rightName) && !$this->usersDAO->hasCurrentBpUserRight($rightName)) {
			throw new XenChatUnauthorizedAccessException('Not enough privileges to execute this request');
		}
	}

	/**
	 * @param string $encryptedChannelId
	 * @return XenChatChannel|null
	 * @throws Exception
	 */
	protected function getChannelFromEncryptedId($encryptedChannelId) {
		$channelTypeAndId = XenChatCrypt::decryptFromString($encryptedChannelId);
		if ($channelTypeAndId === null) {
			throw new Exception('Invalid channel');
		}

		if (strpos($channelTypeAndId, 'c|') !== false) {
			$channel = $this->channelsDAO->get(intval(str_replace('c|', '', $channelTypeAndId)));
			if ($channel && $channel->getName() === XenChatChannelsService::PRIVATE_MESSAGES_CHANNEL) {
				throw new Exception('Unknown channel ID');
			}
		} else {
			throw new Exception('Unknown channel');
		}

		return $channel;
	}

	protected function sendBadRequestStatus() {
		header('HTTP/1.0 400 Bad Request', true, 400);
	}

	protected function sendUnauthorizedStatus() {
		header('HTTP/1.0 401 Unauthorized', true, 401);
	}

	protected function sendNotFoundStatus() {
		header('HTTP/1.0 404 Not Found', true, 404);
	}

	protected function jsonContentType() {
		header('Content-Type: application/json; charset='.get_option('blog_charset'));
	}
}