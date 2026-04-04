<?php
/**
 * Reseller Hub – Withdrawals content template.
 *
 * Variables provided by the render method:
 *   $rm_withdrawals (array)
 */

defined( 'ABSPATH' ) || exit;

$rm_withdrawals = isset( $rm_withdrawals ) ? (array) $rm_withdrawals : [];
?>

<div class="rm-page-header">
    <h1 class="rm-page-title"><?php esc_html_e( 'Withdrawal Requests', 'reseller-management' ); ?></h1>
</div>
<p style="font-size:13.5px;color:#6b7280;margin:0 0 20px;">
    <?php esc_html_e( 'Review pending payout requests and mark them as paid after off-site settlement.', 'reseller-management' ); ?>
</p>

<div class="rm-users-table-wrap">
    <table class="rm-withdrawals-table">
        <thead>
            <tr>
                <th><?php esc_html_e( 'Reseller', 'reseller-management' ); ?></th>
                <th><?php esc_html_e( 'Amount', 'reseller-management' ); ?></th>
                <th><?php esc_html_e( 'Method', 'reseller-management' ); ?></th>
                <th><?php esc_html_e( 'Account Details', 'reseller-management' ); ?></th>
                <th><?php esc_html_e( 'Status', 'reseller-management' ); ?></th>
                <th><?php esc_html_e( 'Requested At', 'reseller-management' ); ?></th>
                <th><?php esc_html_e( 'Action', 'reseller-management' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ( empty( $rm_withdrawals ) ) : ?>
                <tr>
                    <td colspan="7">
                        <div class="rm-empty-state">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z"/>
                            </svg>
                            <p><?php esc_html_e( 'No withdrawal requests found yet.', 'reseller-management' ); ?></p>
                        </div>
                    </td>
                </tr>
            <?php else : ?>
                <?php foreach ( $rm_withdrawals as $w ) :
                    $w_user = get_user_by( 'id', (int) $w->reseller_id );
                ?>
                <tr>
                    <td>
                        <div class="rm-user-name-cell">
                            <div class="rm-user-avatar" style="width:32px;height:32px;font-size:13px;">
                                <?php echo $w_user ? esc_html( strtoupper( mb_substr( $w_user->display_name, 0, 1 ) ) ) : '?'; ?>
                            </div>
                            <span style="font-weight:600;">
                                <?php echo $w_user ? esc_html( $w_user->display_name ) : '#' . (int) $w->reseller_id; ?>
                            </span>
                        </div>
                    </td>
                    <td style="font-weight:600;color:#005f5a;">
                        <?php echo function_exists( 'wc_price' ) ? wp_kses_post( wc_price( (float) $w->amount ) ) : esc_html( number_format( (float) $w->amount, 2 ) ); ?>
                    </td>
                    <td><?php echo esc_html( (string) $w->payment_method ); ?></td>
                    <td><?php echo esc_html( (string) $w->account_details ); ?></td>
                    <td>
                        <span class="rm-status-badge <?php echo 'completed' === $w->status ? 'approved' : 'pending'; ?>">
                            <?php echo esc_html( ucfirst( (string) $w->status ) ); ?>
                        </span>
                    </td>
                    <td style="color:#6b7280;font-size:13px;"><?php echo esc_html( (string) $w->created_at ); ?></td>
                    <td>
                        <?php if ( 'completed' !== $w->status ) : ?>
                            <?php
                            $pay_url = wp_nonce_url(
                                admin_url( 'admin-post.php?action=rm_mark_withdrawal_paid&withdrawal_id=' . absint( $w->id ) ),
                                'rm_mark_withdrawal_paid_' . absint( $w->id )
                            );
                            ?>
                            <a href="<?php echo esc_url( $pay_url ); ?>" class="rm-pay-btn">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:14px;height:14px;">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                                </svg>
                                <?php esc_html_e( 'Mark Paid', 'reseller-management' ); ?>
                            </a>
                        <?php else : ?>
                            <span style="color:#9ca3af;font-size:13px;"><?php esc_html_e( 'Completed', 'reseller-management' ); ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
