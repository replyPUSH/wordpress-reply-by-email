<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class ReplyByEmailNotifyHooks extends ReplyByEmailHooks {

	public function notify_route__query_vars__filter( $query_vars ) {
		$query_vars[] = 'replypush';
		$query_vars[] = 'uri';
		return $query_vars;
	}

	public function incoming_notification__template_redirect__action() {
		if ( $this->service('utility')->query_vars('replypush') == 'notify' && $this->service('utility')->query_vars('uri') ) {
			$this->service('notify_controller')->process_incoming_notification( $this->service('utility')->query_vars('uri') );
		}
	}

	public function approval__pre_comment_approved__filter( $approved, $commentdata ) {
		// unregistered subscribers require approval
		if ( $this->service('utility')->query_vars('replypush') == 'notify'
		&& $this->service('utility')->query_vars('uri')
		&& ! $commentdata['user_id'] ) {
			$approved = 0;
		}
		return $approved;
	}

	public function duplicate_error__comment_duplicate_trigger__action( $commentdata ) {
		if ( $this->service('utility')->query_vars('replypush') == 'notify' && $this->service('utility')->query_vars('uri') ) {
			$msg = __('Duplicate comment detected; it looks as though you&#8217;ve already said that!', 'reply-by-email' );
			$this->service('notify_controller')->send_reply_error( $commentdata['comment_author_email'], $msg );
		}
	}

	public function duplicate_error__comment_flood_trigger__action( $commentdata ) {
		if ( $this->service('utility')->query_vars('replypush') == 'notify' && $this->service('utility')->query_vars('uri') ) {
			$msg = __('You are posting comments too quickly. Slow down.', 'reply-by-email' );
			$this->service('notify_controller')->send_reply_error( $commentdata['comment_author_email'], $msg );
		}
	}
}
