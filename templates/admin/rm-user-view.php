<?php
/**
 * Reseller Hub – User view / profile page content template.
 *
 * Variables provided by the render method:
 *   $rm_reseller_id   (int)
 *   $rm_user          (WP_User)
 *   $rm_status        (string)
 *   $rm_balance       (float)
 *   $rm_phone         (string)
 *   $rm_business_name (string)
 *   $rm_fb_url        (string)
 *   $rm_web_url       (string)
 *   $rm_front_id      (int)   attachment ID for NID front
 *   $rm_back_id       (int)   attachment ID for NID back
 *   $rm_banned_until   (int)   Unix timestamp
 *   $rm_reseller_orders(array) WC_Order instances for this reseller (_assigned_reseller_id)
 */

defined( 'ABSPATH' ) || exit;

$rm_reseller_id   = isset( $rm_reseller_id )   ? (int) $rm_reseller_id   : 0;
$rm_user          = isset( $rm_user )          ? $rm_user                 : null;
$rm_status        = isset( $rm_status )        ? (string) $rm_status      : 'pending';
$rm_balance       = isset( $rm_balance )       ? (float) $rm_balance      : 0.0;
$rm_phone         = isset( $rm_phone )         ? (string) $rm_phone       : '';
$rm_business_name = isset( $rm_business_name ) ? (string) $rm_business_name : '';
$rm_fb_url        = isset( $rm_fb_url )        ? (string) $rm_fb_url      : '';
$rm_web_url       = isset( $rm_web_url )       ? (string) $rm_web_url     : '';
$rm_front_id      = isset( $rm_front_id )      ? (int) $rm_front_id       : 0;
$rm_back_id       = isset( $rm_back_id )       ? (int) $rm_back_id        : 0;
$rm_banned_until  = isset( $rm_banned_until )  ? (int) $rm_banned_until   : 0;

if ( ! $rm_user ) {
    wp_die( esc_html__( 'Invalid reseller.', 'reseller-management' ) );
}

// Always read status and ban from the database (avoids stale values if cache or scope is off).
$rm_status       = \BOILERPLATE\Inc\Reseller_Helper::get_reseller_status( $rm_reseller_id );
$rm_banned_until = (int) get_user_meta( $rm_reseller_id, '_reseller_banned_until', true );

$avatar_id     = (int) get_user_meta( $rm_reseller_id, '_reseller_avatar_id', true );
$avatar_letter = strtoupper( mb_substr( $rm_user->display_name, 0, 1 ) );
$joined_date   = date_i18n( get_option( 'date_format' ), strtotime( $rm_user->user_registered ) );

$status_css = [
    'approved' => 'approved',
    'pending'  => 'pending',
    'rejected' => 'rejected',
    'banned'   => 'banned',
];
$badge_class = $status_css[ $rm_status ] ?? 'pending';

// URLs for status actions.
$approve_url = wp_nonce_url(
    admin_url( 'admin-post.php?action=rm_change_reseller_status&status=approved&reseller_id=' . $rm_reseller_id ),
    'rm_change_reseller_status_' . $rm_reseller_id
);
$reject_url = wp_nonce_url(
    admin_url( 'admin-post.php?action=rm_change_reseller_status&status=rejected&reseller_id=' . $rm_reseller_id ),
    'rm_change_reseller_status_' . $rm_reseller_id
);
$pending_url = wp_nonce_url(
    admin_url( 'admin-post.php?action=rm_change_reseller_status&status=pending&reseller_id=' . $rm_reseller_id ),
    'rm_change_reseller_status_' . $rm_reseller_id
);

$back_url = admin_url( 'admin.php?page=reseller-hub-users' );

$rm_reseller_orders = isset( $rm_reseller_orders ) && is_array( $rm_reseller_orders ) ? $rm_reseller_orders : [];
$rm_orders_count    = count( $rm_reseller_orders );

$rm_orders_url     = admin_url( 'admin.php?page=reseller-hub-user-orders&reseller_id=' . $rm_reseller_id );
$rm_statements_url = admin_url( 'admin.php?page=reseller-hub-user-statements&reseller_id=' . $rm_reseller_id );
?>

