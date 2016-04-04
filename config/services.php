<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$services = array(
	'reply_push' => array(
		'class'     => 'ReplyPush',
		'path'      => 'library/reply-push/class.replypush.php'
	),
	'utility' => array(
		'requires'  => array(
			'reply_push'
		),
		'class'     => 'ReplyByEmailUtility',
		'path'      => 'helper/class-utility.php',
		'args'      => array(
			'wp_query'   => '%%wp_query.query'
		)
	),
	'mailer' => array(
		'requires'  => array(
			'reply_push'
		),
		'class'     => 'ReplyByEmailMailer',
		'path'      => 'helper/class-mailer.php',
		'args'      => array(
			'utility'    => '@@utility',
			'reply_push_model'   => '@@reply_push_model',
			'the_post'   => '%%post',
			'subscribe2' => '%%mysubscribe2'
		)
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
			'controller',
			'reply_push'
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
			'reply_push_model'  => '@@reply_push_model',
			'utility'   => '@@utility',
			'post'      => '%%_POST'
		)
	)
);
