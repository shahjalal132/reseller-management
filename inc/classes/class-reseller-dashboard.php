<?php
/**
 * Frontend dashboard shell and tab rendering.
 */

namespace BOILERPLATE\Inc;

use BOILERPLATE\Inc\Traits\Singleton;

class Reseller_Dashboard {
    use Singleton;

    /**
     * Register hooks.
     */
    protected function __construct() {
        add_shortcode( 'reseller_dashboard', [ $this, 'render_dashboard_shortcode' ] );
        add_filter( 'template_include', [ $this, 'maybe_use_dashboard_template' ] );
        add_action( 'wp_ajax_reseller_update_profile', [ $this, 'handle_profile_update' ] );
        add_action( 'wp_ajax_reseller_change_password', [ $this, 'handle_password_change' ] );
    }

    /**
     * Shortcode fallback content.
     *
     * @return string
     */
    public function render_dashboard_shortcode() {
        if ( ! is_user_logged_in() ) {
            return '<div class="rm-dashboard-message">' . esc_html__( 'Please log in to access the reseller dashboard.', 'reseller-management' ) . '</div>';
        }

        if ( ! Reseller_Helper::is_reseller( get_current_user_id() ) ) {
            return '<div class="rm-dashboard-message">' . esc_html__( 'This dashboard is available only for reseller accounts.', 'reseller-management' ) . '</div>';
        }

        if ( ! Reseller_Helper::is_reseller_approved( get_current_user_id() ) ) {
            return '<div class="rm-dashboard-message">' . esc_html__( 'Your reseller account is not approved yet.', 'reseller-management' ) . '</div>';
        }

        return '<div class="rm-dashboard-shortcode-placeholder"></div>';
    }

    /**
     * Swap the page template when the dashboard shortcode is present.
     *
     * @param string $template Current template path.
     *
     * @return string
     */
    public function maybe_use_dashboard_template( $template ) {
        if ( ! is_singular() ) {
            return $template;
        }

        global $post;

        if ( ! $post instanceof \WP_Post || ! has_shortcode( (string) $post->post_content, 'reseller_dashboard' ) ) {
            return $template;
        }

        return PLUGIN_BASE_PATH . '/templates/dashboard-layout.php';
    }

    /**
     * Render the dashboard app.
     *
     * @return void
     */
    public function render_dashboard_layout() {
        $user_id = get_current_user_id();
        $user    = get_userdata( $user_id );
        $tabs    = Reseller_Helper::get_dashboard_tabs();
        $tab     = sanitize_key( wp_unslash( $_GET['tab'] ?? 'dashboard' ) );

        if ( empty( $tabs[ $tab ] ) ) {
            $tab = 'dashboard';
        }
        ?>
        <div class="rm-dashboard-app">
            <aside class="rm-dashboard-sidebar">
                <div class="rm-sidebar-brand">
                    <div class="rm-logo">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                        <span><?php echo get_bloginfo( 'name' ); ?></span>
                    </div>
                    <div class="rm-reseller-badge">
                        <span class="rm-badge-label"><?php echo $user->display_name; ?></span>
                        <span class="rm-badge-tag"><?php esc_html_e( 'Reseller', 'reseller-management' ); ?></span>
                    </div>
                </div>

                <nav class="rm-sidebar-nav">
                    <?php
                    $icons = [
                        'dashboard' => 'dashboard',
                        'orders'    => 'cart',
                        'products'  => 'grid',
                        'account'   => 'user',
                        'settings'  => 'chart',
                        'customers' => 'users',
                    ];
                    foreach ( $tabs as $tab_key => $label ) :
                        $icon = $icons[ $tab_key ] ?? 'database';
                        ?>
                        <a class="rm-nav-link <?php echo $tab_key === $tab ? 'is-active' : ''; ?>" href="<?php echo esc_url( $this->get_dashboard_tab_url( $tab_key ) ); ?>">
                            <span class="rm-nav-icon rm-icon-<?php echo esc_attr( $icon ); ?>"></span>
                            <span class="rm-nav-label"><?php echo esc_html( $label ); ?></span>
                        </a>
                    <?php endforeach; ?>
                </nav>

                <div class="rm-sidebar-footer">
                    <a href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>" class="rm-logout-link">
                        <span class="rm-nav-icon rm-icon-logout"></span>
                        <span><?php esc_html_e( 'Logout', 'reseller-management' ); ?></span>
                    </a>
                </div>
            </aside>

            <main class="rm-dashboard-content">
                <header class="rm-dashboard-header">
                    <div class="rm-header-left">
                        <button class="rm-sidebar-toggle">
                            <span class="dashicons dashicons-menu"></span>
                        </button>
                    </div>
                    <div class="rm-header-right">
                        <div class="rm-user-profile-header">
                            <?php echo get_avatar( $user_id, 32 ); ?>
                            <span class="rm-user-name-header"><?php echo esc_html( $user->display_name ); ?></span>
                        </div>
                    </div>
                </header>

                <div class="rm-dashboard-body-inner">
                    <div class="rm-balance-check-container">
                        <button class="rm-button rm-button-balance-check">
                            <?php esc_html_e( 'Balance Check', 'reseller-management' ); ?>
                        </button>
                    </div>

                    <section class="rm-dashboard-panel">
                        <?php $this->render_tab_content( $tab ); ?>
                    </section>
                </div>
            </main>
        </div>
        <?php
    }

