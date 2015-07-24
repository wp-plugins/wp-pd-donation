<div id="donate-form">

	<?php /** if( isset($_GET['donate']) && $_GET['donate']=='success' ): ?>
		<p class="form-submission-message success">Thank you for contributing to Georgia's EMC PAC. </p>
	<?php else: ?>

		<?php if( isset($_GET['donate']) && $_GET['donate']=='success' ): ?>
			<p class="form-submission-message error">Error occurred while submitting the form. Please try again.</p>
		<?php endif; **/ ?>

		<form method="POST" action="">
			<?php wp_nonce_field( $this->nonce_field, $this->nonce_action ); ?>
			<div class="donate-form-group">
				<div class="donate-form-label">
					<label for="membership_year">Membership Year * </label>
				</div>
				<div class="donate-form-field">
					<input type="text" name="membership_year" class="narrow-field" id="membership_year" />
					<p class="field-error-message"></p>
				</div>
			</div>

			<div class="donate-form-group">
				<div class="donate-form-label">
					<label for="contribution">Enclosed is my personal contribution of *</label>
				</div>
				<div class="donate-form-field">
					<div class="donate-form-radio">
						<input type="radio" name="contribution" id="contribution-member" value="1" checked="checked" /> 
						<label for="contribution-member">$25 Member</label>
					</div>
					<div class="donate-form-radio">
						<input type="radio" name="contribution" id="contribution-representative" value="2" /> 
						<label for="contribution-representative">$50 Representative's Club</label>
					</div>
					<div class="donate-form-radio">
						<input type="radio" name="contribution" id="contribution-senator" value="3" />
						<label for="contribution-senator">$100 Senator's Club</label>
					</div>
					<div class="donate-form-radio">
						<input type="radio" name="contribution" id="contribution-governor" value="4" />
						<label for="contribution-governor">$250 Governor's Club</label>
					</div>
					<div class="donate-form-radio">
						<input type="radio" name="contribution" id="contribution-president" value="5" />
						<label for="contribution-president">$500 President's Club</label>
					</div>
					<div class="donate-form-radio">
						<input type="radio" name="contribution" id="contribution-payroll" value="6" />
						<label for="contribution-payroll">Annual recurring payroll deduction</label>

						<div class="deduction-amount donate-form-group">
							<label for="deduction-amount">* Amount: $</label>
							<input type="text" name="deduction-amount" id="deduction-amount" />
							<p class="field-error-message"></p>
						</div>

						<div class="donate-form-note">
							(Georgia EMC employees only. Check with your EMC's payroll department about deductions at your co-op.)
						</div>
					</div>
				</div>
			</div>

			<div class="donate-form-group">
				<div class="donate-form-label">
					<label for="donor_name">Name * </label>
				</div>
				<div class="donate-form-field">
					<input type="text" name="donor_name" id="donor_name" />
					<p class="field-error-message"></p>
				</div>
			</div>

			<div class="donate-form-group">
				<div class="donate-form-label">
					<label for="donor_employer">Employer * </label>
				</div>
				<div class="donate-form-field">
					<input type="text" name="donor_employer" id="donor_employer" />
					<p class="field-error-message"></p>
				</div>
			</div>

			<div class="donate-form-group">
				<div class="donate-form-label">
					<label for="donor_occupation">Occupation * </label>
				</div>
				<div class="donate-form-field">
					<input type="text" name="donor_occupation" id="donor_occupation" />
					<p class="field-error-message"></p>
				</div>
			</div>

			<div class="donate-form-group">
				<div class="donate-form-label">
					<label for="donor_system_name">System Name * </label>
				</div>
				<div class="donate-form-field">
					<input type="text" name="donor_system_name" id="donor_system_name" />
					<p class="field-error-message"></p>
				</div>
			</div>

			<div class="donate-form-group">
				<div class="donate-form-label">
					<label for="donor_home_address">Home Address * </label>
				</div>
				<div class="donate-form-field">
					<input type="text" name="donor_home_address" id="donor_home_address" />
					<p class="field-error-message"></p>
				</div>
			</div>

			<div class="donate-form-group">
				<div class="donate-form-label">
					<label for="donor_city">City * </label>
				</div>
				<div class="donate-form-field">
					<input type="text" name="donor_city" id="donor_city" />
					<p class="field-error-message"></p>
				</div>
			</div>

			<div class="donate-form-group">
				<div class="donate-form-label">
					<label for="donor_zipcode">Zipcode * </label>
				</div>
				<div class="donate-form-field">
					<input type="text" name="donor_zipcode" id="donor_zipcode" />
					<p class="field-error-message"></p>
				</div>
			</div>

			<div class="donate-form-group">
				<div class="donate-form-label">
					<label for="donor_state">State * </label>
				</div>
				<div class="donate-form-field">
					<input type="text" name="donor_state" id="donor_state" />
					<p class="field-error-message"></p>
				</div>
			</div>

			<div class="donate-form-group">
				<div class="donate-form-label">
					<label for="donor_email">Email Address *</label>
				</div>
				<div class="donate-form-field">
					<input type="text" name="donor_email" id="donor_email" />
					<p class="field-error-message"></p>
				</div>
			</div>

			<div class="donate-form-group">
				<span class="formfeedback"></span>
				<input type="submit" class="donate-button" value="Submit" />
				<span class="form-icon-loading"></span>
			</div>

			<div id="donate-form-feedback" class="donate-form-group">
				<p class="form-message"></p>
			</div>

			</form>

			

	<?php // endif; ?>

</div>

<div style="display:none;">
	<form id="ppform" action="<?php echo esc_url($this->paypal_url); ?>" method="post" target="_top">
	<input type="hidden" name="return" value="<?php echo $this->thankyou_url; ?>" />
	<input type="hidden" name="cmd" value="_donations"/>
	<input type="hidden" name="custom" value=""/>
	<input type="hidden" name="business" value="<?php echo $this->paypal_email; ?>"/>
	<input type="hidden" name="lc" value="US"/>
	<input type="hidden" name="item_name" value="Paypal Donation Plugin"/>
	<input type="hidden" name="item_number" value="Fall Cleanup Campaign"/>
	<input type="hidden" name="amount" value="25.00"/>
	<input type="hidden" name="currency_code" value="USD"/>
	<input type="hidden" name="no_note" value="0"/>
	<input type="hidden" name="bn" value="PP-DonationsBF:btn_donate_SM.gif:NonHostedGuest"/>
	<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!"/>
	<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1"/>
	</form>
</div>