<?php
/**
 * Account tab.
 *
 * @package reseller-management
 */

defined( 'ABSPATH' ) || exit;

$reseller_id   = get_current_user_id();
$transactions  = \BOILERPLATE\Inc\Reseller_Finance::get_transactions( $reseller_id );
$withdrawals   = \BOILERPLATE\Inc\Reseller_Finance::get_withdrawals( $reseller_id );
$current_total = \BOILERPLATE\Inc\Reseller_Helper::get_current_balance( $reseller_id );
?>
<div class="rm-stat-grid">
    <div class="rm-stat-card">
        <span><?php esc_html_e( 'Available Balance', 'reseller-management' ); ?></span>
        <strong><?php echo wp_kses_post( wc_price( $current_total ) ); ?></strong>
    </div>
</div>

<div class="rm-card">
    <h3><?php esc_html_e( 'Request Withdrawal', 'reseller-management' ); ?></h3>
    <form id="rm-withdrawal-form" class="rm-form">
        <div class="rm-grid rm-grid-2">
            <label class="rm-field">
                <span><?php esc_html_e( 'Amount', 'reseller-management' ); ?></span>
                <input type="number" name="amount" min="0.01" step="0.01" required>
            </label>
            <label class="rm-field">
                <span><?php esc_html_e( 'Payment Method', 'reseller-management' ); ?></span>
                <select name="payment_method" required>
                    <option value=""><?php esc_html_e( 'Select one', 'reseller-management' ); ?></option>
                    <option value="bank"><?php esc_html_e( 'Bank', 'reseller-management' ); ?></option>
                    <option value="bkash"><?php esc_html_e( 'bKash', 'reseller-management' ); ?></option>
                    <option value="nagad"><?php esc_html_e( 'Nagad', 'reseller-management' ); ?></option>
                </select>
            </label>
            <label class="rm-field rm-field-full">
                <span><?php esc_html_e( 'Account Details', 'reseller-management' ); ?></span>
                <textarea name="account_details" rows="3" required></textarea>
            </label>
        </div>
        <div class="rm-form-actions">
            <button type="submit" class="rm-button"><?php esc_html_e( 'Submit Withdrawal Request', 'reseller-management' ); ?></button>
        </div>
        <div class="rm-form-response" aria-live="polite"></div>
    </form>
</div>

<div class="rm-card">
    <h3><?php esc_html_e( 'Transaction History', 'reseller-management' ); ?></h3>
    <table class="rm-table">
        <thead>
            <tr>
                <th><?php esc_html_e( 'Date', 'reseller-management' ); ?></th>
                <th><?php esc_html_e( 'Type', 'reseller-management' ); ?></th>
                <th><?php esc_html_e( 'Description', 'reseller-management' ); ?></th>
                <th><?php esc_html_e( 'Amount', 'reseller-management' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ( empty( $transactions ) ) : ?>
                <tr>
                    <td colspan="4"><?php esc_html_e( 'No transactions available yet.', 'reseller-management' ); ?></td>
                </tr>
            <?php else : ?>
                <?php foreach ( $transactions as $row ) : ?>
                    <tr>
                        <td><?php echo esc_html( (string) $row->created_at ); ?></td>
                        <td><?php echo esc_html( ucfirst( str_replace( '_', ' ', (string) $row->type ) ) ); ?></td>
                        <td><?php echo esc_html( (string) $row->description ); ?></td>
                        <td><?php echo wp_kses_post( wc_price( (float) $row->amount ) ); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="rm-card">
    <h3><?php esc_html_e( 'Withdrawal Requests', 'reseller-management' ); ?></h3>
    <table class="rm-table">
        <thead>
            <tr>
                <th><?php esc_html_e( 'Requested At', 'reseller-management' ); ?></th>
                <th><?php esc_html_e( 'Amount', 'reseller-management' ); ?></th>
                <th><?php esc_html_e( 'Method', 'reseller-management' ); ?></th>
                <th><?php esc_html_e( 'Status', 'reseller-management' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ( empty( $withdrawals ) ) : ?>
                <tr>
                    <td colspan="4"><?php esc_html_e( 'No withdrawal requests submitted yet.', 'reseller-management' ); ?></td>
                </tr>
            <?php else : ?>
                <?php foreach ( $withdrawals as $withdrawal ) : ?>
                    <tr>
                        <td><?php echo esc_html( (string) $withdrawal->created_at ); ?></td>
                        <td><?php echo wp_kses_post( wc_price( (float) $withdrawal->amount ) ); ?></td>
                        <td><?php echo esc_html( (string) $withdrawal->payment_method ); ?></td>
                        <td><?php echo esc_html( ucfirst( (string) $withdrawal->status ) ); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
