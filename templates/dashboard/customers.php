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
    <h3 style="margin-bottom: 20px; font-weight: 600; color: #374151; font-size: 1.1rem;"><?php esc_html_e( 'Customer Table', 'reseller-management' ); ?></h3>
    
    <div class="rm-customers-controls" style="display: flex; justify-content: flex-end; align-items: center; margin-bottom: 20px;">
        <div class="rm-search-wrapper" style="display: flex;">
            <input type="text" id="rm-customer-search-input" placeholder="<?php esc_attr_e( 'customer name or 01xxxxxxxxx', 'reseller-management' ); ?>" style="border: 1px solid #ddd; padding: 8px 12px; border-radius: 4px 0 0 4px; width: 280px; font-size: 14px; outline: none;">
            <button id="rm-customer-search-btn" style="background-color: #10b981; color: white; border: none; padding: 8px 16px; border-radius: 0 4px 4px 0; cursor: pointer; font-size: 14px;">
                <?php esc_html_e( 'search', 'reseller-management' ); ?>
            </button>
        </div>
    </div>

    <div class="rm-enriched-table-container">
        <table class="rm-enriched-table">
            <thead>
                <tr>
                    <th width="40">#</th>
                    <th><?php esc_html_e( 'Name', 'reseller-management' ); ?></th>
                    <th><?php esc_html_e( 'Phone', 'reseller-management' ); ?></th>
                    <th><?php esc_html_e( 'City', 'reseller-management' ); ?></th>
                    <th><?php esc_html_e( 'Address', 'reseller-management' ); ?></th>
                </tr>
            </thead>
            <tbody id="rm-customers-tbody">
                <?php if ( empty( $customers ) ) : ?>
                    <tr>
                        <td colspan="5" class="rm-empty-state" style="text-align: center; padding: 30px;"><?php esc_html_e( 'No customers found from assigned reseller orders.', 'reseller-management' ); ?></td>
                    </tr>
                <?php else : $i = 1; foreach ( $customers as $customer ) : ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td><?php echo esc_html( (string) $customer['name'] ); ?></td>
                        <td><?php echo esc_html( (string) $customer['phone'] ); ?></td>
                        <td><?php echo esc_html( (string) $customer['city'] ); ?></td>
                        <td><?php echo esc_html( (string) $customer['address'] ); ?></td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
