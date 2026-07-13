<?php
/**
 * Reseller Hub – Settings page.
 *
 * @package reseller-management
 */
defined( 'ABSPATH' ) || exit;

$rm_settings       = $rm_settings ?? get_option( 'rm_settings', [] );
$shipping_presets  = \BOILERPLATE\Inc\Reseller_Helper::get_shipping_presets();

// Notices.
$notice       = $_GET['rm_notice'] ?? '';
$notice_msg   = '';
$notice_class = 'success';

if ( 'settings-updated' === $notice ) {
    $notice_msg = __( 'Settings updated successfully.', 'reseller-management' );
}
?>

<div class="rm-page-header">
    <h1 class="rm-page-title"><?php esc_html_e( 'Settings', 'reseller-management' ); ?></h1>
</div>

<?php if ( $notice_msg ) : ?>
    <div class="rm-notice <?php echo esc_attr( $notice_class ); ?>">
        <p><?php echo esc_html( $notice_msg ); ?></p>
    </div>
<?php endif; ?>

<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
    <?php wp_nonce_field( 'rm_save_settings' ); ?>
    <input type="hidden" name="action" value="rm_save_settings">

    <div class="rm-section-card rm-settings-card">
        <div class="rm-card-header">
            <h3 class="rm-card-title"><?php esc_html_e( 'COD Settings', 'reseller-management' ); ?></h3>
        </div>
        <div class="rm-card-body">
            <div class="rm-form-group rm-settings-toggle-wrap">
                <label class="rm-toggle">
                    <input type="checkbox" name="cod_enabled" value="yes" <?php checked( 'yes', $rm_settings['cod_enabled'] ?? 'no' ); ?>>
                    <span class="rm-toggle-slider"></span>
                    <span class="rm-toggle-label"><?php esc_html_e( 'Enable COD Option', 'reseller-management' ); ?></span>
                </label>
            </div>
            
            <div class="rm-settings-grid mt-20">
                <div class="rm-settings-field">
                    <label><?php esc_html_e( 'COD Percentage (%)', 'reseller-management' ); ?></label>
                    <input type="number" step="0.01" name="cod_input1" value="<?php echo esc_attr( $rm_settings['cod_input1'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'Enter percentage (e.g. 5)', 'reseller-management' ); ?>">
                </div>
            </div>
        </div>
    </div>

    <div class="rm-section-card rm-settings-card mt-20">
        <div class="rm-card-header">
            <h3 class="rm-card-title"><?php esc_html_e( 'Packaging Cost Settings', 'reseller-management' ); ?></h3>
        </div>
        <div class="rm-card-body">
            <div class="rm-form-group rm-settings-toggle-wrap">
                <label class="rm-toggle">
                    <input type="checkbox" name="packaging_cost_enabled" value="yes" <?php checked( 'yes', $rm_settings['packaging_cost_enabled'] ?? 'no' ); ?>>
                    <span class="rm-toggle-slider"></span>
                    <span class="rm-toggle-label"><?php esc_html_e( 'Enable Packaging Cost Option', 'reseller-management' ); ?></span>
                </label>
            </div>
            
            <div class="rm-settings-grid mt-20">
                <div class="rm-settings-field">
                    <label><?php esc_html_e( 'Packaging Cost', 'reseller-management' ); ?></label>
                    <input type="number" step="0.01" min="0" name="packaging_cost_input1" value="<?php echo esc_attr( $rm_settings['packaging_cost_input1'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'Enter amount (e.g. 10)', 'reseller-management' ); ?>">
                </div>
            </div>
        </div>
    </div>

    <div class="rm-section-card rm-settings-card mt-20">
        <div class="rm-card-header">
            <h3 class="rm-card-title"><?php esc_html_e( 'Withdrawals', 'reseller-management' ); ?></h3>
        </div>
        <div class="rm-card-body">
            <div class="rm-settings-grid mt-20">
                <div class="rm-settings-field">
                    <label><?php esc_html_e( 'Minimum balance to retain', 'reseller-management' ); ?></label>
                    <input type="number" step="0.01" min="0" name="minimum_balance" value="<?php echo esc_attr( isset( $rm_settings['minimum_balance'] ) ? (string) $rm_settings['minimum_balance'] : '0' ); ?>" placeholder="<?php esc_attr_e( '0.00', 'reseller-management' ); ?>">
                    <p class="description"><?php esc_html_e( 'Resellers cannot withdraw an amount that would leave their balance below this value. Use 0 for no minimum.', 'reseller-management' ); ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="rm-section-card rm-settings-card mt-20">
        <div class="rm-card-header">
            <h3 class="rm-card-title"><?php esc_html_e( 'Shipping charge presets', 'reseller-management' ); ?></h3>
        </div>
        <div class="rm-card-body">
            <p class="description rm-settings-description-top"><?php esc_html_e( 'Resellers can pick these on Add New Order to fill the shipping charge (they may still edit the amount). Rows without a title are ignored when saving.', 'reseller-management' ); ?></p>
            <div class="rm-table-responsive">
                <table id="rm-shipping-presets-table" class="rm-users-table rm-shipping-presets-table">
                <thead>
                    <tr>
                        <th scope="col"><?php esc_html_e( 'Title', 'reseller-management' ); ?></th>
                        <th scope="col" style="width: 140px;"><?php esc_html_e( 'Charge', 'reseller-management' ); ?></th>
                        <th scope="col" style="width: 100px;"></th>
                    </tr>
                </thead>
                <tbody id="rm-shipping-presets-tbody">
                    <?php foreach ( $shipping_presets as $preset ) : ?>
                        <tr class="rm-shipping-preset-row">
                            <td>
                                <input type="text" name="rm_shipping_preset_title[]" class="rm-settings-inline-input" value="<?php echo esc_attr( $preset['title'] ); ?>">
                            </td>
                            <td>
                                <input type="number" name="rm_shipping_preset_charge[]" step="0.01" min="0" class="rm-settings-inline-input rm-settings-inline-input--sm" value="<?php echo esc_attr( (string) $preset['charge'] ); ?>">
                            </td>
                            <td>
                                <button type="button" class="rm-settings-btn rm-settings-btn--ghost rm-shipping-preset-remove"><?php esc_html_e( 'Remove', 'reseller-management' ); ?></button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
            <p class="rm-settings-actions-row">
                <button type="button" class="rm-settings-btn rm-settings-btn--secondary" id="rm-shipping-preset-add"><?php esc_html_e( 'Add Preset', 'reseller-management' ); ?></button>
            </p>
            <template id="rm-shipping-preset-row-template">
                <tr class="rm-shipping-preset-row">
                    <td>
                        <input type="text" name="rm_shipping_preset_title[]" class="rm-settings-inline-input" value="">
                    </td>
                    <td>
                        <input type="number" name="rm_shipping_preset_charge[]" step="0.01" min="0" class="rm-settings-inline-input rm-settings-inline-input--sm" value="">
                    </td>
                    <td>
                        <button type="button" class="rm-settings-btn rm-settings-btn--ghost rm-shipping-preset-remove"><?php esc_html_e( 'Remove', 'reseller-management' ); ?></button>
                    </td>
                </tr>
            </template>
        </div>
    </div>

    <div class="rm-section-card rm-settings-card mt-20">
        <div class="rm-card-header">
            <h3 class="rm-card-title"><?php esc_html_e( 'Branding / Appearance', 'reseller-management' ); ?></h3>
        </div>
        <div class="rm-card-body">
            <p class="description rm-settings-description-top"><?php esc_html_e( 'These colors and fonts apply to the reseller dashboard, homepage, admin hub, and invoices.', 'reseller-management' ); ?></p>
            <?php
            $branding      = \BOILERPLATE\Inc\Reseller_Helper::get_branding_settings();
            $color_fields  = \BOILERPLATE\Inc\Reseller_Helper::get_branding_color_fields();
            $font_choices  = \BOILERPLATE\Inc\Reseller_Helper::get_font_choices();
            $color_field_ids = [];
            ?>
            <div class="rm-settings-grid">
                <?php foreach ( $color_fields as $color_key => $color_field ) :
                    $input_id          = 'branding_' . $color_key;
                    $color_field_ids[] = $input_id;
                    $color_value       = $branding[ $color_key ];
                    ?>
                    <div class="rm-settings-field">
                        <label for="<?php echo esc_attr( $input_id ); ?>"><?php echo esc_html( $color_field['label'] ); ?></label>
                        <div class="rm-settings-color-row">
                            <input type="color" id="<?php echo esc_attr( $input_id ); ?>" name="<?php echo esc_attr( $input_id ); ?>" value="<?php echo esc_attr( $color_value ); ?>" class="rm-settings-color-input">
                            <input type="text" id="<?php echo esc_attr( $input_id ); ?>_hex" value="<?php echo esc_attr( $color_value ); ?>" class="rm-settings-color-hex" maxlength="7" pattern="^#([A-Fa-f0-9]{6})$" aria-label="<?php echo esc_attr( $color_field['label'] ); ?>">
                        </div>
                    </div>
                <?php endforeach; ?>
                <div class="rm-settings-field">
                    <label for="branding_body_font"><?php esc_html_e( 'Body Font', 'reseller-management' ); ?></label>
                    <select id="branding_body_font" name="branding_body_font" class="rm-settings-select">
                        <?php foreach ( $font_choices as $font_value => $font_label ) : ?>
                            <option value="<?php echo esc_attr( $font_value ); ?>" <?php selected( $branding['body_font'], $font_value ); ?>><?php echo esc_html( $font_label ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="rm-settings-field">
                    <label for="branding_heading_font"><?php esc_html_e( 'Heading Font', 'reseller-management' ); ?></label>
                    <select id="branding_heading_font" name="branding_heading_font" class="rm-settings-select">
                        <?php foreach ( $font_choices as $font_value => $font_label ) : ?>
                            <option value="<?php echo esc_attr( $font_value ); ?>" <?php selected( $branding['heading_font'], $font_value ); ?>><?php echo esc_html( $font_label ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="rm-section-card rm-settings-card mt-20">
        <div class="rm-card-header">
            <h3 class="rm-card-title"><?php esc_html_e( 'Contact Information', 'reseller-management' ); ?></h3>
        </div>
        <div class="rm-card-body">
            <p class="description rm-settings-description-top"><?php esc_html_e( 'Shown in the website footer and used as defaults for live chat.', 'reseller-management' ); ?></p>
            <?php
            $contact_settings = \BOILERPLATE\Inc\Reseller_Helper::get_contact_settings( false );
            $contact_fields   = \BOILERPLATE\Inc\Reseller_Helper::get_contact_field_defs();
            ?>
            <div class="rm-settings-grid">
                <?php foreach ( $contact_fields as $field_key => $field ) :
                    if ( 'contact' !== $field['section'] ) {
                        continue;
                    }
                    $value = $contact_settings[ $field_key ] ?? '';
                    ?>
                    <div class="rm-settings-field<?php echo 'textarea' === $field['type'] ? ' rm-settings-field--full' : ''; ?>">
                        <label for="<?php echo esc_attr( $field_key ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
                        <?php if ( 'textarea' === $field['type'] ) : ?>
                            <textarea id="<?php echo esc_attr( $field_key ); ?>" name="<?php echo esc_attr( $field_key ); ?>" rows="3" class="rm-settings-textarea" placeholder="<?php echo esc_attr( $field['placeholder'] ?? '' ); ?>"><?php echo esc_textarea( $value ); ?></textarea>
                        <?php else : ?>
                            <input type="<?php echo esc_attr( 'email' === $field['type'] ? 'email' : 'text' ); ?>" id="<?php echo esc_attr( $field_key ); ?>" name="<?php echo esc_attr( $field_key ); ?>" value="<?php echo esc_attr( $value ); ?>" placeholder="<?php echo esc_attr( $field['placeholder'] ?? '' ); ?>">
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="rm-section-card rm-settings-card mt-20">
        <div class="rm-card-header">
            <h3 class="rm-card-title"><?php esc_html_e( 'Social Media', 'reseller-management' ); ?></h3>
        </div>
        <div class="rm-card-body">
            <p class="description rm-settings-description-top"><?php esc_html_e( 'Leave blank to hide a network from the footer.', 'reseller-management' ); ?></p>
            <div class="rm-settings-grid">
                <?php foreach ( $contact_fields as $field_key => $field ) :
                    if ( 'social' !== $field['section'] ) {
                        continue;
                    }
                    $value = $contact_settings[ $field_key ] ?? '';
                    ?>
                    <div class="rm-settings-field">
                        <label for="<?php echo esc_attr( $field_key ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
                        <input type="url" id="<?php echo esc_attr( $field_key ); ?>" name="<?php echo esc_attr( $field_key ); ?>" value="<?php echo esc_attr( $value ); ?>" placeholder="<?php echo esc_attr( $field['placeholder'] ?? '' ); ?>">
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="rm-section-card rm-settings-card mt-20">
        <div class="rm-card-header">
            <h3 class="rm-card-title"><?php esc_html_e( 'Live Chat', 'reseller-management' ); ?></h3>
        </div>
        <div class="rm-card-body">
            <p class="description rm-settings-description-top"><?php esc_html_e( 'Floating buttons for Messenger, WhatsApp, and Call on public pages. Empty fields are hidden.', 'reseller-management' ); ?></p>
            <div class="rm-form-group rm-settings-toggle-wrap">
                <label class="rm-toggle">
                    <input type="checkbox" name="chat_enabled" value="yes" <?php checked( 'yes', $contact_settings['chat_enabled'] ?? 'yes' ); ?>>
                    <span class="rm-toggle-slider"></span>
                    <span class="rm-toggle-label"><?php esc_html_e( 'Enable Live Chat Buttons', 'reseller-management' ); ?></span>
                </label>
            </div>
            <div class="rm-settings-grid mt-20">
                <?php foreach ( $contact_fields as $field_key => $field ) :
                    if ( 'chat' !== $field['section'] || 'checkbox' === $field['type'] ) {
                        continue;
                    }
                    $value = $contact_settings[ $field_key ] ?? '';
                    ?>
                    <div class="rm-settings-field">
                        <label for="<?php echo esc_attr( $field_key ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
                        <input type="text" id="<?php echo esc_attr( $field_key ); ?>" name="<?php echo esc_attr( $field_key ); ?>" value="<?php echo esc_attr( $value ); ?>" placeholder="<?php echo esc_attr( $field['placeholder'] ?? '' ); ?>">
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="rm-section-card rm-settings-card mt-20">
        <div class="rm-card-header">
            <h3 class="rm-card-title"><?php esc_html_e( 'Steadfast Webhook Integration', 'reseller-management' ); ?></h3>
        </div>
        <div class="rm-card-body">
            <div class="rm-form-group">
                <div class="rm-settings-field">
                    <label><?php esc_html_e( 'Callback URL', 'reseller-management' ); ?></label>
                    <input type="text" class="rm-settings-readonly-input" value="<?php echo esc_url( get_rest_url( null, 'reseller/v1/steadfast-webhook' ) ); ?>" readonly onclick="this.select();">
                    <p class="description"><?php esc_html_e( 'Copy this URL and paste it into your Steadfast Webhook configuration.', 'reseller-management' ); ?></p>
                </div>
            </div>
            
            <div class="rm-settings-grid mt-20">
                <div class="rm-settings-field">
                    <label><?php esc_html_e( 'Secret Token', 'reseller-management' ); ?></label>
                    <input type="text" name="steadfast_secret_token" value="<?php echo esc_attr( $rm_settings['steadfast_secret_token'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'Enter secret token from Steadfast', 'reseller-management' ); ?>">
                </div>
            </div>
        </div>
    </div>

    <div class="rm-form-actions mt-20">
        <button type="submit" class="rm-settings-btn rm-settings-btn--primary">
            <?php esc_html_e( 'Save Settings', 'reseller-management' ); ?>
        </button>
    </div>
</form>

<style>
    .mt-20 { margin-top: 20px; }
    .rm-settings-card .rm-card-header {
        padding: 14px 20px;
        border-bottom: 1px solid #f0f0f0;
        background: #fff;
    }
    .rm-settings-card .rm-card-title {
        margin: 0;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: #9ca3af;
    }
    .rm-settings-card .rm-card-body { padding: 20px; }

    .rm-settings-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }
    .rm-settings-field { display: flex; flex-direction: column; gap: 6px; }
    .rm-settings-field label {
        font-size: 12px;
        font-weight: 700;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: .04em;
    }
    .rm-settings-field input[type="text"],
    .rm-settings-field input[type="number"],
    .rm-settings-field input[type="email"],
    .rm-settings-field input[type="url"],
    .rm-settings-field select.rm-settings-select,
    .rm-settings-field textarea.rm-settings-textarea {
        width: 100%;
        border: 1.5px solid #e5e7eb;
        border-radius: 8px;
        min-height: 40px;
        padding: 9px 12px;
        font-size: 13.5px;
        color: #374151;
        background: #fff;
        box-shadow: none;
        outline: none;
        transition: border-color .18s, box-shadow .18s;
    }
    .rm-settings-field textarea.rm-settings-textarea {
        min-height: 84px;
        resize: vertical;
    }
    .rm-settings-field--full {
        grid-column: 1 / -1;
    }
    .rm-settings-field input:focus,
    .rm-settings-field select.rm-settings-select:focus,
    .rm-settings-field textarea.rm-settings-textarea:focus {
        border-color: #005f5a;
        box-shadow: 0 0 0 3px rgba(0,95,90,.09);
    }
    .rm-settings-color-row {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .rm-settings-color-input {
        width: 48px !important;
        min-height: 40px !important;
        padding: 4px !important;
        border: 1.5px solid #e5e7eb;
        border-radius: 8px;
        background: #fff;
        cursor: pointer;
    }
    .rm-settings-color-hex {
        flex: 1;
        min-width: 0;
    }
    .rm-settings-description-top { margin-top: 0; }

    .rm-settings-toggle-wrap { margin-bottom: 0; }
    .rm-toggle { display: inline-flex; align-items: center; cursor: pointer; gap: 10px; }
    .rm-toggle input { display: none; }
    .rm-toggle-slider {
        position: relative;
        width: 42px;
        height: 22px;
        background: #d1d5db;
        border-radius: 999px;
        transition: .2s;
    }
    .rm-toggle-slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 2px;
        top: 2px;
        background: #fff;
        border-radius: 50%;
        transition: .2s;
        box-shadow: 0 1px 2px rgba(0,0,0,.2);
    }
    .rm-toggle input:checked + .rm-toggle-slider { background: #005f5a; }
    .rm-toggle input:checked + .rm-toggle-slider:before { transform: translateX(20px); }
    .rm-toggle-label { font-weight: 600; color: #374151; font-size: 14px; }

    .rm-settings-inline-input {
        width: 100%;
        min-width: 180px;
        border: 1.5px solid #e5e7eb;
        border-radius: 8px;
        min-height: 36px;
        padding: 7px 10px;
        font-size: 13px;
    }
    .rm-settings-inline-input--sm {
        min-width: 110px;
        max-width: 140px;
    }
    .rm-shipping-presets-table th,
    .rm-shipping-presets-table td {
        vertical-align: middle;
    }
    .rm-settings-actions-row { margin: 14px 0 0; }

    .rm-settings-btn {
        border: none;
        border-radius: 8px;
        padding: 9px 16px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all .18s;
    }
    .rm-settings-btn--primary {
        background: #005f5a;
        color: #fff;
    }
    .rm-settings-btn--primary:hover { background: #007a73; }
    .rm-settings-btn--secondary {
        background: #fff;
        color: #005f5a;
        border: 1.5px solid #005f5a;
    }
    .rm-settings-btn--secondary:hover {
        background: #005f5a;
        color: #fff;
    }
    .rm-settings-btn--ghost {
        background: #fff;
        color: #ef4444;
        border: 1.5px solid #fecaca;
    }
    .rm-settings-btn--ghost:hover {
        background: #fef2f2;
        border-color: #ef4444;
    }

    .rm-settings-readonly-input {
        width: 100%;
        border: 1.5px solid #e5e7eb;
        border-radius: 8px;
        min-height: 40px;
        padding: 9px 12px;
        font-size: 13px;
        color: #4b5563;
        background: #f9fafb;
    }

    @media (max-width: 782px) {
        .rm-settings-card .rm-card-body { padding: 16px; }
        .rm-settings-grid { grid-template-columns: 1fr; }
        .rm-settings-inline-input,
        .rm-settings-inline-input--sm { min-width: 120px; max-width: 100%; width: 100%; }
        .rm-settings-btn--primary { width: 100%; }
        .rm-settings-actions-row { margin-top: 12px; }
    }
</style>
<script>
(function () {
    function syncColorPair(colorId, hexId) {
        var colorEl = document.getElementById(colorId);
        var hexEl = document.getElementById(hexId);
        if (!colorEl || !hexEl) return;
        colorEl.addEventListener('input', function () {
            hexEl.value = colorEl.value;
        });
        hexEl.addEventListener('input', function () {
            var v = hexEl.value.trim();
            if (/^#([A-Fa-f0-9]{6})$/.test(v)) {
                colorEl.value = v;
            }
        });
        hexEl.addEventListener('change', function () {
            var v = hexEl.value.trim();
            if (/^#([A-Fa-f0-9]{6})$/.test(v)) {
                colorEl.value = v.toLowerCase();
                hexEl.value = v.toLowerCase();
            } else {
                hexEl.value = colorEl.value;
            }
        });
    }
    <?php echo wp_json_encode( $color_field_ids ); ?>.forEach(function (colorId) {
        syncColorPair(colorId, colorId + '_hex');
    });

    var form = document.querySelector('form[action*="admin-post.php"]');
    if (form) {
        form.addEventListener('submit', function () {
            <?php echo wp_json_encode( $color_field_ids ); ?>.forEach(function (colorId) {
                var colorEl = document.getElementById(colorId);
                var hexEl = document.getElementById(colorId + '_hex');
                if (!colorEl || !hexEl) return;
                var v = hexEl.value.trim();
                if (/^#([A-Fa-f0-9]{6})$/.test(v)) {
                    colorEl.value = v.toLowerCase();
                }
            });
        });
    }
})();
</script>
