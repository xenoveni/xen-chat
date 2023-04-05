<?php

/**
 * XenChat abstract command.
 *
 * @author Kainex <contact@kainex.pl>
 */
abstract class XenChatAbstractCommand {

	/**
	* @var XenChatChannel
	*/
	protected $channel;
	
	/**
	* @var string
	*/
	protected $arguments;
	
	/**
	* @var XenChatMessagesDAO
	*/
	protected $messagesDAO;
	
	/**
	* @var XenChatUsersDAO
	*/
	protected $usersDAO;
	
	/**
	* @var XenChatChannelUsersDAO
	*/
	protected $channelUsersDAO;
	
	/**
	* @var XenChatBansDAO
	*/
	protected $bansDAO;

	/**
	 * @var XenChatAuthentication
	 */
	protected $authentication;

	/**
	 * @var XenChatBansService
	 */
	protected $bansService;

	/**
	 * @var XenChatMessagesService
	 */
	private $messagesService;

	/**
	 * @param XenChatChannel $channel
	 * @param array $arguments
	 */
	public function __construct($channel, $arguments) {
		$this->messagesDAO = XenChatContainer::get('dao/XenChatMessagesDAO');
		$this->bansDAO = XenChatContainer::get('dao/XenChatBansDAO');
		$this->usersDAO = XenChatContainer::get('dao/user/XenChatUsersDAO');
		$this->channelUsersDAO = XenChatContainer::get('dao/XenChatChannelUsersDAO');
		$this->authentication = XenChatContainer::getLazy('services/user/XenChatAuthentication');
		$this->bansService = XenChatContainer::get('services/XenChatBansService');
		$this->messagesService = XenChatContainer::get('services/XenChatMessagesService');
		$this->arguments = $arguments;
		$this->channel = $channel;
	}
	
	protected function addMessage($message) {
		$this->messagesService->addMessage($this->authentication->getSystemUser(), $this->channel, $message, array(), true);
	}

    /**
     * Executes command using arguments.
     *
     * @return null
     */
    abstract public function execute();
}