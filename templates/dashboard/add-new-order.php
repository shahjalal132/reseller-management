<?php
/**
 * Add New Order template.
 *
 * @package reseller-management
 */

defined( 'ABSPATH' ) || exit;

$dashboard         = \BOILERPLATE\Inc\Reseller_Dashboard::get_instance();
$shipping_presets  = \BOILERPLATE\Inc\Reseller_Helper::get_shipping_presets();

$order_id = isset( $_GET['order_id'] ) ? absint( $_GET['order_id'] ) : 0;
$is_edit  = ( 'edit' === ( $_GET['subtab'] ?? '' ) && $order_id > 0 );
$order    = $is_edit ? wc_get_order( $order_id ) : null;

// Security check: ensure the order belongs to the current reseller
if ( $is_edit && (! $order || (int) $order->get_meta( '_assigned_reseller_id' ) !== (int) get_current_user_id()) ) {
    echo '<div class="rm-alert rm-alert-danger" style="margin: 20px;">' . esc_html__( 'Error: Order not found or permission denied.', 'reseller-management' ) . '</div>';
    return;
}

$customer_phone   = $is_edit ? $order->get_billing_phone() : '';
$customer_name    = $is_edit ? $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() : '';
$customer_address = $is_edit ? $order->get_billing_address_1() : '';
$district         = $is_edit ? $order->get_meta( '_order_district' ) : '';
$thana            = $is_edit ? $order->get_meta( '_order_thana' ) : '';
$order_notes      = $is_edit ? $order->get_customer_note() : '';

$shipping_charge = $is_edit ? $order->get_shipping_total() : '';
$discount        = $is_edit ? $order->get_discount_total() : '';
$paid_amount     = $is_edit ? $order->get_meta( '_paid_amount' ) : ''; // Using custom meta if exists
if ( $paid_amount === '' && $is_edit ) {
    $paid_amount = 0; // Fallback to 0
}

// Prepare items for JS
$prefilled_items = [];
if ( $is_edit ) {
    $reseller_orders_class = \BOILERPLATE\Inc\Reseller_Orders::get_instance();
    foreach ( $order->get_items() as $item ) {
        $product         = $item->get_product();
        if ( ! $product ) continue;

        $recommended_price = $product->get_meta( '_reseller_recommended_price' );
        if ( empty( $recommended_price ) ) {
            $recommended_price = $product->get_price();
        }

        // Get variants if variable product
        $variants = [];
        if ( $product->is_type( 'variation' ) ) {
            $parent = wc_get_product( $product->get_parent_id() );
            if ( $parent ) {
                // This is a bit complex to get all variants of the parent
                // For now, we'll just include the current one's data
            }
        }

        $prefilled_items[] = [
            'id'                => $product->get_id(),
            'name'              => $item->get_name(),
            'image'             => wp_get_attachment_image_url( $product->get_image_id(), 'thumbnail' ),
            'price'             => (float) $product->get_price(),
            'resale_price'      => (float) ($item->get_subtotal() / $item->get_quantity()),
            'recommended_price' => (float) $recommended_price,
            'quantity'          => (int) $item->get_quantity(),
            'variants'          => [], // Optional for edit mode if they don't change variant
            'selected_variant'  => $product->is_type( 'variation' ) ? $product->get_id() : 0,
        ];
    }
} elseif ( ! empty( $_GET['product_id'] ) ) {
    $p_id = absint( $_GET['product_id'] );
    $qty  = isset( $_GET['qty'] ) ? absint( $_GET['qty'] ) : 1;
    $product = wc_get_product( $p_id );
    if ( $product ) {
        $recommended_price = $product->get_meta( '_reseller_recommended_price' );
        if ( empty( $recommended_price ) ) {
            $recommended_price = $product->get_price();
        }

        $variants = [];
        if ( $product->is_type( 'variable' ) ) {
            foreach ( $product->get_available_variations() as $variation_data ) {
                $v_id = $variation_data['variation_id'];
                $variation = wc_get_product( $v_id );
                $v_recommended = $variation->get_meta( '_reseller_recommended_price' );
                if ( ! $v_recommended ) {
                    $v_recommended = $variation->get_price();
                }

                $variants[] = [
                    'id'                => $v_id,
                    'attributes'        => $variation_data['attributes'],
                    'price'             => (float) $variation->get_price(),
                    'recommended_price' => (float) $v_recommended,
                ];
            }
        }

        $prefilled_items[] = [
            'id'                => $product->get_id(),
            'name'              => $product->get_name(),
            'image'             => wp_get_attachment_image_url( $product->get_image_id(), 'thumbnail' ),
            'price'             => (float) $product->get_price(),
            'resale_price'      => (float) $recommended_price,
            'recommended_price' => (float) $recommended_price,
            'quantity'          => $qty,
            'variants'          => $variants,
            'selected_variant'  => 0,
        ];
    }
}

