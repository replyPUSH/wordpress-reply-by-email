<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class ReplyByEmailMailerHooks extends ReplyByEmailHooks {

	protected $notify = false;
	protected $add_ref = false;
	protected $recipients = array();

	protected function start_notify( $recipients ) {

		$this->recipients = array_unique( $recipients );

		$recipients = $this->service('mailer')->exclude_self( $recipients );

		if ( ! empty( $this->recipients ) ) {
			$this->notify = true;
		}
		return $recipients;
	}

	protected function clear_recipient( $recipient ) {
		$this->recipients = array_diff( $this->recipients, array( $recipient ) );
	}

	protected function end_notify() {
		if ( $this->notify && empty( $this->recipients ) ) {
			$this->notify = false;
			$this->add_ref = false;
		}
	}

	public function moderate__notify_moderator__action( $comment_id ) {
		$comment = get_comment( $comment_id );
		if ( $comment && !$comment->comment_approved ) {
			$this->add_ref = true;
			$this->service('mailer')->load_comment( $comment );
		}
	}

	public function notify_init__comment_post__action( $comment_id, $comment_approved ) {
		$comment = get_comment( $comment_id );
		if ( $comment->comment_approved ) {
			$this->service('mailer')->send_to_subscribers('comment', $comment );
		} else {
			$this->add_ref = true;
			$this->service('mailer')->send_approval_notice('comment', $comment );
		}
	}

	public function notify_init__comment_unapproved_to_approved__action( $comment ) {
		$this->service('mailer')->send_to_subscribers('comment', $comment );
	}

	public function notify__s2_send_plain_excerpt_subscribers__filter( $recipients ) {
		$this->start_notify( $recipients );
		return $recipients;
	}

	public function notify__s2_send_plain_fullcontent_subscribers__filter( $recipients ) {
		$recipients = $this->start_notify( $recipients);
		return $recipients;
	}

	public function notify__s2_send_html_excerpt_subscribers__filter( $recipients) {
		$recipients = $this->start_notify( $recipients);
		return $recipients;
	}

	public function notify__s2_send_html_fullcontent_subscribers__filter( $recipients) {
		$recipients = $this->start_notify( $recipients);
		return $recipients;
	}

	public function notify__s2_send_public_subscribers__filter( $recipients ) {
		$recipients = $this->start_notify( $recipients );
		return $recipients;
	}

	public function add_comments__s2_post_types__filter( $post_types ) {
		$post_types[] = 'comment';
		$post_types[] = 'post';
		$post_types = array_unique( $post_types );
		return $post_types;
	}

	public function collate_subject__s2_email_subject__filter( $subject ) {
		if ( $this->notify ) {
			$subject = $this->service('mailer')->collate_subject( $subject );
		}
		return $subject;
	}

	public function add_post_keywords__s2_custom_keywords__filter( $string ) {
		return $this->service('mailer')->add_keywords( $string );
	}

	public function change_link__s2_email_template__filter( $mailtext ) {
		$this->service('mailer')->change_link();
		return $mailtext;
	}

	public function prep__s2_html_email__filter( $html, $subject, $message ) {
		if ( $this->notify ) {
			$html = $this->service('mailer')->html_email( $html, $subject, $message );
		}
		return $html;
	}

	public function prep__s2_plain_email__filter( $message ) {
		if ( $this->notify ) {
			$message = $this->service('mailer')->text_email( $message );
		}
		return $message;
	}

	public function add_headers__wp_mail__filter( $atts ) {
		if ( $this->notify ) {
			$this->clear_recipient( $atts['to'] );
			$atts['headers'] = $this->service('mailer')->add_headers( $atts['headers'] );
		}
		return $atts;
	}

	public function add_reference__phpmailer_init__action( $phpmailer ) {
		if ( $this->notify || $this->add_ref ) {
			$this->service('mailer')->add_reference( $phpmailer );
			$this->end_notify();
		}
	}
}
