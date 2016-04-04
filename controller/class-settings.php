<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class ReplyByEmailSettingsController extends ReplyByEmailController {

	protected $utility;

	function __construct( $args ) {
		$this->utility = $args['utility'];
		$this->post = array_map('stripslashes_deep', $args['post']);
		parent::__construct( $args );
	}

	public function index() {

		$notify_uri = $this->utility->notify_uri();
		$errors = array();

		if( !empty( $this->post ) && isset( $this->post['reply_push_settings'] ) && wp_verify_nonce( $this->post['reply_push_settings'], 'save_credencials' ) ){
			if ( !isset( $this->post['account_no'] ) ) {
				$errors[] = __( 'Account No missing', 'reply-by-email' );
			}

			if ( !isset( $this->post['secret_id'] ) ) {
				$errors[] = __( 'Secret ID missing', 'reply-by-email' );
			}

			if ( !isset( $this->post['secret_key'] ) ) {
				$errors[] = __( 'Secret Key missing', 'reply-by-email' );
			}

			if ( isset( $this->post['main_template'] ) ) {
				$this->utility->main_template( $this->post['main_template'] );
			} else {
				$errors[] = __( 'Notify Template missing', 'reply-by-email' );
			}

			if (isset( $this->post['sig_template'] ) ) {
				$this->utility->template('reply-push-sig', $this->post['sig_template'] );
			} else {
				$errors[] = __( 'Notify Footer missing', 'reply-by-email' );
			}

			if ( isset( $this->post['main_template'] ) && strpos( $this->post['main_template'], '{TYPE_LINK_WORDS}') === false ) {
				$errors[] = __( 'Recommended {TYPE_LINK_WORDS} missing from Notify Template', 'reply_by_email' );
			}

			if ( isset( $this->post['main_template'] ) && strpos( $this->post['main_template'], '{POST}') === false ) {
				$errors[] = __( 'Recommended {POST} or {BLOCKQUOTE_POST} missing from Notify Template', 'reply_by_email' );
			}

			if ( isset( $this->post['main_template'] ) && strpos( $this->post['main_template'], '{PERMALINK}') === false && strpos( $this->post['main_template'], '{TINYLINK}') === false ) {
				$errors[] = __( 'Recommended {PERMALINK} or {TINYLINK} missing from Notify Template', 'reply_by_email' );
			}

			if ( isset( $this->post['sig_template'] ) && strpos( $this->post['sig_template'], '{CODE}') === false ) {
				$errors[] = __( 'Recommended {CODE} missing from Notify Footer', 'reply_by_email' );
			}

			if ( empty( $errors ) ) {
				$account_no = $this->post['account_no'];
				$secret_id  = $this->post['secret_id'];
				$secret_key = $this->post['secret_key'];
				try {
					ReplyPush::validateCredentials( $account_no, $secret_id, $secret_key );
					$credencials = compact( 'account_no', 'secret_id', 'secret_key' );
					$this->utility->credentials( $credencials );
				} catch ( ReplyPushError $e ) {
					$errors[] = __( $e->getMessage(), 'reply_by_email' );
				}
			}
		}

		$this->render('settings', array( 'credencials' => $this->utility->credentials(), 'notify_uri' => $notify_uri, 'errors' => $errors, 'main_template' => $this->utility->main_template(), 'sig_template' => $this->utility->template('reply-push-sig') ) );
	}
}
