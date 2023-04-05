<?php

/**
 * XenChat actions service.
 */
class XenChatActions {
    /**
     * @var XenChatActionsDAO
     */
    private $actionsDAO;

    /**
     * XenChatActions constructor.
     */
    public function __construct() {
        $this->actionsDAO = XenChatContainer::get('dao/XenChatActionsDAO');
    }

	/**
	 * @return int|null
	 */
    public function getLastActionId() {
	    $lastAction = $this->actionsDAO->getLast();

	    return $lastAction !== null ? $lastAction->getId() : null;
    }

    /**
     * Publishes the action in the queue. If the user is not specified the action is public.
     * Otherxen it is directed to the specified user.
     *
     * @param string $name Name of the action
     * @param array $commandData Data of the action
     * @param XenChatUser $user Recipient of the action

     * @throws Exception
     */
    public function publishAction($name, $commandData, $user = null) {
        $name = trim($name);
        if (strlen($name) === 0) {
            throw new Exception('Action name cannot be empty');
        }

        $action = new XenChatAction();
        $action->setCommand(array(
            'name' => $name,
            'data' => $commandData
        ));
        $action->setTime(time());
        if ($user !== null) {
            $action->setUserId($user->getId());
        }
        $this->actionsDAO->save($action);
    }

    /**
     * Returns actions of the user and beginning from specified ID and (optionally) by user.
     * The result array is JSON ready. Some of the fields are hidden and command is decoded to array.
     *
     * @param integer $fromId Offset
     * @param XenChatUser $user Actions directed to the specific user
     *
     * @return array
     */
    public function getJSONReadyActions($fromId, $user) {
        $actions = $this->actionsDAO->getBeginningFromIdAndByUser($fromId, $user !== null ? $user->getId() : null);
        $actionsCommands = array();
        foreach ($actions as $action) {
            $actionsCommands[] = array(
                'id' => $action->getId(),
                'command' => $action->getCommand()
            );
        }

        return $actionsCommands;
    }
}