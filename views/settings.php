<?php
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<div class="wrap">
<h2><?php _e('Reply By Email Settings', 'reply-by-email'); ?></h2>
<?php 
if ( !empty( $this->data('errors') ) ) { 
?>
<div class="error reply-push-settings-error">
<?php 
	foreach( $this->data('errors') as $error ) { 
?>
<p>
<strong><?php echo $this->data('errors'); ?></strong>
</p>
<?php
	}
?>
</div>
<?php
} 
?>
<form method="post" action="<?php echo get_permalink(); ?>">
<?php wp_nonce_field('save_credencials','reply_push_settings'); ?>
	<table class="form-table">
		<tr valign="top">
		<th scope="row"><?php _e('Account No', 'reply-by-email') ?></th>
		<td><input type="text" name="account_no" value="<?php echo esc_attr( $this->data('credencials')['account_no'] ); ?>" maxlength="8" /></td>
		</tr>
		<tr valign="top">
		<th scope="row"><?php _e('Secret ID', 'reply-by-email') ?></th>
		<td><input type="text" name="secret_id" value="<?php echo esc_attr( $this->data('credencials')['secret_id'] ); ?>" maxlength="32" /></td>
		</tr>
		<tr valign="top">
		<th scope="row"><?php _e('Secret Key', 'reply-by-email') ?></th>
		<td><input type="text" name="secret_key" value="<?php echo esc_attr( $this->data('credencials')['secret_key'] ); ?>" maxlength="32" /></td>
		</tr>
		<tr valign="top">
		<th scope="row"><?php _e('Notify URL', 'reply-by-email') ?></th>
		<td><input type="text" name="secret_key" value="<?php echo site_url('/secrets/') . esc_attr( $this->data('notify_uri') ); ?>" readonly="readonly" /></td>
		</tr>
	</table>
	<input type="submit" value="<?php _e('Save Settings', 'reply-by-email') ?>" />    
</form>
</div>
