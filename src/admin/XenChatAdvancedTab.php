<?php 

/**
 * Xen Chat admin advanced settings tab class.
 *
 * @author Kainex <contact@kainex.pl>
 */
class XenChatAdvancedTab extends XenChatAbstractTab {

	public function getFields() {
		return array(
			array('_section', 'User Authentication'),
			array(
				'user_auth_expiration_days', 'Expiration Time (in days)', 'stringFieldCallback', 'integer',
				'The authentication cookie timeout. After the timeout is reached the authentication cookie is deleted and user authentication is lost.<br />'.
				'<strong>Notice: </strong>Empty or zero value means session-time cookie. The authentication is lost as soon as the web browser is closed (including its all tabs and windows).<br />'.
				'<strong>Notice: </strong>Any changes to this field affect new chat users only<br />'
			),
			array(
				'user_auth_keep_logged_in', 'Keep Authenticated', 'booleanFieldCallback', 'boolean',
				'Refreshes authentication cookie if its expiration time is less than half of its initial setting. This will make user always authenticated if the user keeps visiting the chat page at least one in the number of days set in Expiration Time field.<br />'.
				'<strong>Notice:</strong> If Expiration Time field is set to empty or zero value then this setting has no effect.'
			),
			array('_section', 'Chat engine'),
			array(
				'ajax_engine', 'AJAX Engine', 'selectCallback', 'string', 
				"Engine for AJAX requests generated by the chat. <br />The Default engine is the most compatible but it has a poor performance. The Lightweight AJAX and Ultra Lightweight AJAX engines are a lot faster and consume less CPU, however, it is slightly possible that they could be unstable in future versions of WordPress.",
				XenChatAdvancedTab::getAllEngines()
			),
			array(
				'messages_refresh_time', 'Refresh Time', 'selectCallback', 'string', 
				"Determines how often the chat should check for new messages. Lower value means higher CPU usage and more HTTP requests.", 
				XenChatAdvancedTab::getRefreshTimes()
			),
			array('enabled_debug', 'Enable Debug Mode', 'booleanFieldCallback', 'boolean', "Displays extended error log. It is useful when reporting issues."),
			array(
				'ajax_validity_time', 'AJAX Validity Time', 'stringFieldCallback', 'integer',
				'Determines how many minutes AJAX requests are considered as valid. It is useful to prevent indexing internal API calls by search engines and Web crawlers.<br />
				<strong>Warning:</strong> Too low value may cause errors on mobile devices. The default value is: 1 day (1440 minutes). '
			),
			array(
				'enabled_xhr_check', 'Enable XHR Request Check', 'booleanFieldCallback', 'boolean',
				'Enables checking for "X-Requested-With" header in AJAX requests'
			),
			array('user_actions', 'Actions', 'adminActionsCallback', 'void'),
		);
	}
	
	public function getDefaultValues() {
		return array(
			'ajax_engine' => 'ultralightweight',
			'messages_refresh_time' => 4000,
			'enabled_debug' => 0,
			'ajax_validity_time' => 1440,
			'enabled_xhr_check' => 1,
			'user_auth_expiration_days' => 14,
			'user_auth_keep_logged_in' => 1,
			'user_actions' => null,
		);
	}
	
	public static function getAllEngines() {
		return array(
			'' => 'Default',
			'lightweight' => 'Lightweight AJAX',
			'ultralightweight' => 'Ultra Lightweight AJAX'
		);
	}
	
	public static function getRefreshTimes() {
		return array(
			3000 => '3s',
			4000 => '4s',
			5000 => '5s',
			10000 => '10s',
			15000 => '15s',
			20000 => '20s',
			30000 => '30s',
			60000 => '60s',
			120000 => '120s',
		);
	}

	public function adminActionsCallback() {
		$url = admin_url("options-general.php?page=".XenChatSettings::MENU_SLUG."&wc_action=resetAnonymousCounter");

		printf(
			'<a class="button-secondary" href="%s" title="Resets username prefix" onclick="return confirm(\'Are you sure you want to reset the prefix?\')">Reset Username Prefix</a><p class="description">Resets prefix number used to generate username for anonymous users.</p>',
			wp_nonce_url($url)
		);

		$url = admin_url("options-general.php?page=".XenChatSettings::MENU_SLUG."&wc_action=resetSettings");
		printf(
			'<br /><a class="button-secondary" href="%s" title="Resets Xen Chat settings" onclick="return confirm(\'WARNING: All settings will be permanently deleted. \\n\\nAre you sure you want to reset the settings?\')">Reset All Settings</a><p class="description">Resets all settings to default values.</p>',
			wp_nonce_url($url)
		);

		$url = admin_url("options-general.php?page=".XenChatSettings::MENU_SLUG."&wc_action=deleteAllUsersAndMessages");
		printf(
			'<br /><a class="button-secondary" href="%s" title="Deletes all messages and users" onclick="return confirm(\'WARNING: All messages and users will be permanently deleted. \\n\\nAre you sure you want to proceed?\')">Delete All Messages and Users</a><p class="description">Deletes all messages and users.</p>',
			wp_nonce_url($url)
		);
	}

	public function resetAnonymousCounterAction() {
		$this->options->resetUserNameSuffix();
		$this->addMessage('The prefix has been reset.');
	}

	public function resetSettingsAction() {
		$this->options->dropAllOptions();

		// set the default options:
		$settings = XenChatContainer::get('XenChatSettings');
		$settings->setDefaultSettings();

		$this->addMessage('All settings have been reset to defaults.');
	}

	public function deleteAllUsersAndMessagesAction() {
		$this->messagesService->deleteAll();
		$this->usersDAO->deleteAll();
		$this->actions->publishAction('deleteAllMessages', array());
		$this->addMessage('All messages and users have been deleted.');
	}
}