<?php

/**
 * Xen Chat user authorization service.
 */
class XenChatAuthorization {
    const PROPERTY_NAME = 'channel_authorization';

    /**
     * @var XenChatUserService
     */
    private $userService;

    /**
     * @var XenChatOptions
     */
    private $options;

    /**
     * XenChatAuthorization constructor.
     */
    public function __construct() {
        $this->options = XenChatOptions::getInstance();
        $this->userService = XenChatContainer::get('services/user/XenChatUserService');
    }

	/**
	 * Determines whether the current user is authorized to access the channel.
	 *
	 * @param XenChatChannel $channel
	 *
	 * @return boolean
	 * @throws Exception
	 */
    public function isUserAuthorizedForChannel($channel) {
    	if (strlen($channel->getPassword()) === 0) {
    		return true;
	    }

        $grants = $this->userService->getProperty(self::PROPERTY_NAME);

        return is_array($grants) && array_key_exists($channel->getId(), $grants) && $grants[$channel->getId()] === $channel->getPassword();
    }

	/**
	 * Grants access to the channel for the current user.
	 *
	 * @param XenChatChannel $channel
	 * @throws Exception
	 */
    public function markAuthorizedForChannel($channel) {
        $grants = $this->userService->getProperty(self::PROPERTY_NAME);
        if (!is_array($grants)) {
            $grants = array();
        }

        $grants[$channel->getId()] = $channel->getPassword();
        $this->userService->setProperty(self::PROPERTY_NAME, $grants);
    }
}