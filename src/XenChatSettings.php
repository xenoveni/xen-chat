<?php

/**
 * XenChat admin settings page.
 *
 * @author Kainex <contact@kainex.pl>
 */
class XenChatSettings {
	const OPTIONS_GROUP = 'xen_chat_options_group';
	const MENU_SLUG = 'xen-chat-admin';
	
	const PAGE_TITLE = 'Settings Admin';
	const MENU_TITLE = 'Xen Chat Settings';
	
	const SECTION_FIELD_KEY = '_section';
	
	/**
	* @var array Tabs definition
	*/
	private $tabs = array(
		'xen-chat-general' => 'General',
		'xen-chat-externalLogin' => 'External Login',
		'xen-chat-messages' => 'Messages Posting',
		'xen-chat-moderation' => 'Moderation',
		'xen-chat-permissions' => 'Permissions',
		'xen-chat-modes' => 'Chat Modes',
		'xen-chat-features' => 'Features',
		'xen-chat-appearance' => 'Appearance',
		'xen-chat-emoticons' => 'Emoticons',
		'xen-chat-channels' => 'Channels',
		'xen-chat-notifications' => 'Notifications',
		'xen-chat-filters' => 'Filters',
		'xen-chat-bans' => 'Muted Users',
		'xen-chat-kicks' => 'Banned Users',
		'xen-chat-localization' => 'Localization',
		'xen-chat-advanced' => 'Advanced',
		'xen-chat-pro' => 'Xen Chat Pro',
	);
	
	/**
	* @var array Generated sections
	*/
	private $sections = array();
	
	public function __construct() {
		XenChatContainer::load('admin/XenChatAbstractTab');
	}
	
	/**
	* Initializes settings page link in admin menu.
	*/
	public function initialize() {
		add_action('admin_menu', array($this, 'addAdminMenu'));
		add_action('admin_enqueue_scripts', array($this, 'enqueueScripts'));
		add_action('admin_init', array($this, 'pageInit'));
		if (array_key_exists('showDocs', $_GET)) {
			add_filter('admin_init', array($this, 'showDocs'));
		}
	}
	
	public function addAdminMenu() {
		add_options_page(self::PAGE_TITLE, self::MENU_TITLE, 'manage_options', self::MENU_SLUG, array($this, 'renderAdminPage'));
		$this->handleActions();
	}
	
	public function enqueueScripts() {
		wp_enqueue_style('wp-color-picker');
		wp_enqueue_script('wp-color-picker-script', plugins_url('../js/xen_chat_admin.js', __FILE__), array('wp-color-picker'), false, true);
	}
	
	public function pageInit() {
		register_setting(self::OPTIONS_GROUP, XenChatOptions::OPTIONS_NAME, array($this, 'getSanitizedFormValues'));

		foreach ($this->tabs as $key => $caption) {
			$sectionKey = "section_{$key}";
			$tabObject = $this->getTabObject($key);
			
			$fields = $tabObject->getFields();
			foreach ($fields as $field) {
				$id = $field[0];
				$name = $field[1];
				
				if ($id === self::SECTION_FIELD_KEY) {
					$sectionKey = "section_{$key}_".md5($name);
					add_settings_section($sectionKey, $name, null, $key);
					$this->sections[$key][] = array(
						'id' => $sectionKey,
						'name' => $name,
						'hint' => array_key_exists(2, $field) ? $field[2] : '',
						'options' => array_key_exists(3, $field) ? $field[3] : array()
					);
				} else {
					$args = array(
						'id' => $id,
						'name' => $name,
						'hint' => array_key_exists(4, $field) ? $field[4] : '',
						'options' => array_key_exists(5, $field) ? $field[5] : array()
					);
				
					add_settings_field($id, $name, array($tabObject, $field[2]), $key, $sectionKey, $args);
				}
			}
		}
	}

