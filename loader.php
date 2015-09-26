<?php
if ( ! defined( 'ABSPATH' ) ) exit;

define( 'REPLY_BY_EMAIL_PATH' , plugin_dir_path( __FILE__ ) );

require REPLY_BY_EMAIL_PATH . 'library/implicit-hooks/class.implicit-hooks.php';

ImplicitHooks:load(
	REPLY_BY_EMAIL_PATH,
	'config',
	'hooks',
	'ReplyByEmail'
);
