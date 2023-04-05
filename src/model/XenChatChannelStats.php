<?php

/**
 * XenChat channel statistics model.
 */
class XenChatChannelStats {
    /**
     * @var integer
     */
    private $channelId;

    /**
     * @var XenChatChannel
     */
    private $channel;

    /**
     * @var integer
     */
    private $numberOfUsers;

    /**
     * @return integer
     */
    public function getChannelId() {
        return $this->channelId;
    }

    /**
     * @param integer $channelId
     */
    public function setChannelId($channelId) {
        $this->channelId = $channelId;
    }

    /**
     * @return XenChatChannel
     */
    public function getChannel() {
        return $this->channel;
    }

    /**
     * @param XenChatChannel $channel
     */
    public function setChannel($channel) {
        $this->channel = $channel;
    }

    /**
     * @return integer
     */
    public function getNumberOfUsers() {
        return $this->numberOfUsers;
    }

    /**
     * @param integer $numberOfUsers
     */
    public function setNumberOfUsers($numberOfUsers) {
        $this->numberOfUsers = $numberOfUsers;
    }
}