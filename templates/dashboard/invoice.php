<?php
/**
 * Professional Invoice Template
 *
 * @var WC_Order $order
 */
defined( 'ABSPATH' ) || exit;

// We expect $order to be available from the include in handle_print_invoice()
if ( ! isset( $order ) || ! $order instanceof WC_Order ) {
    exit;
}

$reseller_id = (int) $order->get_meta( '_assigned_reseller_id' );
$reseller    = $reseller_id ? get_userdata( $reseller_id ) : false;

if ( $reseller_id && $reseller ) {
	$business_name    = get_user_meta( $reseller_id, '_reseller_business_name', true ) ?: $reseller->display_name;
	$reseller_phone   = get_user_meta( $reseller_id, '_reseller_phone', true );
	$reseller_website = get_user_meta( $reseller_id, '_reseller_web_url', true );
	$reseller_email   = $reseller->user_email;
} else {
	$business_name    = get_bloginfo( 'name' );
	$reseller_phone   = get_option( 'woocommerce_store_phone', '' );
	$reseller_website = '';
	$reseller_email   = get_option( 'woocommerce_email_from_address', get_bloginfo( 'admin_email' ) );
}

$items = $order->get_items();
$items_subtotal = 0;
foreach ( $items as $item_id => $item ) {
    $resale_price = $item->get_meta('_resale_price');
    $unit_price = $resale_price ? floatval($resale_price) : ($item->get_quantity() > 0 ? $item->get_subtotal() / $item->get_quantity() : 0);
    $items_subtotal += $unit_price * $item->get_quantity();
}

$discount = $order->get_total_discount();
$shipping = $order->get_shipping_total();
$paid = floatval( $order->get_meta('_paid_amount') ?: '0' );

