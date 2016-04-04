<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class ReplyByEmailSettingsHooks extends ReplyByEmailHooks {

	public function migrate__register__activate() {
		if ( is_admin() && current_user_can( 'activate_plugins' ) &&  !is_plugin_active( 'subscribe2/subscribe2.php' ) ) {
			$this->get('general')->load_language();
			die('<div class="error"><p><strong>' . __('subscribe2-required', 'reply-by-email') . '</strong></p></div>');
		} else {
			$subscribe2_options = get_option('subscribe2_options');

			$subscribe2_options['mailtext_org'] = $subscribe2_options['mailtext'];

			if ( isset( $subscribe2_options['mailtext_rp'] ) ) {
				$subscribe2_options['mailtext'] = $subscribe2_options['mailtext_rp'];
			}

			update_option('subscribe2_options', $subscribe2_options );

			$this->service('reply_push_model')->structure();
		}
	}

	public function require_plugins__register__deactivate() {
		$subscribe2_options = get_option('subscribe2_options');

		$subscribe2_options['mailtext_rp'] = $subscribe2_options['mailtext'];

		if ( isset( $subscribe2_options['mailtext_org'] ) ) {
			$subscribe2_options['mailtext'] = $subscribe2_options['mailtext_org'];
		}
		// ensure there is no bcc
		if ( $subscribe2_options['bcclimit'] !== 1 ) {
			$subscribe2_options['bcclimit'] = 1;
		}

		update_option('subscribe2_options', $subscribe2_options );
	}

	public function add_link__plugin_action_links__action( $links, $plugin_file ) {
		if ( dirname( $plugin_file ) == basename( REPLY_BY_EMAIL_PATH ) ) {
			$settings_link = '<a href="admin.php?page=reply-by-email-settings">Settings</a>';
			array_unshift( $links, $settings_link );
		}
		return $links;
	}

	public function main__admin_menu__action() {
		$menu_icon = plugins_url( basename( REPLY_BY_EMAIL_PATH ) . '/design/images/smallicon.png' );
		add_menu_page( __('Reply By Email', 'reply-by-email'), __('Reply By Email', 'reply-by-email'), 'read', 'reply-by-email-settings', array( $this, 'settings_page'), $menu_icon );
	}

	public function settings_page() {
		$this->service('settings_controller')->index();
	}
}
