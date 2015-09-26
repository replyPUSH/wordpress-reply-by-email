<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class ReplyByEmailSettingsHooks {
	
	public $required_msg = '';
	public $deactivate = false;
	
	public function migrate__init__on_activate__action() {
		if ( is_admin() && current_user_can( 'activate_plugins' ) &&  !is_plugin_active( 'subscribe2/subscribe2.php' ) ) {
			$this->deactivate = true;
		} else {
			$this->services('rp_model')->structure();
		}
	}
	
	public function require_plugins__admin_init__action() {
		if ( $this->deactivate ) {
			
			$this->required_msg = __('subscribe2-required', 'reply-by-email');

			deactivate_plugins( REPLY_BY_EMAIL_PATH . 'reply-by-email.php' ); 

			if ( isset( $_GET['activate'] ) ) {
				unset( $_GET['activate'] );
			}
		} else {
			
			$subscribe2_options = get_option('subscribe2_options');
			
			// ensure there is no bcc
			if ( $subscribe2_options['bcclimit'] !== 1 ) {
				$subscribe2_options['bcclimit'] = 1;
				update_option( 'subscribe2_options', $this->subscribe2_options );
			}
			
		}
	}
	
	public function deactivate_notice__admin_notices__action() {
		if ( $this->required_msg !='' ) {
			echo '<div class="error"><p><strong>' . $this->required_msg . '</strong></p></div>';
		}
	}
	
	public function main__admin_menu__action() {
		add_options_page( __('Reply By Email', 'reply-by-email'), __('Reply By Email', 'reply-by-email'), 'manage_options', __FILE__, array( $this, 'settings_page') ); 
	}
	
	public function settings_page() {
		$this->services('settings_controller')->index();
	}
}
