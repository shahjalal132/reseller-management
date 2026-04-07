<?php
/**
 * Reseller Hub – Dashboard overview content template.
 *
 * Variables provided by the render method:
 *   $rm_total_resellers  (int)
 *   $rm_active_resellers (int)
 *   $rm_pending_resellers(int)
 *   $rm_recent_users     (array)
 */

defined( 'ABSPATH' ) || exit;

$rm_total_resellers   = isset( $rm_total_resellers )   ? (int) $rm_total_resellers   : 0;
$rm_active_resellers  = isset( $rm_active_resellers )  ? (int) $rm_active_resellers  : 0;
$rm_pending_resellers = isset( $rm_pending_resellers ) ? (int) $rm_pending_resellers : 0;
$rm_recent_users      = isset( $rm_recent_users )      ? (array) $rm_recent_users    : [];
?>

<h1 class="rm-page-title" style="margin-bottom:22px;"><?php esc_html_e( 'Dashboard Overview', 'reseller-management' ); ?></h1>

<!-- Stat cards -->
<div class="rm-dashboard-cards">

    <div class="rm-stat-card">
        <div class="rm-stat-icon teal">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/>
            </svg>
        </div>
        <div class="rm-stat-info">
            <div class="rm-stat-number"><?php echo esc_html( (string) $rm_total_resellers ); ?></div>
            <div class="rm-stat-label"><?php esc_html_e( 'Total Resellers', 'reseller-management' ); ?></div>
        </div>
    </div>

    <div class="rm-stat-card">
        <div class="rm-stat-icon green">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
            </svg>
        </div>
        <div class="rm-stat-info">
            <div class="rm-stat-number"><?php echo esc_html( (string) $rm_active_resellers ); ?></div>
            <div class="rm-stat-label"><?php esc_html_e( 'Active Resellers', 'reseller-management' ); ?></div>
        </div>
    </div>

    <div class="rm-stat-card">
        <div class="rm-stat-icon amber">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
            </svg>
        </div>
        <div class="rm-stat-info">
            <div class="rm-stat-number"><?php echo esc_html( (string) $rm_pending_resellers ); ?></div>
            <div class="rm-stat-label"><?php esc_html_e( 'Pending Approvals', 'reseller-management' ); ?></div>
        </div>
    </div>

    <div class="rm-stat-card">
        <div class="rm-stat-icon blue">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z"/>
            </svg>
        </div>
        <div class="rm-stat-info">
            <div class="rm-stat-number">
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=reseller-hub-withdrawals' ) ); ?>"
                   style="color:inherit;text-decoration:none;">
                    <?php esc_html_e( 'View', 'reseller-management' ); ?>
                </a>
            </div>
            <div class="rm-stat-label"><?php esc_html_e( 'Withdrawals', 'reseller-management' ); ?></div>
        </div>
    </div>

</div>

<!-- Recent resellers -->
<div class="rm-section-card">
    <div class="rm-section-card-header">
        <p class="rm-section-card-title"><?php esc_html_e( 'Recent Resellers', 'reseller-management' ); ?></p>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=reseller-hub-users' ) ); ?>" class="rm-view-all-link">
            <?php esc_html_e( 'View All →', 'reseller-management' ); ?>
        </a>
    </div>

    <table class="rm-users-table">
        <thead>
            <tr>
                <th><?php esc_html_e( 'Name', 'reseller-management' ); ?></th>
                <th><?php esc_html_e( 'Email', 'reseller-management' ); ?></th>
                <th><?php esc_html_e( 'Status', 'reseller-management' ); ?></th>
                <th><?php esc_html_e( 'Actions', 'reseller-management' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ( empty( $rm_recent_users ) ) : ?>
                <tr>
                    <td colspan="4">
                        <div class="rm-empty-state" style="padding:32px;">
                            <p><?php esc_html_e( 'No reseller users yet.', 'reseller-management' ); ?></p>
                        </div>
                    </td>
                </tr>
            <?php else : ?>
                <?php foreach ( $rm_recent_users as $ru ) :
                    $uid    = absint( $ru['ID'] );
                    $status = (string) $ru['status'];
                ?>
                <tr>
                    <td>
                        <div class="rm-user-name-cell">
                            <div class="rm-user-avatar"><?php echo esc_html( strtoupper( mb_substr( (string) $ru['name'], 0, 1 ) ) ); ?></div>
                            <span class="rm-user-display-name"><?php echo esc_html( (string) $ru['name'] ); ?></span>
                        </div>
                    </td>
                    <td><?php echo esc_html( (string) $ru['email'] ); ?></td>
                    <td>
                        <span class="rm-status-badge <?php echo esc_attr( $status ); ?>">
                            <?php echo esc_html( 'approved' === $status ? __( 'active', 'reseller-management' ) : ucfirst( $status ) ); ?>
                        </span>
                    </td>
                    <td>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=reseller-hub-user-view&reseller_id=' . $uid ) ); ?>"
                           class="rm-action-btn view" style="display:inline-flex;" title="<?php esc_attr_e( 'View Profile', 'reseller-management' ); ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                            </svg>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
