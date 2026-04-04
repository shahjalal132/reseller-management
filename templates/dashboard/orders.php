<?php
/**
 * Orders tab.
 *
 * @package reseller-management
 */

defined( 'ABSPATH' ) || exit;

$orders   = \BOILERPLATE\Inc\Reseller_Orders::get_reseller_orders( get_current_user_id() );
$products = get_posts(
    [
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
    ]
);
?>
<div class="rm-card">
    <h3><?php esc_html_e( 'Add New Order', 'reseller-management' ); ?></h3>
    <form id="rm-create-order-form" class="rm-form">
        <div class="rm-grid rm-grid-2">
            <label class="rm-field">
                <span><?php esc_html_e( 'Customer Name', 'reseller-management' ); ?></span>
                <input type="text" name="customer_name" required>
            </label>
            <label class="rm-field">
                <span><?php esc_html_e( 'Customer Phone', 'reseller-management' ); ?></span>
                <input type="text" name="customer_phone" required>
            </label>
            <label class="rm-field rm-field-full">
                <span><?php esc_html_e( 'Customer Address', 'reseller-management' ); ?></span>
                <textarea name="customer_address" rows="3" required></textarea>
            </label>
            <label class="rm-field rm-field-full">
                <span><?php esc_html_e( 'Products', 'reseller-management' ); ?></span>
                <select name="product_ids[]" multiple required style="min-height: 160px;">
                    <?php foreach ( $products as $product_post ) : ?>
                        <option value="<?php echo esc_attr( (string) $product_post->ID ); ?>"><?php echo esc_html( $product_post->post_title ); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        </div>
        <div class="rm-form-actions">
            <button type="submit" class="rm-button"><?php esc_html_e( 'Create Order', 'reseller-management' ); ?></button>
        </div>
        <div class="rm-form-response" aria-live="polite"></div>
    </form>
</div>

<div class="rm-card">
    <h3><?php esc_html_e( 'All Orders', 'reseller-management' ); ?></h3>
    <table class="rm-table">
        <thead>
            <tr>
                <th><?php esc_html_e( 'Order ID', 'reseller-management' ); ?></th>
                <th><?php esc_html_e( 'Customer', 'reseller-management' ); ?></th>
                <th><?php esc_html_e( 'Status', 'reseller-management' ); ?></th>
                <th><?php esc_html_e( 'Total', 'reseller-management' ); ?></th>
                <th><?php esc_html_e( 'Commission', 'reseller-management' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ( empty( $orders ) ) : ?>
                <tr>
                    <td colspan="5"><?php esc_html_e( 'No reseller orders found yet.', 'reseller-management' ); ?></td>
                </tr>
            <?php else : ?>
                <?php foreach ( $orders as $order ) : ?>
                    <tr>
                        <td>#<?php echo esc_html( (string) $order->get_id() ); ?></td>
                        <td><?php echo esc_html( $order->get_formatted_billing_full_name() ); ?></td>
                        <td><?php echo esc_html( wc_get_order_status_name( $order->get_status() ) ); ?></td>
                        <td><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></td>
                        <td><?php echo wp_kses_post( wc_price( \BOILERPLATE\Inc\Reseller_Finance::get_order_commission_total( $order ) ) ); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
