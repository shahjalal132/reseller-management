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
    <form id="rm-profile-form" class="rm-form" method="post" enctype="multipart/form-data">
        <div class="rm-grid rm-grid-2" style="padding-left: 25px; padding-bottom: 25px;">
            <div class="rm-field rm-field-full rm-profile-avatar-field">
                <span><?php esc_html_e( 'Profile photo', 'reseller-management' ); ?></span>
                <div class="rm-profile-avatar-row">
                    <?php echo get_avatar( $user_id, 96, '', '', [ 'class' => 'rm-profile-avatar-preview' ] ); ?>
                    <label class="rm-profile-avatar-upload">
                        <span class="screen-reader-text"><?php esc_html_e( 'Upload profile photo', 'reseller-management' ); ?></span>
                        <input type="file" name="avatar" accept="image/jpeg,image/png,image/gif,image/webp">
                    </label>
                </div>
                <p class="rm-profile-avatar-hint"><?php esc_html_e( 'JPG, PNG, GIF, or WebP. Leave empty to keep your current photo.', 'reseller-management' ); ?></p>
            </div>
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

            <div class="rm-form-actions">
            <button type="submit" class="rm-button"><?php esc_html_e( 'Save Profile', 'reseller-management' ); ?></button>
        </div>
        </div>
        
        <div class="rm-form-response" aria-live="polite"></div>
    </form>
</div>

<div class="rm-card">
    <h3><?php esc_html_e( 'Change Password', 'reseller-management' ); ?></h3>
    <form id="rm-password-form" class="rm-form">
        <div class="rm-grid rm-grid-2" style="padding-left: 25px; padding-bottom: 25px;">
            <label class="rm-field">
                <span><?php esc_html_e( 'New Password', 'reseller-management' ); ?></span>
                <input type="password" name="password" minlength="8" placeholder="Password" required>
            </label>
            <label class="rm-field">
                <span><?php esc_html_e( 'Confirm Password', 'reseller-management' ); ?></span>
                <input type="password" name="confirm_password" minlength="8" placeholder="Confirm Password" required>
            </label>

            <div class="rm-form-actions">
                <button type="submit" class="rm-button"><?php esc_html_e( 'Update Password', 'reseller-management' ); ?></button>
            </div>
        </div>
       
        <div class="rm-form-response" aria-live="polite"></div>
    </form>
</div>
