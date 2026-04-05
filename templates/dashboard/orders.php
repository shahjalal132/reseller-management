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
<?php if ( in_array( $_GET['subtab'] ?? '', [ 'add', 'edit' ], true ) ) : ?>
    <?php include PLUGIN_BASE_PATH . '/templates/dashboard/add-new-order.php'; ?>
<?php endif; ?>

<?php 
$active_subtab = $_GET['subtab'] ?? 'all';
if ( ! in_array( $active_subtab, [ 'add', 'edit' ], true ) ) : 
$user_id      = get_current_user_id();
$status_counts = \BOILERPLATE\Inc\Reseller_Orders::get_order_status_counts( $user_id );
$dashboard    = \BOILERPLATE\Inc\Reseller_Dashboard::get_instance();

$stats_config = [
    'new'        => [ 'label' => __( 'New', 'reseller-management' ), 'icon' => 'status_new', 'color' => '#000' ],
    'pending'    => [ 'label' => __( 'Pending', 'reseller-management' ), 'icon' => 'status_pending', 'color' => '#f59e0b' ],
    'confirmed'  => [ 'label' => __( 'Confirmed', 'reseller-management' ), 'icon' => 'status_confirmed', 'color' => '#10b981' ],
    'packaging'    => [ 'label' => __( 'Packaging', 'reseller-management' ), 'icon' => 'status_packaging', 'color' => '#3b82f6' ],
    'shipping'   => [ 'label' => __( 'Shipping', 'reseller-management' ), 'icon' => 'status_shipment', 'color' => '#6366f1' ],
    'delivered'  => [ 'label' => __( 'Delivered', 'reseller-management' ), 'icon' => 'status_delivered', 'color' => '#059669' ],
    'wfr'        => [ 'label' => __( 'WFR', 'reseller-management' ), 'icon' => 'status_wfr', 'color' => '#d97706' ],
    'returned'   => [ 'label' => __( 'Returned', 'reseller-management' ), 'icon' => 'status_returned', 'color' => '#9333ea' ],
    'cancel'     => [ 'label' => __( 'Cancel', 'reseller-management' ), 'icon' => 'status_cancel', 'color' => '#ef4444' ],
    'all'        => [ 'label' => __( 'All', 'reseller-management' ), 'icon' => 'status_all', 'color' => '#1e293b' ],
    'incomplete' => [ 'label' => __( 'Incomplete Order', 'reseller-management' ), 'icon' => 'status_incomplete', 'color' => '#64748b' ],
];
$active_subtab = $_GET['subtab'] ?? 'all';
$paged         = max( 1, get_query_var( 'paged' ), get_query_var( 'page' ), absint( $_GET['paged'] ?? 1 ) );
$per_page      = ( $_GET['limit'] === 'all' ) ? -1 : absint( $_GET['limit'] ?? 20 );
$search        = sanitize_text_field( $_GET['search'] ?? '' );
$date_from     = sanitize_text_field( $_GET['date_from'] ?? '' );
$date_to       = sanitize_text_field( $_GET['date_to'] ?? '' );

// Determine WooCommerce status from our subtab
$wc_status = '';
if ( ! in_array( $active_subtab, [ 'all', 'add', 'edit' ], true ) ) {
    switch ( $active_subtab ) {
        case 'new':        $wc_status = 'processing'; break;
        case 'pending':    $wc_status = [ 'pending', 'on-hold' ]; break;
        case 'confirmed':  $wc_status = 'confirmed'; break;
        case 'packaging':  $wc_status = 'packaging'; break;
        case 'shipping':   $wc_status = 'shipping'; break;
        case 'delivered':  $wc_status = [ 'delivered', 'completed' ]; break;
        case 'wfr':        $wc_status = 'wfr'; break;
        case 'returned':   $wc_status = [ 'returned', 'refunded' ]; break;
        case 'cancel':     $wc_status = 'cancelled'; break;
        case 'incomplete': $wc_status = 'failed'; break;
    }
}

// Fetch total count for pagination
$total_orders = \BOILERPLATE\Inc\Reseller_Orders::get_reseller_order_count( $user_id, $wc_status, $search, [
    'date_from' => $date_from,
    'date_to'   => $date_to,
] );
$total_pages  = ( $per_page > 0 ) ? ceil( $total_orders / $per_page ) : 1;

