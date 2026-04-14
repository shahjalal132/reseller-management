<?php
/**
 * Reseller Hub – Add / deduct balance (manual ledger entry).
 *
 * Variables from render_user_balance_adjust_page():
 *   $rm_reseller_id (int)
 *   $rm_user        (WP_User)
 *   $rm_balance     (float)
 */

defined( 'ABSPATH' ) || exit;

$rm_reseller_id = isset( $rm_reseller_id ) ? (int) $rm_reseller_id : 0;
$rm_user        = isset( $rm_user )        ? $rm_user              : null;
$rm_balance     = isset( $rm_balance )     ? (float) $rm_balance   : 0.0;

if ( ! $rm_user ) {
	wp_die( esc_html__( 'Invalid reseller.', 'reseller-management' ) );
}

$back_view_url       = admin_url( 'admin.php?page=reseller-hub-user-view&reseller_id=' . $rm_reseller_id );
$back_statements_url = admin_url( 'admin.php?page=reseller-hub-user-statements&reseller_id=' . $rm_reseller_id );

$fmt_balance = static function ( $amount ) {
	return '৳' . number_format( (float) $amount, 2 );
};

$default_local_dt = wp_date( 'Y-m-d\TH:i' );
?>

<div class="rm-balance-adjust-page">

<a href="<?php echo esc_url( $back_view_url ); ?>" class="rm-back-btn">
	<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
		<path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
	</svg>
	<?php esc_html_e( 'Back to profile', 'reseller-management' ); ?>
</a>

<div class="rm-balance-adjust-header">
	<h1 class="rm-page-title rm-balance-adjust-title">
		<?php esc_html_e( 'Adjust balance', 'reseller-management' ); ?>
	</h1>
	<p class="rm-balance-adjust-meta">
		<?php echo esc_html( $rm_user->display_name ); ?>
		<span class="rm-balance-adjust-meta-sep"> · </span>
		<?php echo esc_html( $rm_user->user_email ); ?>
	</p>
	<p class="rm-balance-adjust-balance">
		<?php esc_html_e( 'Current balance:', 'reseller-management' ); ?>
		<?php echo esc_html( $fmt_balance( $rm_balance ) ); ?>
	</p>
</div>

<div class="rm-balance-form-wrap">
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="rm_adjust_reseller_balance" />
		<input type="hidden" name="reseller_id" value="<?php echo esc_attr( (string) $rm_reseller_id ); ?>" />
		<?php wp_nonce_field( 'rm_adjust_reseller_balance_' . $rm_reseller_id ); ?>

		<div class="rm-form-group">
			<label for="rm_adjusted_at"><?php esc_html_e( 'Date', 'reseller-management' ); ?></label>
			<input type="datetime-local" id="rm_adjusted_at" name="rm_adjusted_at" value="<?php echo esc_attr( $default_local_dt ); ?>" required />
		</div>

		<div class="rm-form-group">
			<label for="reference"><?php esc_html_e( 'Reference', 'reseller-management' ); ?></label>
			<input type="text" id="reference" name="reference" maxlength="191" autocomplete="off" placeholder="<?php esc_attr_e( 'e.g. invoice or voucher ID', 'reseller-management' ); ?>" />
		</div>

		<div class="rm-form-group">
			<label for="description"><?php esc_html_e( 'Description', 'reseller-management' ); ?></label>
			<textarea id="description" name="description" required placeholder="<?php esc_attr_e( 'Reason for this adjustment', 'reseller-management' ); ?>"></textarea>
		</div>

		<div class="rm-form-group">
			<label for="adjustment_type"><?php esc_html_e( 'Type', 'reseller-management' ); ?></label>
			<select id="adjustment_type" name="adjustment_type" required>
				<option value="add"><?php esc_html_e( 'Add balance', 'reseller-management' ); ?></option>
				<option value="deduct"><?php esc_html_e( 'Deduct balance', 'reseller-management' ); ?></option>
			</select>
		</div>

		<div class="rm-form-group">
			<label for="amount"><?php esc_html_e( 'Amount', 'reseller-management' ); ?></label>
			<input type="number" id="amount" name="amount" step="0.01" min="0.01" required placeholder="0.00" />
		</div>

		<div class="rm-balance-form-actions">
			<?php submit_button( __( 'Save adjustment', 'reseller-management' ), 'primary', 'submit', false ); ?>
		</div>
	</form>

	<div class="rm-balance-secondary-links">
		<a href="<?php echo esc_url( $back_statements_url ); ?>"><?php esc_html_e( 'View statements', 'reseller-management' ); ?></a>
	</div>
</div>

</div><!-- .rm-balance-adjust-page -->
