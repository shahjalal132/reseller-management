<?php
/**
 * Reseller Hub – Reseller Statements page.
 *
 * Variables injected by render_user_statements_page():
 *   $rm_reseller_id (int)
 *   $rm_user        (WP_User)
 *   $rm_balance     (float)
 */

defined( 'ABSPATH' ) || exit;

$rm_reseller_id = isset( $rm_reseller_id ) ? (int) $rm_reseller_id : 0;
$rm_user        = isset( $rm_user )        ? $rm_user              : null;
$rm_balance     = isset( $rm_balance )     ? (float) $rm_balance   : 0.0;

if ( ! $rm_user ) {
    wp_die( esc_html__( 'Invalid reseller.', 'reseller-management' ) );
}

$back_url = admin_url( 'admin.php?page=reseller-hub-user-view&reseller_id=' . $rm_reseller_id );

// ── Dummy statement rows ────────────────────────────────────────────────────
// Each entry: date, type (credit|debit), description, amount, running_balance
$dummy_statements = [
    [
        'date'            => '2026-04-01',
        'type'            => 'credit',
        'description'     => 'Commission — Order #10241 (Delivered)',
        'amount'          => 520.00,
        'running_balance' => 3870.50,
        'ref'             => 'ORD-10241',
    ],
    [
        'date'            => '2026-03-30',
        'type'            => 'debit',
        'description'     => 'Withdrawal — bKash to 01712-345678',
        'amount'          => -1500.00,
        'running_balance' => 3350.50,
        'ref'             => 'WD-8812',
    ],
    [
        'date'            => '2026-03-28',
        'type'            => 'credit',
        'description'     => 'Commission — Order #10238 (Delivered)',
        'amount'          => 380.00,
        'running_balance' => 4850.50,
        'ref'             => 'ORD-10238',
    ],
    [
        'date'            => '2026-03-25',
        'type'            => 'credit',
        'description'     => 'Commission — Order #10230 (Delivered)',
        'amount'          => 250.00,
        'running_balance' => 4470.50,
        'ref'             => 'ORD-10230',
    ],
    [
        'date'            => '2026-03-22',
        'type'            => 'debit',
        'description'     => 'Shipping deduction — Order #10221',
        'amount'          => -60.00,
        'running_balance' => 4220.50,
        'ref'             => 'ORD-10221',
    ],
    [
        'date'            => '2026-03-20',
        'type'            => 'credit',
        'description'     => 'Commission — Order #10221 (Delivered)',
        'amount'          => 640.00,
        'running_balance' => 4280.50,
        'ref'             => 'ORD-10221',
    ],
    [
        'date'            => '2026-03-18',
        'type'            => 'debit',
        'description'     => 'Withdrawal — Nagad to 01811-987654',
        'amount'          => -2000.00,
        'running_balance' => 3640.50,
        'ref'             => 'WD-8791',
    ],
    [
        'date'            => '2026-03-15',
        'type'            => 'credit',
        'description'     => 'Commission — Order #10209 (Delivered)',
        'amount'          => 410.00,
        'running_balance' => 5640.50,
        'ref'             => 'ORD-10209',
    ],
    [
        'date'            => '2026-03-12',
        'type'            => 'credit',
        'description'     => 'Bonus — March performance incentive',
        'amount'          => 500.00,
        'running_balance' => 5230.50,
        'ref'             => 'BONUS-MAR26',
    ],
    [
        'date'            => '2026-03-10',
        'type'            => 'debit',
        'description'     => 'Shipping deduction — Order #10198',
        'amount'          => -80.00,
        'running_balance' => 4730.50,
        'ref'             => 'ORD-10198',
    ],
    [
        'date'            => '2026-03-08',
        'type'            => 'credit',
        'description'     => 'Commission — Order #10198 (Delivered)',
        'amount'          => 290.00,
        'running_balance' => 4810.50,
        'ref'             => 'ORD-10198',
    ],
    [
        'date'            => '2026-03-05',
        'type'            => 'credit',
        'description'     => 'Commission — Order #10185 (Delivered)',
        'amount'          => 175.00,
        'running_balance' => 4520.50,
        'ref'             => 'ORD-10185',
    ],
];

// Summary stats
$total_credits = array_sum( array_column(
    array_filter( $dummy_statements, fn( $r ) => $r['type'] === 'credit' ),
    'amount'
) );
$total_debits = abs( array_sum( array_column(
    array_filter( $dummy_statements, fn( $r ) => $r['type'] === 'debit' ),
    'amount'
) ) );

$fmt = function ( $amount ) {
    return '৳' . number_format( abs( $amount ), 2 );
};
?>

<!-- Back link -->
<a href="<?php echo esc_url( $back_url ); ?>" class="rm-back-btn">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
    </svg>
    <?php
    printf(
        /* translators: %s reseller display name */
        esc_html__( 'Back to %s', 'reseller-management' ),
        esc_html( $rm_user->display_name )
    );
    ?>
</a>

