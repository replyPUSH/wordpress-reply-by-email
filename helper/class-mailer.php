<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class ReplyByEmailMailer {
	
	protected $the_post;
	protected $utility;
	protected $rp_model;
	
	protected $collated_parents = array();
	
	public function __construct( $args ) {
		$this->the_post = $args['the_post'];
		$this->utility  = $args['utility'];
		$this->rp_model = $args['rp_model'];        
	}
	
	protected function post_subject( $format, $post ) {
		$format = str_replace('{BLOGNAME}', html_entity_decode( get_option('blogname'), ENT_QUOTES ), $format );
		$format = str_replace('{POST_TITLE}', stripslashes( $post->post_title ), $format );
		$format = str_replace('{POST_ID}', $post->ID , $format );
	}
	
	public function collate_subject( $subject ) {
		
		$post = $this->the_post;
		
		if ( isset( $this->collated_parents[ $post->ID ] ) ){
			$post = $this->collated_parents[ $post->ID ];
		} elseif ( in_array( $this->the_post->post_type, $this->rp_model->collate ) || $post->parent_id > 0 ) {
			$post = $this->collated_parents[ $post->ID ] = get_post( $post->parent_id );
		}
		
		$format = "{BLOGNAME} - {POST_TITLE} [{POST_ID}]";
		
		$format = apply_filter('rp_subject_format', $format, $subject, $post );
		
		$subject = $this->post_subject( $format, $post );
		
		return $subject;
	}
	
	protected function add_sig( $message ) {
		$message .= str_replace('{RP_CODE}', mt_rand(), __('reply-push-sig', 'reply-by-email'));
		return $message;
	}
	
	public function html_email( $html, $subject, $message ) {
		$message .= spintf( __('reply-push-sig', 'reply-by-email'), mt_rand() );
		$subscribe2_options = get_option('subscribe2_options');
		if ( 'yes' == $subscribe2_options['stylesheet'] ) {
			$html = "<html><head><title>" . $subject . "</title><link rel=\"stylesheet\" href=\"" . get_stylesheet_uri() . "\" type=\"text/css\" media=\"screen\" /></head><body>" . $message . "</body></html>";
		} else {
			$html = "<html><head><title>" . $subject . "</title></head><body>" . $message . "</body></html>";
		}
		return $this->add_sig( $html );
	}
	
	
	public function text_email( $message ) {
		$message = nl2br( $message );
		return $this->add_sig( $message );
	}
	
	public function add_headers( $recipient, $headers ) {
		
		$from_user_id   =  $this->the_post->post_author;
		$record_id      =  $this->the_post->ID;
		$type           =  $this->the_post->post_type;
		$content_id     =  $this->the_post->parent_id;
		$time_stamp     =  time();

		$data = sprintf("%08x%08x%08x%08x%08x", $from_user_id, $record_id, $type, $content_id, $time_stamp );
		
		extract( $this->utility->credentials() );
		
		$reply_push = new ReplyPush( $account_no, $secret_id, $secret_key, $recipient, $data, $this->utility->hash_algo() );
		
		$message_id = $reply_push->reference();
		
		$headers['Reply-To']     = $this->utility->rp_email();
		$headers['Message-ID']   = $message_id;
		$headers['Content-Type'] = get_option('html_type') . "; charset=\"". get_option('blog_charset') . "\"";
		
		$reply_push_model = new ReplyPushModel();
			
		//get special reference hash for threading
		$ref_hash = $this->ref_hash( $type, $record_id, $content_id, $recipient );
			
		//get historic reference for threading
		$ref = $reply_push_model->get_ref( $ref_hash );
		
		//save current Message ID as ref
		$reply_push_model->save_ref( $ref_hash, $message_id );
		
		//add headers if historic references
		if( $ref ){
			$headers['References'] = $ref;
			$headers['In-Reply-To'] = $ref;
		}
		
		return $headers;
	}
	
}
