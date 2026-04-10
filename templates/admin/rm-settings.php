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
$notice_class = 'rm-notice-success';

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

    <div class="rm-section-card">
        <div class="rm-card-header">
            <h3 class="rm-card-title"><?php esc_html_e( 'COD Settings', 'reseller-management' ); ?></h3>
        </div>
        <div class="rm-card-body">
            <div class="rm-form-group">
                <label class="rm-toggle">
                    <input type="checkbox" name="cod_enabled" value="yes" <?php checked( 'yes', $rm_settings['cod_enabled'] ?? 'no' ); ?>>
                    <span class="rm-toggle-slider"></span>
                    <span class="rm-toggle-label"><?php esc_html_e( 'Enable COD Option', 'reseller-management' ); ?></span>
                </label>
            </div>
            
            <div class="rm-grid rm-grid-1 mt-20">
                <div class="rm-field">
                    <span><?php esc_html_e( 'COD Percentage (%)', 'reseller-management' ); ?></span>
                    <input type="number" step="0.01" name="cod_input1" value="<?php echo esc_attr( $rm_settings['cod_input1'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'Enter percentage (e.g. 5)', 'reseller-management' ); ?>">
                </div>
            </div>
        </div>
    </div>

    <div class="rm-section-card mt-20">
        <div class="rm-card-header">
            <h3 class="rm-card-title"><?php esc_html_e( 'Packaging Cost Settings', 'reseller-management' ); ?></h3>
        </div>
        <div class="rm-card-body">
            <div class="rm-form-group">
                <label class="rm-toggle">
                    <input type="checkbox" name="packaging_cost_enabled" value="yes" <?php checked( 'yes', $rm_settings['packaging_cost_enabled'] ?? 'no' ); ?>>
                    <span class="rm-toggle-slider"></span>
                    <span class="rm-toggle-label"><?php esc_html_e( 'Enable Packaging Cost Option', 'reseller-management' ); ?></span>
                </label>
            </div>
            
            <div class="rm-grid rm-grid-1 mt-20">
                <div class="rm-field">
                    <span><?php esc_html_e( 'Packaging Cost', 'reseller-management' ); ?></span>
                    <input type="text" name="packaging_cost_input1" value="<?php echo esc_attr( $rm_settings['packaging_cost_input1'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'Enter value', 'reseller-management' ); ?>">
                </div>
            </div>
        </div>
    </div>

    <div class="rm-section-card mt-20">
        <div class="rm-card-header">
            <h3 class="rm-card-title"><?php esc_html_e( 'Withdrawals', 'reseller-management' ); ?></h3>
        </div>
        <div class="rm-card-body">
            <div class="rm-grid rm-grid-1 mt-20">
                <div class="rm-field">
                    <span><?php esc_html_e( 'Minimum balance to retain', 'reseller-management' ); ?></span>
                    <input type="number" step="0.01" min="0" name="minimum_balance" value="<?php echo esc_attr( isset( $rm_settings['minimum_balance'] ) ? (string) $rm_settings['minimum_balance'] : '0' ); ?>" placeholder="<?php esc_attr_e( '0.00', 'reseller-management' ); ?>">
                    <p class="description"><?php esc_html_e( 'Resellers cannot withdraw an amount that would leave their balance below this value. Use 0 for no minimum.', 'reseller-management' ); ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="rm-section-card mt-20">
        <div class="rm-card-header">
            <h3 class="rm-card-title"><?php esc_html_e( 'Shipping charge presets', 'reseller-management' ); ?></h3>
        </div>
        <div class="rm-card-body">
            <p class="description" style="margin-top:0;"><?php esc_html_e( 'Resellers can pick these on Add New Order to fill the shipping charge (they may still edit the amount). Rows without a title are ignored when saving.', 'reseller-management' ); ?></p>
            <div class="rm-table-responsive">
                <table id="rm-shipping-presets-table" class="widefat striped rm-shipping-presets-table" style="max-width: 720px; margin-top: 12px;">
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
                                <input type="text" name="rm_shipping_preset_title[]" class="regular-text" value="<?php echo esc_attr( $preset['title'] ); ?>">
                            </td>
                            <td>
                                <input type="number" name="rm_shipping_preset_charge[]" step="0.01" min="0" style="width: 120px;" value="<?php echo esc_attr( (string) $preset['charge'] ); ?>">
                            </td>
                            <td>
                                <button type="button" class="button rm-shipping-preset-remove"><?php esc_html_e( 'Remove', 'reseller-management' ); ?></button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
            <p style="margin-top: 12px;">
                <button type="button" class="button" id="rm-shipping-preset-add"><?php esc_html_e( 'Add preset', 'reseller-management' ); ?></button>
            </p>
            <template id="rm-shipping-preset-row-template">
                <tr class="rm-shipping-preset-row">
                    <td>
                        <input type="text" name="rm_shipping_preset_title[]" class="regular-text" value="">
                    </td>
                    <td>
                        <input type="number" name="rm_shipping_preset_charge[]" step="0.01" min="0" style="width: 120px;" value="">
                    </td>
                    <td>
                        <button type="button" class="button rm-shipping-preset-remove"><?php esc_html_e( 'Remove', 'reseller-management' ); ?></button>
                    </td>
                </tr>
            </template>
        </div>
    </div>

    <div class="rm-section-card mt-20">
        <div class="rm-card-header">
            <h3 class="rm-card-title"><?php esc_html_e( 'Steadfast Webhook Integration', 'reseller-management' ); ?></h3>
        </div>
        <div class="rm-card-body">
            <div class="rm-form-group">
                <div class="rm-field">
                    <span><?php esc_html_e( 'Callback URL', 'reseller-management' ); ?></span>
                    <input type="text" value="<?php echo esc_url( get_rest_url( null, 'reseller/v1/steadfast-webhook' ) ); ?>" readonly onclick="this.select();" style="width: 100%; border: 1px solid #ddd; padding: 8px; border-radius: 4px; background: #f9f9f9;">
                    <p class="description"><?php esc_html_e( 'Copy this URL and paste it into your Steadfast Webhook configuration.', 'reseller-management' ); ?></p>
                </div>
            </div>
            
            <div class="rm-grid rm-grid-1 mt-20">
                <div class="rm-field">
                    <span><?php esc_html_e( 'Secret Token', 'reseller-management' ); ?></span>
                    <input type="text" name="steadfast_secret_token" value="<?php echo esc_attr( $rm_settings['steadfast_secret_token'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'Enter secret token from Steadfast', 'reseller-management' ); ?>" style="width: 100%; border: 1px solid #ddd; padding: 8px; border-radius: 4px;">
                </div>
            </div>
        </div>
    </div>

    <div class="rm-form-actions mt-20">
        <button type="submit" class="rm-button">
            <?php esc_html_e( 'Save Settings', 'reseller-management' ); ?>
        </button>
    </div>
