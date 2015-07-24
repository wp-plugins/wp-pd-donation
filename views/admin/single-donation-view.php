<div class="wrap">

	<h2>Donation Information <a class="add-new-h2" href="<?php echo admin_url('admin.php?page=gemc-donation'); ?>">&larr; see all list</a></h2>

	<?php if( ! empty($this->donation) ): ?>

		<table class="form-table donation-table">
			<tr>
				<th>Name: </th>
				<td><?php echo esc_html($this->donation->donor_name); ?></td>
				<th>Employer: </th>
				<td><?php echo esc_html($this->donation->donor_employer); ?></td>
			</tr>
			<tr>
				<th>Occupation: </th>
				<td><?php echo esc_html($this->donation->donor_occupation); ?></td>
				<th>System Name: </th>
				<td><?php echo esc_html($this->donation->donor_system_name); ?></td>
			</tr>
			<tr>
				<th>Home Address: </th>
				<td><?php echo esc_html($this->donation->donor_home_address); ?></td>
				<th>City: </th>
				<td><?php echo esc_html($this->donation->donor_city); ?></td>
			</tr>
			<tr>
				<th>State: </th>
				<td><?php echo esc_html($this->donation->donor_state); ?></td>
				<th>Zipcode: </th>
				<td><?php echo esc_html($this->donation->donor_zip); ?></td>
			</tr>
			<tr>
				<th>Email Address: </th>
				<td><?php echo esc_html($this->donation->donor_email); ?></td>
				<th>Donation Type: </th>
				<td><?php echo esc_html($this->donation_amounts[intval($this->donation->donor_donation_type)]); ?></td>
			</tr>
			<tr>
				<th>Date Donated: </th>
				<td><?php echo esc_html(date('Y-m-d h:i a', strtotime($this->donation->donor_date_donated))); ?></td>
				<th>Membership Year: </th>
				<td><?php echo esc_html($this->donation->donor_membership_year); ?></td>
			</tr>
			<tr>
				<th>Amount Donated: </th>
				<td><?php echo esc_html($this->donation->payment_amount); ?></td>
				<th>Payment Currency: </th>
				<td><?php echo esc_html($this->donation->payment_currency); ?></td>
			</tr>
			<tr>
				<th>Payer ID: </th>
				<td><?php echo esc_html($this->donation->payer_id); ?></td>
				<th>Transaction ID: </th>
				<td><?php echo esc_html($this->donation->txn_id); ?></td>
			</tr>
		</table>

	<?php else: ?>

	<h3><em>Sorry, donation not found.</em></h3>

	<?php endif; ?>

</div>