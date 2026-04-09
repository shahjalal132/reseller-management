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

// ── Pagination logic ──────────────────────────────────────────────────────
$paged    = max( 1, (int) ( $_GET['rm_paged'] ?? 1 ) );
$per_page = 20;
$offset   = ( $paged - 1 ) * $per_page;

$total_transactions = \BOILERPLATE\Inc\Reseller_Finance::get_total_transactions_count( $rm_reseller_id );
$total_pages        = max( 1, (int) ceil( $total_transactions / $per_page ) );
$paged              = min( $paged, $total_pages );
$offset             = ( $paged - 1 ) * $per_page;

// ── Real statement rows ────────────────────────────────────────────────────
$all_transactions = \BOILERPLATE\Inc\Reseller_Finance::get_transactions( $rm_reseller_id, $per_page, $offset );

// To calculate running balance correctly for this page, we need to know the starting balance.
// Starting balance for this page = Current Balance - Sum of transactions BEFORE this page.
$sum_before           = \BOILERPLATE\Inc\Reseller_Finance::get_transactions_sum_before_offset( $rm_reseller_id, $offset );
$current_temp_balance = $rm_balance - $sum_before;

// Calculate summary stats (for the whole history, we might need separate sums or just use the injected $rm_balance)
// The $total_credits and $total_debits shown in summary usually reflect the WHOLE history.
// However, the original code calculated them from $all_transactions (which was all rows).
// Since we now paginate, we should probably fetch the absolute totals for the summary cards.
// But for now, let's keep the logic simple if the user only wanted pagination for the table.
// Wait, the original code did:
$total_credits = 0.0;
$total_debits  = 0.0;

// If we want accurate summary cards, we need the full sums from DB.
global $wpdb;
$table = \BOILERPLATE\Inc\Reseller_Helper::get_ledger_table_name();
$summary_stats = $wpdb->get_row( $wpdb->prepare(
    "SELECT 
        SUM(CASE WHEN amount > 0 THEN amount ELSE 0 END) as total_credits,
        SUM(CASE WHEN amount < 0 THEN ABS(amount) ELSE 0 END) as total_debits
    FROM {$table} WHERE reseller_id = %d",
    $rm_reseller_id
) );

$total_credits = (float) ( $summary_stats->total_credits ?? 0 );
$total_debits  = (float) ( $summary_stats->total_debits  ?? 0 );

$processed_statements = [];
foreach ( $all_transactions as $tx ) {
    $amount = (float) $tx->amount;

    $processed_statements[] = [
        'date'            => $tx->created_at,
        'type'            => $amount >= 0 ? 'credit' : 'debit',
        'description'     => $tx->description,
        'amount'          => $amount,
        'running_balance' => $current_temp_balance,
        'ref'             => $tx->order_id ? 'ORD-' . $tx->order_id : 'TXN-' . $tx->id,
    ];

    // Subtract the amount to get the balance before this transaction.
    $current_temp_balance -= $amount;
}

// URL helper for pagination
$base_url = admin_url( 'admin.php?page=reseller-hub-user-statements&reseller_id=' . $rm_reseller_id );
$page_url = function ( $p ) use ( $base_url ) {
    return esc_url( add_query_arg( 'rm_paged', $p, $base_url ) );
};

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
                <?php echo esc_html( $fmt( $rm_balance ) ); ?>
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
            <span class="rm-stmt-card-value"><?php echo esc_html( (string) $total_transactions ); ?></span>
        </div>
    </div>

</div>

<!-- Statement table -->
<div class="rm-section-card" style="margin-top:20px;">
    <div class="rm-section-card-header">
        <p class="rm-section-card-title"><?php esc_html_e( 'Transaction Ledger', 'reseller-management' ); ?></p>
    </div>

    <div class="rm-table-responsive">
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
            <?php if ( empty( $processed_statements ) ) : ?>
                <tr>
                    <td colspan="6" style="text-align:center;padding:40px;color:#9ca3af;">
                        <?php esc_html_e( 'No transactions found for this reseller.', 'reseller-management' ); ?>
                    </td>
                </tr>
            <?php else : ?>
                <?php foreach ( $processed_statements as $stmt ) :
                    $is_credit   = $stmt['type'] === 'credit';
                    $type_class  = $is_credit ? 'rm-stmt-type--credit' : 'rm-stmt-type--debit';
                    $type_label  = $is_credit ? __( 'Credit', 'reseller-management' ) : __( 'Debit', 'reseller-management' );
                    $amount_sign = $is_credit ? '+' : '−';
                    $amount_cls  = $is_credit ? 'rm-stmt-amount--credit' : 'rm-stmt-amount--debit';
                ?>
                <tr>
                    <td style="white-space:nowrap;color:#6b7280;font-size:13px;">
                        <?php echo esc_html( date_i18n( get_option( 'date_format' ) . ', h:i A', strtotime( $stmt['date'] ) ) ); ?>
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
            <?php endif; ?>
        </tbody>
    </table>
    </div>

    <!-- Pagination -->
    <?php if ( $total_pages > 1 ) : ?>
    <div class="rm-pagination" style="padding: 15px 20px; border-top: 1px solid #f3f4f6;">
        <div class="rm-pagination-info" style="font-size: 13px; color: #6b7280;">
            <?php
            $from_item = $offset + 1;
            $to_item   = min( $offset + $per_page, $total_transactions );
            printf(
                /* translators: 1: from, 2: to, 3: total */
                esc_html__( 'Showing %1$d–%2$d of %3$d transactions', 'reseller-management' ),
                (int) $from_item,
                (int) $to_item,
                (int) $total_transactions
            );
            ?>
        </div>
        <div class="rm-pagination-links">
            <?php if ( $paged > 1 ) : ?>
                <a href="<?php echo $page_url( $paged - 1 ); ?>" class="rm-page-btn rm-page-btn--prev">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" width="15" height="15">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/>
                    </svg>
                </a>
            <?php endif; ?>

            <?php
            $range = 2;
            for ( $i = 1; $i <= $total_pages; $i++ ) :
                if ( $i === 1 || $i === $total_pages || abs( $i - $paged ) <= $range ) :
            ?>
                <a href="<?php echo $page_url( $i ); ?>"
                   class="rm-page-btn<?php echo $i === $paged ? ' rm-page-btn--active' : ''; ?>">
                    <?php echo esc_html( (string) $i ); ?>
                </a>
            <?php
                elseif ( abs( $i - $paged ) === $range + 1 ) :
            ?>
                <span class="rm-page-ellipsis">…</span>
            <?php
                endif;
            endfor;
            ?>

            <?php if ( $paged < $total_pages ) : ?>
                <a href="<?php echo $page_url( $paged + 1 ); ?>" class="rm-page-btn rm-page-btn--next">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" width="15" height="15">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
                    </svg>
                </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
