<?php
/**
 * Authentication and reseller access rules.
 */

namespace BOILERPLATE\Inc;

use BOILERPLATE\Inc\Traits\Singleton;

class Reseller_Auth {
    use Singleton;

    /**
     * Register hooks.
     */
    protected function __construct() {
        add_filter( 'wp_authenticate_user', [ $this, 'restrict_reseller_login' ], 10, 2 );
        add_action( 'login_head', [ $this, 'custom_login_design' ] );
        add_action( 'login_enqueue_scripts', [ $this, 'enqueue_login_assets' ] );
        add_filter( 'login_message', [ $this, 'inject_global_header' ] );
        add_action( 'login_footer', [ $this, 'inject_global_footer' ] );
        add_filter( 'woocommerce_login_redirect', [ $this, 'custom_login_redirect' ], 10, 2 );
        add_filter( 'login_redirect', [ $this, 'custom_login_redirect' ], 10, 3 );
        add_action( 'template_redirect', [ $this, 'restrict_dashboard_access' ] );
    }

    /**
     * Customize the WordPress login page design.
     *
     * @return void
     */
    public function custom_login_design() {
        ?>
        <style>
            :root {
                --rm-primary: #005b4e;
                --rm-sidebar-bg: #004d40;
                --rm-border: #e2e8f0;
                --rm-text: #0f172a;
                --rm-muted: #64748b;
            }
            body.login {
                background: #f8fafc;
                display: flex;
                flex-direction: column;
                min-height: 100vh;
                margin: 0;
                overflow-y: auto !important;
                height: auto !important;
            }
            body.login > header,
            body.login > footer {
                width: 100%;
            }
            body.login #login {
                width: 100%;
                max-width: none;
                padding: 0;
                margin: 0;
            }
            
            body.login .language-switcher { display: none; }
            
            .rm-login-wrapper {
                max-width: 900px;
                width: 100%;
                margin: auto; /* Vertically centers the wrapper between header and footer */
                display: flex;
                background: #ffffff;
                border-radius: 20px;
                box-shadow: 0 15px 35px rgba(0,0,0,0.06);
                overflow: hidden;
                border: 1px solid var(--rm-border);
                flex-shrink: 0;
            }
            .rm-login-left {
                flex: 0 0 400px;
                background: linear-gradient(135deg, var(--rm-primary) 0%, var(--rm-sidebar-bg) 100%);
                color: #fff;
                padding: 40px;
                display: flex;
                flex-direction: column;
                justify-content: center;
            }
            .rm-login-left-content h3 {
                font-size: 28px;
                font-weight: 800;
                margin: 0 0 16px;
                color: #fff;
            }
            .rm-login-left-content p {
                color: rgba(255,255,255,0.85);
                font-size: 15px;
                line-height: 1.6;
            }
            .rm-login-right {
                flex: 1;
                padding: 40px;
                background: #ffffff;
                display: flex;
                flex-direction: column;
                justify-content: center;
            }
            