<!-- Top toolbar: back (left) + action buttons (center) -->
<div class="rm-user-view-toolbar">
    <div class="rm-user-view-toolbar-left">
        <a href="<?php echo esc_url( $back_url ); ?>" class="rm-back-btn" style="margin-bottom:0;">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
            </svg>
            <?php esc_html_e( 'Back to Users', 'reseller-management' ); ?>
        </a>
    </div>
    <div class="rm-user-view-toolbar-center">
        <a href="<?php echo esc_url( $rm_orders_url ); ?>" class="rm-toolbar-btn rm-toolbar-btn--orders">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" width="17" height="17">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>
            </svg>
            <?php esc_html_e( 'Orders', 'reseller-management' ); ?>
            <span class="rm-toolbar-badge"><?php echo esc_html( (string) $rm_orders_count ); ?></span>
        </a>
        <a href="<?php echo esc_url( $rm_statements_url ); ?>" class="rm-toolbar-btn rm-toolbar-btn--statements">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" width="17" height="17">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z"/>
            </svg>
            <?php esc_html_e( 'Statements', 'reseller-management' ); ?>
        </a>
    </div>
    <div class="rm-user-view-toolbar-right" aria-hidden="true"></div>
</div>

<!-- Page header row -->
<div class="rm-view-header">
    <div>
        <h1 class="rm-view-title"><?php echo esc_html( $rm_user->display_name ); ?></h1>
        <p class="rm-view-subtitle"><?php echo esc_html( $rm_user->user_email ); ?></p>
    </div>
    <span class="rm-status-badge <?php echo esc_attr( $badge_class ); ?>" style="font-size:13px;padding:6px 16px;">
        <?php echo esc_html( 'approved' === $rm_status ? __( 'Active', 'reseller-management' ) : ucfirst( $rm_status ) ); ?>
    </span>
</div>

