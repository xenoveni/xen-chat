<?php

/**
 * XenChat bans services.
 *
 * @author Kainex <contact@kainex.pl>
 */
class XenChatChannelsService {

	const PRIVATE_MESSAGES_CHANNEL = '__private';

	/**
	 * @var XenChatChannelsDAO
	 */
	private $channelsDAO;

	/**
	 * @var XenChatOptions
	 */
	private $options;

	public function __construct() {
		XenChatContainer::load('model/XenChatChannel');

		$this->options = XenChatOptions::getInstance();
		$this->channelsDAO = XenChatContainer::getLazy('dao/XenChatChannelsDAO');
	}

	/**
	 * @param integer[] $channelIds
	 * @return XenChatChannel[]
	 * @throws Exception If a channel cannot be found
	 */
	public function getChannelsByIds($channelIds) {
		$channels = array();

		foreach ($channelIds as $channelId) {
			$requestedChannel = $this->channelsDAO->get($channelId);
			if ($requestedChannel === null) {
				throw new Exception('The channel does not exist: '.$channelId);
			}
			$channels[] = $requestedChannel;
		}

		return $channels;
	}

}