<?php

/**
 * Shortcode that renders Xen Chat basic statistics for given channel.
 *
 * @author Kainex <contact@kainex.pl>
 */
class XenChatStatsShortcode {
    /**
     * @var XenChatOptions
     */
    private $options;

    /**
     * @var XenChatService
     */
    private $service;

    /**
     * @var XenChatMessagesService
     */
    private $messagesService;

    /**
     * @var XenChatChannelsDAO
     */
    private $channelsDAO;

    /**
     * @var XenChatRenderer
     */
    private $renderer;

    /**
     * XenChatStatsShortcode constructor.
     */
    public function __construct() {
        $this->options = XenChatOptions::getInstance();
        $this->service = XenChatContainer::get('services/XenChatService');
        $this->messagesService = XenChatContainer::get('services/XenChatMessagesService');
        $this->channelsDAO = XenChatContainer::get('dao/XenChatChannelsDAO');
        $this->renderer = XenChatContainer::get('rendering/XenChatRenderer');
    }

    /**
     * Renders shortcode: [xen-chat-channel-stats]
     *
     * @param array $attributes
     * @return string
     */
    public function getRenderedChannelStatsShortcode($attributes) {
        if (!is_array($attributes)) {
            $attributes = array();
        }

        $attributes['channel'] = $this->service->getValidChatChannelName(
            array_key_exists('channel', $attributes) ? $attributes['channel'] : ''
        );

        $channel = $this->channelsDAO->getByName($attributes['channel']);
        if ($channel !== null) {
            $this->options->replaceOptions($attributes);

            $this->messagesService->startUpMaintenance();

            return $this->renderer->getRenderedChannelStats($channel);
        } else {
            return 'ERROR: channel does not exist';
        }
    }
}