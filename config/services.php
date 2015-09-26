<?php
if ( ! ( defined( 'ABSPATH' ) && __CLASS__ == 'ImplicitHooksServices') ) exit;

$services = array(
	'utility' => array(
		'class'     => 'ReplyByEmailUtility',
		'path'      => 'helper/class-utility.php',
		'args'      => array(
			'wp_rewrite' => '%%wp_rewrite',
			'wp_query'   => '%%query_vars'
		)
	),
	'mailer' => array(
		'class'     => 'ReplyByEmailMailer',
		'path'      => 'helper/class-mailer.php',
		'args'      => array(
			'utility'    => '@@utility',
			'rp_model'   => '@@reply_push_model',
			'the_post'   => '%%post',
		)
	),
	'reply_push' => array(
		'class'     => 'ReplyPush',
		'path'      => 'library/reply-push/class.replypush.php'
	),
	'reply_push_model' => array(
		'class'     => 'ReplyPushModel',
		'path'      => 'model/class-reply-push-model.php',
		'args'      => array(
			'wpdb'      => '%%wpdb'
		)
	),
	'controller' => array(
		'class'     => 'ReplyByEmailController',
		'path'      => 'controller/class-controller.php',
		'args'      => array(
			'views_dir' => REPLY_BY_EMAIL_PATH . 'views'
		)
	),
	'settings_controller' => array(
		'requires'  => array(
			'controller'
		),
		'class'     => 'ReplyByEmailSettingsController',
		'path'      => 'controller/class-settings.php',
		'args'      => array(
			'views_dir' => REPLY_BY_EMAIL_PATH . 'views',
			'utility'   => '@@utility',
			'post'      => '%%_POST'
		)
	),
	'notify_controller' => array(
		'requires'  => array(
			'controller',
			'reply_push'
		),
		'class'     => 'ReplyByEmailNotifyController',
		'path'      => 'controller/class-notify.php',
		'args'      => array(
			'views_dir' => REPLY_BY_EMAIL_PATH . 'views',
			'rp_model'  => '@@reply_push_model',
			'utility'   => '@@utility',
			'post'      => '%%_POST'
		)
	)
);
