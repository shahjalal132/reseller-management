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
<?php
$pending_orders = array_filter(
    $orders,
    function( $order ) {
        return in_array( $order->get_status(), [ 'pending', 'on-hold', 'processing' ], true );
    }
);

$recent_orders = array_slice( $orders, 0, 5 );

// Dynamic Stats Loading
$top_sale_count = count( wc_get_products( [ 'status' => 'publish', 'limit' => -1, 'orderby' => 'meta_value_num', 'meta_key' => 'total_sales', 'order' => 'DESC' ] ) );
$ready_to_campaign_count = count( wc_get_products( [ 'status' => 'publish', 'limit' => -1, 'category' => [ 'campaign' ] ] ) ); // Fallback to 'campaign' category
if ( 0 === $ready_to_campaign_count ) {
    $ready_to_campaign_count = count( wc_get_products( [ 'status' => 'publish', 'limit' => -1, 'tag' => [ 'featured' ] ] ) ); // Fallback to featured
}

$updated_count = count( wc_get_products( [ 'status' => 'publish', 'limit' => -1, 'date_modified' => '>' . date( 'Y-m-d', strtotime( '-7 days' ) ) ] ) );
$new_arrived_count = count( wc_get_products( [ 'status' => 'publish', 'limit' => -1, 'date_created' => '>' . date( 'Y-m-d', strtotime( '-7 days' ) ) ] ) );
$top_view_count = $top_sale_count; // Proxy for top view if no tracking exists
$max_in_stock_count = count( wc_get_products( [ 'status' => 'publish', 'limit' => -1, 'stock_status' => 'instock' ] ) );
?>
<div class="rm-stats-container-new">
    <div class="rm-stat-card-new color-blue">
        <div class="rm-stat-icon-new">
            <span class="dashicons dashicons-chart-area"></span>
        </div>
        <div class="rm-stat-value-new"><?php echo (int) $top_sale_count; ?></div>
        <div class="rm-stat-label-new"><?php esc_html_e( 'Top Sale Products', 'reseller-management' ); ?></div>
    </div>

    <div class="rm-stat-card-new color-green">
        <div class="rm-stat-icon-new">
            <span class="dashicons dashicons-megaphone"></span>
        </div>
        <div class="rm-stat-value-new"><?php echo (int) $ready_to_campaign_count; ?></div>
        <div class="rm-stat-label-new"><?php esc_html_e( 'Ready to Campaign', 'reseller-management' ); ?></div>
    </div>

    <div class="rm-stat-card-new color-orange">
        <div class="rm-stat-icon-new">
            <span class="dashicons dashicons-update"></span>
        </div>
        <div class="rm-stat-value-new"><?php echo (int) $updated_count; ?></div>
        <div class="rm-stat-label-new"><?php esc_html_e( 'Updated Products', 'reseller-management' ); ?></div>
    </div>

    <div class="rm-stat-card-new color-purple">
        <div class="rm-stat-icon-new">
            <span class="dashicons dashicons-images-alt2"></span>
        </div>
        <div class="rm-stat-value-new"><?php echo (int) $new_arrived_count; ?></div>
        <div class="rm-stat-label-new"><?php esc_html_e( 'New Arrived Products', 'reseller-management' ); ?></div>
    </div>

    <div class="rm-stat-card-new color-red">
        <div class="rm-stat-icon-new">
            <span class="dashicons dashicons-visibility"></span>
        </div>
        <div class="rm-stat-value-new"><?php echo (int) $top_view_count; ?></div>
        <div class="rm-stat-label-new"><?php esc_html_e( 'Top View Products', 'reseller-management' ); ?></div>
    </div>

    <div class="rm-stat-card-new color-yellow">
        <div class="rm-stat-icon-new">
            <span class="dashicons dashicons-layers"></span>
        </div>
        <div class="rm-stat-value-new"><?php echo (int) $max_in_stock_count; ?></div>
        <div class="rm-stat-label-new"><?php esc_html_e( 'Maximum In Stock', 'reseller-management' ); ?></div>
    </div>
</div>

<div class="rm-charts-row">
    <div class="rm-chart-container rm-profit-chart">
        <div class="rm-chart-header">
            <h3><?php esc_html_e( 'Profit', 'reseller-management' ); ?></h3>
        </div>
        <canvas id="rm-profit-canvas"></canvas>
    </div>

    <div class="rm-chart-container rm-order-count-chart">
        <div class="rm-chart-header">
            <h3><?php esc_html_e( 'Order Count:', 'reseller-management' ); ?></h3>
            <select class="rm-chart-filter">
                <option value="7"><?php esc_html_e( 'Filter By Days', 'reseller-management' ); ?></option>
                <option value="30"><?php esc_html_e( 'Last 30 Days', 'reseller-management' ); ?></option>
            </select>
        </div>
        <div class="rm-donut-wrapper">
            <canvas id="rm-order-count-canvas"></canvas>
        </div>
    </div>
</div>