?>
<script>
    window.rmOrderPrefilledItems = <?php echo json_encode( $prefilled_items ); ?>;
</script>

<div class="rm-new-order-container">
    <?php if ( isset( $_GET['success'] ) && '1' === $_GET['success'] ) : ?>
        <div class="rm-alert rm-alert-success" style="margin-bottom: 24px; padding: 16px; background: var(--rm-success-bg); color: var(--rm-success); border-radius: 12px; border: 1px solid var(--rm-success); display: flex; align-items: center; gap: 12px;">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
            <span><?php esc_html_e( 'Order created successfully!', 'reseller-management' ); ?></span>
        </div>
    <?php endif; ?>
    <div class="rm-order-grid">
        <!-- Left Column: Customer Information -->
        <div class="rm-order-col-left">
            <div class="rm-card">
                <div class="rm-card-header">
                    <h3 class="rm-card-title"><?php esc_html_e( 'Customer Information', 'reseller-management' ); ?></h3>
                </div>
                <div class="rm-card-body">
                    <form id="rm-create-order-form-advanced" class="rm-form">
                        <?php if ( $is_edit ) : ?>
                            <input type="hidden" name="order_id" value="<?php echo esc_attr( $order_id ); ?>">
                            <input type="hidden" name="is_edit" value="1">
                        <?php endif; ?>
                        <div class="rm-form-group">
                            <label><?php esc_html_e( 'Customer Phone', 'reseller-management' ); ?></label>
                            <input type="text" name="customer_phone" placeholder="Enter Customar Phone" value="<?php echo esc_attr( $customer_phone ); ?>" required>
                        </div>
                        <div class="rm-form-group">
                            <label><?php esc_html_e( 'Customer Name', 'reseller-management' ); ?></label>
                            <input type="text" name="customer_name" placeholder="Enter Customer Name" value="<?php echo esc_attr( $customer_name ); ?>" required>
                        </div>
                        <div class="rm-form-group">
                            <label><?php esc_html_e( 'Customer Address', 'reseller-management' ); ?></label>
                            <textarea name="customer_address" rows="4" placeholder="Enter Customer Address" required><?php echo esc_textarea( $customer_address ); ?></textarea>
                        </div>
                        <div class="rm-form-group">
                            <label><?php esc_html_e( 'District', 'reseller-management' ); ?></label>
                            <select name="district" id="rm-order-district" class="rm-select2">
                                <option value=""><?php esc_html_e( 'Search City...', 'reseller-management' ); ?></option>
                                <?php if ( $is_edit && $district ) : ?>
                                    <option value="<?php echo esc_attr( $district ); ?>" selected><?php echo esc_html( $district ); ?></option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="rm-form-group">
                            <label><?php esc_html_e( 'Thana/Upazila', 'reseller-management' ); ?></label>
                            <select name="thana" id="rm-order-thana" class="rm-select2">
                                <option value=""><?php esc_html_e( 'Search Sub City...', 'reseller-management' ); ?></option>
                                <?php if ( $is_edit && $thana ) : ?>
                                    <option value="<?php echo esc_attr( $thana ); ?>" selected><?php echo esc_html( $thana ); ?></option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <?php if ( ! empty( $shipping_presets ) ) : ?>
                        <div class="rm-form-group rm-shipping-preset-group">
                            <fieldset class="rm-shipping-preset-fieldset">
                                <legend class="rm-shipping-preset-legend"><?php esc_html_e( 'Shipping option', 'reseller-management' ); ?></legend>
                                <div class="rm-shipping-preset-radios">
                                    <label class="rm-shipping-preset-label">
                                        <input type="radio" name="rm_shipping_preset" value="" checked>
                                        <span><?php esc_html_e( 'Custom', 'reseller-management' ); ?></span>
                                    </label>
                                    <?php foreach ( $shipping_presets as $idx => $preset ) : ?>
                                        <label class="rm-shipping-preset-label">
                                            <input type="radio" name="rm_shipping_preset" value="<?php echo esc_attr( (string) $idx ); ?>" data-charge="<?php echo esc_attr( (string) $preset['charge'] ); ?>">
                                            <span><?php echo esc_html( $preset['title'] ); ?> — <?php echo esc_html( number_format( $preset['charge'], 2 ) ); ?> ৳</span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                                <p class="rm-shipping-preset-hint"><?php esc_html_e( 'Selecting a preset fills shipping charge; you can change the amount in the order summary.', 'reseller-management' ); ?></p>
                            </fieldset>
                        </div>
                        <?php endif; ?>
                        <div class="rm-form-group">
                            <label><?php esc_html_e( 'Order Notes (Optional)', 'reseller-management' ); ?></label>
                            <textarea name="order_notes" rows="3" placeholder=""><?php echo esc_textarea( $order_notes ); ?></textarea>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right Column: Product Information -->
        <div class="rm-order-col-right">
            <div class="rm-card">
                <div class="rm-card-header">
                    <h3 class="rm-card-title"><?php esc_html_e( 'Product information', 'reseller-management' ); ?></h3>
                </div>
                <div class="rm-card-body">
                    <div class="rm-form-group">
                        <label><?php esc_html_e( 'Search product or enter Product SKU', 'reseller-management' ); ?></label>
                        <div class="rm-search-input-container">
                            <input type="text" style="width: 100%" id="rm-product-search-input" placeholder="<?php esc_attr_e( 'Type product code or name', 'reseller-management' ); ?>">
                            <div id="rm-product-search-results" class="rm-search-results-dropdown"></div>
                        </div>
                    </div>

                    <div class="rm-order-items-table-wrapper">
                        <table class="rm-order-items-table">
                            <thead>
                                <tr>
                                    <th width="40">#</th>
                                    <th><?php esc_html_e( 'Product', 'reseller-management' ); ?></th>
                                    <th width="120"><?php esc_html_e( 'Variant', 'reseller-management' ); ?></th>
                                    <th width="80"><?php esc_html_e( 'Quantity', 'reseller-management' ); ?></th>
                                    <th width="80"><?php esc_html_e( 'Price', 'reseller-management' ); ?></th>
                                    <th width="100"><?php esc_html_e( 'Resale Price', 'reseller-management' ); ?></th>
                                    <th width="100"><?php esc_html_e( 'Total', 'reseller-management' ); ?></th>
                                    <th width="50"><?php esc_html_e( 'Remove', 'reseller-management' ); ?></th>
                                </tr>
                            </thead>
                            <tbody id="rm-order-items-body">
                                <!-- Items will be added here dynamically -->
                                <tr class="rm-no-items">
                                    <td colspan="8"><?php esc_html_e( 'No products added yet.', 'reseller-management' ); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="rm-order-summary">
                        <div class="rm-summary-row">
                            <span><?php esc_html_e( 'Items Subtotal:', 'reseller-management' ); ?></span>
                            <div><span id="rm-summary-items-subtotal">0.00</span>৳</div>
                        </div>
                        <div class="rm-summary-row">
                            <span><?php esc_html_e( 'Shipping(+):', 'reseller-management' ); ?></span>
                            <div class="rm-summary-input-wrapper" style="display:flex; align-items:center;">
                                <input type="number" id="rm-shipping-charge" value="<?php echo esc_attr( $shipping_charge ); ?>" style="width: 80px; text-align: right; margin-right: 5px;"> ৳
                            </div>
                        </div>
                        <div class="rm-summary-row" style="display:none;">
                            <span><?php esc_html_e( 'Discount:', 'reseller-management' ); ?></span>
                            <div class="rm-summary-input-wrapper" style="display:flex; align-items:center;">
                                <input type="number" id="rm-discount" value="<?php echo esc_attr( $discount ); ?>" style="width: 80px; text-align: right; margin-right: 5px;"> ৳
                            </div>
                        </div>

                        <hr style="margin: 10px 0; border: none; border-top: 1px dashed #ccc;">

                        <div class="rm-summary-row">
                            <span><?php esc_html_e( 'Total:', 'reseller-management' ); ?></span>
                            <div><span id="rm-summary-total">0.00</span>৳</div>
                        </div>
                        <div class="rm-summary-row">
                            <span><?php esc_html_e( 'Advance Paid:', 'reseller-management' ); ?> <small>(it would be deduct from total amount)</small></span>
                            <div class="rm-summary-input-wrapper" style="display:flex; align-items:center;">
                                <input type="number" id="rm-paid-amount" value="<?php echo esc_attr( $paid_amount ); ?>" style="width: 80px; text-align: right; margin-right: 5px;"> ৳
                            </div>
                        </div>

                        <hr style="margin: 10px 0; border: none; border-top: 1px dashed #ccc;">
                        
                        <div class="rm-summary-row">
                            <span><?php esc_html_e( 'Due Amount:', 'reseller-management' ); ?></span>
                            <div><span id="rm-summary-due">0.00</span>৳</div>
                        </div>
                        <div class="rm-summary-row" style="font-size: 1.1em; font-weight: bold; color: var(--rm-primary, #000);">
                            <span><?php esc_html_e( 'Order Total:', 'reseller-management' ); ?></span>
                            <div><span id="rm-summary-order-total">0.00</span>৳</div>
                        </div>
                        
                        <div class="rm-summary-row rm-summary-profit">
                            <span><?php esc_html_e( 'Profit Amount:', 'reseller-management' ); ?></span>
                            <div><span id="rm-summary-profit">0.00</span>৳</div>
                        </div>
                    </div>

                    <div class="rm-order-actions">
                        <div class="rm-form-response"></div>
                        <button type="button" id="rm-submit-order-advanced" class="rm-button rm-button-submit">
                            <?php echo $is_edit ? esc_html__( 'Update Order', 'reseller-management' ) : esc_html__( 'Submit', 'reseller-management' ); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
