<?php

/**
 * XenChat Elementor integration class.
 *
 * @author Kainex <contact@kainex.pl>
 */
class XenChatElementor {

	public function register($widgetsManager) {
		$widgetsManager->register(XenChatContainer::get('integrations/elementor/addons/XenChatAddon'));
	}

}