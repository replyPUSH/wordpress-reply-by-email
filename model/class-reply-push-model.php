<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class ReplyPushModel {

	protected $wpdb;

	protected $post_types = array('post' => 'post', 'comment' => 'comnt');
	protected $collate = array('comnt' => 'post');

	static protected $ref = array();

	function __construct( $args ) {
		$this->wpdb = $args['wpdb'];
	}

	public function post_types( $name ) {
		return $this->post_types[ $name ];
	}

	public function collated( $type ) {
		return isset( $this->collate[ $this->post_types( $type ) ] );
	}

	public function ref_hash( $type, $record_id, $content_id, $recipient ) {

		if ( isset( $this->collate[ $type ] ) ) {
			$record_id = $content_id;
			$type = $this->collate[ $type ];
		}

		return md5( $type . $record_id . $recipient );
	}

	public function is_subscriber( $email ) {
		$this->wpdb->prepare(
			"SELECT id FROM {$this->wpdb->prefix}subscribe2 WHERE email = %s",
			$email
		);
		return $this->wpdb->get_var( $sql );
	}

   /**
	* Gets Ref using ref hash if exits
	*
	* @param   string            $ref_hash
	* @return  string
	*/
	public function get_ref( $ref_hash ) {
		if ( array_key_exists( $ref_hash, self::$ref ) ) {
			return self::$ref[ $ref_hash ];
		}

		$sql = $this->wpdb->prepare(
			"SELECT ref FROM {$this->wpdb->prefix}reply_push_ref WHERE ref_hash = %s",
			$ref_hash
		);

		$row = $this->wpdb->get_row( $sql );

		if ( !$row ) {
			return '';
		}
		return $row->ref;
	}

	/**
	* Stashed Ref by ref_hash
	*
	* @param   string            $ref_hash
	* @param   string            $ref
	* @return  null
	*/
	public function save_ref( $ref_hash, $ref ) {
		if ( !$ref_hash || !$ref ) {
			return;
		}

		if ($this->get_ref( $ref_hash )) {
			$sql = $this->wpdb->prepare(
				"UPDATE {$this->wpdb->prefix}reply_push_ref SET ref = %s WHERE ref_hash = %s",
				$ref,
				$ref_hash
			);

			$result = $this->wpdb->query( $sql );

			self::$ref[ $ref_hash ] = $ref;
		} else {
			$sql = $this->wpdb->prepare(
				"INSERT INTO {$this->wpdb->prefix}reply_push_ref (ref, ref_hash) VALUES (%s, %s)",
				$ref,
				$ref_hash
			);
			$result = $this->wpdb->query( $sql );
		}

		return $result;
	}

	/**
	* Gets Transaction, to prevent collisions.
	*
	* @param   int            $msg_id
	* @return  array[sting]string
	*/
	public function get_transaction($msg_id) {
		$sql = $this->wpdb->prepare(
			"SELECT message_id FROM {$this->wpdb->prefix}reply_push_log WHERE message_id = %s",
			$msg_id
		);

		return $this->wpdb->get_row( $sql );
	}

	/**
	* Log Transaction, with transaction locking.
	*
	* @param   array[string]mixed    $notification
	* @return  null
	*/
	public function log_transaction( $notification ) {
		try {
			@mysqli_query("BEGIN", $this->wpdb->dbh);
			$sql = $this->wpdb->prepare(
				"INSERT INTO {$this->wpdb->prefix}reply_push_log (message_id, notification) VALUES (%s, %s)",
				$notification['msg_id'],
				serialize( $notification )
			);
			$this->wpdb->query($sql);
			@mysqli_query("COMMIT", $this->wpdb->dbh );
		} catch(Exception $ex) {
			@mysqli_query("ROLLBACK", $this->wpdb->dbh );
			throw $ex;
		}
	}

	public function structure() {

		require_once ABSPATH . '/wp-admin/includes/upgrade.php';

		$query = "CREATE TABLE {$this->wpdb->prefix}reply_push_ref (
			ref_hash varchar(32) NOT NULL,
			ref text NOT NULL,
			PRIMARY KEY  (ref_hash)
			);";

		dbDelta( $query );

		$query = "CREATE TABLE {$this->wpdb->prefix}reply_push_log (
			message_id varchar(36) NOT NULL,
			notification text NOT NULL,
			PRIMARY KEY  (message_id)
			);";

		dbDelta( $query );

		// check anon user exist if not create
		$anon = get_user_by('email', 'anon@replypush.com');
		if ( !$anon ) {
			$anon_id = wp_create_user('anon@replypush.com', wp_generate_password(15, true, true), 'anon@replypush.com');
			$anon = new WP_User( $anon_id );
			$anon->set_role('subscriber');
			$this->wpdb->update( $this->wpdb->users, array('display_name' => 'anon', 'user_nicename' => 'anon'), array('ID' => $anon_id ) );
		} else {
			// else change password to prevent funny business.
			wp_set_password( wp_generate_password(15, true, true), $anon->ID );
		}
	}
}