            body.login h1 {
                display: none;
            }
            body.login form {
                margin-top: 0;
                padding: 0;
                border: none;
                box-shadow: none;
                background: transparent;
            }
            body.login label {
                font-size: 13px;
                font-weight: 600;
                color: var(--rm-text);
            }
            body.login input[type="text"],
            body.login input[type="password"] {
                padding: 12px 16px;
                border: 1px solid var(--rm-border);
                border-radius: 10px;
                font-size: 14px;
                background: #f8fafc;
                transition: all 0.2s;
                width: 100%;
                box-shadow: none;
                color: var(--rm-text);
            }
            body.login input:focus {
                outline: none;
                border-color: var(--rm-primary);
                background: #fff;
                box-shadow: 0 0 0 3px rgba(0, 91, 78, 0.1);
            }
            body.login .button-primary {
                width: 100%;
                background: var(--rm-primary) !important;
                color: #fff !important;
                padding: 10px;
                border-radius: 10px;
                font-size: 15px;
                font-weight: 700;
                border: none !important;
                cursor: pointer;
                box-shadow: none !important;
                text-shadow: none !important;
                transition: all 0.2s;
            }
            body.login .button-primary:hover {
                background: var(--rm-sidebar-bg) !important;
            }
            body.login #nav, body.login #backtoblog {
                margin: 20px 0 0;
                padding: 0;
                text-align: center;
            }
            body.login #nav a, body.login #backtoblog a {
                color: var(--rm-primary) !important;
                font-weight: 600;
                text-decoration: none;
            }
            body.login #nav a:hover, body.login #backtoblog a:hover {
                text-decoration: underline;
            }
            body.login .rm-login-reseller-link {
                margin: 12px 0 0;
                padding: 0;
                text-align: center;
            }
            body.login .rm-login-reseller-link a {
                color: var(--rm-primary) !important;
                font-weight: 600;
                text-decoration: none;
            }
            body.login .rm-login-reseller-link a:hover {
                text-decoration: underline;
            }
            .rm-auth-header-modern {
                margin-bottom: 20px;
            }
            .rm-auth-header-modern h2 {
                font-size: 24px;
                font-weight: 700;
                color: var(--rm-text);
                margin: 0 0 8px;
            }
            .rm-auth-header-modern p {
                color: var(--rm-muted);
                font-size: 14px;
                margin: 0;
            }
            
            @media (max-width: 900px) {
                .rm-login-wrapper {
                    max-width: 800px;
                    margin: 20px;
                }
                .rm-login-left {
                    flex: 0 0 320px;
                }
            }

            @media (max-width: 768px) {
                .rm-login-wrapper {
                    flex-direction: column;
                    margin: 20px;
                    width: auto;
                }
                .rm-login-left {
                    flex: none;
                    padding: 40px 30px;
                }
                .rm-login-right {
                    padding: 40px 30px;
                }
            }

            @media (max-width: 480px) {
                .rm-login-wrapper {
                    margin: 20px 10px;
                    width: calc(100% - 20px);
                }
                .rm-login-left, .rm-login-right {
                    padding: 30px 20px;
                }
                .rm-login-right {
                    min-height: 400px;
                }
                .rm-login-left-content h3 {
                    font-size: 24px;
                }
                .rm-auth-header-modern h2 {
                    font-size: 20px;
                }
                body.login .button-primary {
                    padding: 12px;
                }
            }
        </style>
        <script>
            var rmResellerRegistration = {
                url: <?php echo wp_json_encode( home_url( '/reseller-registration/' ) ); ?>,
                label: <?php echo wp_json_encode( __( 'Become a Reseller', 'reseller-management' ) ); ?>
            };
            document.addEventListener("DOMContentLoaded", function() {
                var loginDiv = document.getElementById("login");
                if (!loginDiv) return;
                
                var wrapper = document.createElement("div");
                wrapper.className = "rm-login-wrapper";
                
                var leftPane = document.createElement("div");
                leftPane.className = "rm-login-left";
                leftPane.innerHTML = '<div class="rm-login-left-content"><h3><?php esc_html_e( 'Welcome Back', 'reseller-management' ); ?></h3><p><?php esc_html_e( 'Manage your reseller business, track orders, and withdraw your earnings easily.', 'reseller-management' ); ?></p></div>';

                var rightPane = document.createElement("div");
                rightPane.className = "rm-login-right";
                
                var customHeader = document.createElement("div");
                customHeader.className = "rm-auth-header-modern";
                customHeader.innerHTML = '<h2><?php esc_html_e( 'Sign In', 'reseller-management' ); ?></h2><p><?php esc_html_e( 'Enter your credentials to access your account.', 'reseller-management' ); ?></p>';
                
                loginDiv.parentNode.insertBefore(wrapper, loginDiv);
                wrapper.appendChild(leftPane);
                wrapper.appendChild(rightPane);
                
                rightPane.appendChild(customHeader);
                rightPane.appendChild(loginDiv);

                var nav = loginDiv.querySelector("#nav");
                if (nav && rmResellerRegistration && rmResellerRegistration.url) {
                    var regP = document.createElement("p");
                    regP.className = "rm-login-reseller-link";
                    var regA = document.createElement("a");
                    regA.href = rmResellerRegistration.url;
                    regA.textContent = rmResellerRegistration.label;
                    regP.appendChild(regA);
                    nav.parentNode.insertBefore(regP, nav.nextSibling);
                }
            });
        </script>
        <?php
    }

    /**
     * Enqueue public CSS and JS for the login page so the header looks right.
     *
     * @return void
     */
    public function enqueue_login_assets() {
        wp_enqueue_style( 'wpb-public-css', PLUGIN_PUBLIC_ASSETS_URL . '/css/public-style.css', [], time(), 'all' );
        wp_enqueue_script( 'wpb-public-js', PLUGIN_PUBLIC_ASSETS_URL . '/js/public-script.js', [ 'jquery' ], time(), true );
    }

    /**
     * Inject the global header into the login page.
     * We use login_message to echo it and wrap it in a hidden container,
     * then JS will prepend it to the body.
     *
     * @param string $message Existing message.
     * @return string
     */
    public function inject_global_header( $message ) {
        ob_start();
        echo '<div id="rm-global-header-container" style="display:none;">';
        include PLUGIN_BASE_PATH . '/templates/template-parts/global-header.php';
        echo '</div>';
        $header_html = ob_get_clean();

        return $message . "\n" . $header_html;
    }

    /**
     * Inject the global footer into the login page and JS to place the header properly.
     *
     * @return void
     */
    public function inject_global_footer() {
        include PLUGIN_BASE_PATH . '/templates/template-parts/global-footer.php';
        ?>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                var headerContainer = document.getElementById("rm-global-header-container");
                if (headerContainer) {
                    var body = document.body;
                    body.insertBefore(headerContainer, body.firstChild);
                    
                    // The actual header is inside the container
                    var actualHeader = headerContainer.querySelector('header');
                    if (actualHeader) {
                        body.insertBefore(actualHeader, body.firstChild);
                        headerContainer.parentNode.removeChild(headerContainer);
                    } else {
                        headerContainer.style.display = "block";
                    }
                }
            });
        </script>
        <?php
    }

    /**
     * Prevent pending, rejected, and banned resellers from logging in.
     *
     * @param \WP_User|\WP_Error $user     User object.
     * @param string             $password Password.
     *
     * @return \WP_User|\WP_Error
     */
    public function restrict_reseller_login( $user, $password ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
        if ( is_wp_error( $user ) || ! Reseller_Helper::is_reseller( $user ) ) {
            return $user;
        }

        $user_id = (int) $user->ID;
        $status  = Reseller_Helper::get_reseller_status( $user_id );

        if ( 'approved' === $status ) {
            return $user;
        }

        if ( Reseller_Helper::is_currently_banned( $user_id ) ) {
            $banned_until = (int) get_user_meta( $user_id, '_reseller_banned_until', true );

            return new \WP_Error(
                'reseller_banned',
                sprintf(
                    /* translators: %s: ban end date. */
                    __( 'Your reseller account is banned until %s.', 'reseller-management' ),
                    wp_date( get_option( 'date_format' ), $banned_until )
                )
            );
        }

        if ( 'rejected' === $status ) {
            return new \WP_Error(
                'reseller_rejected',
                __( 'Your reseller account has been rejected. Please contact the site administrator.', 'reseller-management' )
            );
        }

        return new \WP_Error(
            'reseller_pending',
            __( 'Your reseller account is pending approval from the administrator.', 'reseller-management' )
        );
    }

    /**
     * Custom login redirect for resellers and users.
     *
     * @param string            $redirect_to     The redirect URL.
     * @param \WP_User|string   $request_or_user The requested URL or user object.
     * @param \WP_User|null     $user            The user object.
     *
     * @return string
     */
    public function custom_login_redirect( $redirect_to, $request_or_user, $user = null ) {
        $current_user = null;
        if ( $user instanceof \WP_User ) {
            $current_user = $user;
        } elseif ( $request_or_user instanceof \WP_User ) {
            $current_user = $request_or_user;
        }

        if ( ! $current_user ) {
            return $redirect_to;
        }

        if ( Reseller_Helper::is_reseller( $current_user ) ) {
            if ( user_can( $current_user, 'manage_options' ) ) {
                return admin_url();
            }

            $dashboard_page = get_page_by_path( 'reseller-dashboard' );
            if ( $dashboard_page ) {
                return get_permalink( $dashboard_page->ID );
            }
        } elseif ( in_array( 'customer', (array) $current_user->roles, true ) || in_array( 'subscriber', (array) $current_user->roles, true ) ) {
            $my_account_page = get_option( 'woocommerce_myaccount_page_id' );
            if ( $my_account_page ) {
                return get_permalink( $my_account_page );
            }
        }

        return $redirect_to;
    }

    /**
     * Restrict regular users from accessing the reseller dashboard.
     *
     * @return void
     */
    public function restrict_dashboard_access() {
        if ( is_page( 'reseller-dashboard' ) ) {
            if ( is_user_logged_in() && ! Reseller_Helper::is_reseller( get_current_user_id() ) ) {
                $my_account_page = get_option( 'woocommerce_myaccount_page_id' );
                if ( $my_account_page ) {
                    wp_safe_redirect( get_permalink( $my_account_page ) );
                    exit;
                } else {
                    wp_safe_redirect( home_url() );
                    exit;
                }
            }
        }
    }
}
