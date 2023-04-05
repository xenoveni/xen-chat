<?php

/**
 * XenChat command: /ban [userName] [duration]
 *
 * @author Kainex <contact@kainex.pl>
 */
class XenChatBanCommand extends XenChatAbstractCommand {
	public function execute() {
		$userName = isset($this->arguments[0]) ? $this->arguments[0] : null;
        if ($userName === null) {
            $this->addMessage('Please specify the user');
            return;
        }
		
        $user = $this->usersDAO->getLatestByName($userName);
        if ($user === null) {
            $this->addMessage('User was not found');
            return;
        }

        $duration = $this->bansService->getDurationFromString($this->arguments[1]);
        if ($this->bansService->banIpAddress($user->getIp(), $duration)) {
            $this->addMessage("IP " . $user->getIp() . " has been banned, time: {$duration} seconds");
        } else {
            $this->addMessage("IP " . $user->getIp() . " is already banned");
        }
	}
}