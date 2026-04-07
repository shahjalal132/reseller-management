<?php
/**
 * Reseller Hub admin top-level menu and page rendering.
 */

namespace BOILERPLATE\Inc;

use BOILERPLATE\Inc\Traits\Singleton;

class Admin_Top_Menu {
    use Singleton;

    /**
     * Register hooks.
     */
    protected function __construct() {
        $this->setup_hooks();
    }

    /**
     * Set up admin hooks.
     *
     * @return void
     */
    public function setup_hooks() {
        add_action( 'admin_menu', [ $this, 'register_admin_top_menu' ] );
        add_filter( 'plugin_action_links_' . PLUGIN_BASE_NAME, [ $this, 'add_plugin_action_links' ] );

        // Status/ban/withdrawal handlers (existing).
        add_action( 'admin_post_rm_change_reseller_status', [ $this, 'handle_reseller_status_change' ] );
        add_action( 'admin_post_rm_update_reseller_ban',    [ $this, 'handle_reseller_ban_update' ] );
        add_action( 'admin_post_rm_mark_withdrawal_paid',  [ $this, 'handle_mark_withdrawal_paid' ] );

        // Admin Withdrawals actions
        add_action( 'admin_post_rm_delete_withdrawal', [ $this, 'handle_delete_withdrawal' ] );
        add_action( 'admin_post_rm_edit_withdrawal',   [ $this, 'handle_edit_withdrawal' ] );

        // Delete reseller handler.
        add_action( 'admin_post_rm_delete_reseller', [ $this, 'handle_delete_reseller' ] );

        // Settings handler.
        add_action( 'admin_post_rm_save_settings', [ $this, 'handle_settings_save' ] );
    }

    /**
     * Add plugin shortcut links.
     *
     * @param array<int, string> $links Existing links.
     *
     * @return array<int, string>
     */
    public function add_plugin_action_links( $links ) {
        $settings_link = '<a href="' . esc_url( admin_url( 'admin.php?page=reseller-hub' ) ) . '">' . esc_html__( 'Reseller Hub', 'reseller-management' ) . '</a>';
        array_unshift( $links, $settings_link );

        return $links;
    }

