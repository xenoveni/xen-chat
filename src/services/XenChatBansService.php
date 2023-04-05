<?php

/**
 * XenChat bans services.
 *
 * @author Kainex <contact@kainex.pl>
 */
class XenChatBansService {

	/**
	* @var XenChatBansDAO
	*/
	private $bansDAO;

	/**
	 * @var XenChatUsersDAO
	 */
	private $usersDAO;
	
	/**
	* @var XenChatMessagesDAO
	*/
	private $messagesDAO;
	
	/**
	* @var XenChatChannelUsersDAO
	*/
	private $channelUsersDAO;
	
	/**
	* @var XenChatOptions
	*/
	private $options;
	
	public function __construct() {
		XenChatContainer::load('model/XenChatBan');
		$this->options = XenChatOptions::getInstance();
		$this->bansDAO = XenChatContainer::getLazy('dao/XenChatBansDAO');
		$this->messagesDAO = XenChatContainer::getLazy('dao/XenChatMessagesDAO');
		$this->channelUsersDAO = XenChatContainer::getLazy('dao/XenChatChannelUsersDAO');
		$this->usersDAO = XenChatContainer::getLazy('dao/user/XenChatUsersDAO');
	}
	
	/**
	* Maintenance actions performed periodically.
	*/
	public function periodicMaintenance() {
		$this->bansDAO->deleteOlder(time());
	}
	
	/**
	* Bans user by message ID.
	*
	* @param integer $messageId
	* @param string $durationString
	*
	* @throws Exception If the message is not found
	*/
	public function banByMessageId($messageId, $durationString = '1d') {
		$message = $this->messagesDAO->get($messageId);
		if ($message === null) {
			throw new Exception('Message was not found');
		}

		$user = $this->usersDAO->get($message->getUserId());
		if ($user !== null) {
			$duration = $this->getDurationFromString($durationString);
			$this->banIpAddress($user->getIp(), $duration);
		}
	}

	/**
	 * Creates and saves a new ban on IP address if the IP was not banned previously.
	 *
	 * @param string $ip Given IP address
	 * @param integer $duration Duration of the ban (in seconds)
	 *
	 * @return boolean Returns true the ban was created
	 */
	public function banIpAddress($ip, $duration) {
		if ($this->bansDAO->getByIp($ip) === null) {
			$ban = new XenChatBan();
			$ban->setCreated(time());
			$ban->setTime(time() + $duration);
			$ban->setIp($ip);
			$this->bansDAO->save($ban);

			return true;
		}

		return false;
	}

    /**
     * Checks if given IP address is banned,
     *
     * @param string $ip
     * @return bool
     */
    public function isIpAddressBanned($ip) {
        return $this->bansDAO->getByIp($ip) !== null;
    }

	/**
	 * Converts duration string into amount of seconds.
	 * If the value cannot be determined the default value is returned.
	 *
	 * @param string $durationString Eg. 1h, 2d, 7m
	 * @param integer $defaultValue One hour
	 *
	 * @return integer
	 */
	public function getDurationFromString($durationString, $defaultValue = 3600) {
		$duration = $defaultValue;

		if (strlen($durationString) > 0) {
			if (preg_match('/\d+m/', $durationString)) {
				$duration = intval($durationString) * 60;
			}
			if (preg_match('/\d+h/', $durationString)) {
				$duration = intval($durationString) * 60 * 60;
			}
			if (preg_match('/\d+d/', $durationString)) {
				$duration = intval($durationString) * 60 * 60 * 24;
			}

			if ($duration === 0) {
				$duration = $defaultValue;
			}
		}

		return $duration;
	}
}