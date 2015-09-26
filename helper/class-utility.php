<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class ReplyByEmailUtility {
	
	protected $wp_rewrite;
	protected $wp_query;
	protected $credentials;
	
	public function __construct( $args ) {
		$this->wp_rewrite = $args['wp_rewrite'];
		$this->wp_query   = $args['wp_query'];
	}
	
	public function query_vars( $key ) {
		if ( isset( $this->wp_query[ $key ] ) ) {
			return $this->wp_query[ $key ];
		} else {
			return null;
		}
	}
	
	public function flush_rewrite_rules() {
		$this->wp_rewrite->flush_rules();
	}
	
	public function rp_email() {
		return defined('REPLY_PUSH_EMAIL') ? REPLY_PUSH_EMAIL : 'post@replypush.com';
	}

	public function hash_algo() {
		return in_array('sha1', hash_algos() ) ? 'sha1': 'md5';
	}
	
	public function check_uri( $uri ) {
		return $uri == $this->notify_uri();
	}
	
	public function notify_uri() {
		$notify_uri = get_option('reply_push_notify_uri');
		
		if ( !$notify_uri ) {
			$notify_uri = uniqid();
			update_option( 'reply_push_notify_uri', $notify_uri );
		}
		return $notify_uri;
	}
	
	public function credentials( $save = array() ) {
		if( !empty( $save ) ) {
			update_option( 'reply_push_credencials', $save );
		}
		
		if( $this->credentials ) {
			$this->credentials = get_option('reply_push_credencials');
		}
		
		return $this->credentials;
	}
	
	/**
	* Parse html to text
	*
	* @param   string   $content
	* @return  string
	*/

	public function pre_format_html_content($content)
	{
		return trim(
			html_entity_decode(
				strip_tags(
					preg_replace(
						array(
							'`\n`',
							'`<br\s*/?>`i',
							'`<p(\s[^>]+)?>(.*?)</\s*p(\s[^>]+)?>`i',
							'`<div(\s[^>]+)?>(.*?)</\s*div(\s[^>]+)?>`i',
						),
						array(
							'',
							"\n",
							"$2\n",
							"$2\n",
						),
						$content
					)
				)
			)
		);
	}

	/**
	* Parse text clean it up
	*
	* @param   string   $content
	* @return  string
	*/
	public function pre_format_text_content($content)
	{
		return trim($content);
	}
	
	/**
	* Leave
	*
	* Convenience method for exiting framework
	* post-haste
	*
	* @param   string   $message output this message
	* @param   string   $code HTTP status code
	* @return  null
	*/
	public function leave( $message = '', $code = 200 ){
		status_header( $code );
		die( $message );
	}
}
