<?php
/**
 * Add New Order template.
 *
 * @package reseller-management
 */

defined( 'ABSPATH' ) || exit;

$dashboard = \BOILERPLATE\Inc\Reseller_Dashboard::get_instance();
?>

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
                        <div class="rm-form-group">
                            <label><?php esc_html_e( 'Customer Phone', 'reseller-management' ); ?></label>
                            <input type="text" name="customer_phone" placeholder="Enter Customar Phone" required>
                        </div>
                        <div class="rm-form-group">
                            <label><?php esc_html_e( 'Customer Name', 'reseller-management' ); ?></label>
                            <input type="text" name="customer_name" placeholder="Enter Customer Name" required>
                        </div>
                        <div class="rm-form-group">
                            <label><?php esc_html_e( 'Customer Address', 'reseller-management' ); ?></label>
                            <textarea name="customer_address" rows="4" placeholder="Enter Customer Address" required></textarea>
                        </div>
                        <div class="rm-form-group">
                            <label><?php esc_html_e( 'District', 'reseller-management' ); ?></label>
                            <select name="district" id="rm-order-district" class="rm-select2">
                                <option value=""><?php esc_html_e( 'Search City...', 'reseller-management' ); ?></option>
                            </select>
                        </div>
                        <div class="rm-form-group">
                            <label><?php esc_html_e( 'Thana/Upazila', 'reseller-management' ); ?></label>
                            <select name="thana" id="rm-order-thana" class="rm-select2">
                                <option value=""><?php esc_html_e( 'Search Sub City...', 'reseller-management' ); ?></option>
                            </select>
                        </div>
                        <div class="rm-form-group">
                            <label><?php esc_html_e( 'Order Notes (Optional)', 'reseller-management' ); ?></label>
                            <textarea name="order_notes" rows="3" placeholder=""></textarea>
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
                            <input type="text" style="width: 50%" id="rm-product-search-input" placeholder="<?php esc_attr_e( 'Type product code or name', 'reseller-management' ); ?>">
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
                            <span><?php esc_html_e( 'Total Amount', 'reseller-management' ); ?></span>
                            <span id="rm-summary-total">0</span>
                        </div>
                        <div class="rm-summary-row">
                            <span><?php esc_html_e( 'Shipping charge', 'reseller-management' ); ?></span>
                            <div class="rm-summary-input-wrapper">
                                <input type="number" id="rm-shipping-charge" value="">
                            </div>
                        </div>
                        <div class="rm-summary-row">
                            <span><?php esc_html_e( 'Discount (discount amount will reduce from your profit)', 'reseller-management' ); ?></span>
                            <div class="rm-summary-input-wrapper">
                                <input type="number" id="rm-discount" value="">
                            </div>
                        </div>
                        <div class="rm-summary-row">
                            <span><?php esc_html_e( 'Paid (paid amount will pay from your cashbook)', 'reseller-management' ); ?></span>
                            <div class="rm-summary-input-wrapper">
                                <input type="number" id="rm-paid-amount" value="">
                            </div>
                        </div>
                        <div class="rm-summary-row rm-summary-payable">
                            <span><?php esc_html_e( 'Payable Amount', 'reseller-management' ); ?></span>
                            <span id="rm-summary-payable">0</span>
                        </div>
                        <div class="rm-summary-row">
                            <span><?php esc_html_e( 'Due Amount', 'reseller-management' ); ?></span>
                            <span id="rm-summary-due">0</span>
                        </div>
                        <div class="rm-summary-row rm-summary-profit">
                            <span><?php esc_html_e( 'Profit Amount', 'reseller-management' ); ?></span>
                            <span id="rm-summary-profit">0</span>
                        </div>
                    </div>

                    <div class="rm-order-actions">
                        <div class="rm-form-response"></div>
                        <button type="button" id="rm-submit-order-advanced" class="rm-button rm-button-submit">
                            <?php esc_html_e( 'Submit', 'reseller-management' ); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
