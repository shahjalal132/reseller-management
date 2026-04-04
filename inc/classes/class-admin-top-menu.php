<?php

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
        add_action( 'admin_post_rm_change_reseller_status', [ $this, 'handle_reseller_status_change' ] );
        add_action( 'admin_post_rm_update_reseller_ban', [ $this, 'handle_reseller_ban_update' ] );
        add_action( 'admin_post_rm_mark_withdrawal_paid', [ $this, 'handle_mark_withdrawal_paid' ] );
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
        add_menu_page(
            __( 'Reseller Hub', 'reseller-management' ),
            __( 'Reseller Hub', 'reseller-management' ),
            'manage_options',
            'reseller-hub',
            [ $this, 'render_reseller_list_page' ],
            'dashicons-groups',
            35
        );

        add_submenu_page(
            'reseller-hub',
            __( 'All Resellers', 'reseller-management' ),
            __( 'All Resellers', 'reseller-management' ),
            'manage_options',
            'reseller-hub',
            [ $this, 'render_reseller_list_page' ]
        );

        add_submenu_page(
            'reseller-hub',
            __( 'Withdrawals', 'reseller-management' ),
            __( 'Withdrawals', 'reseller-management' ),
            'manage_options',
            'reseller-hub-withdrawals',
            [ $this, 'render_withdrawals_page' ]
        );

        add_submenu_page(
            null,
            __( 'Reseller Profile', 'reseller-management' ),
            __( 'Reseller Profile', 'reseller-management' ),
            'manage_options',
            'reseller-hub-profile',
            [ $this, 'render_reseller_profile_page' ]
        );
    }

    /**
     * Render the reseller list page.
     *
     * @return void
     */
    public function render_reseller_list_page() {
        require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';

        $table = new Reseller_List_Table();
        $table->prepare_items();
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Reseller Hub', 'reseller-management' ); ?></h1>
            <p><?php esc_html_e( 'Review applications, inspect reseller balances, and open profile details for approval actions.', 'reseller-management' ); ?></p>

            <?php $this->render_admin_notice(); ?>

            <form method="post">
                <?php $table->display(); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Render the reseller profile page.
     *
     * @return void
     */
    public function render_reseller_profile_page() {
        $reseller_id = absint( $_GET['reseller_id'] ?? 0 );
        $user        = $reseller_id ? get_user_by( 'id', $reseller_id ) : false;

        if ( ! $user || ! Reseller_Helper::is_reseller( $user ) ) {
            wp_die( esc_html__( 'Invalid reseller profile.', 'reseller-management' ) );
        }

        $status       = Reseller_Helper::get_reseller_status( $reseller_id );
        $front_id     = (int) get_user_meta( $reseller_id, '_reseller_nid_front_id', true );
        $back_id      = (int) get_user_meta( $reseller_id, '_reseller_nid_back_id', true );
        $banned_until = (int) get_user_meta( $reseller_id, '_reseller_banned_until', true );
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( $user->display_name ); ?></h1>
            <p>
                <?php
                printf(
                    /* translators: 1: email, 2: status. */
                    esc_html__( 'Email: %1$s | Current status: %2$s', 'reseller-management' ),
                    esc_html( $user->user_email ),
                    esc_html( ucfirst( $status ) )
                );
                ?>
            </p>

            <?php $this->render_admin_notice(); ?>

            <table class="widefat striped" style="max-width: 900px; margin-bottom: 24px;">
                <tbody>
                    <tr>
                        <th><?php esc_html_e( 'Business Name', 'reseller-management' ); ?></th>
                        <td><?php echo esc_html( (string) get_user_meta( $reseller_id, '_reseller_business_name', true ) ); ?></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Phone', 'reseller-management' ); ?></th>
                        <td><?php echo esc_html( (string) get_user_meta( $reseller_id, '_reseller_phone', true ) ); ?></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Facebook URL', 'reseller-management' ); ?></th>
                        <td>
                            <?php if ( get_user_meta( $reseller_id, '_reseller_fb_url', true ) ) : ?>
                                <a href="<?php echo esc_url( (string) get_user_meta( $reseller_id, '_reseller_fb_url', true ) ); ?>" target="_blank" rel="noreferrer"><?php esc_html_e( 'Open', 'reseller-management' ); ?></a>
                            <?php else : ?>
                                &mdash;
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Website URL', 'reseller-management' ); ?></th>
                        <td>
                            <?php if ( get_user_meta( $reseller_id, '_reseller_web_url', true ) ) : ?>
                                <a href="<?php echo esc_url( (string) get_user_meta( $reseller_id, '_reseller_web_url', true ) ); ?>" target="_blank" rel="noreferrer"><?php esc_html_e( 'Open', 'reseller-management' ); ?></a>
                            <?php else : ?>
                                &mdash;
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Current Balance', 'reseller-management' ); ?></th>
                        <td><?php echo wp_kses_post( wc_price( Reseller_Helper::get_current_balance( $reseller_id ) ) ); ?></td>
                    </tr>
                </tbody>
            </table>

            <div style="display: flex; gap: 24px; flex-wrap: wrap; margin-bottom: 24px;">
                <div>
                    <h2><?php esc_html_e( 'NID Front', 'reseller-management' ); ?></h2>
                    <?php echo $front_id ? wp_get_attachment_image( $front_id, 'medium', false, [ 'style' => 'max-width: 320px; height: auto;' ] ) : esc_html__( 'Not uploaded', 'reseller-management' ); ?>
                </div>
                <div>
                    <h2><?php esc_html_e( 'NID Back', 'reseller-management' ); ?></h2>
                    <?php echo $back_id ? wp_get_attachment_image( $back_id, 'medium', false, [ 'style' => 'max-width: 320px; height: auto;' ] ) : esc_html__( 'Not uploaded', 'reseller-management' ); ?>
                </div>
            </div>

            <h2><?php esc_html_e( 'Approval Actions', 'reseller-management' ); ?></h2>
            <p>
                <?php
                foreach ( [ 'pending', 'approved', 'rejected' ] as $action_status ) {
                    $url = wp_nonce_url(
                        admin_url( 'admin-post.php?action=rm_change_reseller_status&status=' . $action_status . '&reseller_id=' . $reseller_id ),
                        'rm_change_reseller_status_' . $reseller_id
                    );

                    printf(
                        '<a class="button button-secondary" style="margin-right: 8px;" href="%s">%s</a>',
                        esc_url( $url ),
                        esc_html( ucfirst( $action_status ) )
                    );
                }
                ?>
            </p>

            <h2 id="rm-ban-controls"><?php esc_html_e( 'Ban Controls', 'reseller-management' ); ?></h2>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <input type="hidden" name="action" value="rm_update_reseller_ban">
                <input type="hidden" name="reseller_id" value="<?php echo esc_attr( (string) $reseller_id ); ?>">
                <?php wp_nonce_field( 'rm_update_reseller_ban_' . $reseller_id ); ?>

                <p>
                    <label for="banned_until"><strong><?php esc_html_e( 'Ban Until', 'reseller-management' ); ?></strong></label><br>
                    <input type="date" id="banned_until" name="banned_until" value="<?php echo $banned_until ? esc_attr( gmdate( 'Y-m-d', $banned_until ) ) : ''; ?>">
                </p>

                <p>
                    <button type="submit" class="button button-primary"><?php esc_html_e( 'Save Ban Date', 'reseller-management' ); ?></button>
                    <button type="submit" class="button" name="clear_ban" value="1"><?php esc_html_e( 'Clear Ban', 'reseller-management' ); ?></button>
                </p>
            </form>
        </div>
        <?php
    }

    /**
     * Render withdrawals page.
     *
     * @return void
     */
    public function render_withdrawals_page() {
        $withdrawals = Reseller_Finance::get_withdrawals();
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Reseller Withdrawals', 'reseller-management' ); ?></h1>
            <p><?php esc_html_e( 'Review pending payout requests and mark them as paid after off-site settlement.', 'reseller-management' ); ?></p>

            <?php $this->render_admin_notice(); ?>

            <table class="widefat striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Reseller', 'reseller-management' ); ?></th>
                        <th><?php esc_html_e( 'Amount', 'reseller-management' ); ?></th>
                        <th><?php esc_html_e( 'Method', 'reseller-management' ); ?></th>
                        <th><?php esc_html_e( 'Details', 'reseller-management' ); ?></th>
                        <th><?php esc_html_e( 'Status', 'reseller-management' ); ?></th>
                        <th><?php esc_html_e( 'Requested At', 'reseller-management' ); ?></th>
                        <th><?php esc_html_e( 'Action', 'reseller-management' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( empty( $withdrawals ) ) : ?>
                        <tr>
                            <td colspan="7"><?php esc_html_e( 'No withdrawals found yet.', 'reseller-management' ); ?></td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ( $withdrawals as $withdrawal ) : ?>
                            <?php $user = get_user_by( 'id', (int) $withdrawal->reseller_id ); ?>
                            <tr>
                                <td><?php echo esc_html( $user ? $user->display_name : '#' . (int) $withdrawal->reseller_id ); ?></td>
                                <td><?php echo wp_kses_post( wc_price( (float) $withdrawal->amount ) ); ?></td>
                                <td><?php echo esc_html( (string) $withdrawal->payment_method ); ?></td>
                                <td><?php echo esc_html( (string) $withdrawal->account_details ); ?></td>
                                <td><?php echo esc_html( ucfirst( (string) $withdrawal->status ) ); ?></td>
                                <td><?php echo esc_html( (string) $withdrawal->created_at ); ?></td>
                                <td>
                                    <?php if ( 'completed' !== $withdrawal->status ) : ?>
                                        <?php
                                        $url = wp_nonce_url(
                                            admin_url( 'admin-post.php?action=rm_mark_withdrawal_paid&withdrawal_id=' . absint( $withdrawal->id ) ),
                                            'rm_mark_withdrawal_paid_' . absint( $withdrawal->id )
                                        );
                                        ?>
                                        <a class="button button-primary" href="<?php echo esc_url( $url ); ?>"><?php esc_html_e( 'Mark as Paid', 'reseller-management' ); ?></a>
                                    <?php else : ?>
                                        <?php esc_html_e( 'Completed', 'reseller-management' ); ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

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
        if ( 'approved' === $status ) {
            delete_user_meta( $reseller_id, '_reseller_banned_until' );
        }

        $this->redirect_with_notice(
            admin_url( 'admin.php?page=reseller-hub-profile&reseller_id=' . $reseller_id ),
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
            $this->redirect_with_notice(
                admin_url( 'admin.php?page=reseller-hub-profile&reseller_id=' . $reseller_id ),
                'reseller-ban-cleared'
            );
        }

        $banned_until = sanitize_text_field( wp_unslash( $_POST['banned_until'] ?? '' ) );
        if ( empty( $banned_until ) ) {
            delete_user_meta( $reseller_id, '_reseller_banned_until' );
        } else {
            update_user_meta( $reseller_id, '_reseller_status', 'banned' );
            update_user_meta( $reseller_id, '_reseller_banned_until', strtotime( $banned_until . ' 23:59:59' ) );
        }

        $this->redirect_with_notice(
            admin_url( 'admin.php?page=reseller-hub-profile&reseller_id=' . $reseller_id ),
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
     * Render simple success notices.
     *
     * @return void
     */
    protected function render_admin_notice() {
        $notice = sanitize_key( wp_unslash( $_GET['rm_notice'] ?? '' ) );
        if ( empty( $notice ) ) {
            return;
        }

        $messages = [
            'reseller-status-updated'  => __( 'Reseller status updated successfully.', 'reseller-management' ),
            'reseller-ban-updated'     => __( 'Reseller ban information updated successfully.', 'reseller-management' ),
            'reseller-ban-cleared'     => __( 'Reseller ban removed successfully.', 'reseller-management' ),
            'withdrawal-marked-paid'   => __( 'Withdrawal marked as completed.', 'reseller-management' ),
        ];

        if ( empty( $messages[ $notice ] ) ) {
            return;
        }

        printf(
            '<div class="notice notice-success is-dismissible"><p>%s</p></div>',
            esc_html( $messages[ $notice ] )
        );
    }

    /**
     * Redirect with an admin notice parameter.
     *
     * @param string $url    Destination URL.
     * @param string $notice Notice key.
     *
     * @return void
     */
    protected function redirect_with_notice( $url, $notice ) {
        wp_safe_redirect(
            add_query_arg(
                [
                    'rm_notice' => $notice,
                ],
                $url
            )
        );
        exit;
    }
}