<?php
/**
 * Dashboard summary tab.
 *
 * @package reseller-management
 */

defined( 'ABSPATH' ) || exit;

$reseller_id     = get_current_user_id();
$orders          = \BOILERPLATE\Inc\Reseller_Orders::get_reseller_orders( $reseller_id );
$transactions    = \BOILERPLATE\Inc\Reseller_Finance::get_transactions( $reseller_id );
$monthly_profits = \BOILERPLATE\Inc\Reseller_Helper::get_monthly_profit_summary( $reseller_id );
?>
<div class="rm-stat-grid">
    <div class="rm-stat-card">
        <span><?php esc_html_e( 'Current Balance', 'reseller-management' ); ?></span>
        <strong><?php echo wp_kses_post( wc_price( \BOILERPLATE\Inc\Reseller_Helper::get_current_balance( $reseller_id ) ) ); ?></strong>
    </div>
    <div class="rm-stat-card">
        <span><?php esc_html_e( 'Total Orders', 'reseller-management' ); ?></span>
        <strong><?php echo esc_html( (string) count( $orders ) ); ?></strong>
    </div>
    <div class="rm-stat-card">
        <span><?php esc_html_e( 'Transactions', 'reseller-management' ); ?></span>
        <strong><?php echo esc_html( (string) count( $transactions ) ); ?></strong>
    </div>
</div>

<div class="rm-card">
    <h3><?php esc_html_e( 'Monthly Profit Summary', 'reseller-management' ); ?></h3>
    <table class="rm-table">
        <thead>
            <tr>
                <th><?php esc_html_e( 'Month', 'reseller-management' ); ?></th>
                <th><?php esc_html_e( 'Amount', 'reseller-management' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ( empty( $monthly_profits ) ) : ?>
                <tr>
                    <td colspan="2"><?php esc_html_e( 'No profit data yet.', 'reseller-management' ); ?></td>
                </tr>
            <?php else : ?>
                <?php foreach ( $monthly_profits as $row ) : ?>
                    <tr>
                        <td><?php echo esc_html( (string) $row->month_key ); ?></td>
                        <td><?php echo wp_kses_post( wc_price( (float) $row->total ) ); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
