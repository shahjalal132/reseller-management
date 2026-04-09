<?php
/**
 * Reseller Hub – Users list content template.
 *
 * Variables provided by the render method:
 *   $rm_users  (array) – list of reseller users
 */

defined( 'ABSPATH' ) || exit;

$rm_users  = isset( $rm_users ) ? $rm_users : [];
$rm_search = sanitize_text_field( wp_unslash( $_GET['rm_search'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
?>

<div class="rm-page-header">
    <h1 class="rm-page-title"><?php esc_html_e( 'User Management', 'reseller-management' ); ?></h1>
    <a href="<?php echo esc_url( admin_url( 'user-new.php' ) ); ?>" class="rm-btn-add">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor" style="width:15px;height:15px;">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
        </svg>
        <?php esc_html_e( 'Add User', 'reseller-management' ); ?>
    </a>
</div>

<div class="rm-users-table-wrap">

    <!-- Search bar -->
    <div class="rm-search-bar-row">
        <form method="get" action="">
            <input type="hidden" name="page" value="reseller-hub-users">
            <div class="rm-search-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
                </svg>
                <input
                    type="text"
                    name="rm_search"
                    value="<?php echo esc_attr( $rm_search ); ?>"
                    placeholder="<?php esc_attr_e( 'Search users by name or email…', 'reseller-management' ); ?>"
                    class="rm-search-input">
            </div>
        </form>
    </div>

    <!-- Table -->
    <table class="rm-users-table">
        <thead>
            <tr>
                <th><?php esc_html_e( 'Name', 'reseller-management' ); ?></th>
                <th><?php esc_html_e( 'Email', 'reseller-management' ); ?></th>
                <th><?php esc_html_e( 'Role', 'reseller-management' ); ?></th>
                <th><?php esc_html_e( 'Status', 'reseller-management' ); ?></th>
                <th><?php esc_html_e( 'Actions', 'reseller-management' ); ?></th>
            </tr>
        </thead>
        <tbody>

        <?php if ( empty( $rm_users ) ) : ?>
            <tr>
                <td colspan="5">
                    <div class="rm-empty-state">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/>
                        </svg>
                        <p><?php esc_html_e( 'No reseller users found.', 'reseller-management' ); ?></p>
                    </div>
                </td>
            </tr>
        <?php else : ?>
            <?php foreach ( $rm_users as $rm_u ) :
                $uid           = absint( $rm_u['ID'] );
                $avatar_id     = (int) get_user_meta( $uid, '_reseller_avatar_id', true );
                $avatar_letter = strtoupper( mb_substr( (string) $rm_u['name'], 0, 1 ) );
                $status        = (string) $rm_u['status'];
                $status_label  = 'approved' === $status ? __( 'active', 'reseller-management' ) : ucfirst( $status );

                $view_url       = admin_url( 'admin.php?page=reseller-hub-user-view&reseller_id=' . $uid );
                $edit_url       = admin_url( 'user-edit.php?user_id=' . $uid );
                $orders_url     = admin_url( 'admin.php?page=reseller-hub-user-orders&reseller_id=' . $uid );
                $statements_url = admin_url( 'admin.php?page=reseller-hub-user-statements&reseller_id=' . $uid );
                $balance_url    = admin_url( 'admin.php?page=reseller-hub-user-balance&reseller_id=' . $uid );
                $delete_url     = wp_nonce_url(
                    admin_url( 'admin-post.php?action=rm_delete_reseller&reseller_id=' . $uid ),
                    'rm_delete_reseller_' . $uid
                );
            ?>
            <tr>
                <td>
                    <div class="rm-user-name-cell">
                        <div class="rm-user-avatar<?php echo ( $avatar_id && wp_attachment_is_image( $avatar_id ) ) ? ' rm-user-avatar--has-photo' : ''; ?>">
                            <?php if ( $avatar_id && wp_attachment_is_image( $avatar_id ) ) : ?>
                                <?php echo wp_get_attachment_image( $avatar_id, [ 76, 76 ], false, [ 'alt' => '' ] ); ?>
                            <?php else : ?>
                                <?php echo esc_html( $avatar_letter ); ?>
                            <?php endif; ?>
                        </div>
                        <span class="rm-user-display-name"><?php echo esc_html( (string) $rm_u['name'] ); ?></span>
                    </div>
                </td>
                <td><?php echo esc_html( (string) $rm_u['email'] ); ?></td>
                <td>
                    <span class="rm-role-badge reseller">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
                        </svg>
                        <?php esc_html_e( 'Reseller', 'reseller-management' ); ?>
                    </span>
                </td>
                <td>
                    <span class="rm-status-badge <?php echo esc_attr( $status ); ?>">
                        <?php echo esc_html( $status_label ); ?>
                    </span>
                </td>
                <td>
                    <div class="rm-action-btns">
                        <!-- View -->
                        <a href="<?php echo esc_url( $view_url ); ?>"
                           class="rm-action-btn view"
                           title="<?php esc_attr_e( 'View Profile', 'reseller-management' ); ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                            </svg>
                        </a>
                        <!-- View Orders -->
                        <a href="<?php echo esc_url( $orders_url ); ?>"
                           class="rm-action-btn orders"
                           title="<?php esc_attr_e( 'View Orders', 'reseller-management' ); ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                            </svg>
                        </a>
                        <!-- View Statements -->
                        <a href="<?php echo esc_url( $statements_url ); ?>"
                           class="rm-action-btn statements"
                           title="<?php esc_attr_e( 'View Statements', 'reseller-management' ); ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                        </a>
                        <!-- Adjust balance -->
                        <a href="<?php echo esc_url( $balance_url ); ?>"
                           class="rm-action-btn balance"
                           title="<?php esc_attr_e( 'Add or deduct balance', 'reseller-management' ); ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 0 0-2.25-2.25H15a3 3 0 1 1-6 0H5.25A2.25 2.25 0 0 0 3 12m18 0v6a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 18v-6m18 0V9M3 12V9m18 3.75h-4.5m-9 0H3" />
                            </svg>
                        </a>
                        <!-- Edit -->
                        <a href="<?php echo esc_url( $edit_url ); ?>"
                           class="rm-action-btn edit"
                           title="<?php esc_attr_e( 'Edit User', 'reseller-management' ); ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/>
                            </svg>
                        </a>
                        <!-- Delete -->
                        <a href="<?php echo esc_url( $delete_url ); ?>"
                           class="rm-action-btn delete"
                           title="<?php esc_attr_e( 'Delete User', 'reseller-management' ); ?>"
                           onclick="return confirm('<?php echo esc_js( __( 'Are you sure you want to permanently delete this reseller? This cannot be undone.', 'reseller-management' ) ); ?>');">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
                            </svg>
                        </a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>

        </tbody>
    </table>
</div>