$calculated_total = $items_subtotal + $shipping - $discount;
$due = $calculated_total - $paid;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php printf( esc_html__( 'Invoice - Order #%s', 'reseller-management' ), $order->get_order_number() ); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #0d9488;
            --text-color: #334155;
            --text-muted: #64748b;
            --bg-color: #f8fafc;
            --bg-table-head: #f1f5f9;
            --border-color: #e2e8f0;
        }
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #e5e7eb;
            color: var(--text-color);
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .invoice-wrapper {
            max-width: 800px;
            margin: 40px auto;
            background: #ffffff;
            padding: 40px 50px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            border-radius: 8px;
        }
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid var(--border-color);
            padding-bottom: 24px;
            margin-bottom: 24px;
        }
        .brand-section {
            display: flex;
            flex-direction: column;
        }
        .brand-section h1 {
            margin: 0;
            font-size: 28px;
            color: #0f172a;
            font-weight: 700;
        }
        .brand-section p {
            margin: 4px 0 0;
            color: var(--text-muted);
            font-size: 14px;
        }
        .title-section {
            text-align: right;
        }
        .title-section h2 {
            margin: 0 0 8px;
            font-size: 32px;
            color: var(--primary-color);
            letter-spacing: 2px;
            text-transform: uppercase;
        }
        .title-section table {
            font-size: 14px;
            margin-left: auto;
        }
        .title-section table td {
            padding: 2px 0 2px 16px;
        }
        .title-section table td:first-child {
            color: var(--text-muted);
            font-weight: 500;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 32px;
        }
        .info-card {
            background: var(--bg-color);
            padding: 20px;
            border-radius: 6px;
            border: 1px solid var(--border-color);
        }
        .info-card h3 {
            margin: 0 0 12px;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-muted);
        }
        .info-card p {
            margin: 0 0 4px;
            font-size: 14px;
            line-height: 1.5;
        }
        .info-card strong {
            color: #0f172a;
            font-size: 15px;
            display: block;
            margin-bottom: 4px;
        }
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 32px;
        }
        .invoice-table th, .invoice-table td {
            padding: 12px 16px;
            text-align: left;
            font-size: 14px;
        }
        .invoice-table th {
            background-color: var(--bg-table-head);
            color: #0f172a;
            font-weight: 600;
            border-bottom: 2px solid var(--border-color);
        }
        .invoice-table td {
            border-bottom: 1px solid var(--border-color);
        }
        .invoice-table th:last-child, .invoice-table td:last-child {
            text-align: right;
        }
        .invoice-table th:nth-last-child(2), .invoice-table td:nth-last-child(2) {
            text-align: right;
        }
        .summary-wrapper {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .order-notes {
            flex: 1;
            margin-right: 40px;
            font-size: 13px;
            color: var(--text-muted);
        }
        .summary-totals {
            width: 320px;
        }
        .summary-totals table {
            width: 100%;
            border-collapse: collapse;
        }
        .summary-totals table td {
            padding: 8px 12px;
            font-size: 14px;
        }
        .summary-totals table tr:not(.total-row) td:first-child {
            color: var(--text-muted);
        }
        .summary-totals table td:last-child {
            text-align: right;
            font-weight: 500;
        }
        .total-row {
            background: var(--bg-color);
            border-radius: 4px;
        }
        .total-row td {
            padding-top: 12px;
            padding-bottom: 12px;
            font-weight: 700 !important;
            font-size: 18px !important;
            color: var(--primary-color) !important;
        }
        .invoice-footer {
            margin-top: 40px;
            text-align: center;
            font-size: 13px;
            color: var(--text-muted);
            border-top: 1px solid var(--border-color);
            padding-top: 20px;
        }
        .print-btn-wrapper {
            text-align: center;
            margin-bottom: 20px;
        }
        .btn-print {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 10px 24px;
            font-size: 15px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 6px -1px rgba(13, 148, 136, 0.2);
            transition: all 0.2s;
        }
        .btn-print:hover {
            background: #0f766e;
            transform: translateY(-1px);
        }
        @media print {
            body { background: white; margin: 0; padding: 0; }
            .invoice-wrapper { margin: 0; padding: 0; box-shadow: none; max-width: 100%; }
            .print-btn-wrapper { display: none; }
            @page { margin: 1cm; }
        }
    </style>
</head>
<body>
    <div class="print-btn-wrapper" style="margin-top: 20px;">
        <button class="btn-print" onclick="window.print()">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
            <?php esc_html_e( 'Print Invoice', 'reseller-management' ); ?>
        </button>
    </div>

    <div class="invoice-wrapper">
        <header class="invoice-header">
            <div class="brand-section">
                <h1><?php echo esc_html( $business_name ); ?></h1>
                <?php if ( $reseller_phone ) : ?><p><?php echo esc_html( $reseller_phone ); ?></p><?php endif; ?>
                <?php if ( $reseller_email ) : ?><p><?php echo esc_html( $reseller_email ); ?></p><?php endif; ?>
                <?php if ( $reseller_website ) : ?><p><?php echo esc_html( $reseller_website ); ?></p><?php endif; ?>
            </div>
            <div class="title-section">
                <h2><?php esc_html_e( 'INVOICE', 'reseller-management' ); ?></h2>
                <table>
                    <tr>
                        <td><?php esc_html_e( 'Invoice #:', 'reseller-management' ); ?></td>
                        <td><?php echo esc_html( $order->get_order_number() ); ?></td>
                    </tr>
                    <tr>
                        <td><?php esc_html_e( 'Date:', 'reseller-management' ); ?></td>
                        <td><?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?></td>
                    </tr>
                    <tr>
                        <td><?php esc_html_e( 'Status:', 'reseller-management' ); ?></td>
                        <td><span style="text-transform: capitalize; font-weight: 600; color: #0f172a;"><?php echo esc_html( wc_get_order_status_name( $order->get_status() ) ); ?></span></td>
                    </tr>
                </table>
            </div>
        </header>

        <div class="info-grid">
            <div class="info-card">
                <h3><?php esc_html_e( 'Billed To', 'reseller-management' ); ?></h3>
                <strong><?php echo esc_html( $order->get_formatted_billing_full_name() ); ?></strong>
                <p><?php echo nl2br( esc_html( $order->get_billing_address_1() ) ); ?></p>
                <?php if ( $order->get_billing_address_2() ) : ?>
                    <p><?php echo nl2br( esc_html( $order->get_billing_address_2() ) ); ?></p>
                <?php endif; ?>
                <p><?php echo esc_html( $order->get_billing_city() ); ?> <?php echo esc_html( $order->get_billing_postcode() ); ?></p>
                <p style="margin-top: 8px; color: var(--text-muted);">
                    <?php if ( $order->get_billing_phone() ) echo '📞 ' . esc_html( $order->get_billing_phone() ); ?>
                </p>
            </div>
            
            <div class="info-card">
                <h3><?php esc_html_e( 'Shipped To', 'reseller-management' ); ?></h3>
                <strong><?php echo esc_html( $order->get_formatted_shipping_full_name() ?: $order->get_formatted_billing_full_name() ); ?></strong>
                <p><?php echo nl2br( esc_html( $order->get_shipping_address_1() ?: $order->get_billing_address_1() ) ); ?></p>
                <?php if ( $order->get_shipping_address_2() || $order->get_billing_address_2() ) : ?>
                    <p><?php echo nl2br( esc_html( $order->get_shipping_address_2() ?: $order->get_billing_address_2() ) ); ?></p>
                <?php endif; ?>
                <p><?php echo esc_html( $order->get_shipping_city() ?: $order->get_billing_city() ); ?> <?php echo esc_html( $order->get_shipping_postcode() ?: $order->get_billing_postcode() ); ?></p>
            </div>
        </div>

        <table class="invoice-table">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Item Description', 'reseller-management' ); ?></th>
                    <th width="10%"><?php esc_html_e( 'Qty', 'reseller-management' ); ?></th>
                    <th width="15%"><?php esc_html_e( 'Price', 'reseller-management' ); ?></th>
                    <th width="15%"><?php esc_html_e( 'Total', 'reseller-management' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $items as $item_id => $item ) : 
                    $product = $item->get_product();
                    // Get custom resale price or use subtotal/qty
                    $resale_price = $item->get_meta('_resale_price');
                    $unit_price = $resale_price ? floatval($resale_price) : ($item->get_quantity() > 0 ? $item->get_subtotal() / $item->get_quantity() : 0);
                    $line_total = $unit_price * $item->get_quantity();
                ?>
                <tr>
                    <td>
                        <strong style="display: block; font-weight: 500; color: #0f172a; margin-bottom: 4px;"><?php echo esc_html( $item->get_name() ); ?></strong>
                        <?php if ( $product && $product->get_sku() ) : ?>
                            <span style="font-size: 12px; color: var(--text-muted);"><?php echo esc_html( 'SKU: ' . $product->get_sku() ); ?></span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo esc_html( $item->get_quantity() ); ?></td>
                    <td><?php echo wc_price( $unit_price ); ?></td>
                    <td><?php echo wc_price( $line_total ); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="summary-wrapper">
            <div class="order-notes">
                <?php if ( $order->get_customer_note() ) : ?>
                    <h4 style="margin: 0 0 8px; font-size: 14px; color: #0f172a;"><?php esc_html_e( 'Notes', 'reseller-management' ); ?></h4>
                    <p style="margin: 0; line-height: 1.6;"><?php echo nl2br( esc_html( $order->get_customer_note() ) ); ?></p>
                <?php endif; ?>
            </div>
            
            <div class="summary-totals">
                <table>
                    <tr>
                        <td><?php esc_html_e( 'Subtotal', 'reseller-management' ); ?></td>
                        <td><?php echo wc_price( $items_subtotal ); ?></td>
                    </tr>
                    <?php if ( $discount > 0 ) : ?>
                    <tr>
                        <td><?php esc_html_e( 'Discount', 'reseller-management' ); ?></td>
                        <td style="color: #ef4444;">-<?php echo wc_price( $discount ); ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td><?php esc_html_e( 'Shipping(+)', 'reseller-management' ); ?></td>
                        <td><?php echo wc_price( $shipping ); ?></td>
                    </tr>
                    <tr style="border-top: 1px dashed var(--border-color);">
                        <td><strong><?php esc_html_e( 'Total', 'reseller-management' ); ?></strong></td>
                        <td><strong><?php echo wc_price( $calculated_total ); ?></strong></td>
                    </tr>
                    <tr>
                        <td><?php esc_html_e( 'Advance Paid', 'reseller-management' ); ?></td>
                        <td><?php echo wc_price( $paid ); ?></td>
                    </tr>
                    <tr class="total-row">
                        <td><?php esc_html_e( 'Due Amount', 'reseller-management' ); ?></td>
                        <td><?php echo wc_price( $due ); ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <footer class="invoice-footer">
            <p style="margin: 0;"><?php esc_html_e( 'Thank you for your business!', 'reseller-management' ); ?></p>
            <p style="margin: 4px 0 0;"><?php printf( esc_html__( 'Generated on %s', 'reseller-management' ), date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ) ); ?></p>
        </footer>
    </div>
</body>
</html>
