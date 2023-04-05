<?php 

/**
 * Xen Chat admin pro settings tab class.
 *
 * @author Kainex <contact@kainex.pl>
 */
class XenChatProTab extends XenChatAbstractTab {

	public function getFields() {
		return array(
			array(
				'_section', 'Xen Chat Pro Features',
				'<a href="https://kainex.pl/projects/wp-plugins/xen-chat-pro?source=settings-page"><img src="'.$this->options->getBaseDir().'/gfx/pro/wordpress-xen-chat-pro.png" /></a>'.
				'<style type="text/css">#xen-chat-proContainer .button { display: none; } #xen-chat-proContainer ul li { font-size: 1.3em; }</style>'.
				'<br />'.
				'<h2>Boost user engagement, build a community, increase conversion!</h2>'.
				'<h2 style="padding-top: 1px; font-size: 20px;">Try Xen Chat Pro plugin for WordPress and BuddyPress</h2>'.
				'<br />'.
				'<a class="button-secondary wcAdminButtonPro" target="_blank" href="https://kainex.pl/projects/wp-plugins/xen-chat-pro?source=settings-page" title="Check Xen Chat Pro">
					Check Xen Chat <strong>Pro</strong>
				</a>'.
				' <a class="button-secondary wcAdminButtonPro wcAdminButtonProDemo" target="_blank" href="https://kainex.pl/projects/wp-plugins/xen-chat-pro/demo/?source=settings-page" title="Check Xen Chat Pro">
					<strong>See Demo</strong>
				</a>'.
				'<br /><h3 style="font-size: 17px;">Xen Chat Pro features:</h3>'.
				'<ul>'.
				'<li>&#8226; All the features of Xen Chat free edition</li>'.
				'<li>&#8226; Private one-to-one messages</li>'.
				'<li>&#8226; Avatars</li>'.
				'<li>&#8226; Voice messages</li>'.
				'<li>&#8226; Messages reactions (liking)</li>'.
				'<li>&#8226; Facebook-like chat mode</li>'.
				'<li>&#8226; BuddyPress integration: friends and groups</li>'.
				'<li>&#8226; Custom emoticons</li>'.
				'<li>&#8226; E-mail notifications</li>'.
				'<li>&#8226; Pending messages (fully moderated messages)</li>'.
				'<li>&#8226; External authentication (via Facebook, Twitter or Google+)</li>'.
				'<li>&#8226; WordPress multisite support</li>'.
				'<li>&#8226; Three Pro themes</li>'.
				'<li>&#8226; Chat button on profile page</li>'.
				'<li>&#8226; Edit posted messages</li>'.
				'<li>&#8226; Replying to messages</li>'.
				'<li>&#8226; Hooks</li>'.
				'<li>&#8226; Free updates for 6, 12 or 18 months</li>'.
				'<li>&#8226; Eternal license</li>'.
				'<li>&#8226; Premium support</li>'.
				'<li>&#8226; Pay once, use forever</li>'.
				'</ul>'.
				'<a target="_blank" href="https://kainex.pl/projects/wp-plugins/xen-chat-pro?source=settings-page" title="Check Xen Chat Pro">
					<img src="'.$this->options->getBaseDir().'/gfx/pro/xen-chat-pro-lead.png" />
				</a>'.
				'<br />'.
				'<a class="button-secondary wcAdminButtonPro" target="_blank" href="https://kainex.pl/projects/wp-plugins/xen-chat-pro?source=settings-page" title="Check Xen Chat Pro">
					Check Xen Chat <strong>Pro</strong>
				</a>'
			),

		);
	}
	
	public function getDefaultValues() {
		return array(

		);
	}
}