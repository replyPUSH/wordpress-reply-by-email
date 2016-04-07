<?php
if ( ! defined( 'ABSPATH' ) ) exit;
add_thickbox();
?>
<div class="wrap">
	<h2><?php _e('Reply By Email Settings', 'reply-by-email'); ?></h2>
	<?php
	if ( !empty( $this->data['errors'] ) ) {
	?>
	<div class="error reply-push-settings-error">
	<?php
		foreach( $this->data('errors') as $error ) {
	?>
	<p>
	<strong><?php echo $error; ?></strong>
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
			<td>
				<input type="text" name="account_no" value="<?php echo esc_attr( $this->data['credencials']['account_no'] ); ?>" maxlength="8" size="8" />
				<br><small><?php _e('The Account No found <a href="http://replypush.com/profile">here</a>. Sign up for an account first.', 'reply-by-email') ?></small>
			</td>
			</tr>
			<tr valign="top">
			<th scope="row"><?php _e('Secret ID', 'reply-by-email') ?></th>
			<td>
				<input type="text" name="secret_id" value="<?php echo esc_attr( $this->data['credencials']['secret_id'] ); ?>" maxlength="32" size="32" />
				<br><small><?php _e('The API ID found <a href="http://replypush.com/profile">here</a>.', 'reply-by-email') ?></small>
			</td>
			</tr>
			<tr valign="top">
			<th scope="row"><?php _e('Secret Key', 'reply-by-email') ?></th>
			<td>
				<input type="text" name="secret_key" value="<?php echo esc_attr( $this->data['credencials']['secret_key'] ); ?>" maxlength="32" size="32" />
				<br><small><?php _e('The API Key found <a href="http://replypush.com/profile">here</a>.', 'reply-by-email') ?></small>
			</td>
			</tr>
			<tr valign="top">
			<th scope="row"><?php _e('Notify URL', 'reply-by-email') ?></th>
			<td>
				<input type="text" value="<?php echo site_url('index.php?replypush=notify&uri=') . esc_attr( $this->data('notify_uri') ); ?>" readonly="readonly"  size="72" />
				<br><small><?php _e('Save this Notify Url <a href="http://replypush.com/profile">here</a>.', 'reply-by-email') ?></small>
			</td>
			</tr>
			<tr valign="top">
			<th scope="row"><?php _e('Notify Template', 'reply-by-email') ?></th>
			<td>
				<textarea cols="72" rows="12" name="main_template"><?php echo esc_html( $this->data('main_template') ); ?></textarea>
				<br><small><?php _e('Template tag substitution reference <a href="#TB_inline?width=600&height=550&inlineId=notify-substitutions"  title="Notify Template substitutions" class="thickbox">here</a>.', 'reply-by-email') ?></small>
			</td>
			</td>
			</tr>
			<tr valign="top">
			<th scope="row"><?php _e('Notify Footer', 'reply-by-email') ?></th>
			<td>
				<textarea cols="72" rows="12" name="sig_template"><?php echo esc_html( $this->data('sig_template') ); ?></textarea>
			</td>
			</tr>
		</table>
		<input type="submit" value="<?php _e('Save Settings', 'reply-by-email') ?>" />
	</form>
	<div id="notify-substitutions" style="display:none;">
		<dl>
			<dt><b>{BLOGNAME}</b></dt><dd><?php echo get_option('blogname'); ?></dd>
			<dt><b>{BLOGLINK}</b></dt><dd><?php echo get_option('home'); ?></dd>
			<dt><b>{TITLE}</b></dt><dd><?php _e("The post's title", 'reply-by-email'); ?></dd>
			<dt><b>{POST}</b></dt><dd><?php _e("The excerpt or the entire post, based on the subscriber's preferences <b>(recommended)</b>", 'reply-by-email'); ?></dd>
			<dt><b>{TYPE_LINK_WORDS}</b></dt><dd><?php _e("The post type lin; word e.g 'post' or 'comment on' <b>(recommended)</b>", 'reply-by-email'); ?></dd>
			<dt><b>{REFERENCELINKS}</b></dt><dd><?php _e("A reference style list of links at the end of the email with corresponding numbers in the content", 'reply-by-email'); ?></dd>
			<dt><b>{PERMALINK}</b></dt><dd><?php _e("The post's permalink <b>(recomended or {TINYLINK})</b>", 'reply-by-email'); ?></dd>
			<dt><b>{TINYLINK}</b></dt><dd><?php _e("The post's permalink after conversion by TinyURL <b>(recommended or {PERMALINK})</b>", 'reply-by-email'); ?></dd>
			<dt><b>{DATE}</b></dt><dd><?php _e("The date the post was made", 'reply-by-email'); ?></dd>
			<dt><b>{TIME}</b></dt><dd><?php _e("The time the post was made", 'reply-by-email'); ?></dd>
			<dt><b>{MYNAME}</b></dt><dd><?php _e("The admin or post author's name", 'reply-by-email'); ?></dd>
			<dt><b>{EMAIL}</b></dt><dd><?php _e("The admin or post author's email", 'reply-by-email'); ?></dd>
			<dt><b>{AUTHORNAME}</b></dt><dd><?php _e("The post author's name", 'reply-by-email'); ?></dd>
			<dt><b>{CATS}</b></dt><dd><?php _e("The post's assigned categories", 'reply-by-email'); ?></dd>
			<dt><b>{TAGS}</b></dt><dd><?php _e("The post's assigned Tags", 'reply-by-email'); ?></dd>
		</dl>
	</div>
</div>
