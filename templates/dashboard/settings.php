<?php
/**
 * Settings tab.
 *
 * @package reseller-management
 */

defined( 'ABSPATH' ) || exit;

$user_id = get_current_user_id();
$user    = get_userdata( $user_id );
?>
<div class="rm-card">
    <h3><?php esc_html_e( 'Profile Settings', 'reseller-management' ); ?></h3>
    <form id="rm-profile-form" class="rm-form">
        <div class="rm-grid rm-grid-2">
            <label class="rm-field">
                <span><?php esc_html_e( 'Name', 'reseller-management' ); ?></span>
                <input type="text" name="display_name" value="<?php echo esc_attr( $user ? $user->display_name : '' ); ?>" required>
            </label>
            <label class="rm-field">
                <span><?php esc_html_e( 'Phone', 'reseller-management' ); ?></span>
                <input type="text" name="phone" value="<?php echo esc_attr( (string) get_user_meta( $user_id, '_reseller_phone', true ) ); ?>" required>
            </label>
            <label class="rm-field">
                <span><?php esc_html_e( 'Business Name', 'reseller-management' ); ?></span>
                <input type="text" name="business_name" value="<?php echo esc_attr( (string) get_user_meta( $user_id, '_reseller_business_name', true ) ); ?>" required>
            </label>
            <label class="rm-field">
                <span><?php esc_html_e( 'Facebook URL', 'reseller-management' ); ?></span>
                <input type="url" name="facebook_url" value="<?php echo esc_attr( (string) get_user_meta( $user_id, '_reseller_fb_url', true ) ); ?>">
            </label>
            <label class="rm-field rm-field-full">
                <span><?php esc_html_e( 'Website URL', 'reseller-management' ); ?></span>
                <input type="url" name="website_url" value="<?php echo esc_attr( (string) get_user_meta( $user_id, '_reseller_web_url', true ) ); ?>">
            </label>
        </div>
        <div class="rm-form-actions">
            <button type="submit" class="rm-button"><?php esc_html_e( 'Save Profile', 'reseller-management' ); ?></button>
        </div>
        <div class="rm-form-response" aria-live="polite"></div>
    </form>
</div>

<div class="rm-card">
    <h3><?php esc_html_e( 'Change Password', 'reseller-management' ); ?></h3>
    <form id="rm-password-form" class="rm-form">
        <div class="rm-grid rm-grid-2">
            <label class="rm-field">
                <span><?php esc_html_e( 'New Password', 'reseller-management' ); ?></span>
                <input type="password" name="password" minlength="8" required>
            </label>
            <label class="rm-field">
                <span><?php esc_html_e( 'Confirm Password', 'reseller-management' ); ?></span>
                <input type="password" name="confirm_password" minlength="8" required>
            </label>
        </div>
        <div class="rm-form-actions">
            <button type="submit" class="rm-button"><?php esc_html_e( 'Update Password', 'reseller-management' ); ?></button>
        </div>
        <div class="rm-form-response" aria-live="polite"></div>
    </form>
</div>
