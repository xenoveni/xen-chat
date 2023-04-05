<?php

/**
 * Xen Chat rendering class.
 *
 * @author Kainex <contact@kainex.pl>
 */
class XenChatRenderer {
	
	/**
	* @var XenChatMessagesService
	*/
	private $messagesService;
	
	/**
	* @var XenChatChannelUsersDAO
	*/
	private $channelUsersDAO;
	
	/**
	* @var XenChatOptions
	*/
	private $options;
	
	/**
	* @var XenChatTemplater
	*/
	private $templater;
	
	public function __construct() {
		$this->options = XenChatOptions::getInstance();
		$this->messagesService = XenChatContainer::get('services/XenChatMessagesService');
		$this->channelUsersDAO = XenChatContainer::get('dao/XenChatChannelUsersDAO');
		XenChatContainer::load('rendering/XenChatTemplater');
		$this->templater = new XenChatTemplater($this->options->getPluginBaseDir());
	}
	
	/**
	* Returns rendered channel statistics.
	*
	* @param XenChatChannel $channel
	*
	* @return string HTML source
	*/
	public function getRenderedChannelStats($channel) {
		if ($channel === null) {
			return 'ERROR: channel does not exist';
		}

		$variables = array(
			'channel' => $channel->getName(),
			'messages' => $this->messagesService->getNumberByChannelName($channel->getName())
		);
	
		return $this->getTemplatedString($variables, $this->options->getOption('template', 'ERROR: TEMPLATE NOT SPECIFIED'));
	}
	
	public function getTemplatedString($variables, $template, $encodeValues = true) {
		foreach ($variables as $key => $value) {
			$template = str_replace("{".$key."}", $encodeValues ? urlencode($value) : $value, $template);
		}
		
		return $template;
	}

}