</form>

<style>
    .mt-20 { margin-top: 20px; }
    .rm-section-card { background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden; }
    .rm-card-header { padding: 15px 20px; border-bottom: 1px solid #eee; background: #fafafa; }
    .rm-card-title { margin: 0; font-size: 16px; font-weight: 600; color: #333; }
    .rm-card-body { padding: 20px; }
    .rm-toggle { display: flex; align-items: center; cursor: pointer; gap: 10px; }
    .rm-toggle input { display: none; }
    .rm-toggle-slider { position: relative; width: 40px; height: 20px; background: #ccc; border-radius: 20px; transition: .3s; }
    .rm-toggle-slider:before { position: absolute; content: ""; height: 16px; width: 16px; left: 2px; bottom: 2px; background: white; border-radius: 50%; transition: .3s; }
    .rm-toggle input:checked + .rm-toggle-slider { background: #2271b1; }
    .rm-toggle input:checked + .rm-toggle-slider:before { transform: translateX(20px); }
    .rm-toggle-label { font-weight: 500; color: #444; }
    
    .rm-notice { padding: 10px 15px; border-left: 4px solid #46b450; background: #fff; box-shadow: 0 1px 1px rgba(0,0,0,0.04); margin-bottom: 20px; }
    .rm-notice p { margin: 0.5em 0; padding: 2px; }
    
    .rm-button { background: #2271b1; color: #fff; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-weight: 600; transition: background 0.2s; }
    .rm-button:hover { background: #135e96; }

    @media (max-width: 782px) {
        .rm-card-body { padding: 15px; }
        .rm-grid { grid-template-columns: 1fr !important; }
        .rm-field input { width: 100% !important; }
        .rm-shipping-presets-table input { width: 100% !important; min-width: 100px; }
        .rm-button { width: 100%; }
        .rm-page-header { flex-direction: column; align-items: flex-start; }
    }
</style>