<!-- Page header -->
<div class="rm-page-header" style="margin-bottom:24px;">
    <div>
        <h1 class="rm-page-title">
            <?php
            printf(
                /* translators: %s reseller name */
                esc_html__( 'Account Statement — %s', 'reseller-management' ),
                esc_html( $rm_user->display_name )
            );
            ?>
        </h1>
        <p style="font-size:13.5px;color:#6b7280;margin:4px 0 0;">
            <?php esc_html_e( 'Full ledger history of credits, commissions, and withdrawals.', 'reseller-management' ); ?>
        </p>
    </div>
    <div class="rm-statement-export-hint">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" width="15" height="15">
            <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/>
        </svg>
        <?php esc_html_e( 'Showing dummy data for demonstration', 'reseller-management' ); ?>
    </div>
</div>

<!-- Summary cards -->
<div class="rm-statement-summary">

    <div class="rm-stmt-card rm-stmt-card--balance">
        <div class="rm-stmt-card-icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
            </svg>
        </div>
        <div class="rm-stmt-card-body">
            <span class="rm-stmt-card-label"><?php esc_html_e( 'Current Balance', 'reseller-management' ); ?></span>
            <span class="rm-stmt-card-value">
                <?php echo esc_html( $fmt( $rm_balance > 0 ? $rm_balance : 3870.50 ) ); ?>
            </span>
        </div>
    </div>

    <div class="rm-stmt-card rm-stmt-card--credits">
        <div class="rm-stmt-card-icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941"/>
            </svg>
        </div>
        <div class="rm-stmt-card-body">
            <span class="rm-stmt-card-label"><?php esc_html_e( 'Total Credits', 'reseller-management' ); ?></span>
            <span class="rm-stmt-card-value"><?php echo esc_html( $fmt( $total_credits ) ); ?></span>
        </div>
    </div>

    <div class="rm-stmt-card rm-stmt-card--debits">
        <div class="rm-stmt-card-icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6 9 12.75l4.286-4.286a11.948 11.948 0 0 1 4.306 6.43l.776 2.898m0 0 3.182-5.511m-3.182 5.51-5.511-3.181"/>
            </svg>
        </div>
        <div class="rm-stmt-card-body">
            <span class="rm-stmt-card-label"><?php esc_html_e( 'Total Debits', 'reseller-management' ); ?></span>
            <span class="rm-stmt-card-value"><?php echo esc_html( $fmt( $total_debits ) ); ?></span>
        </div>
    </div>

    <div class="rm-stmt-card rm-stmt-card--transactions">
        <div class="rm-stmt-card-icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>
            </svg>
        </div>
        <div class="rm-stmt-card-body">
            <span class="rm-stmt-card-label"><?php esc_html_e( 'Total Transactions', 'reseller-management' ); ?></span>
            <span class="rm-stmt-card-value"><?php echo esc_html( (string) count( $dummy_statements ) ); ?></span>
        </div>
    </div>

</div>

<!-- Statement table -->
<div class="rm-section-card" style="margin-top:20px;">
    <div class="rm-section-card-header">
        <p class="rm-section-card-title"><?php esc_html_e( 'Transaction Ledger', 'reseller-management' ); ?></p>
        <span style="font-size:12px;color:#9ca3af;font-style:italic;"><?php esc_html_e( 'Showing sample data', 'reseller-management' ); ?></span>
    </div>

    <table class="rm-users-table rm-stmt-table">
        <thead>
            <tr>
                <th><?php esc_html_e( 'Date', 'reseller-management' ); ?></th>
                <th><?php esc_html_e( 'Reference', 'reseller-management' ); ?></th>
                <th><?php esc_html_e( 'Description', 'reseller-management' ); ?></th>
                <th><?php esc_html_e( 'Type', 'reseller-management' ); ?></th>
                <th style="text-align:right;"><?php esc_html_e( 'Amount', 'reseller-management' ); ?></th>
                <th style="text-align:right;"><?php esc_html_e( 'Running Balance', 'reseller-management' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ( $dummy_statements as $stmt ) :
                $is_credit   = $stmt['type'] === 'credit';
                $type_class  = $is_credit ? 'rm-stmt-type--credit' : 'rm-stmt-type--debit';
                $type_label  = $is_credit ? __( 'Credit', 'reseller-management' ) : __( 'Debit', 'reseller-management' );
                $amount_sign = $is_credit ? '+' : '−';
                $amount_cls  = $is_credit ? 'rm-stmt-amount--credit' : 'rm-stmt-amount--debit';
            ?>
            <tr>
                <td style="white-space:nowrap;color:#6b7280;font-size:13px;">
                    <?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $stmt['date'] ) ) ); ?>
                </td>
                <td>
                    <code class="rm-stmt-ref"><?php echo esc_html( $stmt['ref'] ); ?></code>
                </td>
                <td style="font-size:13.5px;color:#374151;"><?php echo esc_html( $stmt['description'] ); ?></td>
                <td>
                    <span class="rm-stmt-type-badge <?php echo esc_attr( $type_class ); ?>">
                        <?php echo esc_html( $type_label ); ?>
                    </span>
                </td>
                <td style="text-align:right;">
                    <span class="rm-stmt-amount <?php echo esc_attr( $amount_cls ); ?>">
                        <?php echo esc_html( $amount_sign . ' ' . $fmt( $stmt['amount'] ) ); ?>
                    </span>
                </td>
                <td style="text-align:right;font-weight:600;color:#111827;">
                    <?php echo esc_html( $fmt( $stmt['running_balance'] ) ); ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
