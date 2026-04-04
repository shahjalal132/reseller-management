<?php
/**
 * Reseller Hub admin layout wrapper.
 *
 * Expected variables set by the calling render method:
 *   $rm_active           (string)  – active nav key
 *   $rm_content_template (string)  – absolute path to content template
 *   $rm_data             (array)   – optional extra data (unused by layout itself)
 */

defined( 'ABSPATH' ) || exit;

$rm_active = isset( $rm_active ) ? (string) $rm_active : 'dashboard';

$rm_nav_items = [
    'dashboard' => [
        'label' => __( 'Dashboard', 'reseller-management' ),
        'url'   => admin_url( 'admin.php?page=reseller-hub' ),
        'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/></svg>',
    ],
    'users' => [
        'label' => __( 'Users', 'reseller-management' ),
        'url'   => admin_url( 'admin.php?page=reseller-hub-users' ),
        'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/></svg>',
    ],
    'withdrawals' => [
        'label' => __( 'Withdrawals', 'reseller-management' ),
        'url'   => admin_url( 'admin.php?page=reseller-hub-withdrawals' ),
        'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z"/></svg>',
    ],
    'settings' => [
        'label' => __( 'Settings', 'reseller-management' ),
        'url'   => admin_url( 'admin.php?page=reseller-hub-settings' ),
        'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>',
    ],
];

// Notice messages for query-string driven feedback.
$rm_notice_map = [
    'reseller-status-updated' => [ 'type' => 'success', 'msg' => __( 'Reseller status updated successfully.', 'reseller-management' ) ],
    'reseller-ban-updated'    => [ 'type' => 'success', 'msg' => __( 'Reseller ban information updated.', 'reseller-management' ) ],
    'reseller-ban-cleared'    => [ 'type' => 'success', 'msg' => __( 'Reseller ban removed successfully.', 'reseller-management' ) ],
    'withdrawal-marked-paid'  => [ 'type' => 'success', 'msg' => __( 'Withdrawal marked as completed.', 'reseller-management' ) ],
    'reseller-deleted'        => [ 'type' => 'success', 'msg' => __( 'Reseller deleted successfully.', 'reseller-management' ) ],
    'reseller-delete-error'   => [ 'type' => 'error',   'msg' => __( 'Could not delete the reseller. Please try again.', 'reseller-management' ) ],
];

$rm_notice_key = sanitize_key( wp_unslash( $_GET['rm_notice'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
?>
<div class="rm-hub-wrap">

    <!-- ── Top navigation bar ── -->
    <div class="rm-hub-top-bar">
        <div class="rm-hub-brand">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z"/>
            </svg>
            <span><?php esc_html_e( 'Reseller Hub', 'reseller-management' ); ?></span>
        </div>

        <nav class="rm-hub-nav">
            <?php foreach ( $rm_nav_items as $key => $item ) : ?>
                <a href="<?php echo esc_url( $item['url'] ); ?>"
                   class="rm-hub-nav-item<?php echo $rm_active === $key ? ' active' : ''; ?>">
                    <?php echo $item['icon']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    <?php echo esc_html( $item['label'] ); ?>
                </a>
            <?php endforeach; ?>
        </nav>
    </div>

    <!-- ── Content area ── -->
    <div class="rm-hub-content">

        <?php if ( ! empty( $rm_notice_map[ $rm_notice_key ] ) ) : ?>
            <div class="rm-notice <?php echo esc_attr( $rm_notice_map[ $rm_notice_key ]['type'] ); ?>" role="status" data-rm-auto-dismiss="1">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:16px;height:16px;flex-shrink:0;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                </svg>
                <?php echo esc_html( $rm_notice_map[ $rm_notice_key ]['msg'] ); ?>
            </div>
        <?php endif; ?>

        <?php if ( isset( $rm_content_template ) && file_exists( $rm_content_template ) ) : ?>
            <?php include $rm_content_template; ?>
        <?php endif; ?>

    </div><!-- .rm-hub-content -->

</div><!-- .rm-hub-wrap -->
