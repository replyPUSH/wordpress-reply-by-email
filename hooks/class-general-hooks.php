<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class ReplyByEmailGeneralHooks {
	
	public function i18n__plugins_loaded__action() {
		load_plugin_textdomain( 'reply-by-email', false, REPLY_BY_EMAIL_PATH . 'languages' );
	}
	
}
