<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class ReplyByEmailNotifyController extends ReplyByEmailController {

	protected $reply_push_model;
	protected $utility;
	protected $notification;
	protected $ref;

	function __construct( $args ) {
		$this->reply_push_model     = $args['reply_push_model'];
		$this->utility              = $args['utility'];
		$this->notification         = $args['post'];
		parent::__construct( $args );
	}

	/**
	* Denies Access
	*
	* Outputs Denied satus code and exit, with optional message
	*
	* @param string $denied_msg message to output on exit
	*/
	protected function denied( $denied_msg = '' ) {
		$this->utility->leave( $denied_msg, 403 );
	}

	/**
	* leave Request
	*
	* Ends request
	*
	* @param string $denied_msg message to output on exit
	*/
	protected function leave( $leave_msg = '' ) {
		$this->utility->leave( $leave_msg );
	}

	/**
	* For Checking Notification Url
	*
	* Checks uri code is correct, if not denies
	*
	* @param string $uri code randomly generated on setup
	*/
	public function ping( $uri )
	{
		if ( !$this->utility->check_uri( $uri ) ) {
			$this->denied('DENIED');
		}
		// I'm here ...
		$this->leave('OK');
	}

	public function process_incoming_notification( $uri ) {
		if ( !$this->utility->check_uri( $uri ) ) {
			$this->denied('DENIED');
		}

		// no credentials can't process
		if ( !$this->utility->credentials() ) {
			$this->denied();
		}

		$notification = $this->notification;

		if ( empty( $notification ) ) {
			$this->leave();
		}

		if ( !isset( $notification['msg_id'] ) ) {
			$this->denied();
		}

		if ( $this->reply_push_model->get_transaction( $notification['msg_id'] ) ) {
			$this->leave();
		}

		// get credentials
		extract( $this->utility->credentials() );

		// authenticate
		$reply_push = new ReplyPush( $account_no, $secret_id, $secret_key, $notification['from'], $notification['in_reply_to'] );

		if ( $reply_push->hashCheck() ) {

			// split 56 bytes into 8 byte components and process
			$message_data = str_split( $reply_push->referenceData, 8 );

			$from_user_id = hexdec( $message_data[2] );
			$record_id    = hexdec( $message_data[3] );
			$type         = trim( $message_data[4] );
			$content_id   = hexdec( $message_data[5] );

			// don't know what you are talking about
			if ( !isset( $type ) ) {
				$this->leave();
			}

			// get special reference key for threading
			$ref_hash = $this->reply_push_model->ref_hash( $type, $record_id, $content_id, $notification['from'] );

			// get historic Reference for threading
			$this->ref = $this->reply_push_model->get_ref( $ref_hash );

			// save current message id as Ref
			$this->reply_push_model->save_ref( $ref_hash, $notification['from_msg_id'] );

			$this->process_comment_notification(
				$notification['from'],
				isset( $notification['from_name'] ) && $notification['from_name'] ? $notification['from_name'] : 'anon',
				$content_id ? $content_id : $record_id,
				$content_id ? $record_id : 0,
				$notification['content']['text/html'] ?
					$this->utility->pre_format_html_content( $notification['content']['text/html'] ) :
					$this->utility->pre_format_text_content( $notification['content']['text/plain'] )
			);
		}

		// don't save actual message
		unset( $notification['content'] );

		// save transaction
		$this->reply_push_model->log_transaction( $notification );

		// no output
		$this->leave();
	}

	public function process_comment_notification( $email, $email_name, $post_id, $parent_id, $comment ) {

		// this section similar to wp-comments-post.php
		$user = get_user_by( 'email', $email );

		if ( $user && $user->exists() ) {
			wp_set_current_user( $user->ID );
		}

		$post = get_post( $post_id );

		if ( empty( $post->comment_status ) ) {
			do_action( 'comment_id_not_found', $post_id );
			$this->leave();
		}

		$status = get_post_status( $post );

		$status_obj = get_post_status_object( $status );

		if ( ! comments_open( $post_id ) ) {
			do_action( 'comment_closed', $post_id );
			$this->denied();
		} elseif ( 'trash' == $status ) {
			do_action( 'comment_on_trash', $post_id );
			$this->leave();
		} elseif ( ! $status_obj->public && ! $status_obj->private ) {
			do_action( 'comment_on_draft', $post_id );
			$this->leave();
		} elseif ( post_password_required( $post_id ) ) {
			do_action( 'comment_on_password_protected', $post_id );
			$this->leave();
		} else {
			do_action( 'pre_comment_on_post', $post_id );
		}

		if ( $user && $user->exists() ) {

			if ( empty( $user->display_name ) ) {
				$user->display_name = $user->user_login;
			}

			$author       = wp_slash( $user->display_name );
			$author_url   = wp_slash( $user->user_url ? $user->user_url : 'http://' );
			$author_id    = $user->ID;
		} else {

			if ( get_option( 'comment_registration' ) || 'private' == $status ) {
				$this->denied();
			}

			$anon = get_user_by('email', 'anon@replypush.com');

			if ( !$anon ) {
				$this->leave();
			}

			$author       = $email_name;
			$author_url   = 'http://';
			$author_id    = $anon->ID;
		}

		$data = array(
			'comment_post_ID'       => $post_id,
			'comment_author'        => $author,
			'comment_author_email'  => $email,
			'comment_author_url'    => $author_url,
			'comment_content'       => $comment,
			'comment_type'          => 'comment',
			'comment_parent'        => $parent_id,
			'user_id'               => $author_id,
		);

		// ensure notified
		if ( !get_option( 'moderation_notify' ) && get_option( 'rp_moderation_notify' ) ) {
				$alloptions = wp_load_alloptions();
				if ( isset( $alloptions['moderation_notify'] ) ) {
					$alloptions['moderation_notify'] = 1;
					wp_cache_set( 'alloptions', $alloptions, 'options' );
				} else {
					wp_cache_set( 'moderation_notify', 1, 'options' );
				}
		}

		$comment_id = wp_new_comment( $data );
	}

	public function send_reply_error( $email, $msg ) {
		wp_mail( $email, $this->notification['subject'], str_replace('{ERROR_MSG}', html_entity_decode( $msg ), __('reply-push-send-error', 'reply-by-email') ), $this->utility->email_headers_zip( array( 'References' => $this->notification['from_msg_id'], 'In-Reply-To' => $this->notification['from_msg_id'] ) ) );
	}
}