    /**
     * Render a tab partial with safe fallback.
     *
     * @param string $tab Current tab slug.
     *
     * @return void
     */
    protected function render_tab_content( $tab ) {
        $template = PLUGIN_BASE_PATH . '/templates/dashboard/' . $tab . '.php';

        if ( file_exists( $template ) ) {
            include $template;
            return;
        }

        echo '<p>' . esc_html__( 'Dashboard section is not available yet.', 'reseller-management' ) . '</p>';
    }

    /**
     * Generate a tab URL for the current page.
     *
     * @param string $tab Tab slug.
     *
     * @return string
     */
    public function get_dashboard_tab_url( $tab ) {
        global $post;

        $url = $post instanceof \WP_Post ? get_permalink( $post ) : home_url( '/' );

        return add_query_arg(
            [
                'tab' => $tab,
            ],
            $url
        );
    }

    /**
     * Handle profile updates from the settings tab.
     *
     * @return void
     */
    public function handle_profile_update() {
        check_ajax_referer( 'rm_public_nonce', 'nonce' );

        if ( ! is_user_logged_in() || ! Reseller_Helper::is_reseller( get_current_user_id() ) ) {
            wp_send_json_error( __( 'You are not allowed to update this profile.', 'reseller-management' ), 403 );
        }

        $user_id       = get_current_user_id();
        $display_name  = sanitize_text_field( wp_unslash( $_POST['display_name'] ?? '' ) );
        $phone         = sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) );
        $business_name = sanitize_text_field( wp_unslash( $_POST['business_name'] ?? '' ) );
        $facebook_url  = esc_url_raw( wp_unslash( $_POST['facebook_url'] ?? '' ) );
        $website_url   = esc_url_raw( wp_unslash( $_POST['website_url'] ?? '' ) );

        if ( empty( $display_name ) || empty( $phone ) || empty( $business_name ) ) {
            wp_send_json_error( __( 'Name, phone, and business name are required.', 'reseller-management' ), 422 );
        }

        wp_update_user(
            [
                'ID'           => $user_id,
                'display_name' => $display_name,
                'first_name'   => $display_name,
            ]
        );

        update_user_meta( $user_id, '_reseller_phone', $phone );
        update_user_meta( $user_id, '_reseller_business_name', $business_name );
        update_user_meta( $user_id, '_reseller_fb_url', $facebook_url );
        update_user_meta( $user_id, '_reseller_web_url', $website_url );

        wp_send_json_success( __( 'Profile updated successfully.', 'reseller-management' ) );
    }

    /**
     * Handle reseller password changes.
     *
     * @return void
     */
    public function handle_password_change() {
        check_ajax_referer( 'rm_public_nonce', 'nonce' );

        if ( ! is_user_logged_in() || ! Reseller_Helper::is_reseller( get_current_user_id() ) ) {
            wp_send_json_error( __( 'You are not allowed to change this password.', 'reseller-management' ), 403 );
        }

        $password         = (string) wp_unslash( $_POST['password'] ?? '' );
        $confirm_password = (string) wp_unslash( $_POST['confirm_password'] ?? '' );

        if ( strlen( $password ) < 8 ) {
            wp_send_json_error( __( 'Password must be at least 8 characters long.', 'reseller-management' ), 422 );
        }

        if ( $password !== $confirm_password ) {
            wp_send_json_error( __( 'Password confirmation does not match.', 'reseller-management' ), 422 );
        }

        wp_set_password( $password, get_current_user_id() );
        wp_send_json_success( __( 'Password updated successfully. Please log in again.', 'reseller-management' ) );
    }
}
