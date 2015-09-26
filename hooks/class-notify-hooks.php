<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class ReplyByEmailNotifyHooks extends ImplicitHooks {
	
	protected $flush = false;
	
	protected $notify_url = array(
		'replypush/notify/([^/]*)/?' => 'index.php?replypush=notify&uri=$matches[1]'
	);
	
	public function notify_url__rewrite_rules_array__on_activate__filter( $rules ) {
		$this->flush = true;
		return array_merge( $this->notify_url, $rules );
	}
	
	public function notify_url__rewrite_rules_array__on_deactivate__filter( $rules ) {
		$notify_url_index = key( $this->notify_url );
		if( isset( $rules[ $notify_url_index ] ) ) {
			unset( $rules[ $notify_url_index ] );
			$this->flush = true;
		}
		
		return $rules;
	}
	
	public function flush_rules__wp_loaded__action() {
		if( $this->flush ) {
			$this->service('utility')->flush_rewrite_rules();
			$this->flush = false;
		}
	}
	
	public function notify_route__query_vars__filter( $query_vars ) {
		$query_vars[] = 'replypush';
		$query_vars[] = 'uri';
		return $query_vars;
	}
	
	public function incoming_notification__init__action() {
		if ( $this->services('utility')->query_vars('replypush') == 'notify' && $this->services('utility')->query_vars('uri') ) {
			$this->services('notify_controller')->process_incoming_notification( $this->services('utility')->query_vars('uri') );
		}
	}
	
	public function approval__pre_comment_approved__filter( $approved, $commentdata ) {
		// unregistered subscribers require approval
		if ( $this->services('utility')->query_vars('replypush') == 'notify' 
		&& $this->services('utility')->query_vars('uri') 
		&& !$commentdata['user_id'] ) {
			$approved = 0;
		}
		return $approved;
	}
	
}