<!-- Two-column grid: profile card | details card -->
<div class="rm-view-grid">

    <!-- ── Left: profile summary ── -->
    <div>
        <div class="rm-card" style="text-align:center;">
            <p class="rm-card-title"><?php esc_html_e( 'Reseller Profile', 'reseller-management' ); ?></p>
            <div class="rm-profile-card-inner">
                <div class="rm-big-avatar<?php echo ( $avatar_id && wp_attachment_is_image( $avatar_id ) ) ? ' rm-big-avatar--has-photo' : ''; ?>">
                    <?php if ( $avatar_id && wp_attachment_is_image( $avatar_id ) ) : ?>
                        <?php echo wp_get_attachment_image( $avatar_id, [ 160, 160 ], false, [ 'alt' => '' ] ); ?>
                    <?php else : ?>
                        <?php echo esc_html( $avatar_letter ); ?>
                    <?php endif; ?>
                </div>
                <p class="rm-profile-name"><?php echo esc_html( $rm_user->display_name ); ?></p>
                <p class="rm-profile-email"><?php echo esc_html( $rm_user->user_email ); ?></p>
                <p class="rm-profile-joined">
                    <?php
                    printf(
                        /* translators: %s = date */
                        esc_html__( 'Joined %s', 'reseller-management' ),
                        esc_html( $joined_date )
                    );
                    ?>
                </p>
                <hr style="border:none;border-top:1px solid #f0f0f0;margin:16px 0;">
                <p class="rm-profile-balance"><?php echo wp_kses_post( function_exists( 'wc_price' ) ? wc_price( $rm_balance ) : esc_html( number_format( $rm_balance, 2 ) ) ); ?></p>
                <p class="rm-profile-balance-label"><?php esc_html_e( 'Current Balance', 'reseller-management' ); ?></p>
            </div>
        </div>

        <!-- Quick stats -->
        <div class="rm-card" style="margin-top:18px;">
            <p class="rm-card-title"><?php esc_html_e( 'Quick Info', 'reseller-management' ); ?></p>
            <ul class="rm-info-list">
                <li>
                    <span class="rm-info-label"><?php esc_html_e( 'User ID', 'reseller-management' ); ?></span>
                    <span class="rm-info-value">#<?php echo esc_html( (string) $rm_reseller_id ); ?></span>
                </li>
                <li>
                    <span class="rm-info-label"><?php esc_html_e( 'Username', 'reseller-management' ); ?></span>
                    <span class="rm-info-value"><?php echo esc_html( $rm_user->user_login ); ?></span>
                </li>
                <li>
                    <span class="rm-info-label"><?php esc_html_e( 'Role', 'reseller-management' ); ?></span>
                    <span class="rm-info-value">
                        <span class="rm-role-badge reseller" style="font-size:11px;">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
                            </svg>
                            <?php esc_html_e( 'Reseller', 'reseller-management' ); ?>
                        </span>
                    </span>
                </li>
                <li>
                    <span class="rm-info-label"><?php esc_html_e( 'Status', 'reseller-management' ); ?></span>
                    <span class="rm-info-value">
                        <span class="rm-status-badge <?php echo esc_attr( $badge_class ); ?>">
                            <?php echo esc_html( 'approved' === $rm_status ? __( 'Active', 'reseller-management' ) : ucfirst( $rm_status ) ); ?>
                        </span>
                    </span>
                </li>
                <?php if ( $rm_banned_until > time() ) : ?>
                <li>
                    <span class="rm-info-label"><?php esc_html_e( 'Banned Until', 'reseller-management' ); ?></span>
                    <span class="rm-info-value" style="color:#7c3aed;">
                        <?php echo esc_html( date_i18n( get_option( 'date_format' ), $rm_banned_until ) ); ?>
                    </span>
                </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <!-- ── Right: contact + documents ── -->
    <div style="display:flex;flex-direction:column;gap:18px;">

        <!-- Contact & business details -->
        <div class="rm-card">
            <p class="rm-card-title"><?php esc_html_e( 'Contact & Business Details', 'reseller-management' ); ?></p>
            <ul class="rm-info-list">
                <li>
                    <span class="rm-info-label">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:14px;height:14px;vertical-align:middle;margin-right:4px;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0 1 12 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 0 1-.673-.38m0 0A2.18 2.18 0 0 1 3 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 0 1 3.413-.387m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.894m7.5 0a48.667 48.667 0 0 0-7.5 0M12 12.75h.008v.008H12v-.008Z"/>
                        </svg>
                        <?php esc_html_e( 'Business Name', 'reseller-management' ); ?>
                    </span>
                    <span class="rm-info-value"><?php echo $rm_business_name ? esc_html( $rm_business_name ) : '<span style="color:#d1d5db;">—</span>'; ?></span>
                </li>
                <li>
                    <span class="rm-info-label">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:14px;height:14px;vertical-align:middle;margin-right:4px;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/>
                        </svg>
                        <?php esc_html_e( 'Phone', 'reseller-management' ); ?>
                    </span>
                    <span class="rm-info-value"><?php echo $rm_phone ? esc_html( $rm_phone ) : '<span style="color:#d1d5db;">—</span>'; ?></span>
                </li>
                <li>
                    <span class="rm-info-label">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:14px;height:14px;vertical-align:middle;margin-right:4px;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/>
                        </svg>
                        <?php esc_html_e( 'Email', 'reseller-management' ); ?>
                    </span>
                    <span class="rm-info-value"><?php echo esc_html( $rm_user->user_email ); ?></span>
                </li>
                <li>
                    <span class="rm-info-label">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:14px;height:14px;vertical-align:middle;margin-right:4px;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244"/>
                        </svg>
                        <?php esc_html_e( 'Facebook URL', 'reseller-management' ); ?>
                    </span>
                    <span class="rm-info-value">
                        <?php if ( $rm_fb_url ) : ?>
                            <a href="<?php echo esc_url( $rm_fb_url ); ?>" target="_blank" rel="noreferrer noopener">
                                <?php esc_html_e( 'Open Link', 'reseller-management' ); ?>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:12px;height:12px;vertical-align:middle;">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/>
                                </svg>
                            </a>
                        <?php else : ?>
                            <span style="color:#d1d5db;">—</span>
                        <?php endif; ?>
                    </span>
                </li>
                <li>
                    <span class="rm-info-label">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:14px;height:14px;vertical-align:middle;margin-right:4px;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253M12 10.5a14.94 14.94 0 0 1 4.243.572"/>
                        </svg>
                        <?php esc_html_e( 'Website URL', 'reseller-management' ); ?>
                    </span>
                    <span class="rm-info-value">
                        <?php if ( $rm_web_url ) : ?>
                            <a href="<?php echo esc_url( $rm_web_url ); ?>" target="_blank" rel="noreferrer noopener">
                                <?php esc_html_e( 'Open Link', 'reseller-management' ); ?>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:12px;height:12px;vertical-align:middle;">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/>
                                </svg>
                            </a>
                        <?php else : ?>
                            <span style="color:#d1d5db;">—</span>
                        <?php endif; ?>
                    </span>
                </li>
            </ul>
        </div>

        <!-- NID documents -->
        <div class="rm-card">
            <p class="rm-card-title"><?php esc_html_e( 'Identity Documents (NID)', 'reseller-management' ); ?></p>
            <div class="rm-nid-grid">
                <div class="rm-nid-doc">
                    <?php if ( $rm_front_id ) : ?>
                        <a href="<?php echo esc_url( (string) wp_get_attachment_url( $rm_front_id ) ); ?>" target="_blank" rel="noreferrer">
                            <?php echo wp_get_attachment_image( $rm_front_id, 'medium', false, [ 'style' => 'max-width:100%;height:auto;border-radius:6px;' ] ); ?>
                        </a>
                    <?php else : ?>
                        <div class="rm-nid-empty">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>
                            </svg>
                            <p><?php esc_html_e( 'Not uploaded', 'reseller-management' ); ?></p>
                        </div>
                    <?php endif; ?>
                    <p class="rm-nid-label"><?php esc_html_e( 'NID Front', 'reseller-management' ); ?></p>
                </div>

                <div class="rm-nid-doc">
                    <?php if ( $rm_back_id ) : ?>
                        <a href="<?php echo esc_url( (string) wp_get_attachment_url( $rm_back_id ) ); ?>" target="_blank" rel="noreferrer">
                            <?php echo wp_get_attachment_image( $rm_back_id, 'medium', false, [ 'style' => 'max-width:100%;height:auto;border-radius:6px;' ] ); ?>
                        </a>
                    <?php else : ?>
                        <div class="rm-nid-empty">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>
                            </svg>
                            <p><?php esc_html_e( 'Not uploaded', 'reseller-management' ); ?></p>
                        </div>
                    <?php endif; ?>
                    <p class="rm-nid-label"><?php esc_html_e( 'NID Back', 'reseller-management' ); ?></p>
                </div>
            </div>
        </div>

    </div><!-- right column -->
