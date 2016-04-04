<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class ReplyByEmailUtility {

	protected $wp_query;
	protected $credentials;
	protected $subscribe2_options;

	public function __construct( $args ) {
		$this->wp_query   = $args['wp_query'];
	}

	public function query_vars( $key ) {
		if ( isset( $this->wp_query[ $key ] ) ) {
			return $this->wp_query[ $key ];
		} else {
			return null;
		}
	}

	public function service_email() {
		return defined('REPLY_PUSH_EMAIL') ? REPLY_PUSH_EMAIL : 'post@replypush.com';
	}

	public function email_headers_zip( $headers ) {
		$header_txt = '';
		foreach( $headers as $key => $value ) {
			$header_txt .= "{$key}: {$value}\n";
		}

		return $header_txt;
	}

	public function email_headers_unzip( $headers ) {
		$lines = explode("\n", $headers );
		$header_array = array();
		foreach( $lines as $line ) {
			list( $key, $value ) = explode( ":", $line, 2 );
			if ( ! $key ) {
				continue;
			}
			$header_array[ trim( $key ) ] = trim( $value );
		}
		return $header_array;
	}

	/**
	* Encode email name
	*
	* UTF-8 encoding of email name for headers
	*
	* @param   string   $name
	* @param   string   $email
	* @return  string
	*/
	public function encode_email_name( $name, $email = null ) {
		return sprintf('=?UTF-8?B?%s?= <%s>', base64_encode( $name ), $email ? $email : $this->service_email());
	}

	public function hash_algo() {
		return in_array('sha1', hash_algos() ) ? 'sha1': 'md5';
	}

	public function check_uri( $uri ) {
		return $uri == $this->notify_uri();
	}

	public function notify_uri() {
		$notify_uri = get_option('reply_push_notify_uri');

		if ( ! $notify_uri ) {
			$notify_uri = uniqid();
			update_option( 'reply_push_notify_uri', $notify_uri );
		}
		return $notify_uri;
	}

	public function credentials( $save = array() ) {

		if( !empty( $save ) ) {
			update_option( 'reply_push_credencials', $save );
		}

		if( empty( $this->credentials ) ) {
			$this->credentials = get_option('reply_push_credencials');
		}

		return $this->credentials;
	}

	public function valid_credencials() {

		$this->credentials();

		if ( !isset( $this->credentials['account_no'] )
			|| !isset( $this->credentials['secret_id'] )
			|| !isset( $this->credentials['secret_key'] ) ) {
			return false;
		}

		try {
			ReplyPush::validateCredentials(
				$this->credentials['account_no'],
				$this->credentials['secret_id'],
				$this->credentials['secret_key']
			);
		} catch (ReplyPushError $ex) {
			return false;
		}

		return true;
	}

	/**
	* Parse html to text
	*
	* @param   string   $content
	* @return  string
	*/
	public function pre_format_html_content( $content ) {
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
	public function pre_format_text_content( $content ) {
		return trim( $content );
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
	public function leave( $message = '', $code = 200 ) {
		status_header( $code );
		die( $message );
	}

	public function main_template( $save = false ) {

		require_once S2PATH . "include/options.php";
		$subscribe2_options_current = get_option('subscribe2_options');

		if ( $save ) {
			$subscribe2_options_current['mailtext'] = $save;
			update_option('subscribe2_options', $subscribe2_options_current );
			return;
		}

		if ($this->subscribe2_options['mailtext'] == $subscribe2_options_current['mailtext']) {
			return __('reply-push-main', 'reply-by-email');
		} else {
			return $subscribe2_options_current['mailtext'];
		}
	}

	public function template( $key, $save = false ) {

		$templates = get_option('reply_push_templates');

		if ( $save ) {
			$templates[ $key ] = $save;
			update_option('reply_push_templates', $templates );
			return;
		}

		$template_defaults = array(
			'reply-push-sig' => __('reply-push-sig', 'reply-by-email')
		);

		if ( ! $templates ) {
			$templates = array();
		}

		if ( isset( $templates[ $key ] ) ) {
			return $templates[ $key ];
		} else {
			return isset( $template_defaults[ $key ] ) ? $template_defaults[ $key ] : null;
		}
	}
}