	/**
	* Sets the default values of all configuration fields.
	* It should be used right after the activation of the plugin.
	*
	* @return null
	*/
	public function setDefaultSettings() {
		$options = get_option(XenChatOptions::OPTIONS_NAME, array());
		
		foreach ($this->tabs as $key => $caption) {
			$tabObject = $this->getTabObject($key);
			foreach ($tabObject->getDefaultValues() as $key => $value) {
				if (!array_key_exists($key, $options)) {
					$options[$key] = $value;
				}
			}
		}
		update_option(XenChatOptions::OPTIONS_NAME, $options);
	}

	public function renderAdminPage() {
		$options = XenChatOptions::getInstance();

		?>
			<div class="wrap">
				<style type="text/css">
					.wcAdminFl { float: left; }
					.wcAdminFr { float: right; }
					.wcAdminCb { clear:both; }
					.wcAdminMenu, .wcAdminMenu * { -moz-box-sizing: border-box; box-sizing: border-box; }
					.wcAdminTabContainer { overflow:hidden; }
					.wcAdminMenu *:focus { outline: none; box-shadow: none; }
					.wcAdminMenu { width: 200px; margin-right: 10px; border: 1px solid #e5e5e5; -webkit-box-shadow: 0 1px 1px rgba(0,0,0,.04); box-shadow: 0 1px 1px rgba(0,0,0,.04); }
					.wcAdminMenu ul { margin: 0px; list-style: none; padding: 0px; }
					.wcAdminMenu ul li { border-bottom: 1px solid #dfdfdf; margin: 0; display: list-item; text-align: -webkit-match-parent; }
					.wcAdminMenu ul li a { background-color: #fff; display: inline-block; padding: 10px 20px; width: 100%; font-size: 1.1em; text-decoration: none; color: #000; }
					.wcAdminMenu ul li a:hover { background-color: #fafafa; color: #000; outline: 0;}
					.wcAdminMenu ul li a:visited { color: #000; }
					.wcAdminMenu ul li a.wcAdminMenuActive { font-weight: bold; background-color: #fafafa; }
					.wcAdminMenu ul li a#xen-chat-pro {
						background: #4F3B5E url("<?php echo $options->getBaseDir(); ?>/gfx/icons/small-pro-icon.png") no-repeat 17px center;
						color: #fff; padding-left: 48px;
					}
					.wcAdminDonation span { padding-top: 5px; display: inline-block; font-size: 1.1em; }
					.wcAdminDonation a.wcAdminButton { border-color: #11f; color: #005; font-size: 1.1em; }
					.wp-admin a.wcAdminButtonPro, .wp-admin a.wcAdminButtonPro:hover, .wp-admin a.wcAdminButtonPro:focus, #xen-chat-proContainer a.wcAdminButtonPro, #xen-chat-proContainer a.wcAdminButtonPro:hover {
						background: #4f3b5e url("<?php echo $options->getBaseDir(); ?>/gfx/icons/small-pro-icon.png") no-repeat 10px 4px;
						border: 1px solid #4f3b5e; color: #fff; font-size: 1.2em; padding-left: 61px; padding-right: 35px;
					}
					.wp-admin p.wcProDescription {
						color: #f00;
					}
					.wp-admin p.description a.wcAdminButtonPro {
						vertical-align: middle; padding-right: 6px; padding-left: 40px; font-style: normal;
					}
					.wp-admin p.description a.wcAdminButtonProSize {
						width: 150px;
					}
					#xen-chat-proContainer a.wcAdminButtonPro, #xen-chat-proContainer a.wcAdminButtonPro:hover {
						padding-top: 10px; padding-bottom: 10px; background-position: 10px center;
						padding-left: 60px; padding-right: 40px;
					}

					.wp-admin a.wcAdminButtonPro:hover, #xen-chat-proContainer a.wcAdminButtonPro:hover { background-color: #533f62; border: 1px solid #533f62; color: #fff; }

					.wp-admin a.wcAdminButtonProDemo, .wp-admin a.wcAdminButtonProDemo:hover, .wp-admin a.wcAdminButtonProDemo:focus, #xen-chat-proContainer a.wcAdminButtonProDemo, #xen-chat-proContainer a.wcAdminButtonProDemo:hover {
						padding-left: 31px; background: #0085ba;
						border: 1px solid #0085ba;
					}
				</style>
			
				<h2><?php echo self::MENU_TITLE ?></h2>
				
				<form method="post" action="options.php" class="metabox-holder">
					<!-- Disabling autocomplete: -->
					<input type="text" style="display: none" />
					<input type="password" style="display: none" />

					<?php settings_fields(self::OPTIONS_GROUP); ?>
					
					<?php $this->renderMenu(); ?>
					
					<?php
						$isFirstContainer = true;
						foreach ($this->tabs as $pageId => $tabCaption) {
							$hideContainer = $isFirstContainer ? '' : 'display:none';
							echo "<div id='{$pageId}Container' class='wcAdminTabContainer' style='{$hideContainer}'>";
							?>
								<div class='postbox' style="border: 1px solid #4f3b5e;">
									<h3 class='hndle'><span>Check our Products</span></h3>
									<div class='inside'>
										<p class='description'>
											<a class="button-secondary wcAdminButtonPro wcAdminButtonProSize" target="_blank" href="https://kainex.pl/projects/wp-plugins/xen-chat-pro?utm_source=xenchat&utm_medium=banner&utm_campaign=top_hint" title="Check Xen Chat Pro">
												Xen Chat <strong>Pro</strong>
											</a>
											<span style='display: inline-block; font-size: 1.2em'>Voice messages, 1-on-1 chats, Pro themes, sidebar mode, message reactions and <a target="_blank" href="https://kainex.pl/projects/wp-plugins/xen-chat-pro?utm_source=xenchat&utm_medium=banner&utm_campaign=top_hint" title="Check Xen Chat Pro">more</a></span>
										</p>
										<p class='description'>
											<a class="button-secondary wcAdminButtonPro wcAdminButtonProSize" style="background-color: #5f463f" target="_blank" href="https://kainex.pl/projects/wp-plugins/xen-chat-live?utm_source=xenchat&utm_medium=banner&utm_campaign=top_hint" title="Check Xen Chat Pro">
												Xen Chat <strong>Live</strong>
											</a>
											<span style='display: inline-block; font-size: 1.2em'>Extended version of Xen Chat Pro + <strong>live chat features:</strong> customizable live chat widget, incoming chats management page, multiple operators and <a target="_blank" href="https://kainex.pl/projects/wp-plugins/xen-chat-live?utm_source=xenchat&utm_medium=banner&utm_campaign=top_hint" title="Check Xen Chat Pro">more</a> </span>
										</p>
									</div>
								</div>
							<?php
							
							$sections = $this->sections[$pageId];
							foreach ($sections as $sectionKey => $section) {
								echo "<div class='postbox'>";
								echo "<h3 class='hndle'><span>".$section['name']."</span></h3>";
								echo "<div class='inside'>";
								echo '<table class="form-table">';
								if (strlen($section['hint']) > 0) {
									echo '<tr><td colspan="2" style="padding:0"><p class="description">'.$section['hint'].'</p></td></tr>';
								}
								do_settings_fields($pageId, $section['id']);
								echo '<tr><td colspan="2">';
								if (!array_key_exists('hideSubmitButton', $section['options']) || $section['options']['hideSubmitButton'] !== true) {
									submit_button('', 'primary large', 'submit', false, array('id' => "submit_{$pageId}_{$sectionKey}", 'onclick' => 'xen_chat_append_tab(\'' . str_replace('xen-chat-', '', $pageId) . '\')'));
								}
								echo '</td></tr>';
								echo '</table>';
								echo "</div></div>";
							}
							
							echo "</div>";
							$isFirstContainer = false;
						}
					?>
					
					<br class="wcAdminCb" />
				</form>
				
				<script type="text/javascript">
					function xen_chat_append_tab(tab) {
						var referrer = jQuery('input[name = "_wp_http_referer"]');
						referrer.val(referrer.val() + '#tab=' + tab);
					}

					jQuery(window).on('load', function() {
						jQuery('.wcAdminMenu a').click(function() {
							jQuery('.wcAdminTabContainer').hide();
							jQuery('#' + jQuery(this).attr('id') + 'Container').show();
							jQuery('.wcAdminMenu a').removeClass('wcAdminMenuActive');
							jQuery(this).addClass('wcAdminMenuActive');
						});

						if (location && location.hash && location.hash.length > 0) {
							var matches = location.hash.match(new RegExp('tab=([^&]*)'));
							if (matches) {
								var tab = matches[1];
								jQuery('.wcAdminTabContainer').hide();
								jQuery('#xen-chat-' + tab + 'Container').show();
								jQuery('.wcAdminMenu a').removeClass('wcAdminMenuActive');
								jQuery('#xen-chat-' + tab).addClass('wcAdminMenuActive');
							}
						}
					});
				</script>
			</div>
		<?php
	}
	
	private function renderMenu() {
		$outHtml = '';
		
		$outHtml .= '<div class="wcAdminMenu wcAdminFl">';
		$outHtml .= '<ul>';
		$isFirstTab = true;
		foreach ($this->tabs as $key => $caption) {
			$isActive = $isFirstTab ? 'wcAdminMenuActive' : '';
			$outHtml .= '<li><a id="'.$key.'" class="'.$isActive.'" href="javascript://">'.$caption.'</a></li>';
			$isFirstTab = false;
		}
		$outHtml .= '</ul>';
		$outHtml .= '</div>';
		
		echo $outHtml;
	}
	
	/**
	* Detects actions passed in parameters and delegates to an action method.
	*
	* @return null
	*/
	public function handleActions() {
		global $wpdb;
		
		if (isset($_GET['wc_action'])) {
			foreach ($this->tabs as $tabKey => $tabCaption) {
				$tabObject = $this->getTabObject($tabKey);
				$actionMethod = $_GET['wc_action'].'Action';
				if (method_exists($tabObject, $actionMethod)) {
					$tabObject->$actionMethod();
				}
			}
			
			$redirURL = admin_url("options-general.php?page=".self::MENU_SLUG).(isset($_GET['tab']) ? '#wc_tab='.urlencode($_GET['tab']) : '');
			echo '<script type="text/javascript">location.replace("' . $redirURL . '");</script>';
		} else {
			$this->showUpdatedMessage();
			$this->showErrorMessage();
		}
	}
	
	/**
	* Filters form input using filters from each tab object.
	*
	* @param array $input A key-value list of form values
	*
	* @return array Filtered array
	*/
	public function getSanitizedFormValues($input) {
		$sanitized = array();
		foreach ($this->tabs as $tabKey => $tabCaption) {
			$sanitized = array_merge($sanitized, $this->getTabObject($tabKey)->sanitizeOptionValue($input));
		}
		$sanitized = array_merge(get_option(XenChatOptions::OPTIONS_NAME, array()), $sanitized);
		
		return $sanitized;
	}
	
	
	/**
	* Returns an instance of the requested tab object.
	*
	* @param string $tabKey A key from $this->tabs array
	*
	* @return XenChatAbstractTab
	*/
	private function getTabObject($tabKey) {
		$tabKey = ucfirst(str_replace('xen-chat-', '', $tabKey));
		$classPathAndName = "admin/XenChat{$tabKey}Tab";
		
		return XenChatContainer::get($classPathAndName);
	}

	/**
	 * Shows a message stored in the transient.
	 */
	private function showUpdatedMessage() {
		$message = get_transient('wc_admin_settings_message');
		if (is_string($message) && strlen($message) > 0) {
			add_settings_error(md5($message), esc_attr('settings_updated'), strip_tags($message), 'updated');
			delete_transient('wc_admin_settings_message');
		}
	}

	/**
	 * Shows a message stored in the transient.
	 */
	private function showErrorMessage() {
		$message = get_transient('wc_admin_settings_error_message');
		if (is_string($message) && strlen($message) > 0) {
			add_settings_error(md5($message), esc_attr('settings_updated'), strip_tags($message), 'error');
			delete_transient('wc_admin_settings_error_message');
		}
	}

	/**
	 * Shows the documentation of all shortcode attributes.
	 */
	public function showDocs() {
		$excludedFields = array(
			'user_actions', 'enable_opening_control', 'opening_days', 'opening_hours',
			'bans', 'ban_add', 'channels', 'admin_actions', 'filters', 'filter_add',
			'kicks', 'kick_add', 'custom_emoticons'
		);
		foreach ($this->tabs as $key => $caption) {
			$tabObject = $this->getTabObject($key);
			$fields = $tabObject->getFields();
			$defaults = $tabObject->getDefaultValues();
			$printSection = null;
			foreach ($fields as $field) {
				$id = $field[0];
				$name = str_replace('<br />', ' ', $field[1]);
				$callback = $field[2];
				$type = $field[3];
				$hint = $field[4];
				$values = $field[5];

				if (in_array($id, $excludedFields)) {
					continue;
				}

				if ($id == '_section') {
					$printSection = "<h4>{$caption}: $name</h4>";
					continue;
				} else {
					if ($printSection !== null) {
						echo $printSection;
						$printSection = null;
					}
					$default = $defaults[$id];
					$defaultLabel = ($default !== '' && $default !== null ? $default : '[not set]');

					$allowedValue = 'a text';
					if ($callback == 'booleanFieldCallback') {
						$allowedValue = '<ul><li><i>0</i> - disabled</li><li><i>1</i> - enabled</li></ul>';
					} else if ($type == 'integer') {
						$allowedValue = '<i>a positive number</i>';
					} else if ($callback == 'colorFieldCallback') {
						$allowedValue = '<i>a color in hex format, eg. #ef2244</i>';
					} else if (is_array($values)) {
						$allowedValues = array();
						foreach ($values as $key => $value) {
							if ($key === '') {
								$key = '[not set]';
							}
							if ($id == 'permission_delete_message_role' || $id == 'permission_ban_user_role') {
								$value = ucfirst($key);
							}
							$allowedValues[] = "<li><i>$key</i> - {$value}</li>";
						}
						$allowedValue = '<ul>'.implode('', $allowedValues).'</ul>';
					}

					if ($id == 'chat_width' || $id == 'chat_height') {
						$allowedValue = null;
					}

					echo "<h5>$id - $name</h5>\n";
					echo "$hint\n";
					if ($allowedValue !== null) {
						echo "Allowed values: {$allowedValue}\n";
					}
					echo "Default: <i>".$defaultLabel."</i>\n\n";
				}
			}
		}

		die();
	}

	public function userSearchEndpoint() {
		$searchTerm = $_POST['keyword'];
		$out = array('type' => 'success', 'users' => array());

		$args = array (
			'order' => 'ASC',
			'orderby' => 'display_name',
			'search' => '*'.esc_attr($searchTerm).'*',
			'number' => 5,
			'search_columns' => array(
				'user_login',
				'user_nicename',
				'user_email',
				'user_url',
				'display_name',
			)
		);
		$query = new WP_User_Query($args);
		$users = $query->get_results();

		if (!empty($users)) {
			foreach ($users as $user) {
				$out['users'][] = array(
					'login' => $user->user_login,
					'text' => $user->user_login.' ('.$user->display_name.')'
				);
			}
		}

		echo json_encode($out);
		die();
	}
}