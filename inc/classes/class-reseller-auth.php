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
}
