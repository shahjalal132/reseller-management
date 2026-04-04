<?php
/**
 * Admin list table for reseller users.
 */

namespace BOILERPLATE\Inc;

class Reseller_List_Table extends \WP_List_Table {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct(
            [
                'singular' => 'reseller',
                'plural'   => 'resellers',
                'ajax'     => false,
            ]
        );
    }

    /**
     * Get columns.
     *
     * @return array<string, string>
     */
    public function get_columns() {
        return [
            'name'           => __( 'Name', 'reseller-management' ),
            'email'          => __( 'Email', 'reseller-management' ),
            'business_name'  => __( 'Business', 'reseller-management' ),
            'status'         => __( 'Status', 'reseller-management' ),
            'current_balance'=> __( 'Current Balance', 'reseller-management' ),
        ];
    }

    /**
     * Prepare list data.
     *
     * @return void
     */
    public function prepare_items() {
        $users = get_users(
            [
                'role'    => Reseller_Helper::get_role_slug(),
                'orderby' => 'registered',
                'order'   => 'DESC',
            ]
        );

        $items = [];
        foreach ( $users as $user ) {
            $items[] = [
                'ID'              => $user->ID,
                'name'            => $user->display_name,
                'email'           => $user->user_email,
                'business_name'   => (string) get_user_meta( $user->ID, '_reseller_business_name', true ),
                'status'          => Reseller_Helper::get_reseller_status( $user->ID ),
                'current_balance' => wc_price( Reseller_Helper::get_current_balance( $user->ID ) ),
            ];
        }

        $this->items = $items;
    }

    /**
     * Render the primary name column.
     *
     * @param array<string, mixed> $item Current row.
     *
     * @return string
     */
    public function column_name( $item ) {
        $view_url     = admin_url( 'admin.php?page=reseller-hub-profile&reseller_id=' . absint( $item['ID'] ) );
        $approve_url  = wp_nonce_url(
            admin_url( 'admin-post.php?action=rm_change_reseller_status&status=approved&reseller_id=' . absint( $item['ID'] ) ),
            'rm_change_reseller_status_' . absint( $item['ID'] )
        );
        $ban_url      = admin_url( 'admin.php?page=reseller-hub-profile&reseller_id=' . absint( $item['ID'] ) . '#rm-ban-controls' );

        $actions = [
            'view'    => sprintf( '<a href="%s">%s</a>', esc_url( $view_url ), esc_html__( 'View Profile', 'reseller-management' ) ),
            'approve' => sprintf( '<a href="%s">%s</a>', esc_url( $approve_url ), esc_html__( 'Approve', 'reseller-management' ) ),
            'ban'     => sprintf( '<a href="%s">%s</a>', esc_url( $ban_url ), esc_html__( 'Ban / Unban', 'reseller-management' ) ),
        ];

        return sprintf(
            '<strong><a href="%1$s">%2$s</a></strong>%3$s',
            esc_url( $view_url ),
            esc_html( (string) $item['name'] ),
            $this->row_actions( $actions )
        );
    }

    /**
     * Default column renderer.
     *
     * @param array<string, mixed> $item        Current row.
     * @param string               $column_name Column name.
     *
     * @return string
     */
    public function column_default( $item, $column_name ) {
        return isset( $item[ $column_name ] ) ? (string) $item[ $column_name ] : '';
    }
}
