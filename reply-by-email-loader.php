<?php
if ( ! defined( 'ABSPATH' ) ) exit;

define( 'REPLY_BY_EMAIL_PATH', plugin_dir_path( __FILE__ ) );

require REPLY_BY_EMAIL_PATH . 'library/implicit-hooks/class-implicit-hooks.php';

class ReplyByEmailPluggable extends ImplicitHooksPluggable_v0_1_1b {}
class ReplyByEmailHooks extends ImplicitHooks_v0_1_1b {}

ReplyByEmailHooks::load(
	'reply-by-email',
	REPLY_BY_EMAIL_PATH,
	'config',
	'hooks',
	'ReplyByEmail'
);
