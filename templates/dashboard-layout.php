<?php
/**
 * Minimal reseller dashboard layout.
 *
 * @package reseller-management
 */

defined( 'ABSPATH' ) || exit;

if ( ! is_user_logged_in() ) {
    auth_redirect();
}

if ( ! \BOILERPLATE\Inc\Reseller_Helper::is_reseller( get_current_user_id() ) ) {
    wp_die( esc_html__( 'This dashboard is reserved for reseller accounts.', 'reseller-management' ) );
}

if ( ! \BOILERPLATE\Inc\Reseller_Helper::is_reseller_approved( get_current_user_id() ) ) {
    wp_die( esc_html__( 'Your reseller account is pending approval.', 'reseller-management' ) );
}
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class( 'rm-dashboard-body' ); ?>>
<?php wp_body_open(); ?>

<?php \BOILERPLATE\Inc\Reseller_Dashboard::get_instance()->render_dashboard_layout(); ?>

<?php wp_footer(); ?>
</body>
</html>
