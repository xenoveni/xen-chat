<?php

/**
 * XenChat client side utilities.
 *
 * @author Kainex <contact@kainex.pl>
 */
class XenChatClientSide {

	/**
	 * @var XenChatOptions
	 */
	protected $options;

	/**
	 * @var XenChatMessagesService
	 */
	private $messagesService;

	public function __construct() {
		XenChatContainer::load('XenChatCrypt');

		$this->messagesService = XenChatContainer::getLazy('services/XenChatMessagesService');
		$this->options = XenChatOptions::getInstance();
	}

	/**
	 * @param $user
	 * @return string
	 */
	public function getUserCacheId($user) {
		return $this->getInstanceId().'_'.XenChatCrypt::encryptToString($user->getId());
	}

	/**
	 * Get chat's instance ID.
	 *
	 * @return string
	 */
	public function getInstanceId() {
		return sha1(serialize($this->options->getOption('channel')));
	}

	/**
	 * @param integer $id
	 * @return string
	 */
	public function encryptUserId($id) {
		return XenChatCrypt::encryptToString($id);
	}

	/**
	 * @param integer $id
	 * @return string
	 */
	public function encryptDirectChannelId($id) {
		return XenChatCrypt::encryptToString('d|'.$id);
	}

	/**
	 * @param integer $id
	 * @return string
	 */
	public function encryptMessageId($id) {
		return XenChatCrypt::encryptToString($id);
	}

	/**
	 * @param integer[] $ids
	 * @return string[]
	 */
	public function encryptMessageIds($ids) {
		return array_map(function($id) {
			return XenChatCrypt::encryptToString($id);
		}, $ids);
	}

	/**
	 * @param string $encryptedId
	 * @return integer
	 */
	public function decryptMessageId($encryptedId) {
		return intval(XenChatCrypt::decryptFromString($encryptedId));
	}

	/**
	 * Decrypts the message ID and loads the message.
	 *
	 * @param string $encryptedMessageId
	 * @return XenChatMessage
	 * @throws Exception If the message does not exist
	 */
	public function getMessageOrThrowException($encryptedMessageId) {
		$message = $this->messagesService->getById($this->decryptMessageId($encryptedMessageId));
		if ($message === null) {
			throw new \Exception('The message does not exist');
		}

		return $message;
	}

}