<?php
/**
 * Customers tab.
 *
 * @package reseller-management
 */

defined( 'ABSPATH' ) || exit;

$customers = \BOILERPLATE\Inc\Reseller_Orders::get_reseller_customers( get_current_user_id() );
?>
<div class="rm-card">
    <h3><?php esc_html_e( 'Customer List', 'reseller-management' ); ?></h3>
    <table class="rm-table">
        <thead>
            <tr>
                <th><?php esc_html_e( 'Name', 'reseller-management' ); ?></th>
                <th><?php esc_html_e( 'Email', 'reseller-management' ); ?></th>
                <th><?php esc_html_e( 'Phone', 'reseller-management' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ( empty( $customers ) ) : ?>
                <tr>
                    <td colspan="3"><?php esc_html_e( 'No customers found from assigned reseller orders.', 'reseller-management' ); ?></td>
                </tr>
            <?php else : ?>
                <?php foreach ( $customers as $customer ) : ?>
                    <tr>
                        <td><?php echo esc_html( (string) $customer['name'] ); ?></td>
                        <td><?php echo esc_html( (string) $customer['email'] ); ?></td>
                        <td><?php echo esc_html( (string) $customer['phone'] ); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
