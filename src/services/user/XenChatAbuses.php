<?php

/**
 * Xen Chat user abuses
 */
class XenChatAbuses {
    const PROPERTY_NAME = 'ban_detector_counter';

    /**
     * @var XenChatUserService
     */
    private $userService;

    /**
     * XenChatAbuses constructor.
     */
    public function __construct() {
        $this->userService = XenChatContainer::get('services/user/XenChatUserService');
    }

    /**
     * Increments and returns the abuses counter.
     *
     * @return integer
     */
    public function incrementAndGetAbusesCounter() {
        $counter = $this->userService->getProperty(self::PROPERTY_NAME);
        if ($counter === null) {
            $counter = 0;
        }
        $counter++;

        $this->userService->setProperty(self::PROPERTY_NAME, $counter);

        return $counter;
    }

    /**
     * Clears the abuses counter.
     *
     * @return null
     */
    public function clearAbusesCounter() {
        $this->userService->setProperty(self::PROPERTY_NAME, 0);
    }
}