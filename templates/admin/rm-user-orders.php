<?php
/**
 * Reseller Hub – Reseller Orders full page.
 *
 * Variables injected by render_user_orders_page():
 *   $rm_reseller_id  (int)
 *   $rm_user         (WP_User)
 *   $rm_all_orders   (WC_Order[])
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.Security.NonceVerification.Recommended
$rm_reseller_id = isset( $rm_reseller_id ) ? (int) $rm_reseller_id : 0;
$rm_user        = isset( $rm_user )        ? $rm_user              : null;
$rm_all_orders  = isset( $rm_all_orders ) && is_array( $rm_all_orders ) ? $rm_all_orders : [];

if ( ! $rm_user ) {
    wp_die( esc_html__( 'Invalid reseller.', 'reseller-management' ) );
}

// ── Filters from query string ──────────────────────────────────────────────
$f_search    = sanitize_text_field( wp_unslash( $_GET['rm_search']   ?? '' ) );
$f_status    = sanitize_key(        wp_unslash( $_GET['rm_status']   ?? '' ) );
$f_date_from = sanitize_text_field( wp_unslash( $_GET['rm_from']     ?? '' ) );
$f_date_to   = sanitize_text_field( wp_unslash( $_GET['rm_to']       ?? '' ) );
$paged       = max( 1, (int) ( $_GET['rm_paged'] ?? 1 ) );
$per_page    = 10;

// ── Build WC status map (wc-xxx → label) ──────────────────────────────────
$wc_statuses = class_exists( 'WooCommerce' ) ? wc_get_order_statuses() : [];

// ── Apply filters to order list ────────────────────────────────────────────
$filtered = [];
foreach ( $rm_all_orders as $order ) {
    if ( ! is_a( $order, \WC_Order::class ) ) {
        continue;
    }

    // Status filter
    if ( $f_status && 'wc-' . $order->get_status() !== $f_status && $order->get_status() !== $f_status ) {
        continue;
    }

    // Date from
    $ord_date = $order->get_date_created();
    if ( $f_date_from && $ord_date && $ord_date->date( 'Y-m-d' ) < $f_date_from ) {
        continue;
    }

    // Date to
    if ( $f_date_to && $ord_date && $ord_date->date( 'Y-m-d' ) > $f_date_to ) {
        continue;
    }

    // Search: order number, customer name/email/phone
    if ( $f_search ) {
        $haystack = implode( ' ', [
            '#' . $order->get_order_number(),
            $order->get_formatted_billing_full_name(),
            $order->get_billing_email(),
            $order->get_billing_phone(),
        ] );
        if ( false === stripos( $haystack, $f_search ) ) {
            continue;
        }
    }

    $filtered[] = $order;
}

// ── Pagination ─────────────────────────────────────────────────────────────
$total_orders = count( $filtered );
$total_pages  = max( 1, (int) ceil( $total_orders / $per_page ) );
$paged        = min( $paged, $total_pages );
$page_orders  = array_slice( $filtered, ( $paged - 1 ) * $per_page, $per_page );

// ── URL helpers ────────────────────────────────────────────────────────────
$back_url       = admin_url( 'admin.php?page=reseller-hub-user-view&reseller_id=' . $rm_reseller_id );
$base_url       = admin_url( 'admin.php?page=reseller-hub-user-orders&reseller_id=' . $rm_reseller_id );
$filter_base    = add_query_arg( array_filter( [
    'rm_search' => $f_search,
    'rm_status' => $f_status,
    'rm_from'   => $f_date_from,
    'rm_to'     => $f_date_to,
] ), $base_url );

// Pagination link builder
$page_url = function ( $p ) use ( $filter_base ) {
    return esc_url( add_query_arg( 'rm_paged', $p, $filter_base ) );
};
// phpcs:enable
?>

<!-- Back link -->
<a href="<?php echo esc_url( $back_url ); ?>" class="rm-back-btn">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
    </svg>
    <?php
    printf(
        /* translators: %s reseller name */
        esc_html__( 'Back to %s', 'reseller-management' ),
        esc_html( $rm_user->display_name )
    );
    ?>
