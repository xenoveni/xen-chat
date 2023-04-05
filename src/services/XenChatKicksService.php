<?php

/**
 * XenChat kicks services.
 *
 * @author Kainex <contact@kainex.pl>
 */
class XenChatKicksService {

	/**
	 * @var XenChatActions
	 */
	protected $actions;

	/**
	 * @var XenChatKicksDAO
	 */
	private $kicksDAO;

	/**
	 * @var XenChatUsersDAO
	 */
	private $usersDAO;

	/**
	 * @var XenChatMessagesDAO
	 */
	private $messagesDAO;

	/**
	 * @var XenChatOptions
	 */
	private $options;

	public function __construct() {
		XenChatContainer::load('model/XenChatKick');
		$this->options = XenChatOptions::getInstance();
		$this->kicksDAO = XenChatContainer::getLazy('dao/XenChatKicksDAO');
		$this->messagesDAO = XenChatContainer::getLazy('dao/XenChatMessagesDAO');
		$this->usersDAO = XenChatContainer::getLazy('dao/user/XenChatUsersDAO');
		$this->actions = XenChatContainer::getLazy('services/user/XenChatActions');
	}

	/**
	 * Kicks the user by message ID.
	 *
	 * @param integer $messageId
	 *
	 * @throws Exception If the message or user was not found
	 */
	public function kickByMessageId($messageId) {
		$message = $this->messagesDAO->get($messageId);
		if ($message === null) {
			throw new Exception('Message was not found');
		}

		$user = $this->usersDAO->get($message->getUserId());
		if ($user !== null) {
			$this->kickIpAddress($user->getIp(), $user->getName());
			$this->actions->publishAction('reload', array(), $user);

			return;
		}

		throw new Exception('User was not found');
	}

	/**
	 * Creates and saves a new kick on IP address if the IP was not kicked previously.
	 *
	 * @param string $ip Given IP address
	 * @param string $userName
	 *
	 * @return boolean Returns true the kick was created
	 */
	public function kickIpAddress($ip, $userName) {
		if ($this->kicksDAO->getByIp($ip) === null) {
			$kick = new XenChatKick();
			$kick->setCreated(time());
			$kick->setLastUserName($userName);
			$kick->setIp($ip);
			$this->kicksDAO->save($kick);

			return true;
		}

		return false;
	}

	/**
	 * Checks if given IP address is kicked,
	 *
	 * @param string $ip
	 * @return bool
	 */
	public function isIpAddressKicked($ip) {
		return $this->kicksDAO->getByIp($ip) !== null;
	}

}
