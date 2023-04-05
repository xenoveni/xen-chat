<?php

use Elementor\Controls_Manager;
use Elementor\Plugin;

/**
 * XenChat Elementor integration class.
 *
 * @author Kainex <contact@kainex.pl>
 */
class XenChatAddon extends \Elementor\Widget_Base {

	public function get_name() {
		return 'xen_chat_widget';
	}

	public function get_title() {
		return 'Xen Chat';
	}

	public function get_icon() {
		return 'eicon-person';
	}

	public function get_categories() {
		return [ 'basic' ];
	}

	public function get_keywords() {
		return [ 'chat', 'xen-chat' ];
	}

	protected function register_controls() {
		$this->start_controls_section(
			'chat_settings',
			[
				'label' => esc_html__( 'Settings', 'xen-chat' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);
		$this->addText('channel', esc_html__( 'Channel', 'xen-chat' ), 'global');
		$this->addCheckbox('access_mode', esc_html__( 'Disable Anonymous Users', 'xen-chat' ), '');
		$this->addCheckbox('force_user_name_selection', esc_html__( 'Force Username Selection', 'xen-chat' ), '');
		$this->addSettingsLink('chat_settings_advanced', 'general');
		$this->end_controls_section();

		$this->startSection('section_style_chat', esc_html__( 'Chat', 'xen-chat' ));
		$this->addText('window_title', esc_html__( 'Window Title', 'xen-chat' ), 'Chat');
		$this->addSelect('theme', esc_html__('Theme', 'xen-chat'), array(
			'' => esc_html__('Default', 'xen-chat'),
			'lightgray' => esc_html__('Light Gray', 'xen-chat'),
			'colddark' => esc_html__('Cold Dark', 'xen-chat'),
			'airflow' => esc_html__('Air Flow', 'xen-chat'),
		), 'lightgray');
		$this->addText('chat_width', esc_html__( 'Width', 'xen-chat' ), '100%');
		$this->addText('chat_height', esc_html__( 'Height', 'xen-chat' ), '500px');
		$this->addSelect('messages_order', esc_html__('Messages Order', 'xen-chat'), array(
			'' => esc_html__('Newest on the bottom', 'xen-chat'),
			'descending' => esc_html__('Newest on the top', 'xen-chat'),
		), '');


		$this->addSettingsLink('section_style_chat_advanced', 'appearance');
		$this->end_controls_section();

		$this->startSection('section_style_messages', esc_html__( 'Messages', 'xen-chat' ));
		$this->addSelect('messages_time_mode', esc_html__('Message Time Mode', 'xen-chat'), array(
			'hidden' => esc_html__('Hidden', 'xen-chat'),
			'' => esc_html__('Full', 'xen-chat'),
			'elapsed' => esc_html__('Elapsed', 'xen-chat'),
		), 'elapsed');
		$this->addColorSelector('background_color', esc_html__('Background Color', 'xen-chat'), array('.wcChannel .wcMessages', '.wcChannel .wcMessages .wcMessage', '.wcChannel .wcMessages .wcMessage .wcContent'), 'background-color');
		$this->addColorSelector('text_color', esc_html__('Font Color', 'xen-chat'), array('.wcMessages *'), 'color');

		$this->addSettingsLink('section_style_messages_advanced', 'appearance');
		$this->end_controls_section();

		$this->startSection('section_style_input', esc_html__( 'Input', 'xen-chat' ));

		$this->addCheckbox('show_emoticon_insert_button', esc_html__( 'Show Emoticon Button', 'xen-chat' ), '1');
		$this->addCheckbox('show_image_upload_button', esc_html__( 'Show Image Button', 'xen-chat' ), '1');
		$this->addCheckbox('show_file_upload_button', esc_html__( 'Show File Button', 'xen-chat' ), '1');
		$this->addCheckbox('show_message_submit_button', esc_html__( 'Show Submit Button', 'xen-chat' ), '1');
		$this->addCheckbox('multiline_support', esc_html__( 'Multiline Messages', 'xen-chat' ), '0');
		$this->addCheckbox('show_user_name', esc_html__( 'Show User Name', 'xen-chat' ), '1');
		$this->addSelect('input_controls_location', esc_html__('Input Location', 'xen-chat'), array(
			'' => esc_html__('Bottom', 'xen-chat'),
			'top' => esc_html__('Top', 'xen-chat'),
		), '');
		$this->addColorSelector('background_color_input', esc_html__('Background Color', 'xen-chat'), array('.wcChannelInput', '.wcDesktop .wcBody .wcMessagesArea .wcCustomizations'), 'background-color');
		$this->addColorSelector('text_color_input_field', esc_html__('Font Color', 'xen-chat'), array('.wcChannelInput *', '.wcDesktop .wcBody .wcMessagesArea .wcCustomizations *'), 'color');

		$this->addSettingsLink('section_style_input_advanced', 'appearance');
		$this->end_controls_section();

		$this->startSection('section_browser', esc_html__( 'Browser', 'xen-chat' ));
		$this->addCheckbox('show_users', esc_html__( 'Enabled', 'xen-chat' ), '1');
		$this->addSelect('browser_location', esc_html__('Location', 'xen-chat'), array(
			'' => esc_html__('Right', 'xen-chat'),
			'left' => esc_html__('Left', 'xen-chat'),
		), '');
		$this->addCheckbox('show_users_list_search_box', esc_html__( 'Show Users Search Box', 'xen-chat' ), '1');
		$this->addCheckbox('show_users_list_avatars', esc_html__( 'Show Avatars', 'xen-chat' ), '1');
		$this->addCheckbox('show_users_flags', esc_html__( 'Show National Flags', 'xen-chat' ), '1');
		$this->addCheckbox('show_users_city_and_country', esc_html__( 'Show City And Country', 'xen-chat' ), '1');
		$this->addCheckbox('show_users_online_offline_mark', esc_html__( 'Show Online / Offline Mark', 'xen-chat' ), '');
		$this->addCheckbox('show_users_counter', esc_html__( 'Show Online Users Counter', 'xen-chat' ), '');
		$this->addColorSelector('background_color_users_list', esc_html__('Background Color', 'xen-chat'), array('.wcBody .wcBrowserArea', '.wcDesktop .wcBrowser'), 'background-color');
		$this->addColorSelector('text_color_users_list', esc_html__('Font Color', 'xen-chat'), '.wcDesktop .wcBrowser *', 'color');
		$this->addSettingsLink('section_browser_advanced', 'appearance');
		$this->end_controls_section();
	}

	private function startSection($id, $name) {
		$this->start_controls_section(
			$id,
			[
				'label' => $name,
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);
	}

	private function addText($id, $name, $default = '') {
		$this->add_control(
			$id,
			[
				'label' => $name,
				'type' => Controls_Manager::TEXT,
				'default' => $default,
			]
		);
	}

	private function addCheckbox($id, $name, $default = 1) {
		$this->add_control(
			$id,
			[
				'label' => $name,
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'xen-chat' ),
				'label_off' => esc_html__( 'No', 'xen-chat' ),
				'return_value' => '1',
				'default' => $default,
			]
		);
	}

	private function addColorSelector($id, $name, $selector, $cssProperty) {
		if (is_array($selector)) {
			$selectorMapped = array_map(function($element) { return '{{WRAPPER}} '.$element; }, $selector);
			$selector = implode(', ', $selectorMapped);
		} else {
			$selector = '{{WRAPPER}} '.$selector;
		}

		$this->add_control(
			$id,
			[
				'label' => $name,
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					$selector => $cssProperty.': {{VALUE}};',
				],
			]
		);
	}

	private function addSelect($id, $name, $options, $default) {
		$this->add_control(
			$id,
			[
				'label' => $name,
				'type' => Controls_Manager::SELECT,
				'default' => $default,
				'options' => $options,
			]
		);
	}

	public function get_script_depends() {
		if (getenv('WC_ENV') === 'DEV') {
			wp_register_script('xenchat', plugins_url('xen-chat/assets/js/xen-chat.js?tmp='.time().'&v='.XEN_CHAT_VERSION), __FILE__);
		} else {
			wp_register_script('xenchat', plugins_url('xen-chat/assets/js/xen-chat.min.js?v='.XEN_CHAT_VERSION), __FILE__);
		}

		return [
			'xenchat',
		];
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$config = array(
			'window_title' => $settings['window_title'],
			'channel' => $settings['channel'],
			'access_mode' => $settings['access_mode'],
			'force_user_name_selection' => $settings['force_user_name_selection'],
			'theme' => $settings['theme'],
			'show_emoticon_insert_button' => $settings['show_emoticon_insert_button'],
			'show_message_submit_button' => $settings['show_message_submit_button'],
			'enable_attachments_uploader' => $settings['show_file_upload_button'],
			'multiline_support' => $settings['multiline_support'],
			'input_controls_location' => $settings['input_controls_location'],
			'show_user_name' => $settings['show_user_name'],
			'background_color_input' => $settings['background_color_input'],
			'text_color_input_field' => $settings['text_color_input_field'],
			'messages_time_mode' => $settings['messages_time_mode'],
			'show_avatars' => $settings['show_avatars'],
			'chat_width' => $settings['chat_width'],
			'chat_height' => $settings['chat_height'],
			'messages_order' => $settings['messages_order'],

			'show_users' => $settings['show_users'],
			'browser_location' => $settings['browser_location'],
			'show_users_list_search_box' => $settings['show_users_list_search_box'],
			'show_users_list_avatars' => $settings['show_users_list_avatars'],
			'show_users_flags' => $settings['show_users_flags'],
			'show_users_city_and_country' => $settings['show_users_city_and_country'],
			'show_users_online_offline_mark' => $settings['show_users_online_offline_mark'],
			'show_users_counter' => $settings['show_users_counter'],
		);

		if ($settings['show_image_upload_button'] === '1') {
			$config['allow_post_images'] = '1';
			$config['enable_images_uploader'] = '1';
		} else {
			$config['allow_post_images'] = '';
			$config['enable_images_uploader'] = '';
		}

		$html = xen_chat_shortcode($config);

		preg_match('/<div id="([^"]+)"/', $html, $matchElements);
		$id = $matchElements[1];
		echo $html;

		if (Plugin::$instance->editor->is_edit_mode() ) {
			echo '<script>_xenChat.init(jQuery("#'.$id.'"));</script>';
		}
	}

	private function addSettingsLink($id, $tag = 'general') {
		$url = site_url().'/wp-admin/options-general.php?page=xen-chat-admin#tab='.$tag;

		$this->add_control(
			$id,
			[
				'type' => Controls_Manager::RAW_HTML,
				'raw' => sprintf(
					'<a href="%s" class="elementor-button elementor-button-default" target="_blank">%s</a>',
					$url, esc_html__( 'Advanced Settings', 'xen-chat' )
				)
			]
		);

		$url = site_url().'/wp-admin/options-general.php?page=xen-chat-admin#tab=pro';

		$this->add_control(
			$id.'_pro_link',
			[
				'type' => Controls_Manager::RAW_HTML,
				'raw' => sprintf(
					'<a href="%s" class="elementor-button elementor-button-default" style="background-color: #4f3b5e; color: #fff;" target="_blank">%s</a>',
					$url, 'Check Xen Chat Pro'
				)
			]
		);
	}

}