</a>

<!-- Header -->
<div class="rm-page-header" style="margin-bottom:20px;">
    <div>
        <h1 class="rm-page-title">
            <?php
            printf(
                /* translators: %s reseller name */
                esc_html__( 'Orders — %s', 'reseller-management' ),
                esc_html( $rm_user->display_name )
            );
            ?>
        </h1>
        <p style="font-size:13.5px;color:#6b7280;margin:4px 0 0;">
            <?php
            printf(
                /* translators: %d total count */
                esc_html( _n( '%d order found', '%d orders found', $total_orders, 'reseller-management' ) ),
                (int) $total_orders
            );
            ?>
        </p>
    </div>
</div>

<!-- Filters bar -->
<form method="get" action="" class="rm-orders-filters-form">
    <input type="hidden" name="page" value="reseller-hub-user-orders">
    <input type="hidden" name="reseller_id" value="<?php echo esc_attr( (string) $rm_reseller_id ); ?>">

    <div class="rm-orders-filters">

        <!-- Search -->
        <div class="rm-filter-group rm-filter-search">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
            </svg>
            <input type="text"
                   name="rm_search"
                   value="<?php echo esc_attr( $f_search ); ?>"
                   placeholder="<?php esc_attr_e( 'Search order #, customer name, email, phone…', 'reseller-management' ); ?>"
                   class="rm-filter-input rm-filter-input--wide">
        </div>

        <!-- Status -->
        <div class="rm-filter-group">
            <label class="rm-filter-label"><?php esc_html_e( 'Status', 'reseller-management' ); ?></label>
            <select name="rm_status" class="rm-filter-select">
                <option value=""><?php esc_html_e( 'All Statuses', 'reseller-management' ); ?></option>
                <?php foreach ( $wc_statuses as $slug => $label ) : ?>
                    <option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $f_status, $slug ); ?>>
                        <?php echo esc_html( $label ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Date from -->
        <div class="rm-filter-group">
            <label class="rm-filter-label"><?php esc_html_e( 'From', 'reseller-management' ); ?></label>
            <input type="date" name="rm_from" value="<?php echo esc_attr( $f_date_from ); ?>" class="rm-filter-input">
        </div>

        <!-- Date to -->
        <div class="rm-filter-group">
            <label class="rm-filter-label"><?php esc_html_e( 'To', 'reseller-management' ); ?></label>
            <input type="date" name="rm_to" value="<?php echo esc_attr( $f_date_to ); ?>" class="rm-filter-input">
        </div>

        <div class="rm-filter-actions">
            <button type="submit" class="rm-filter-btn rm-filter-btn--apply">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" width="14" height="14">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z"/>
                </svg>
                <?php esc_html_e( 'Apply', 'reseller-management' ); ?>
            </button>
            <?php if ( $f_search || $f_status || $f_date_from || $f_date_to ) : ?>
                <a href="<?php echo esc_url( $base_url ); ?>" class="rm-filter-btn rm-filter-btn--clear">
                    <?php esc_html_e( 'Clear', 'reseller-management' ); ?>
                </a>
            <?php endif; ?>
        </div>
    </div>
</form>

<!-- Orders table -->
<div class="rm-users-table-wrap" style="margin-top:16px;">

    <?php if ( empty( $page_orders ) ) : ?>
        <div class="rm-empty-state">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007Z"/>
            </svg>
            <p><?php esc_html_e( 'No orders match the current filters.', 'reseller-management' ); ?></p>
        </div>
    <?php else : ?>

        <table class="rm-users-table rm-orders-table">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Order', 'reseller-management' ); ?></th>
                    <th><?php esc_html_e( 'Date', 'reseller-management' ); ?></th>
                    <th><?php esc_html_e( 'Status', 'reseller-management' ); ?></th>
                    <th><?php esc_html_e( 'Customer Name', 'reseller-management' ); ?></th>
                    <th><?php esc_html_e( 'Email', 'reseller-management' ); ?></th>
                    <th><?php esc_html_e( 'Phone', 'reseller-management' ); ?></th>
                    <th><?php esc_html_e( 'Items', 'reseller-management' ); ?></th>
                    <th><?php esc_html_e( 'Total', 'reseller-management' ); ?></th>
                    <th><?php esc_html_e( 'Action', 'reseller-management' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $page_orders as $order ) :
                    $ord_date    = $order->get_date_created();
                    $date_str    = $ord_date ? $ord_date->date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ) : '—';
                    $status_slug = $order->get_status();
                    $status_lbl  = wc_get_order_status_name( $status_slug );
                    $status_cls  = 'rm-order-status-' . sanitize_html_class( $status_slug );
                    $item_count  = $order->get_item_count();
                ?>
                <tr>
                    <td><strong>#<?php echo esc_html( $order->get_order_number() ); ?></strong></td>
                    <td style="white-space:nowrap;font-size:12.5px;color:#6b7280;"><?php echo esc_html( $date_str ); ?></td>
                    <td><span class="rm-order-status-badge <?php echo esc_attr( $status_cls ); ?>"><?php echo esc_html( $status_lbl ); ?></span></td>
                    <td>
                        <div class="rm-user-name-cell">
                            <div class="rm-user-avatar" style="width:32px;height:32px;font-size:13px;flex-shrink:0;">
                                <?php echo esc_html( strtoupper( mb_substr( $order->get_billing_first_name() ?: 'G', 0, 1 ) ) ); ?>
                            </div>
                            <span style="font-weight:600;color:#111827;"><?php echo esc_html( $order->get_formatted_billing_full_name() ?: '—' ); ?></span>
                        </div>
                    </td>
                    <td><?php echo esc_html( $order->get_billing_email() ?: '—' ); ?></td>
                    <td><?php echo esc_html( $order->get_billing_phone() ?: '—' ); ?></td>
                    <td style="text-align:center;">
                        <span class="rm-item-count-badge"><?php echo esc_html( (string) $item_count ); ?></span>
                    </td>
                    <td style="font-weight:600;color:#005f5a;"><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></td>
                    <td>
                        <a href="<?php echo esc_url( $order->get_edit_order_url() ); ?>" class="rm-pay-btn" style="padding:6px 12px;font-size:12px;white-space:nowrap;">
                            <?php esc_html_e( 'Open order', 'reseller-management' ); ?>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <?php if ( $total_pages > 1 ) : ?>
        <div class="rm-pagination">
            <div class="rm-pagination-info">
                <?php
                $from_item = ( $paged - 1 ) * $per_page + 1;
                $to_item   = min( $paged * $per_page, $total_orders );
                printf(
                    /* translators: 1: from, 2: to, 3: total */
                    esc_html__( 'Showing %1$d–%2$d of %3$d orders', 'reseller-management' ),
                    (int) $from_item,
                    (int) $to_item,
                    (int) $total_orders
                );
                ?>
            </div>
            <div class="rm-pagination-links">
                <?php if ( $paged > 1 ) : ?>
                    <a href="<?php echo $page_url( $paged - 1 ); ?>" class="rm-page-btn rm-page-btn--prev">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" width="15" height="15">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/>
                        </svg>
                    </a>
                <?php endif; ?>

                <?php
                $range = 2;
                for ( $i = 1; $i <= $total_pages; $i++ ) :
                    if ( $i === 1 || $i === $total_pages || abs( $i - $paged ) <= $range ) :
                ?>
                    <a href="<?php echo $page_url( $i ); ?>"
                       class="rm-page-btn<?php echo $i === $paged ? ' rm-page-btn--active' : ''; ?>">
                        <?php echo esc_html( (string) $i ); ?>
                    </a>
                <?php
                    elseif ( abs( $i - $paged ) === $range + 1 ) :
                ?>
                    <span class="rm-page-ellipsis">…</span>
                <?php
                    endif;
                endfor;
                ?>

                <?php if ( $paged < $total_pages ) : ?>
                    <a href="<?php echo $page_url( $paged + 1 ); ?>" class="rm-page-btn rm-page-btn--next">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" width="15" height="15">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
                        </svg>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

    <?php endif; ?>
</div>
