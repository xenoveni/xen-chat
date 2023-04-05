<?php

/**
 * Class for loading channels.
 *
 * @author Kainex <contact@kainex.pl>
 */
class XenChatMaintenanceChannels {

	/** @var XenChatChannelsSourcesService */
	private $channelsSourcesService;

	/**
	 * @var XenChatClientSide
	 */
	protected $clientSide;

	/**
	 * @var XenChatChannelUsersDAO
	 */
	protected $channelUsersDAO;

	/**
	 * @var XenChatAuthentication
	 */
	protected $authentication;

	/**
	 * @var XenChatAuthorization
	 */
	protected $authorization;

	/**
	 * @var XenChatUserService
	 */
	protected $userService;

	/**
	 * @var XenChatService
	 */
	protected $service;

	/**
	 * @var XenChatUsersDAO
	 */
	protected $usersDAO;

	/**
	 * @var XenChatRenderer
	 */
	protected $renderer;

	/**
	 * @var XenChatOptions
	 */
	protected $options;

	public function __construct() {
		$this->options = XenChatOptions::getInstance();
		$this->authentication = XenChatContainer::getLazy('services/user/XenChatAuthentication');
		$this->authorization = XenChatContainer::getLazy('services/user/XenChatAuthorization');
		$this->channelUsersDAO = XenChatContainer::getLazy('dao/XenChatChannelUsersDAO');
		$this->userService = XenChatContainer::getLazy('services/user/XenChatUserService');
		$this->service = XenChatContainer::getLazy('services/XenChatService');
		$this->usersDAO = XenChatContainer::getLazy('dao/user/XenChatUsersDAO');
		$this->renderer = XenChatContainer::getLazy('rendering/XenChatRenderer');
		$this->clientSide = XenChatContainer::getLazy('services/client-side/XenChatClientSide');
		$this->channelsSourcesService = XenChatContainer::getLazy('services/channels/listing/XenChatChannelsSourcesService');
	}

	/**
	 * @return XenChatChannel[]
	 * @throws Exception
	 */
	public function getPublicChannels() {
		$result = array();
		$channels = $this->channelsSourcesService->getPublicChannels();
		$isChatFull = $this->service->isChatFull(); // TODO: "full" channel convert to "full" chat (including direct channels)

		foreach ($channels as $channel) {
			$result[] = array(
				'id' => XenChatCrypt::encryptToString('c|'.$channel->getId()),
				'readOnly' => !$this->userService->isSendingMessagesAllowed(),
				'type' => 'public',
				'name' => $channel->getName(),
				'avatar' => $this->options->getIconsURL().'public-channel.png',
				'full' => $isChatFull,
				'protected' => $this->isProtectedChannel($channel),
				'authorized' => $this->authorization->isUserAuthorizedForChannel($channel)
			);
		}

		return $result;
	}

	/**
	 * @return array
	 */
	public function getDirectChannels() {
		$plainUsers = [];
		$userId = $this->authentication->getUserIdOrNull();
		$channelUsers = $this->channelsSourcesService->getDirectChannels();

		foreach ($channelUsers as $channelUser) {
			// text color defined by role:
			$textColor = $this->userService->getTextColorDefinedByUserRole($channelUser->getUser());

			// custom text color:
			if ($this->options->isOptionEnabled('allow_change_text_color', true)) {
				$textColorProposal = $channelUser->getUser()->getDataProperty('textColor');
				if (strlen($textColorProposal) > 0) {
					$textColor = $textColorProposal;
				}
			}

			// avatar:
			$avatarSrc = $this->options->isOptionEnabled('show_users_list_avatars', true) ? $this->userService->getUserAvatar($channelUser->getUser()) : null;

			$isCurrentUser =  $userId === $channelUser->getUserId();

			// add roles as css classes:
			$roleClasses = $this->options->isOptionEnabled('css_classes_for_user_roles', false) ? $this->userService->getCssClassesForUserRoles($channelUser->getUser(), $wpUser) : null;

			$countryFlagSrc = null;
			$countryCode = null;
			$country = null;
			$city = null;

			if ($this->options->isOptionEnabled('collect_user_stats', false) && $this->options->isOptionEnabled('show_users_flags', false)) {
				$countryCode = $channelUser->getUser()->getDataProperty('countryCode');
				$country = $channelUser->getUser()->getDataProperty('country');
				if (strlen($countryCode) > 0) {
					$countryFlagSrc = $this->options->getFlagURL(strtolower($countryCode));
				}
			}
			if ($this->options->isOptionEnabled('collect_user_stats', false) && $this->options->isOptionEnabled('show_users_city_and_country', false)) {
				$city = $channelUser->getUser()->getDataProperty('city');
				$countryCode = $channelUser->getUser()->getDataProperty('countryCode');
			}

			$isAllowed = false;
			$url = null;
			if ($this->options->isOptionEnabled('users_list_linking', false)) {
				$url = $this->userService->getUserProfileLink($channelUser->getUser(), $channelUser->getUser()->getName(), $channelUser->getUser()->getWordPressId());
			}

			$plainUser = [
				'id' => $this->clientSide->encryptDirectChannelId($channelUser->getUser()->getId()),
				'name' => $channelUser->getUser()->getName(),
				'type' => 'direct',
				'readOnly' => true,
				'url' => $url,
				'textColor' => $textColor,
				'avatar' => $avatarSrc,
				'locked' => $isCurrentUser,
				'classes' => $roleClasses,
				'online' => $channelUser->isActive(),
				'countryCode' => $countryCode,
				'country' => $country,
				'city' => $city,
				'countryFlagSrc' => $countryFlagSrc
			];

			$plainUsers[$channelUser->getUser()->getId()] = $plainUser;
		}

		return array_values($plainUsers);
	}

	/**
	 * TODO: optimize
	 *
	 * @return integer
	 */
	public function getDirectChannelsNumber() {
		$plainUsers = [];
		$channelUsers = $this->channelsSourcesService->getDirectChannels();
		foreach ($channelUsers as $channelUser) {
			if ($channelUser->isActive()) {
				$plainUsers[$channelUser->getUser()->getId()] = true;
			}
		}

		return count($plainUsers);
	}

	/**
	 * @param XenChatChannel $channel
	 * @return bool
	 */
	private function isProtectedChannel($channel) {
		return $channel !== null && strlen($channel->getPassword()) > 0;
	}
}