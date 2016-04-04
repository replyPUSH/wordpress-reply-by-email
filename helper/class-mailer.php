<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class ReplyByEmailMailer {

	protected $the_post;
	protected $utility;
	protected $reply_push_model;
	protected $subscribe2;

	protected $collated_parents = array();

	public function __construct( $args ) {
		$this->the_post = $args['the_post'];
		$this->utility  = $args['utility'];
		$this->reply_push_model = $args['reply_push_model'];
		$this->subscribe2 = $args['subscribe2'];
	}

	protected function load_post( $type, $post ) {
		$load_post = "load_{$type}";
		$this->the_post = $post;
		$post->post_type = $type;
		$this->$load_post( $post );
	}

	public function send_to_subscribers( $type, $post ) {
		$this->load_post( $type, $post );
		$this->subscribe2->subscribe2_options['sender'] = 'blogname';
		$this->subscribe2->publish( $this->the_post );
	}

	public function send_approval_notice( $type, $post ) {
		$this->load_post( $type, $post );
		if ( isset($this->the_post->comment_author_email) ) {
			$email = $this->the_post->comment_author_email;
		} else {
			$user = get_user_by('id', $this->the_post->post_author );
			$email = $user->user_email;
		}
		// email poster
		wp_mail(
			$email,
			$this->collate_subject( __('Awaiting approval', 'reply-by-email') ),
			str_replace(
				'{BLOGNAME}',
				html_entity_decode( get_option('blogname'), ENT_QUOTES ),
				__('reply-push-main-awaiting', 'reply-by-email')
			)
		);

		// email admin
		wp_mail(
			get_option('admin_email'),
			__('Requires approval', 'reply-by-email'),
			str_replace(
				array('{LINK}', '{TYPE_LINK_WORDS}'),
				array( admin_url( 'edit-comments.php?comment_status=moderated'), $this->the_post->post_type ),
				__('reply-push-main-approval', 'reply-by-email')
			)
		);
	}

	protected function get_collated_parent( $post ) {
		if ( isset( $this->the_post ) ) {
			if ( $this->reply_push_model->collated( $post->post_type ) ) {
				$post_id = $this->the_post->post_parent;
			} else {
				$post_id = $this->the_post->ID;
			}

			if ( isset( $this->collated_parents[ $post_id ] ) ) {
				$post = $this->collated_parents[ $post_id ];
			} else {
				$post = $this->collated_parents[ $post_id ] = get_post( $post_id );
			}
		}
		return $post;
	}

	public function load_comment( $comment ) {
		$this->the_post->permalink = get_comment_link( $comment );
		$this->the_post->post_content = $comment->comment_content;
		$this->the_post->post_excerpt = $comment->comment_content;
		$this->the_post->post_author = $comment->user_id;
		$this->the_post->post_name  = $comment->comment_author;
		$this->the_post->ID = $this->the_post->comment_ID;
		$this->the_post->post_parent = $this->the_post->comment_post_ID;
		$post = $this->get_collated_parent( $this->the_post );
		$this->the_post->post_title = $post->post_title;
		$this->the_post->post_password = null;
	}

	public function exclude_self( $recipients ) {
		$user = get_user_by('id', $this->the_post->post_author );

		if ( $user ) {
			$recipients = array_diff( $recipients, array( $user->user_email ) );
		}

		return $recipients;
	}

	public function change_link() {
		if ( isset( $this->the_post->permalink ) ) {
			$this->subscribe2->permalink = $this->the_post->permalink;
			$this->subscribe2->post_title = '<a href="' . $this->subscribe2->permalink . '">' . html_entity_decode( $this->the_post->post_title, ENT_QUOTES ) . '</a>';
		}
	}

	public function add_keywords( $string ) {
		if ( isset( $this->the_post ) ) {
			$type = in_array( $this->the_post->post_type, array('comment') ) ? str_replace('{TYPE}', $this->the_post->post_type, __('{TYPE} on') ) : $this->the_post->post_type;
			$string = str_replace('{TYPE_LINK_WORDS}', $type , $string );
		}
		return $string;
	}

	protected function post_subject( $format, $post ) {
		$format = str_replace('{BLOGNAME}', html_entity_decode( get_option('blogname'), ENT_QUOTES ), $format );
		$format = str_replace('{TITLE}', stripslashes( $post->post_title ), $format );
		$format = str_replace('{ID}', $post->ID, $format );
		return $format;
	}

	public function collate_subject( $subject ) {
		if ( isset( $this->the_post ) ) {
			$post = $this->the_post;

			$post = $this->get_collated_parent( $post );

			$format = "{BLOGNAME} - {TITLE} [{ID}]";

			$format = apply_filters('rp_subject_format', $format, $subject, $post );

			$subject = $this->post_subject( $format, $post );
		}

		return $subject;
	}

	protected function strip_nl( $html ) {
		return str_replace( array("\n", "\r"), '', $html );
	}

	protected function strip_stray_br( $html ) {
		return preg_replace('`(<br />)?(</?(blockquote|p)[^>]*>)(<br />)?`i', '$2', $html );
	}

	protected function add_sig( $message ) {
		$message .= nl2br( str_replace('{CODE}', mt_rand(), __('reply-push-sig', 'reply-by-email') ) );
		return $message;
	}

	public function add_marker( $message ) {
		return '<a name="rp-message"></a><a href="http://replypush.com#rp-message"><wbr></a>' . $message;
	}

	public function html_email( $html, $subject, $message ) {

		$message = $this->add_marker( $message );

		if ( 'yes' == $this->subscribe2->subscribe2_options['stylesheet'] ) {
			$html = '<html><head><title>' . $subject . '</title><link rel="stylesheet" href="' . get_stylesheet_uri() . '" type="text/css" media="screen" /></head><body style="margin:5px;">' . $message . '</body></html>';
		} else {
			$html = '<html><head><title>' . $subject . '</title></head><body style="margin:5px;">' . $message . '</body></html>';
		}

		$html = $this->strip_nl( $html );
		$html = $this->strip_stray_br( $html );

		return $this->add_sig( $html );
	}

	public function text_email( $message ) {
		$message = $this->add_marker( $message );
		$message = nl2br( make_clickable( $message ) );
		return $this->add_sig( $message );
	}

	public function add_reference( $phpmailer ) {
		if ( isset( $this->the_post ) ) {
			$from_user_id   =  $this->the_post->post_author;
			$record_id      =  $this->the_post->ID;
			$type           =  $this->reply_push_model->post_types( $this->the_post->post_type );
			$content_id     =  $this->the_post->post_parent;
			$time_stamp     =  time();

			$recipient = $phpmailer->getToAddresses();
			$recipient = $recipient[0][0];

			$data = sprintf("%08x%08x%-8s%08x%08x", $from_user_id, $record_id, $type, $content_id, $time_stamp );

			extract( $this->utility->credentials() );

			$reply_push = new ReplyPush( $account_no, $secret_id, $secret_key, $recipient, $data, $this->utility->hash_algo() );

			$message_id = $reply_push->reference();

			//get special reference hash for threading
			$ref_hash = $this->reply_push_model->ref_hash( $type, $record_id, $content_id, $recipient );

			//get historic reference for threading
			$ref = $this->reply_push_model->get_ref( $ref_hash );

			//save current Message ID as ref
			$this->reply_push_model->save_ref( $ref_hash, $message_id );

			//add headers if historic references
			if( $ref ){
				$phpmailer->addCustomHeader('References', $ref );
				$phpmailer->addCustomHeader('In-Reply-To', $ref );
			}

			$phpmailer->MessageID = $message_id;
		}
	}

	public function add_headers( $headers ) {
		if ( isset( $this->the_post ) ) {
			if ( ! $this->utility->valid_credencials() ) {
				return $headers;
			}

			$headers = $this->utility->email_headers_unzip( $headers );

			$user = get_user_by('id', $this->the_post->post_author );

			unset( $headers['List-Id'] );
			unset( $headers['Precedence'] );

			$headers['Reply-To']     = $this->utility->encode_email_name( isset($user->display_name) ? $user->display_name : $user->login );
			$headers['Content-Type'] = get_option('html_type') . '; charset="'. get_option('blog_charset') . '"';

			$headers = $this->utility->email_headers_zip( $headers );
		}

		return $headers;
	}
}