    /**
     * Register reseller admin pages.
     *
     * @return void
     */
    public function register_admin_top_menu() {
        // Top-level menu with custom SVG icon (store icon in base64).
        add_menu_page(
            __( 'Reseller Hub', 'reseller-management' ),
            __( 'Reseller Hub', 'reseller-management' ),
            'manage_options',
            'reseller-hub',
            [ $this, 'render_dashboard_page' ],
            'dashicons-store',
            35
        );

        // Dashboard (renames the auto-generated first sub-menu item).
        add_submenu_page(
            'reseller-hub',
            __( 'Dashboard', 'reseller-management' ),
            __( 'Dashboard', 'reseller-management' ),
            'manage_options',
            'reseller-hub',
            [ $this, 'render_dashboard_page' ]
        );

        // Users (resellers list).
        add_submenu_page(
            'reseller-hub',
            __( 'Users', 'reseller-management' ),
            __( 'Users', 'reseller-management' ),
            'manage_options',
            'reseller-hub-users',
            [ $this, 'render_users_page' ]
        );

        // Withdrawals.
        add_submenu_page(
            'reseller-hub',
            __( 'Withdrawals', 'reseller-management' ),
            __( 'Withdrawals', 'reseller-management' ),
            'manage_options',
            'reseller-hub-withdrawals',
            [ $this, 'render_withdrawals_page' ]
        );

        // Settings.
        add_submenu_page(
            'reseller-hub',
            __( 'Settings', 'reseller-management' ),
            __( 'Settings', 'reseller-management' ),
            'manage_options',
            'reseller-hub-settings',
            [ $this, 'render_settings_page' ]
        );

        // Hidden: single user view.
        add_submenu_page(
            null,
            __( 'View Reseller', 'reseller-management' ),
            __( 'View Reseller', 'reseller-management' ),
            'manage_options',
            'reseller-hub-user-view',
            [ $this, 'render_user_view_page' ]
        );

        // Hidden: reseller orders page.
        add_submenu_page(
            null,
            __( 'Reseller Orders', 'reseller-management' ),
            __( 'Reseller Orders', 'reseller-management' ),
            'manage_options',
            'reseller-hub-user-orders',
            [ $this, 'render_user_orders_page' ]
        );

        // Hidden: reseller statements page.
        add_submenu_page(
            null,
            __( 'Reseller Statements', 'reseller-management' ),
            __( 'Reseller Statements', 'reseller-management' ),
            'manage_options',
            'reseller-hub-user-statements',
            [ $this, 'render_user_statements_page' ]
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Render helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Render a page using the layout template.
     *
     * @param string              $active   Active nav key.
     * @param string              $template Absolute path to content template.
     * @param array<string,mixed> $vars     Variables to extract into template scope.
     *
     * @return void
     */
    protected function render_page( $active, $template, array $vars = [] ) {
        $rm_active           = $active;
        $rm_content_template = $template;

        // Expose any additional variables.
        foreach ( $vars as $key => $value ) {
            $$key = $value;
        }

        include PLUGIN_BASE_PATH . '/templates/template-admin-top-menu.php';
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Page callbacks
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Render the dashboard overview page.
     *
     * @return void
     */
    public function render_dashboard_page() {
        $all_users = get_users(
            [
                'role'    => Reseller_Helper::get_role_slug(),
                'orderby' => 'registered',
                'order'   => 'DESC',
            ]
        );

        $total   = count( $all_users );
        $active  = 0;
        $pending = 0;

        foreach ( $all_users as $u ) {
            $status = Reseller_Helper::get_reseller_status( $u->ID );
            if ( 'approved' === $status ) {
                ++$active;
            } elseif ( 'pending' === $status ) {
                ++$pending;
            }
        }

        // Recent 5 resellers.
        $recent = array_slice( $all_users, 0, 5 );
        $recent_users = array_map( function ( $u ) {
            return [
                'ID'     => $u->ID,
                'name'   => $u->display_name,
                'email'  => $u->user_email,
                'status' => Reseller_Helper::get_reseller_status( $u->ID ),
            ];
        }, $recent );

        $this->render_page(
            'dashboard',
            PLUGIN_BASE_PATH . '/templates/admin/rm-dashboard.php',
            [
                'rm_total_resellers'   => $total,
                'rm_active_resellers'  => $active,
                'rm_pending_resellers' => $pending,
                'rm_recent_users'      => $recent_users,
            ]
        );
    }

    /**
     * Render the users (resellers) list page.
     *
     * @return void
     */
    public function render_users_page() {
        $search = sanitize_text_field( wp_unslash( $_GET['rm_search'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

        $query_args = [
            'role'    => Reseller_Helper::get_role_slug(),
            'orderby' => 'registered',
            'order'   => 'DESC',
        ];

        if ( ! empty( $search ) ) {
            $query_args['search']         = '*' . $search . '*';
            $query_args['search_columns'] = [ 'display_name', 'user_email', 'user_login' ];
        }

        $users    = get_users( $query_args );
        $rm_users = array_map( function ( $u ) {
            return [
                'ID'     => $u->ID,
                'name'   => $u->display_name,
                'email'  => $u->user_email,
                'status' => Reseller_Helper::get_reseller_status( $u->ID ),
            ];
        }, $users );

        $this->render_page(
            'users',
            PLUGIN_BASE_PATH . '/templates/admin/rm-users-list.php',
            [ 'rm_users' => $rm_users ]
        );
    }

    /**
     * Render the settings page.
     *
     * @return void
     */
    public function render_settings_page() {
        $settings = get_option( 'rm_settings', [] );
        $this->render_page(
            'settings',
            PLUGIN_BASE_PATH . '/templates/admin/rm-settings.php',
            [ 'rm_settings' => $settings ]
        );
    }

    /**
     * Render the withdrawals page.
     *
     * @return void
     */
    public function render_withdrawals_page() {
        $withdrawals = Reseller_Finance::get_withdrawals();

        $this->render_page(
            'withdrawals',
            PLUGIN_BASE_PATH . '/templates/admin/rm-withdrawals.php',
            [ 'rm_withdrawals' => $withdrawals ]
        );
    }

    /**
     * Render the single user / reseller view page.
     *
     * @return void
     */
    public function render_user_view_page() {
        $reseller_id = absint( $_GET['reseller_id'] ?? 0 ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $user        = $reseller_id ? get_user_by( 'id', $reseller_id ) : false;

        if ( ! $user || ! Reseller_Helper::is_reseller( $user ) ) {
            wp_die( esc_html__( 'Invalid reseller profile.', 'reseller-management' ) );
        }

        $reseller_orders = Reseller_Orders::get_reseller_orders( $reseller_id );

        $this->render_page(
            'users',
            PLUGIN_BASE_PATH . '/templates/admin/rm-user-view.php',
            [
                'rm_reseller_id'    => $reseller_id,
                'rm_user'           => $user,
                'rm_status'         => Reseller_Helper::get_reseller_status( $reseller_id ),
                'rm_balance'        => Reseller_Helper::get_current_balance( $reseller_id ),
                'rm_phone'          => (string) get_user_meta( $reseller_id, '_reseller_phone', true ),
                'rm_business_name'  => (string) get_user_meta( $reseller_id, '_reseller_business_name', true ),
                'rm_fb_url'         => (string) get_user_meta( $reseller_id, '_reseller_fb_url', true ),
                'rm_web_url'        => (string) get_user_meta( $reseller_id, '_reseller_web_url', true ),
                'rm_front_id'       => (int) get_user_meta( $reseller_id, '_reseller_nid_front_id', true ),
                'rm_back_id'        => (int) get_user_meta( $reseller_id, '_reseller_nid_back_id', true ),
                'rm_banned_until'   => (int) get_user_meta( $reseller_id, '_reseller_banned_until', true ),
                'rm_reseller_orders'=> $reseller_orders,
            ]
        );
    }

    /**
     * Render the reseller orders page (full-page with filters & pagination).
     *
     * @return void
     */
    public function render_user_orders_page() {
        $reseller_id = absint( $_GET['reseller_id'] ?? 0 ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $user        = $reseller_id ? get_user_by( 'id', $reseller_id ) : false;

        if ( ! $user || ! Reseller_Helper::is_reseller( $user ) ) {
            wp_die( esc_html__( 'Invalid reseller profile.', 'reseller-management' ) );
        }

        $all_orders = Reseller_Orders::get_reseller_orders( $reseller_id );

        $this->render_page(
            'users',
            PLUGIN_BASE_PATH . '/templates/admin/rm-user-orders.php',
            [
                'rm_reseller_id'  => $reseller_id,
                'rm_user'         => $user,
                'rm_all_orders'   => $all_orders,
            ]
        );
    }

    /**
     * Render the reseller statements page (ledger / dummy data).
     *
     * @return void
     */
    public function render_user_statements_page() {
        $reseller_id = absint( $_GET['reseller_id'] ?? 0 ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $user        = $reseller_id ? get_user_by( 'id', $reseller_id ) : false;

        if ( ! $user || ! Reseller_Helper::is_reseller( $user ) ) {
            wp_die( esc_html__( 'Invalid reseller profile.', 'reseller-management' ) );
        }

        $this->render_page(
            'users',
            PLUGIN_BASE_PATH . '/templates/admin/rm-user-statements.php',
            [
                'rm_reseller_id' => $reseller_id,
                'rm_user'        => $user,
                'rm_balance'     => Reseller_Helper::get_current_balance( $reseller_id ),
            ]
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Action handlers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Handle reseller status changes.
     *
     * @return void
     */
    public function handle_reseller_status_change() {
        $reseller_id = absint( $_GET['reseller_id'] ?? 0 );
        $status      = sanitize_key( wp_unslash( $_GET['status'] ?? '' ) );

        check_admin_referer( 'rm_change_reseller_status_' . $reseller_id );

        if ( ! current_user_can( 'manage_options' ) || ! in_array( $status, [ 'pending', 'approved', 'rejected' ], true ) ) {
            wp_die( esc_html__( 'You are not allowed to perform this action.', 'reseller-management' ) );
        }

        update_user_meta( $reseller_id, '_reseller_status', $status );
        // Clear ban window so get_reseller_status() reflects the new approval state (it returns "banned" while ban_until is future).
        delete_user_meta( $reseller_id, '_reseller_banned_until' );
        clean_user_cache( $reseller_id );

        $this->redirect_with_notice(
            admin_url( 'admin.php?page=reseller-hub-user-view&reseller_id=' . $reseller_id ),
            'reseller-status-updated'
        );
    }

    /**
     * Handle reseller ban updates.
     *
     * @return void
     */
    public function handle_reseller_ban_update() {
        $reseller_id = absint( $_POST['reseller_id'] ?? 0 );
        check_admin_referer( 'rm_update_reseller_ban_' . $reseller_id );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You are not allowed to update bans.', 'reseller-management' ) );
        }

        if ( ! empty( $_POST['clear_ban'] ) ) {
            delete_user_meta( $reseller_id, '_reseller_banned_until' );
            clean_user_cache( $reseller_id );
            $this->redirect_with_notice(
                admin_url( 'admin.php?page=reseller-hub-user-view&reseller_id=' . $reseller_id ),
                'reseller-ban-cleared'
            );
            return;
        }

        $banned_until = sanitize_text_field( wp_unslash( $_POST['banned_until'] ?? '' ) );
        if ( empty( $banned_until ) ) {
            delete_user_meta( $reseller_id, '_reseller_banned_until' );
        } else {
            update_user_meta( $reseller_id, '_reseller_status', 'banned' );
            update_user_meta( $reseller_id, '_reseller_banned_until', strtotime( $banned_until . ' 23:59:59' ) );
        }
        clean_user_cache( $reseller_id );

        $this->redirect_with_notice(
            admin_url( 'admin.php?page=reseller-hub-user-view&reseller_id=' . $reseller_id ),
            'reseller-ban-updated'
        );
    }

    /**
     * Handle marking a withdrawal as paid.
     *
     * @return void
     */
    public function handle_mark_withdrawal_paid() {
        global $wpdb;

        $withdrawal_id = absint( $_GET['withdrawal_id'] ?? 0 );
        check_admin_referer( 'rm_mark_withdrawal_paid_' . $withdrawal_id );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You are not allowed to update withdrawals.', 'reseller-management' ) );
        }

        $wpdb->update(
            Reseller_Helper::get_withdrawals_table_name(),
            [ 'status' => 'completed' ],
            [ 'id' => $withdrawal_id ],
            [ '%s' ],
            [ '%d' ]
        );

        $this->redirect_with_notice(
            admin_url( 'admin.php?page=reseller-hub-withdrawals' ),
            'withdrawal-marked-paid'
        );
    }

    /**
     * Handle permanent deletion of a reseller user.
     *
     * @return void
     */
    public function handle_delete_reseller() {
        $reseller_id = absint( $_GET['reseller_id'] ?? 0 );
        check_admin_referer( 'rm_delete_reseller_' . $reseller_id );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You are not allowed to delete users.', 'reseller-management' ) );
        }

        $user = get_user_by( 'id', $reseller_id );
        if ( ! $user || ! Reseller_Helper::is_reseller( $user ) ) {
            $this->redirect_with_notice( admin_url( 'admin.php?page=reseller-hub-users' ), 'reseller-delete-error' );
            return;
        }

        require_once ABSPATH . 'wp-admin/includes/user.php';
        wp_delete_user( $reseller_id );

        $this->redirect_with_notice( admin_url( 'admin.php?page=reseller-hub-users' ), 'reseller-deleted' );
    }

    /**
     * Handle editing a withdrawal via form submit.
     *
     * @return void
     */
    public function handle_edit_withdrawal() {
        global $wpdb;

        $withdrawal_id = absint( $_POST['wd_id'] ?? 0 );
        check_admin_referer( 'rm_edit_withdrawal_' . $withdrawal_id );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You are not allowed to edit withdrawals.', 'reseller-management' ) );
        }

        $amount          = round( (float) wp_unslash( $_POST['amount'] ?? 0 ), 2 );
        $payment_method  = sanitize_text_field( wp_unslash( $_POST['payment_method'] ?? '' ) );
        $account_details = sanitize_textarea_field( wp_unslash( $_POST['account_details'] ?? '' ) );
        $note            = sanitize_textarea_field( wp_unslash( $_POST['note'] ?? '' ) );
        $status          = sanitize_text_field( wp_unslash( $_POST['status'] ?? 'pending' ) );

        $allowed_statuses = [ 'pending', 'approved', 'rejected', 'completed' ];
        if ( ! in_array( $status, $allowed_statuses, true ) ) {
            $status = 'pending';
        }

        if ( $withdrawal_id > 0 ) {
            $wpdb->update(
                Reseller_Helper::get_withdrawals_table_name(),
                [
                    'amount'          => $amount,
                    'payment_method'  => $payment_method,
                    'account_details' => $account_details,
                    'note'            => $note,
                    'status'          => $status,
                ],
                [ 'id' => $withdrawal_id ],
                [ '%f', '%s', '%s', '%s', '%s' ],
                [ '%d' ]
            );
        }

        $this->redirect_with_notice(
            admin_url( 'admin.php?page=reseller-hub-withdrawals' ),
            'withdrawal-updated'
        );
    }

    /**
     * Handle deleting a withdrawal.
     *
     * @return void
     */
    public function handle_delete_withdrawal() {
        global $wpdb;

        $withdrawal_id = absint( $_GET['wd_id'] ?? 0 );
        check_admin_referer( 'rm_delete_withdrawal_' . $withdrawal_id );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You are not allowed to delete withdrawals.', 'reseller-management' ) );
        }

        if ( $withdrawal_id > 0 ) {
            $wpdb->delete(
                Reseller_Helper::get_withdrawals_table_name(),
                [ 'id' => $withdrawal_id ],
                [ '%d' ]
            );
        }

        $this->redirect_with_notice(
            admin_url( 'admin.php?page=reseller-hub-withdrawals' ),
            'withdrawal-deleted'
        );
    }

    /**
     * Handle saving global plugin settings.
     *
     * @return void
     */
    public function handle_settings_save() {
        check_admin_referer( 'rm_save_settings' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You are not allowed to manage settings.', 'reseller-management' ) );
        }

        $settings = [
            'cod_enabled'            => isset( $_POST['cod_enabled'] ) ? 'yes' : 'no',
            'cod_input1'             => sanitize_text_field( wp_unslash( $_POST['cod_input1'] ?? '' ) ),
            'packaging_cost_enabled' => isset( $_POST['packaging_cost_enabled'] ) ? 'yes' : 'no',
            'packaging_cost_input1'  => sanitize_text_field( wp_unslash( $_POST['packaging_cost_input1'] ?? '' ) ),
            'steadfast_secret_token' => sanitize_text_field( wp_unslash( $_POST['steadfast_secret_token'] ?? '' ) ),
        ];

        update_option( 'rm_settings', $settings );

        $this->redirect_with_notice( admin_url( 'admin.php?page=reseller-hub-settings' ), 'settings-updated' );
    }

    /**
     * Redirect with an admin notice parameter appended to the URL.
     *
     * @param string $url    Destination URL.
     * @param string $notice Notice key.
     *
     * @return void
     */
    protected function redirect_with_notice( $url, $notice ) {
        wp_safe_redirect( add_query_arg( [ 'rm_notice' => $notice ], $url ) );
        exit;
    }
}
