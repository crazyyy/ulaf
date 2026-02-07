<!-- Modal -->
<div class="modal fade" id="donationModal" tabindex="-1" role="dialog" aria-labelledby="donationModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<a href="#" class="close" data-dismiss="modal" aria-label="Close">
					<i class="fa fa-close"></i>
				</a>
				<h4 class="modal-title"><?php esc_html_e("You are donating to:", 'splash'); ?></h4>
				<h5 class="modal-title">"<?php the_title(); ?>"</h5>
			</div>
			<div class="modal-body">
				<form method="post" action="" class="donation-popup-form">
					<div class="row">
						<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
							<div class="form-group">
								<label for="donor_name"><?php esc_html_e("Name *", 'splash'); ?></label>
								<input type="text" name="donor[name]"id="donor_name" value="">
							</div>
							<div class="form-group">
								<label for="donor_phone"><?php esc_html_e("Phone *", 'splash'); ?></label>
								<input type="text" name="donor[phone]" id="donor_phone" value="">
							</div>
						</div>
						<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
							<div class="form-group">
								<label for="donor_email"><?php esc_html_e("E-mail *", 'splash'); ?></label>
								<input type="text" name="donor[email]" id="donor_email" value="">
							</div>
						</div>
						<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
							<div class="form-group">
								<label for="donor_amount"><?php esc_html_e("Amount(USD) *", 'splash'); ?></label>
								<input type="number" name="donor[amount]" id="donor_amount" value="">
							</div>
						</div>
					</div>
					<div class="form-group">
						<label for="donor_message"><?php esc_html_e("Message *", 'splash'); ?></label>
						<textarea id="donor_message" name="donor[message]"></textarea>
					</div>
					<div class="form-group clearfix">
						<button type="submit" class="button"><?php esc_html_e("Donate", 'splash'); ?></button>
						<div class="loading"><i class="fa fa-circle-o-notch fa-spin"></i></div>
						<input type="hidden" name="donor[id]" value="<?php the_ID(); ?>" />
						<input type="hidden" name="action" value="splash_donate_money">
					</div>
				</form>
			</div>
		</div>
	</div>
</div>