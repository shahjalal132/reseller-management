<?php
/**
 * Frontend reseller registration flows.
 */

namespace BOILERPLATE\Inc;

use BOILERPLATE\Inc\Traits\Singleton;

class Reseller_Registration {
    use Singleton;

    /**
     * Register hooks.
     */
    protected function __construct() {
        add_shortcode( 'reseller_registration', [ $this, 'render_registration_shortcode' ] );
        add_action( 'wp_ajax_nopriv_reseller_register_user', [ $this, 'handle_registration' ] );
    }

    /**
     * Render the reseller registration form.
     *
     * @return string
     */
    public function render_registration_shortcode() {
        ob_start();
        ?>
        <div class="rm-auth-card">
            <div class="rm-auth-header">
                <h2><?php esc_html_e( 'Become a Reseller', 'reseller-management' ); ?></h2>
                <p><?php esc_html_e( 'Submit your business details for admin approval.', 'reseller-management' ); ?></p>
            </div>

            <form id="rm-registration-form" class="rm-form" enctype="multipart/form-data">
                <div class="rm-grid rm-grid-2">
                    <label class="rm-field">
                        <span><?php esc_html_e( 'Name', 'reseller-management' ); ?></span>
                        <input type="text" name="name" required>
                    </label>

                    <label class="rm-field">
                        <span><?php esc_html_e( 'Email', 'reseller-management' ); ?></span>
                        <input type="email" name="email" required>
                    </label>

                    <label class="rm-field">
                        <span><?php esc_html_e( 'Phone', 'reseller-management' ); ?></span>
                        <input type="text" name="phone" required>
                    </label>

                    <label class="rm-field">
                        <span><?php esc_html_e( 'Business Name', 'reseller-management' ); ?></span>
                        <input type="text" name="business_name" required>
                    </label>

                    <label class="rm-field">
                        <span><?php esc_html_e( 'Facebook Page URL', 'reseller-management' ); ?></span>
                        <input type="url" name="facebook_url">
                    </label>

                    <label class="rm-field">
                        <span><?php esc_html_e( 'Website URL', 'reseller-management' ); ?></span>
                        <input type="url" name="website_url">
                    </label>

                    <label class="rm-field">
                        <span><?php esc_html_e( 'Password', 'reseller-management' ); ?></span>
                        <input type="password" name="password" required minlength="8">
                    </label>

                    <label class="rm-field">
                        <span><?php esc_html_e( 'Confirm Password', 'reseller-management' ); ?></span>
                        <input type="password" name="confirm_password" required minlength="8">
                    </label>

                    <label class="rm-field">
                        <span><?php esc_html_e( 'NID Front Image', 'reseller-management' ); ?></span>
                        <input type="file" name="nid_front" accept="image/*" required>
                    </label>

                    <label class="rm-field">
                        <span><?php esc_html_e( 'NID Back Image', 'reseller-management' ); ?></span>
                        <input type="file" name="nid_back" accept="image/*" required>
                    </label>
                </div>

                <div class="rm-form-actions">
                    <button type="submit" class="rm-button">
                        <?php esc_html_e( 'Submit Application', 'reseller-management' ); ?>
                    </button>
                </div>

                <div class="rm-form-response" aria-live="polite"></div>
            </form>
        </div>
        <?php

        return (string) ob_get_clean();
    }

    /**
     * Handle frontend reseller registration.
     *
     * @return void
     */
    public function handle_registration() {
        check_ajax_referer( 'rm_public_nonce', 'nonce' );

        $name             = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
        $email            = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
        $phone            = sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) );
        $business_name    = sanitize_text_field( wp_unslash( $_POST['business_name'] ?? '' ) );
        $facebook_url     = esc_url_raw( wp_unslash( $_POST['facebook_url'] ?? '' ) );
        $website_url      = esc_url_raw( wp_unslash( $_POST['website_url'] ?? '' ) );
        $password         = (string) wp_unslash( $_POST['password'] ?? '' );
        $confirm_password = (string) wp_unslash( $_POST['confirm_password'] ?? '' );

        if ( empty( $name ) || empty( $email ) || empty( $phone ) || empty( $business_name ) || empty( $password ) ) {
            wp_send_json_error( __( 'Please fill in all required fields.', 'reseller-management' ), 422 );
        }

        if ( ! is_email( $email ) ) {
            wp_send_json_error( __( 'Please provide a valid email address.', 'reseller-management' ), 422 );
        }

        if ( email_exists( $email ) ) {
            wp_send_json_error( __( 'An account already exists with this email.', 'reseller-management' ), 409 );
        }

        if ( $password !== $confirm_password ) {
            wp_send_json_error( __( 'Password confirmation does not match.', 'reseller-management' ), 422 );
        }

        if ( strlen( $password ) < 8 ) {
            wp_send_json_error( __( 'Password must be at least 8 characters long.', 'reseller-management' ), 422 );
        }

        if ( empty( $_FILES['nid_front']['name'] ) || empty( $_FILES['nid_back']['name'] ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
            wp_send_json_error( __( 'Both NID images are required.', 'reseller-management' ), 422 );
        }

        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';

        $front_id = media_handle_upload( 'nid_front', 0 );
        if ( is_wp_error( $front_id ) ) {
            wp_send_json_error( $front_id->get_error_message(), 422 );
        }

        $back_id = media_handle_upload( 'nid_back', 0 );
        if ( is_wp_error( $back_id ) ) {
            wp_delete_attachment( $front_id, true );
            wp_send_json_error( $back_id->get_error_message(), 422 );
        }

        $email_parts = explode( '@', $email );
        $username    = sanitize_user( $email_parts[0], true );
        $username = $this->ensure_unique_username( $username, $email );

        $user_id = wp_create_user( $username, $password, $email );

        if ( is_wp_error( $user_id ) ) {
            wp_delete_attachment( $front_id, true );
            wp_delete_attachment( $back_id, true );
            wp_send_json_error( $user_id->get_error_message(), 500 );
        }

        wp_update_user(
            [
                'ID'           => $user_id,
                'display_name' => $name,
                'first_name'   => $name,
                'role'         => Reseller_Helper::get_role_slug(),
            ]
        );

        update_user_meta( $user_id, '_reseller_status', 'pending' );
        update_user_meta( $user_id, '_reseller_phone', $phone );
        update_user_meta( $user_id, '_reseller_business_name', $business_name );
        update_user_meta( $user_id, '_reseller_fb_url', $facebook_url );
        update_user_meta( $user_id, '_reseller_web_url', $website_url );
        update_user_meta( $user_id, '_reseller_nid_front_id', $front_id );
        update_user_meta( $user_id, '_reseller_nid_back_id', $back_id );

        wp_send_json_success(
            __( 'Registration submitted successfully. Your account is pending admin approval.', 'reseller-management' )
        );
    }

    /**
     * Generate a unique username from a preferred base.
     *
     * @param string $preferred Preferred username.
     * @param string $email     User email.
     *
     * @return string
     */
    protected function ensure_unique_username( $preferred, $email ) {
        $base_username = $preferred ? $preferred : sanitize_user( str_replace( [ '@', '.' ], '_', $email ), true );
        $username      = $base_username;
        $counter       = 1;

        while ( username_exists( $username ) ) {
            $username = $base_username . $counter;
            $counter++;
        }

        return $username;
    }
}
