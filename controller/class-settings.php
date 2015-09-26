<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class ReplyByEmailSettingsController extends ReplyByEmailController {
	
	protected $utility;
	
	function __construct( $args ) {
		$this->utility = $args['utility'];
		$this->post = $args['post'];
		parrent::__construct( $args );
	}
	
	public function index() {
		$notify_uri = $this->notify_uri();
		$errors = array();
		if( !empty($this->post) && isset( $this->post['reply_push_settings'] ) && wp_verify_nonce( $this->post['reply_push_settings'], 'save_credencials' ) ){
			
			if( !isset($this->post['account_no']) ) {
				$errors[] = _( 'Account No missing', 'reply_by_email' );
			}
			
			if( !isset($this->post['secret_id']) ) {
				$errors[] = _( 'Secret ID missing', 'reply_by_email' );
			}
			
			if( !isset($this->post['secret_key']) ) {
				$errors[] = _( 'Secret Key missing', 'reply_by_email' );
			}
			
			if ( !empty( $errors ) ) {
				$account_no = $this->post['account_no'];
				$secret_id  = $this->post['secret_id'];
				$secret_key = $this->post['secret_key'];
				
				try {
					ReplyPush::validateCredentials( $account_no, $secret_id, $secret_key );
					$credencials = compact( 'account_no', 'secret_id', 'secret_key' );
					$this->utility->credentials( $credencials );
				} catch ( ReplyPushError $e ) {
					$errors[] = _( $e->message, 'reply_by_email' );
				}
			}
		}
		$this->data( array( 'credencials' => $this->utility->credentials(), 'notify_uri' => $notify_uri, 'errors' => $errors ) );
		$this->render('settings');
	}
	
	
}
