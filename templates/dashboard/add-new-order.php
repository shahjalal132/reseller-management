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
