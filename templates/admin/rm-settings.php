<?php
/**
 * Reseller Hub – Settings page.
 *
 * @package reseller-management
 */
defined( 'ABSPATH' ) || exit;

$rm_settings = $rm_settings ?? get_option( 'rm_settings', [] );

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
</style>
