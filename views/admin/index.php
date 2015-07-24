<div classs="wrap">
	<h2>Donation</h2>

	<?php if( isset($_GET['delete']) && $_GET['delete'] == 'success' ): ?>
	<div class="message updated">Donation entry successfully deleted.</div>
	<?php endif; ?>

	<?php if( isset($_GET['delete']) && $_GET['delete'] == 'fail' ): ?>
	<div class="message error">Failed to delete donation entry. Please try again.</div>
	<?php endif; ?>

	<p>
		<strong>Filter Donations: </strong>
		<?php $selected_month = isset($_GET['dmonth']) ? 'data-selected="'.absint($_GET['dmonth']).'"' : ''; ?>
		<select name="donation_years" id="donation_years" <?php echo $selected_month; ?>>
			<option value="0">Select Year</option>
			<?php if( count($this->year_months_pair) ): ?>
				<?php foreach($this->year_months_pair as $key=>$value): ?>
				<option <?php if(isset($_GET['dyear'])) { selected($_GET['dyear'], $key); } ?> data-months="<?php echo htmlspecialchars(json_encode($value)); ?>" value="<?php echo $key; ?>"><?php echo $key; ?></option>
				<?php endforeach; ?>
			<?php endif; ?>
		</select>

		<select name="donation_months" id="donation_months">
			<option value="0">Select Month</option>
		</select>
		
		<?php if( !empty($this->export_url) ): ?>
			<a href="<?php echo esc_url($this->export_url) ?>">Export results to CSV</a>
		<?php endif; ?>
	</p>

	<table class="widefat fixed">
		<thead>
			<tr>
				<th>Name</th>
				<th>Employer</th>
				<th>Occupation</th>
				<th>Type</th>
				<th>Amount</th>
				<th>Date Donated</th>
				<th>Delete</th>
			</tr>
		</thead>
		<tbody>
			<?php if( count($this->donations) > 0 ): ?>
				<?php foreach( $this->donations as $donation ): ?>
				<?php 
					$del_url = admin_url("admin.php?page=pd-donation&action=delete&did=" . $donation->id . "&_delete_nonce=" . $this->delete_nonce); 
					$query_param = array();

					if( isset($_GET['dmonth']) ) { 
						$query_param['dmonth'] = $_GET['dmonth']; 
					}

					if( isset($_GET['dyear']) ) {
						$query_param['dyear'] = $_GET['dyear'];
					}

					$del_url = add_query_arg($query_param, $del_url);
				?>
				<tr>
					<td>
						<a href="<?php echo admin_url("admin.php?page=pd-donation&action=view&id=" . $donation->id ); ?>">
							<?php echo esc_html($donation->donor_name); ?>
						</a>
					</td>
					<td><?php echo esc_html($donation->donor_employer); ?></td>
					<td><?php echo esc_html($donation->donor_occupation); ?></td>
					<td><?php echo esc_html(DonationCtrl::$contribution_options[absint($donation->donor_donation_type)]); ?></td>
					<td><?php echo esc_html($donation->payment_amount); ?> (USD)</td>
					<td><?php echo date('M d, Y h:i a', strtotime($donation->donor_date_donated)); ?> (EST)</td>
					<td>
						<a class="delete-donation" href="<?php echo $del_url; ?>">delete</a>
					</td>
				</tr>
				<?php endforeach; ?>
			<?php else: ?>
			<tr>
				<td colspan="6">No donations found.</td>
			</tr>
			<?php endif; ?>
		</tbody>
	</table>
	
	<div class="donations-paging">
	<?php
		$big = 999999999; // need an unlikely integer

		echo paginate_links( array(
			'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
			'format' => '?paged=%#%',
			'current' => max( 1, $this->curr_page ),
			'total' => ceil($this->donations_total / $this->donations_per_page)
			//'total' => $this->donations_total
		) );
	?>
	</div>
</div>