// Fetch paginated orders
$orders = \BOILERPLATE\Inc\Reseller_Orders::get_reseller_orders( $user_id, [
    'status'    => $wc_status,
    'limit'     => $per_page,
    'offset'    => ( $paged - 1 ) * max( 0, $per_page ),
    'search'    => $search,
    'date_from' => $date_from,
    'date_to'   => $date_to,
] );
?>
<div class="rm-orders-stats-container">
    <div class="rm-orders-stats-grid">
        <?php foreach ( $stats_config as $key => $config ) : 
            $card_url = $dashboard->get_dashboard_tab_url( 'orders', $key );
            $is_card_active = ( $active_subtab === $key );
            ?>
            <a href="<?php echo esc_url( $card_url ); ?>" class="rm-order-stat-card <?php echo $is_card_active ? 'is-active' : ''; ?>" style="border-top: 3px solid <?php echo esc_attr( $config['color'] ); ?>; text-decoration: none; color: inherit;">
                <div class="rm-stat-main">
                    <span class="rm-stat-count"><?php echo esc_html( (string) ($status_counts[ $key ] ?? 0) ); ?></span>
                    <span class="rm-stat-label"><?php echo esc_html( $config['label'] ); ?></span>
                </div>
                <div class="rm-stat-icon" style="color: <?php echo esc_attr( $config['color'] ); ?>">
                    <?php echo $dashboard->get_svg_icon( $config['icon'] ); ?>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<div class="rm-orders-controls">
    <div class="rm-filter-group">
        <input type="date" id="rm-filter-date-from" class="rm-input-date" value="<?php echo esc_attr( $date_from ); ?>" placeholder="mm/dd/yyyy">
        <input type="date" id="rm-filter-date-to" class="rm-input-date" value="<?php echo esc_attr( $date_to ); ?>" placeholder="mm/dd/yyyy">
        <div class="rm-search-wrapper">
            <input type="text" id="rm-filter-search" class="rm-input-search" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Enter Invoice, customer phone, or name', 'reseller-management' ); ?>">
        </div>
        <select id="rm-filter-limit" class="rm-input-select">
            <option value="20" <?php selected( $per_page, 20 ); ?>>20</option>
            <option value="30" <?php selected( $per_page, 30 ); ?>>30</option>
            <option value="50" <?php selected( $per_page, 50 ); ?>>50</option>
            <option value="100" <?php selected( $per_page, 100 ); ?>>100</option>
            <option value="all" <?php selected( $per_page, -1 ); ?>><?php esc_html_e( 'All', 'reseller-management' ); ?></option>
        </select>
    </div>
</div>

