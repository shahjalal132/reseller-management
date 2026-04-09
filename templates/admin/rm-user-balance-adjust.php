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

<a href="<?php echo esc_url( $back_view_url ); ?>" class="rm-back-btn">
	<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
		<path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
	</svg>
	<?php esc_html_e( 'Back to profile', 'reseller-management' ); ?>
</a>

<div class="rm-balance-adjust-header" style="margin:20px 0 8px;">
	<h1 class="rm-page-title" style="margin:0 0 8px;">
		<?php esc_html_e( 'Adjust balance', 'reseller-management' ); ?>
	</h1>
	<p style="margin:0;color:#64748b;font-size:0.95rem;">
		<?php echo esc_html( $rm_user->display_name ); ?>
		<span style="opacity:0.6;"> · </span>
		<?php echo esc_html( $rm_user->user_email ); ?>
	</p>
	<p style="margin:12px 0 0;font-size:1.05rem;font-weight:600;color:#0f766e;">
		<?php esc_html_e( 'Current balance:', 'reseller-management' ); ?>
		<?php echo esc_html( $fmt_balance( $rm_balance ) ); ?>
	</p>
</div>

<style>
	.rm-balance-form-wrap { max-width: 560px; margin-top: 20px; background: #fff; padding: 28px 32px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #e8edf3; }
	.rm-balance-form-wrap .rm-form-group { margin-bottom: 18px; }
	.rm-balance-form-wrap .rm-form-group label { display: block; margin-bottom: 6px; font-size: 0.85rem; font-weight: 700; color: #475569; }
	.rm-balance-form-wrap .rm-form-group input,
	.rm-balance-form-wrap .rm-form-group select,
	.rm-balance-form-wrap .rm-form-group textarea { width: 100%; max-width: 100%; border: 1.5px solid #e2e8f0; border-radius: 8px; padding: 10px 14px; font-size: 0.95rem; color: #1e293b; box-sizing: border-box; }
	.rm-balance-form-wrap .rm-form-group textarea { min-height: 88px; resize: vertical; }
	.rm-balance-form-wrap .rm-form-group input:focus,
	.rm-balance-form-wrap .rm-form-group select:focus,
	.rm-balance-form-wrap .rm-form-group textarea:focus { border-color: #0d9488; outline: none; }
	.rm-balance-form-actions { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 24px; align-items: center; }
	.rm-balance-form-actions .button-primary { background: #0d9488; border-color: #0f766e; }
	.rm-balance-form-actions .button-primary:hover { background: #0f766e; border-color: #115e59; }
	.rm-balance-secondary-links { margin-top: 20px; font-size: 0.9rem; }
	.rm-balance-secondary-links a { color: #0d9488; text-decoration: none; }
	.rm-balance-secondary-links a:hover { text-decoration: underline; }
</style>

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
