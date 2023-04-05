<?php

/**
 * Class XenChatMaintenanceI18n
 *
 * Adds i18n translations table to the maintenance endpoint.
 *
 * @author Kainex <contact@kainex.pl>
 */
class XenChatMaintenanceI18n {

	/**
	 * @var XenChatOptions
	 */
	protected $options;

	public function __construct() {
		$this->options = XenChatOptions::getInstance();
	}

	/**
	 * Returns translations required for later displaying.
	 *
	 * @return array
	 */
	public function getTranslations() {
		return array(
			'unsupportedTypeOfFile' => $this->options->getOption('message_error_7', __('Unsupported type of file.', 'xen-chat')),
			'sizeLimitError' => $this->options->getOption('message_error_8', __('The size of the file exceeds allowed limit.', 'xen-chat')),
			'close' => $this->options->getOption('message_close', __('Close', 'xen-chat')),
			'ok' => $this->options->getOption('message_ok', __('OK', 'xen-chat')),
			'yes' => $this->options->getOption('message_yes', __('Yes', 'xen-chat')),
			'no' => $this->options->getOption('message_no', __('No', 'xen-chat')),
			'error' => $this->options->getOption('message_error', __('Error', 'xen-chat')),
			'information' => $this->options->getOption('message_information', __('Information', 'xen-chat')),
			'confirmation' => $this->options->getOption('message_confirmation', __('Confirmation', 'xen-chat')),
			'enterYourUsername' => $this->options->getOption('message_enter_user_name', __('Enter your username', 'xen-chat')),
			'enterPassword' => $this->options->getOption('message_enter_password', __('Enter password', 'xen-chat')),
			'name' => $this->options->getOption('message_name', __('Name', 'xen-chat')),
			'save' => $this->options->getOption('message_save', __('Save', 'xen-chat')),
			'reset' => $this->options->getOption('message_reset', __('Reset', 'xen-chat')),
			'muteSounds' => $this->options->getOption('message_mute_sounds', __('Mute sounds', 'xen-chat')),
			'textColor' => $this->options->getOption('message_text_color', __('Text color', 'xen-chat')),
			'uploadPicture' => $this->options->getOption('message_picture_upload_hint', __('Upload a picture', 'xen-chat')),
			'attachFile' => $this->options->getOption('message_attach_file_hint', __('Attach a file', 'xen-chat')),
			'insertEmoticon' => $this->options->getOption('message_insert_emoticon', __('Insert an emoticon', 'xen-chat')),
			'messageInputTitle' => $this->options->getOption('message_input_title', __('Use Shift+ENTER in order to move to the next line.', 'xen-chat')),
			'deleteMessage' => $this->options->getOption('message_delete_message', __('Delete the message', 'xen-chat')),
			'banThisUser' => $this->options->getOption('message_ban_this_user', __('Ban this user', 'xen-chat')),
			'muteThisUser' => $this->options->getOption('message_mute_this_user', __('Mute this user', 'xen-chat')),
			'reportSpam' => $this->options->getOption('message_report_spam', __('Report spam', 'xen-chat')),
			'deleteConfirmation' => $this->options->getOption('message_delete_confirmation', __('Are you sure you want to delete this message?', 'xen-chat')),
			'banConfirmation' => $this->options->getOption('message_ban_confirmation', __('Are you sure you want to ban this user?', 'xen-chat')),
			'banConfirmed' => $this->options->getOption('message_user_banned', __('The user has been banned.', 'xen-chat')),
			'muteConfirmation' => $this->options->getOption('message_mute_confirmation', __('Are you sure you want to mute this user?', 'xen-chat')),
			'muteConfirmed' => $this->options->getOption('message_user_muted', __('The user has been muted.', 'xen-chat')),
			'spamReportConfirmation' => $this->options->getOption('message_text_1', __('Are you sure you want to report the message as spam?', 'xen-chat')),
			'spamReportConfirmed' => $this->options->getOption('message_spam_reported', __('Thank you for reporting this.', 'xen-chat')),
			'subChannelsSearchHint' => $this->options->getOption('users_list_search_hint', __('Search ...', 'xen-chat')),
			'savedSettings' => $this->options->getOption('message_saved_settings', __('Settings have been saved.', 'xen-chat')),
			'maximize' => $this->options->getOption('message_maximize', __('Maximize', 'xen-chat')),
			'minimize' => $this->options->getOption('message_minimize', __('Minimize', 'xen-chat')),
			'chatFull' => $this->options->getOption('message_error_6', __('The chat is full now. Try again later.', 'xen-chat')),
			'onlineUsers' => $this->options->getOption('message_online_users', __('Online users', 'xen-chat')),
		);
	}

}