<div class="rm-enriched-table-container">
    <table class="rm-enriched-table">
        <thead>
            <tr>
                <th width="40">#</th>
                <th><?php esc_html_e( 'Customer', 'reseller-management' ); ?></th>
                <th><?php esc_html_e( 'Product', 'reseller-management' ); ?></th>
                <th><?php esc_html_e( 'Invoice', 'reseller-management' ); ?></th>
                <th><?php esc_html_e( 'Details', 'reseller-management' ); ?></th>
                <th><?php esc_html_e( 'Date', 'reseller-management' ); ?></th>
                <th><?php esc_html_e( 'Courier', 'reseller-management' ); ?></th>
                <th><?php esc_html_e( 'Comment', 'reseller-management' ); ?></th>
                <th><?php esc_html_e( 'Status', 'reseller-management' ); ?></th>
                <th><?php esc_html_e( 'Action', 'reseller-management' ); ?></th>
                <th><?php esc_html_e( 'View', 'reseller-management' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ( empty( $orders ) ) : ?>
                <tr>
                    <td colspan="11" class="rm-empty-state"><?php esc_html_e( 'No orders found.', 'reseller-management' ); ?></td>
                </tr>
            <?php else : $i = ( ( $paged - 1 ) * $per_page ) + 1; foreach ( $orders as $order ) : 
                $items = $order->get_items();
                $first_item = reset( $items );
                $product = $first_item ? $first_item->get_product() : null;
                $commission = \BOILERPLATE\Inc\Reseller_Finance::get_order_commission_total( $order );
                $status = $order->get_status();
                $status_name = ( 'processing' === $status ) ? __( 'New', 'reseller-management' ) : wc_get_order_status_name( $status );
                ?>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td class="rm-col-customer">
                        <div class="rm-customer-name"><?php echo esc_html( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() ); ?></div>
                        <div class="rm-customer-phone-badge">
                            <span class="rm-phone-icon"><?php echo $dashboard->get_svg_icon('account'); ?></span>
                            <span><?php echo esc_html( $order->get_billing_phone() ); ?></span>
                            <span class="rm-info-trigger" title="View Info">ⓘ</span>
                        </div>
                        <div class="rm-customer-address"><?php echo esc_html( $order->get_billing_address_1() ); ?></div>
                        <div class="rm-customer-order-id" style="font-size: 0.75rem; color: #000000ff; margin-top: 4px; font-weight: bold;"><?php echo esc_html__( 'Order ID:', 'reseller-management' ); ?> #<?php echo esc_html( (string) $order->get_id() ); ?></div>
                    </td>
                    <td class="rm-col-product">
                        <?php if ( $product ) : ?>
                            <div class="rm-product-preview">
                                <img src="<?php echo esc_url( wp_get_attachment_image_url( $product->get_image_id(), 'thumbnail' ) ); ?>" alt="">
                                <span class="rm-product-name"><?php echo esc_html( $product->get_name() ); ?></span>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td class="rm-col-invoice">
                        <span class="rm-invoice-id">M<?php echo esc_html( (string) $order->get_id() ); ?></span>
                    </td>
                    <td class="rm-col-details">
                        <div class="rm-details-grid">
                            <div class="rm-detail-row"><span>Total:</span> <strong><?php echo $order->get_total(); ?></strong></div>
                            <div class="rm-detail-row"><span>Discount:</span> <strong><?php echo $order->get_total_discount(); ?></strong></div>
                            <div class="rm-detail-row"><span>Paid:</span> <strong>0</strong></div>
                            <div class="rm-detail-row"><span>Shipping:</span> <strong><?php echo $order->get_shipping_total(); ?></strong></div>
                            <div class="rm-detail-row"><span>Due:</span> <strong><?php echo $order->get_total(); ?></strong></div>
                            <div class="rm-detail-row rm-detail-profit"><span>Profit:</span> <strong><?php echo $commission; ?></strong></div>
                        </div>
                    </td>
                    <td class="rm-col-date">
                        <div class="rm-order-date"><?php echo esc_html( $order->get_date_created()->date( 'Y-m-d H:i:s' ) ); ?></div>
                    </td>
                    <td class="rm-col-courier">
                        <div class="rm-courier-info">
                            <span class="rm-courier-name">Steadfast 2</span>
                            <a href="#" class="rm-courier-link">https://steadfast.co</a>
                        </div>
                    </td>
                    <td class="rm-col-comment">
                        <a href="#" class="rm-note-link"><?php esc_html_e( 'Note', 'reseller-management' ); ?></a>
                    </td>
                    <td class="rm-col-status">
                        <span class="rm-status-badge status-<?php echo esc_attr( $status ); ?>">
                            <?php echo esc_html( $status_name ); ?>
                        </span>
                    </td>
                    <td class="rm-col-action">
                        <div class="rm-action-dropdown-container">
                            <?php 
                            $restricted_statuses = [ 'delivered', 'shipping', 'packaging', 'cancelled', 'returned' ];
                            $is_restricted = in_array( $status, $restricted_statuses, true );
                            ?>
                            <button class="rm-btn-action-trigger" title="Action" <?php echo $is_restricted ? 'disabled' : ''; ?> style="<?php echo $is_restricted ? 'opacity: 0.5; cursor: not-allowed;' : ''; ?>">
                                <?php echo $dashboard->get_svg_icon('status_all'); ?>
                            </button>
                            <div class="rm-action-dropdown-menu">
                                <a href="<?php echo esc_url( $dashboard->get_dashboard_tab_url( 'orders', 'edit' ) . '&order_id=' . $order->get_id() ); ?>" class="rm-dropdown-item item-edit">
                                    <span><?php esc_html_e( 'Edit', 'reseller-management' ); ?></span>
                                </a>
                                <button class="rm-dropdown-item item-pending" data-order-id="<?php echo $order->get_id(); ?>" data-status="pending">
                                    <span><?php esc_html_e( 'Pending', 'reseller-management' ); ?></span>
                                </button>
                                <button class="rm-dropdown-item item-confirmed" data-order-id="<?php echo $order->get_id(); ?>" data-status="confirmed">
                                    <span><?php esc_html_e( 'Confirmed', 'reseller-management' ); ?></span>
                                </button>
                                <button class="rm-dropdown-item item-cancel" data-order-id="<?php echo $order->get_id(); ?>" data-status="cancelled">
                                    <span><?php esc_html_e( 'Cancel', 'reseller-management' ); ?></span>
                                </button>
                            </div>
                        </div>
                    </td>
                    <td class="rm-col-view">
                        <a href="<?php echo esc_url( home_url( '/?rm_action=print_invoice&order_id=' . $order->get_id() . '&nonce=' . wp_create_nonce( 'rm_public_nonce' ) ) ); ?>" target="_blank" class="rm-btn-view-teal" style="display: inline-flex; align-items: center; justify-content: center; text-decoration: none;">
                            <span class="rm-view-icon">👁</span>
                            <?php esc_html_e( 'view', 'reseller-management' ); ?>
                        </a>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<?php if ( $total_pages > 1 ) : ?>
    <div class="rm-pagination">
        <?php
        $pagination_args = [
            'base'      => add_query_arg( 'paged', '%#%' ),
            'format'    => '',
            'prev_text' => '&laquo; ' . __( 'Previous', 'reseller-management' ),
            'next_text' => __( 'Next', 'reseller-management' ) . ' &raquo;',
            'total'     => $total_pages,
            'current'   => $paged,
            'type'      => 'list',
        ];

        if ( ! empty( $search ) ) {
            $pagination_args['add_args']['search'] = $search;
        }
        if ( ! empty( $date_from ) ) {
            $pagination_args['add_args']['date_from'] = $date_from;
        }
        if ( ! empty( $date_to ) ) {
            $pagination_args['add_args']['date_to'] = $date_to;
        }
        if ( $per_page > 0 && $per_page !== 20 ) {
            $pagination_args['add_args']['limit'] = $per_page;
        } elseif ( $per_page === -1 ) {
            $pagination_args['add_args']['limit'] = 'all';
        }

        echo paginate_links( $pagination_args );
        ?>
    </div>
<?php endif; ?>
<?php endif; ?>
