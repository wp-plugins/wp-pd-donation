<div class="wrap">
	<h2>Donation Settings <code>[pd_donate]</code></h2>

	<?php if(isset($_GET['update']) && $_GET['update']==1): ?>
	<div class="message updated">Settings updated.</div>
	<?php endif; ?>

	<form method="post">

		<?php wp_nonce_field($this->nonce, $this->nonce_action); ?>

		<table class="form-table">
			<tr>
				<th>
					<label for="donation_page">Donation Page: </label>
				</th>
				<td>
					<select name="donation_page" id="donation_page">
						<option value="0">Choose page</option>
						<?php if( isset($this->pages) && count($this->pages) > 0 ): ?>
							<?php foreach($this->pages as $page): ?>
							<option <?php selected($this->options['donate_page'], $page->ID); ?> value="<?php echo absint($page->ID); ?>"><?php echo esc_html($page->post_title); ?></option>
							<?php endforeach; ?>
						<?php endif; ?>
					</select>

					<?php  if( $this->donate_page_set ): ?>
					<div class="settings-note">
						<p><em>Set the URL below to your PayPal's Instant Payment Notification (IPN) preference:</em> <br/> <code><?php echo esc_url($this->ipn_url); ?></code></p>
						<p>
							<em>This is required as PayPal will send a notification unto this URL and </em><br/> 
							<em>this notification will let our system know that a payment has been process. </em><br/>
							<em>A successful donation will then appear in the GEMC Donation list.</em>
						</p>
					</div>
					<?php endif; ?>

				</td>
			</tr>
			<tr>
				<th>
					<label for="thankyou_page">Thank You Page:</label>
				</th>
				<td>
					<select name="thankyou_page" id="thankyou_page">
					<?php if( isset($this->pages) && count($this->pages) > 0 ): ?>
						<?php foreach($this->pages as $page): ?>
						<option <?php selected($this->options['thankyou_page'], $page->ID); ?> value="<?php echo absint($page->ID); ?>"><?php echo esc_html($page->post_title); ?></option>
						<?php endforeach; ?>
					<?php endif; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th>
					<label for="paypal_email">PayPal Email: </label>
				</th>
				<td>
					<input type="text" name="paypal_email" id="paypal_email" class="regular-text" value="<?php echo esc_attr($this->options['paypal_email']); ?>" />
				</td>
			</tr>
			<tr>
				<th>
					<label for="paypal_env">PayPal Environment: </label>
				</th>
				<td>
					<select id="paypal_env" name="paypal_env">
						<option value="0" <?php selected($this->options['use_live_pp_env'], false); ?>>Sandbox</option>
						<option value="1" <?php selected($this->options['use_live_pp_env'], true); ?>>Live</option>
					</select>
				</td>
			</tr>
			<tr>
				<th>
					<label for="notification_email">Notification Email: </label>
				</th>
				<td>
					<input type="text" name="notification_email" id="notification_email" value="<?php echo esc_attr($this->options['notification_email']); ?>" />
				</td>
			</tr>
			<tr>
				<th></th>
				<td>
					<input type="submit" value="Save Settings" class="button-primary" />
				</td>
			</tr>
		</table>

	</form>
</div>