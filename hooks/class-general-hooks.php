<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class ReplyByEmailGeneralHooks extends ReplyByEmailHooks {

	public function load_language() {
		load_plugin_textdomain( 'reply-by-email', false, dirname( plugin_basename( REPLY_BY_EMAIL_PATH . 'reply-by-email.php' ) ) . '/languages' );
	}

	public function i18n__plugins_loaded__action() {
		$this->load_language();
	}
}