</div><!-- .rm-view-grid -->

<!-- ── Status management ── -->
<div class="rm-card" style="margin-bottom:18px;">
    <p class="rm-card-title"><?php esc_html_e( 'Approval Actions', 'reseller-management' ); ?></p>
    <p style="font-size:13.5px;color:#6b7280;margin:0 0 16px;">
        <?php esc_html_e( 'Change the reseller\'s approval status. Approving removes any active ban.', 'reseller-management' ); ?>
    </p>
    <div class="rm-status-section">
        <a href="<?php echo esc_url( $approve_url ); ?>" class="rm-status-btn approve">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:15px;height:15px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
            </svg>
            <?php esc_html_e( 'Approve', 'reseller-management' ); ?>
        </a>
        <a href="<?php echo esc_url( $reject_url ); ?>" class="rm-status-btn reject">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:15px;height:15px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
            </svg>
            <?php esc_html_e( 'Reject', 'reseller-management' ); ?>
        </a>
        <a href="<?php echo esc_url( $pending_url ); ?>" class="rm-status-btn set-pending">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:15px;height:15px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
            </svg>
            <?php esc_html_e( 'Set Pending', 'reseller-management' ); ?>
        </a>
    </div>
</div>

<!-- ── Ban controls ── -->
<div class="rm-card">
    <p class="rm-card-title" id="rm-ban-controls"><?php esc_html_e( 'Ban Controls', 'reseller-management' ); ?></p>
    <p style="font-size:13.5px;color:#6b7280;margin:0 0 4px;">
        <?php esc_html_e( 'Set a ban expiry date to restrict this reseller\'s access until the specified date.', 'reseller-management' ); ?>
    </p>
    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
        <input type="hidden" name="action" value="rm_update_reseller_ban">
        <input type="hidden" name="reseller_id" value="<?php echo esc_attr( (string) $rm_reseller_id ); ?>">
        <?php wp_nonce_field( 'rm_update_reseller_ban_' . $rm_reseller_id ); ?>

        <div class="rm-ban-form-row">
            <div class="rm-label-group">
                <label for="rm_banned_until"><?php esc_html_e( 'Ban Until', 'reseller-management' ); ?></label>
                <input
                    type="date"
                    id="rm_banned_until"
                    name="banned_until"
                    class="rm-date-input"
                    value="<?php echo $rm_banned_until > time() ? esc_attr( gmdate( 'Y-m-d', $rm_banned_until ) ) : ''; ?>">
            </div>
            <button type="submit" class="rm-ban-btn">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:15px;height:15px;vertical-align:middle;margin-right:4px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636"/>
                </svg>
                <?php esc_html_e( 'Save Ban Date', 'reseller-management' ); ?>
            </button>
            <button type="submit" name="clear_ban" value="1" class="rm-clear-btn">
                <?php esc_html_e( 'Clear Ban', 'reseller-management' ); ?>
            </button>
        </div>
    </form>
</div>
