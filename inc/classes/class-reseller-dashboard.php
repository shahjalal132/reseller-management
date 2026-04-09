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
        add_filter( 'get_avatar_url', [ $this, 'filter_reseller_avatar_url' ], 10, 3 );
        add_action( 'wp_ajax_reseller_update_profile', [ $this, 'handle_profile_update' ] );
        add_action( 'wp_ajax_reseller_change_password', [ $this, 'handle_password_change' ] );
        add_action( 'wp_ajax_reseller_get_order_stats', [ $this, 'handle_get_order_stats' ] );
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

        $subtab     = sanitize_key( wp_unslash( $_GET['subtab'] ?? '' ) );
        $page_title = $tabs[ $tab ]['label'] ?? '';

        if ( ! empty( $subtab ) && isset( $tabs[ $tab ]['children'][ $subtab ] ) ) {
            $page_title = $tabs[ $tab ]['children'][ $subtab ];
        } elseif ( empty( $subtab ) && ! empty( $tabs[ $tab ]['children'] ) ) {
            // Default to the first child if children exist but no subtab is specified
            $first_child = reset( $tabs[ $tab ]['children'] );
            $page_title  = $first_child;
        }
        ?>
        <div class="rm-dashboard-app">
            <div class="rm-sidebar-overlay"></div>
            <aside class="rm-dashboard-sidebar">
                <div class="rm-sidebar-brand">
                    <div class="rm-logo">
                        <?php
                        $custom_logo_id = (int) get_theme_mod( 'custom_logo' );
                        if ( $custom_logo_id ) :
                            ?>
                            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="rm-custom-logo-link">
                                <?php
                                echo wp_get_attachment_image(
                                    $custom_logo_id,
                                    'medium',
                                    false,
                                    [
                                        'class'   => 'rm-site-logo-img',
                                        'alt'     => esc_attr( get_bloginfo( 'name', 'display' ) ),
                                        'loading' => 'lazy',
                                    ]
                                );
                                ?>
                            </a>
                        <?php else : ?>
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                            <span><?php echo esc_html( get_bloginfo( 'name', 'display' ) ); ?></span>
                        <?php endif; ?>
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
                    foreach ( $tabs as $tab_key => $tab_data ) : 
                        $label        = $tab_data['label'] ?? '';
                        $is_active    = ( $tab_key === $tab );
                        $has_children = ! empty( $tab_data['children'] );
                        ?>
                        <div class="rm-nav-item-wrapper <?php echo $is_active ? 'is-active' : ''; ?> <?php echo $has_children ? 'has-children' : ''; ?> <?php echo $is_active ? 'is-expanded' : ''; ?>">
                            <a class="rm-nav-link <?php echo $is_active ? 'is-active' : ''; ?>" href="<?php echo esc_url( $this->get_dashboard_tab_url( $tab_key ) ); ?>">
                                <span class="rm-nav-main-info">
                                    <span class="rm-nav-icon"><?php echo $this->get_svg_icon( $tab_key ); ?></span>
                                    <span class="rm-nav-label"><?php echo esc_html( $label ); ?></span>
                                </span>
                                <?php if ( $has_children ) : ?>
                                    <span class="rm-nav-chevron"><?php echo $this->get_svg_icon( 'chevron' ); ?></span>
                                <?php endif; ?>
                            </a>
                            <?php if ( $has_children ) : ?>
                                <div class="rm-nav-submenu">
                                    <?php foreach ( $tab_data['children'] as $subtab_key => $subtab_label ) : 
                                        $is_subtab_active = ( isset( $_GET['subtab'] ) && $_GET['subtab'] === $subtab_key ) || ( ! isset( $_GET['subtab'] ) && 'all' === $subtab_key );
                                        ?>
                                        <a class="rm-subnav-link <?php echo $is_subtab_active ? 'is-active' : ''; ?>" href="<?php echo esc_url( $this->get_dashboard_tab_url( $tab_key, $subtab_key ) ); ?>">
                                            <span class="rm-subnav-label"><?php echo esc_html( $subtab_label ); ?></span>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </nav>

                <div class="rm-sidebar-footer">
                    <a href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>" class="rm-logout-link">
                        <span class="rm-nav-icon"><?php echo $this->get_svg_icon( 'logout' ); ?></span>
                        <span><?php esc_html_e( 'Logout', 'reseller-management' ); ?></span>
                    </a>
                </div>
            </aside>

            <main class="rm-dashboard-content">
                <header class="rm-dashboard-header">
                    <div class="rm-header-left">
                        <button class="rm-sidebar-toggle" id="rm-sidebar-toggle-btn" aria-label="<?php esc_attr_e( 'Toggle Menu', 'reseller-management' ); ?>">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="3" y1="12" x2="21" y2="12"></line>
                                <line x1="3" y1="6" x2="21" y2="6"></line>
                                <line x1="3" y1="18" x2="21" y2="18"></line>
                            </svg>
                        </button>
                        <h2 class="rm-page-title"><?php echo esc_html( $page_title ); ?></h2>
                    </div>
                    <div class="rm-header-right">
                        <div class="rm-user-profile-header">
                            <?php echo get_avatar( $user_id, 32 ); ?>
                            <span class="rm-user-name-header"><?php echo esc_html( $user->_reseller_business_name ); ?></span>
                        </div>
                    </div>
                </header>

                <div class="rm-dashboard-body-inner">
                    <?php if ( 'dashboard' === $tab ) :
                        $current_balance   = \BOILERPLATE\Inc\Reseller_Helper::get_current_balance( $user_id );
                        $payment_methods   = \BOILERPLATE\Inc\Reseller_Helper::get_payment_methods( $user_id );
                        $min_balance_reserve = \BOILERPLATE\Inc\Reseller_Helper::get_minimum_balance_reserve();
                        $max_withdrawable    = \BOILERPLATE\Inc\Reseller_Helper::get_max_withdrawable_amount( $current_balance );
                    ?>
                    <div class="rm-balance-check-container" style="display: flex; flex-direction: column; align-items: center; text-align: center; margin-bottom: 24px;">
                        <button class="rm-button rm-button-balance-check" id="rm-btn-balance-check" style="margin-bottom: 10px;">
                            <?php esc_html_e( 'Balance Check', 'reseller-management' ); ?>
                        </button>
                        
                        <div class="rm-balance-display-wrap" id="rm-balance-display" style="display: none; background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); border: 1px solid #e8edf3; text-align: center;">
                            <div style="font-size: 0.85rem; color: #64748b; font-weight: 600; text-transform: uppercase; margin-bottom: 4px;"><?php esc_html_e( 'Available Balance', 'reseller-management' ); ?></div>
                            <div class="rm-balance-amount" style="font-size: 28px; font-weight: 800; color: #0f172a; margin-bottom: 16px;">
                                ৳ <?php echo esc_html( number_format( $current_balance, 2 ) ); ?>
                            </div>
                            <?php if ( $min_balance_reserve > 0 ) : ?>
                                <p style="margin: 0 0 12px; font-size: 0.8rem; color: #64748b; max-width: 320px; line-height: 1.45;">
                                    <?php
                                    printf(
                                        /* translators: %s: formatted minimum balance */
                                        esc_html__( 'Minimum balance you must keep: %s', 'reseller-management' ),
                                        esc_html( '৳ ' . number_format( $min_balance_reserve, 2 ) )
                                    );
                                    ?>
                                </p>
                            <?php endif; ?>
                            <button type="button" class="rm-button rm-button-withdraw-request" id="rm-btn-open-withdraw-modal" style="background: linear-gradient(135deg, #10b981, #059669); color: #fff; border: none;" <?php disabled( $max_withdrawable <= 0 ); ?>>
                                <?php esc_html_e( 'Request Withdrawal', 'reseller-management' ); ?>
                            </button>
                            <?php if ( $max_withdrawable <= 0 && $current_balance > 0 ) : ?>
                                <p style="margin: 10px 0 0; font-size: 0.8rem; color: #b45309; max-width: 320px;"><?php esc_html_e( 'Your balance is at or below the required minimum. You cannot submit a withdrawal until your available balance is above the minimum.', 'reseller-management' ); ?></p>
                            <?php elseif ( $max_withdrawable <= 0 && $current_balance <= 0 ) : ?>
                                <p style="margin: 10px 0 0; font-size: 0.8rem; color: #64748b;"><?php esc_html_e( 'No balance available to withdraw.', 'reseller-management' ); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Withdraw Modal -->
                    <div id="rm-withdraw-modal" class="rm-modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.6); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
                        <div class="rm-modal-content" style="background: #fff; padding: 32px; border-radius: 16px; width: 90%; max-width: 420px; position: relative; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
                            <button class="rm-modal-close" id="rm-btn-close-withdraw-modal" style="position: absolute; top: 16px; right: 16px; border: none; background: #f1f5f9; color: #64748b; font-size: 18px; cursor: pointer; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.2s;">&times;</button>
                            <h3 style="margin-top: 0; margin-bottom: 24px; color: #0f172a; font-size: 1.3rem; font-weight: 800;"><?php esc_html_e( 'Withdrawal Request', 'reseller-management' ); ?></h3>
                            
                            <form id="rm-form-withdraw" class="rm-form row">
                                <div class="col-12" style="margin-bottom: 16px;">
                                    <label class="rm-label" style="font-weight: 700; color: #475569; display: block; margin-bottom: 6px;"><?php esc_html_e( 'Amount', 'reseller-management' ); ?> (৳)</label>
                                    <input type="number" name="amount" class="rm-input" id="rm-withdraw-amount-input" min="0.01" step="0.01" max="<?php echo esc_attr( $max_withdrawable ); ?>"<?php echo $max_withdrawable <= 0 ? ' disabled' : ''; ?><?php echo $max_withdrawable > 0 ? ' required' : ''; ?> placeholder="0.00" style="width: 100%; padding: 10px 14px; border: 1.5px solid #e2e8f0; border-radius: 8px; font-weight: 600;">
                                    <?php if ( $max_withdrawable > 0 ) : ?>
                                        <p style="margin: 8px 0 0; font-size: 0.75rem; color: #64748b;">
                                            <?php
                                            printf(
                                                /* translators: %s: maximum withdrawable amount (৳) */
                                                esc_html__( 'Maximum withdrawable: %s', 'reseller-management' ),
                                                esc_html( '৳ ' . number_format( $max_withdrawable, 2 ) )
                                            );
                                            ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                                <div class="col-12" style="margin-bottom: 16px;">
                                    <label class="rm-label" style="font-weight: 700; color: #475569; display: block; margin-bottom: 6px;"><?php esc_html_e( 'Payment Method', 'reseller-management' ); ?></label>
                                    <select name="payment_method" id="rm-withdraw-method-select" class="rm-select" required style="width: 100%; padding: 10px 14px; border: 1.5px solid #e2e8f0; border-radius: 8px; font-weight: 600; background: #fff;">
                                        <option value=""><?php esc_html_e( '-- Select Method --', 'reseller-management' ); ?></option>
                                        <?php if ( ! empty( $payment_methods ) ) : ?>
                                            <?php foreach ( $payment_methods as $method ) : ?>
                                                <option value="<?php echo esc_attr( $method->method_name ); ?>" data-number="<?php echo esc_attr( $method->number ); ?>">
                                                    <?php echo esc_html( ucfirst( $method->method_name ) . ' (' . ucfirst( $method->type ) . ')' ); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php else : ?>
                                            <option value="" disabled><?php esc_html_e( 'No saved methods. Please add one.', 'reseller-management' ); ?></option>
                                        <?php endif; ?>
                                    </select>
                                </div>
                                <div class="col-12" style="margin-bottom: 24px;">
                                    <label class="rm-label" style="font-weight: 700; color: #475569; display: block; margin-bottom: 6px;"><?php esc_html_e( 'Account Details', 'reseller-management' ); ?></label>
                                    <input type="text" name="account_details" id="rm-withdraw-account-details" class="rm-input" readonly required placeholder="<?php esc_attr_e( 'Select a payment method above', 'reseller-management' ); ?>" style="width: 100%; padding: 10px 14px; border: 1.5px solid #e2e8f0; border-radius: 8px; font-weight: 600; background: #f8fafc; color: #64748b;">
                                </div>
                                <div class="col-12" style="margin-bottom: 24px;">
                                    <label class="rm-label" style="font-weight: 700; color: #475569; display: block; margin-bottom: 6px;"><?php esc_html_e( 'Note (Optional)', 'reseller-management' ); ?></label>
                                    <textarea name="note" class="rm-input" placeholder="<?php esc_attr_e( 'Add any additional notes here...', 'reseller-management' ); ?>" style="width: 100%; padding: 10px 14px; border: 1.5px solid #e2e8f0; border-radius: 8px; font-weight: 600; min-height: 80px;"></textarea>
                                </div>
                                <div class="col-12">
                                    <div class="rm-form-response" style="margin-bottom: 15px; font-size: 0.85rem; font-weight: 600; border-radius: 8px;"></div>
                                    <button type="submit" class="rm-button" style="width: 100%; background: #0f172a; color: #fff; padding: 12px; font-weight: 700; border-radius: 8px; border: none; cursor: pointer;"><?php esc_html_e( 'Submit Request', 'reseller-management' ); ?></button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <?php endif; ?>

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
        $subtab = sanitize_key( wp_unslash( $_GET['subtab'] ?? '' ) );

        // Account tab routes to sub-templates by subtab.
        if ( 'account' === $tab ) {
            $allowed_subtabs = [ 'withdrawals', 'payment-methods', 'transactions' ];
            $active_subtab   = in_array( $subtab, $allowed_subtabs, true ) ? $subtab : 'withdrawals';
            $sub_template    = PLUGIN_BASE_PATH . '/templates/dashboard/account/' . $active_subtab . '.php';

            if ( file_exists( $sub_template ) ) {
                include $sub_template;
                return;
            }
        }

        // Single product view routing.
        if ( 'products' === $tab && ! empty( $_GET['product_id'] ) ) {
            $template = PLUGIN_BASE_PATH . '/templates/dashboard/single-product.php';
            if ( file_exists( $template ) ) {
                include $template;
                return;
            }
        }

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
    /**
     * Get the URL for a specific dashboard tab.
     *
     * @param string $tab    Tab slug.
     * @param string $subtab Subtab slug.
     *
     * @return string
     */
    public function get_dashboard_tab_url( $tab, $subtab = '' ) {
        global $post;

        $url = $post instanceof \WP_Post ? get_permalink( $post ) : home_url( '/' );

        $args = [ 'tab' => $tab ];
        if ( $subtab ) {
            $args['subtab'] = $subtab;
        }

        return add_query_arg( $args, $url );
    }

    /**
     * Use uploaded reseller profile image for avatars when set.
     *
     * @param string              $url         Default avatar URL.
     * @param mixed               $id_or_email User id, email, or object.
     * @param array<string,mixed> $args        Avatar arguments.
     *
     * @return string
     */
    public function filter_reseller_avatar_url( $url, $id_or_email, $args ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
        $user_id = 0;

        if ( is_numeric( $id_or_email ) ) {
            $user_id = (int) $id_or_email;
        } elseif ( $id_or_email instanceof \WP_User ) {
            $user_id = (int) $id_or_email->ID;
        } elseif ( is_string( $id_or_email ) && is_email( $id_or_email ) ) {
            $user = get_user_by( 'email', $id_or_email );
            if ( $user ) {
                $user_id = (int) $user->ID;
            }
        }

        if ( ! $user_id ) {
            return $url;
        }

        $attachment_id = (int) get_user_meta( $user_id, '_reseller_avatar_id', true );
        if ( ! $attachment_id ) {
            return $url;
        }

        $custom = wp_get_attachment_image_url( $attachment_id, 'thumbnail' );

        return $custom ? $custom : $url;
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

        if ( ! empty( $_FILES['avatar']['name'] ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
            $upload_error = (int) ( $_FILES['avatar']['error'] ?? UPLOAD_ERR_NO_FILE );
            if ( UPLOAD_ERR_OK !== $upload_error ) {
                wp_send_json_error( __( 'Avatar upload failed. Please try a smaller image or a different file.', 'reseller-management' ), 422 );
            }

            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/image.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';

            $attachment_id = media_handle_upload( 'avatar', 0 );

            if ( is_wp_error( $attachment_id ) ) {
                wp_send_json_error( $attachment_id->get_error_message(), 422 );
            }

            wp_update_post(
                [
                    'ID'          => $attachment_id,
                    'post_author' => $user_id,
                ]
            );

            $old_avatar = (int) get_user_meta( $user_id, '_reseller_avatar_id', true );
            if ( $old_avatar && $old_avatar !== $attachment_id ) {
                wp_delete_attachment( $old_avatar, true );
            }

            update_user_meta( $user_id, '_reseller_avatar_id', $attachment_id );
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

        $avatar_attachment = (int) get_user_meta( $user_id, '_reseller_avatar_id', true );
        $avatar_url        = $avatar_attachment ? (string) wp_get_attachment_image_url( $avatar_attachment, [ 96, 96 ] ) : '';

        wp_send_json_success(
            [
                'message'    => __( 'Profile updated successfully.', 'reseller-management' ),
                'avatar_url' => $avatar_url,
            ]
        );
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

    /**
     * AJAX handler to fetch order stats by days.
     *
     * @return void
     */
    public function handle_get_order_stats() {
        check_ajax_referer( 'rm_public_nonce', 'nonce' );

        $user_id = get_current_user_id();
        if ( ! is_user_logged_in() || ! Reseller_Helper::is_reseller( $user_id ) ) {
            wp_send_json_error( __( 'Unauthorized.', 'reseller-management' ), 403 );
        }

        $days = isset( $_GET['days'] ) ? (int) $_GET['days'] : 7;
        $stats = Reseller_Orders::get_order_stats_by_days( $user_id, $days );

        wp_send_json_success( $stats );
    }

    /**
     * Get SVG icon markup by name.
     *
     * @param string $name Icon name.
     *
     * @return string
     */
    public function get_svg_icon( $name ) {
        $icons = [
            'dashboard' => '<path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>',
            'orders'    => '<path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12.9-1.63h7.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49c.08-.14.12-.31.12-.48 0-.55-.45-1-1-1H5.21l-.94-2H1zm16 16c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z"/>',
            'products'  => '<path d="M4 11h5V5H4v6zm0 7h5v-6H4v6zm6 0h5v-6h-5v6zm6 0h5v-6h-5v6zm-6-7h5V5h-5v6zm6-6v6h5V5h-5z"/>',
            'account'   => '<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/>',
            'settings'  => '<path d="M19.14 12.94c.04-.3.06-.61.06-.94 0-.32-.02-.64-.07-.94l2.03-1.58c.18-.14.23-.41.12-.61l-1.92-3.32c-.12-.22-.37-.29-.59-.22l-2.39.96c-.5-.38-1.03-.7-1.62-.94l-.36-2.54c-.04-.24-.24-.41-.48-.41h-3.84c-.24 0-.43.17-.47.41l-.36 2.54c-.59.24-1.13.57-1.62.94l-2.39-.96c-.22-.08-.47 0-.59.22L2.74 8.87c-.12.21-.08.47.12.61l2.03 1.58c-.05.3-.09.63-.09.94s.02.64.07.94l-2.03 1.58c-.18.14-.23.41-.12.61l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.38 1.03.7 1.62.94l.36 2.54c.05.24.24.41.48.41h3.84c.24 0 .44-.17.47-.41l.36-2.54c.59-.24 1.13-.56 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32c.12-.22.07-.47-.12-.61l-2.01-1.58zM12 15.6c-1.98 0-3.6-1.62-3.6-3.6s1.62-3.6 3.6-3.6 3.6 1.62 3.6 3.6-1.62 3.6-3.6 3.6z"/>',
            'customers' => '<path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>',
            'logout'    => '<path d="M10.09 15.59L11.5 17l5-5-5-5-1.41 1.41L12.67 11H3v2h9.67l-2.58 2.59zM19 3H5c-1.11 0-2 .9-2 2v4h2V5h14v14H5v-4H3v4c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2z"/>',
            'chevron'   => '<path d="M16.59 8.59L12 13.17 7.41 8.59 6 10l6 6 6-6z"/>',
            'status_new'        => '<path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/>',
            'status_pending'    => '<path d="M17 10c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm-5 0c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zM7 10c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm12-6h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V9h14v11z"/>',
            'status_confirmed'  => '<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>',
            'status_packaging'  => '<path d="M20 8l-3-4H7L4 8l-1 5h1l1 7h14l1-7h1l-1-5zM6.24 8l1.5-2h8.52l1.5 2H6.24zM18 18H6v-5h12v5zm-5-3h-2v-2h2v2z"/>',
            'status_shipment'   => '<path d="M20 8h-3V4H3c-1.1 0-2 .9-2 2v11h2c0 1.66 1.34 3 3 3s3-1.34 3-3h6c0 1.66 1.34 3 3 3s3-1.34 3-3h2v-5l-3-4zM6 18c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1zm13.5-8.5l1.96 2.5H17V9.5h2.5zm-1.5 8c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1z"/>',
            'status_delivered'  => '<path d="M19 13c.01 0 .01 0 0 0-1.07 0-2.03.44-2.73 1.15l-6.23-3.62c.43-.88.42-1.93-.03-2.82l6.21-3.61C16.92 4.81 17.88 5.25 19 5.25c1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3c0 .16.02.32.04.47l-6.22 3.61C9.11 5.48 8.12 5 7 5 5.34 5 4 6.34 4 8s1.34 3 3 3c1.12 0 2.11-.48 2.78-1.24l6.23 3.62c-.02.15-.04.31-.04.47 0 1.66 1.34 3 3 3s3-1.34 3-3-1.34-3-3-3z"/>',
            'status_wfr'        => '<path d="M19 8l-4 4h3c0 3.31-2.69 6-6 6-1.01 0-1.97-.25-2.8-.7l-1.46 1.46C8.97 19.54 10.41 20 12 20c4.42 0 8-3.58 8-8h3l-4-4zM6 12c0-3.31 2.69-6 6-6 1.01 0 1.97.25 2.8.7l1.46-1.46C15.03 4.46 13.59 4 12 4c-4.42 0-8 3.58-8 8H1l4 4 4-4H6z"/>',
            'status_returned'   => '<path d="M12.5 8c-2.65 0-5.05.99-6.9 2.6L2 7v9h9l-3.62-3.62c1.39-1.16 3.16-1.88 5.12-1.88 3.54 0 6.55 2.31 7.6 5.5l2.37-.78C21.08 11.03 17.15 8 12.5 8z"/>',
            'status_cancel'     => '<path d="M12 2C6.47 2 2 6.47 2 12s4.47 10 10 10 10-4.47 10-10S17.53 2 12 2zm5 13.59L15.59 17 12 13.41 8.41 17 7 15.59 10.59 12 7 8.41 8.41 7 12 10.59 15.59 7 17 8.41 13.41 12 17 15.59z"/>',
            'status_all'        => '<path d="M3 13h2v-2H3v2zm0 4h2v-2H3v2zm0-8h2V7H3v2zm4 4h14v-2H7v2zm0 4h14v-2H7v2zM7 7v2h14V7H7z"/>',
            'status_incomplete' => '<path d="M20 12l-1.41-1.41L13 16.17V4h-2v12.17l-5.58-5.59L4 12l8 8 8-8z"/>',
            'whatsapp'          => '<path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.414 0 .004 5.408 0 12.044c0 2.123.555 4.191 1.613 6.011L0 24l6.117-1.605a11.845 11.845 0 005.932 1.577h.005c6.631 0 12.046-5.408 12.05-12.044a11.813 11.813 0 00-3.592-8.514z"/>',
        ];

        $path = $icons[ $name ] ?? '';

        return sprintf(
            '<svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor" xmlns="http://www.w3.org/2000/svg">%s</svg>',
            $path
        );
    }
}
