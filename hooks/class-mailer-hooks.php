<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class ReplyByEmailMailerHooks extends ImplicitHooks {
	
	protected $notify = false;
	
	
	protected function start_notify() {
		$this->notify = true;
	}
	
	protected function end_notify() {
		if ( $this->notify ) {
			$this->notify = false;
		}
	}
	
	public function notify__s2_send_plain_excerpt_subscribers__filter( $recipients, $post_id ) {
		$this->start_notify();
		return $recipients;
	}
	
	public function notify__s2_send_plain_fullcontent_subscribers__filter( $recipients, $post_id ) {
		$this->start_notify();
		return $recipients;
	}
	
	public function notify__s2_send_html_excerpt_subscribers__filter( $recipients, $post_id ) {
		$this->start_notify();
		return $recipients;
	}
	
	public function notify__s2_send_html_fullcontent_subscribers__filter( $recipients, $post_id ) {
		$this->start_notify();
		return $recipients;
	}
	
	public function notify__s2_send_public_subscribers_filter( $recipients, $post_id ) {
		$this->start_notify();
		return $recipients;
	}
	
	public function end_notify__wp_mail__filter( $args ) {
		$this->end_notify();
		return $args;
	}
	
	public function add_comments__s2_post_types__filter( $post_types ) {
		if ( $this->notify && is_array($post_types) ) {
			$post_types[] = 'comment';
		}
		
		return $post_types;
	}
	
	public function collate_subject__s2_email_subject__filter( $subject ) {
		if ( $this->notify ) {
			$subject = $this->services->get('mailer')->collate_subject( $subject );
		}
		
		return $subject;
	}
	
	public function add_sig__s2_html_email__filter( $html, $subject, $message ) {
		if ( $this->notify ) {
			$html = $this->services->get('mailer')->html_email( $html, $subject, $message );
		}
		
		return $html;
	}
	
	public function add_sig__s2_plain_email__filter( $message ) {
		if ( $this->notify ) {
			$message = $this->services->get('mailer')->text_email( $message );
		}
		
		return $message;
	}
	
	public function add_headers__wp_mail__filter( $atts ) {
		if ( $this->notify ) {
			$atts['headers'] = $this->services->get('mailer')->add_headers( $atts['recipient'], $atts['headers'] );
		}
		
		return $atts;
	}
	
}

