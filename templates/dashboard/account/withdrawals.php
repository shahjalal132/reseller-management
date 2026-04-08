<?php
/**
 * Account → Withdrawals sub-template.
 *
 * @package reseller-management
 */

defined( 'ABSPATH' ) || exit;

/* ── Helpers ────────────────────────────────────────────── */
$reseller_id     = get_current_user_id();
$live_data       = \BOILERPLATE\Inc\Reseller_Finance::get_withdrawals( $reseller_id );
$all_withdrawals = is_array( $live_data ) ? $live_data : [];

/* Inject txn_id into live data rows that don't have one */
foreach ( $all_withdrawals as &$wd ) {
    if ( ! empty( $wd->transaction_id ) ) {
        $wd->txn_id = (string) $wd->transaction_id;
    } elseif ( empty( $wd->txn_id ) ) {
        $wd->txn_id = 'TXN-' . strtoupper( substr( md5( 'wd' . $wd->id ), 0, 8 ) );
    }
}
unset( $wd );

/* ── Label maps ─────────────────────────────────────────── */
$status_styles = [
    'pending'  => [ 'bg' => '#fff7ed', 'text' => '#c2410c', 'label' => 'Pending' ],
    'approved' => [ 'bg' => '#f0fdf4', 'text' => '#15803d', 'label' => 'Approved' ],
    'rejected' => [ 'bg' => '#fff1f2', 'text' => '#be123c', 'label' => 'Rejected' ],
];
$method_labels = [
    'bkash'  => [ 'bg' => '#fce4ec', 'text' => '#c2185b', 'label' => 'Bkash',  'icon' => 'B' ],
    'nagad'  => [ 'bg' => '#fff3e0', 'text' => '#e65100', 'label' => 'Nagad',  'icon' => 'N' ],
    'rocket' => [ 'bg' => '#ede7f6', 'text' => '#512da8', 'label' => 'Rocket', 'icon' => 'R' ],
    'bank'   => [ 'bg' => '#e3f2fd', 'text' => '#1565c0', 'label' => 'Bank',   'icon' => '🏦' ],
];

/* ── View-detail mode: ?wd_id=N ─────────────────────────── */
$view_id = (int) ( $_GET['wd_id'] ?? 0 );
$view_wd = null;
if ( $view_id > 0 ) {
    foreach ( $all_withdrawals as $wd ) {
        if ( (int) $wd->id === $view_id ) {
            $view_wd = $wd;
            break;
        }
    }
}

/* Current page URL builder */
global $post;
$base_url = ( $post instanceof \WP_Post ) ? get_permalink( $post ) : home_url( '/' );
$list_url = add_query_arg( [ 'tab' => 'account', 'subtab' => 'withdrawals' ], $base_url );

/* ── If in detail view ──────────────────────────────────── */
if ( $view_wd ) :
    $sk  = strtolower( (string) $view_wd->status );
    $st  = $status_styles[ $sk ] ?? $status_styles['pending'];
    $mk  = strtolower( (string) $view_wd->payment_method );
    $ml  = $method_labels[ $mk ] ?? [ 'bg' => '#f1f5f9', 'text' => '#475569', 'label' => ucfirst( $mk ), 'icon' => '?' ];
    ?>
<style>
.rm-wdv-wrap {
    max-width: 680px;
    margin: 0 auto;
}
.rm-wdv-back-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: #64748b;
    font-size: 0.85rem;
    font-weight: 600;
    text-decoration: none;
    margin-bottom: 22px;
    transition: color 0.2s;
}
.rm-wdv-back-btn:hover { color: #1e293b; }
.rm-wdv-card {
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 4px 24px rgba(0,0,0,0.08);
    border: 1px solid #e8edf3;
    overflow: hidden;
}
.rm-wdv-card-header {
    background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%);
    padding: 28px 32px;
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
}
.rm-wdv-card-header-left h2 {
    margin: 0 0 6px;
    font-size: 1.45rem;
    font-weight: 800;
    color: #fff;
    letter-spacing: -0.3px;
}
.rm-wdv-card-header-left p {
    margin: 0;
    font-size: 0.8rem;
    color: rgba(255,255,255,0.55);
    font-weight: 500;
}
.rm-wdv-status-pill {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    border-radius: 30px;
    padding: 7px 18px;
    font-size: 0.8rem;
    font-weight: 700;
    letter-spacing: 0.3px;
    backdrop-filter: blur(4px);
}
.rm-wdv-status-pill::before {
    content:'';
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: currentColor;
    opacity: 0.8;
    animation: rmPulse 1.8s ease-in-out infinite;
}
@keyframes rmPulse {
    0%,100% { opacity: 0.8; }
    50%      { opacity: 0.3; }
}

/* Amount Hero */
.rm-wdv-amount-hero {
    text-align: center;
    padding: 32px 32px 24px;
    border-bottom: 1px solid #f1f5f9;
}
.rm-wdv-amount-hero .label {
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #94a3b8;
    margin-bottom: 8px;
}
.rm-wdv-amount-hero .amount {
    font-size: 2.8rem;
    font-weight: 900;
    color: #0f172a;
    letter-spacing: -1px;
    line-height: 1;
}
.rm-wdv-amount-hero .currency {
    font-size: 1.4rem;
    font-weight: 700;
    color: #64748b;
    margin-right: 4px;
}

/* Detail Grid */
.rm-wdv-details {
    padding: 8px 32px 28px;
}
.rm-wdv-detail-item {
    display: flex;
    align-items: flex-start;
    padding: 16px 0;
    border-bottom: 1px solid #f8fafc;
    gap: 16px;
}
.rm-wdv-detail-item:last-child { border-bottom: none; }
.rm-wdv-detail-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    flex-shrink: 0;
    background: #f8fafc;
}
.rm-wdv-detail-text { flex: 1; }
.rm-wdv-detail-text .key {
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    color: #94a3b8;
    margin-bottom: 4px;
}
.rm-wdv-detail-text .val {
    font-size: 0.95rem;
    font-weight: 700;
    color: #1e293b;
}
.rm-wdv-method-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border-radius: 20px;
    padding: 4px 14px;
    font-size: 0.8rem;
    font-weight: 700;
}
.rm-wdv-footer {
    background: #f8fafc;
    padding: 16px 32px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-top: 1px solid #e8edf3;
}
.rm-wdv-footer-id {
    font-size: 0.75rem;
    color: #94a3b8;
    font-weight: 600;
}
.rm-wdv-footer-id strong { color: #475569; }
.rm-wdv-print-btn {
    background: #0f172a;
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 8px 18px;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: opacity 0.2s;
}
.rm-wdv-print-btn:hover { opacity: 0.85; }
</style>

<div class="rm-wdv-wrap">
    <a href="<?php echo esc_url( $list_url ); ?>" class="rm-wdv-back-btn">
        ← <?php esc_html_e( 'Back to Withdrawals', 'reseller-management' ); ?>
    </a>

    <div class="rm-wdv-card" id="rm-wdv-print-area">
        <!-- Header Strip -->
        <div class="rm-wdv-card-header">
            <div class="rm-wdv-card-header-left">
                <h2><?php esc_html_e( 'Withdrawal Request', 'reseller-management' ); ?></h2>
                <p><?php echo esc_html( (string) $view_wd->txn_id ); ?></p>
            </div>
            <span class="rm-wdv-status-pill" style="background:<?php echo esc_attr( $st['bg'] ); ?>;color:<?php echo esc_attr( $st['text'] ); ?>;">
                <?php echo esc_html( $st['label'] ); ?>
            </span>
        </div>

        <!-- Amount Hero -->
        <div class="rm-wdv-amount-hero">
            <div class="label"><?php esc_html_e( 'Requested Amount', 'reseller-management' ); ?></div>
            <div class="amount">
                <span class="currency">৳</span><?php echo esc_html( number_format( (float) $view_wd->amount, 2 ) ); ?>
            </div>
        </div>

        <!-- Details -->
        <div class="rm-wdv-details">
            <div class="rm-wdv-detail-item">
                <div class="rm-wdv-detail-icon">🏷️</div>
                <div class="rm-wdv-detail-text">
                    <div class="key"><?php esc_html_e( 'Transaction ID', 'reseller-management' ); ?></div>
                    <div class="val" style="font-family:monospace;font-size:1rem;"><?php echo esc_html( (string) $view_wd->txn_id ); ?></div>
                </div>
            </div>
            <div class="rm-wdv-detail-item">
                <div class="rm-wdv-detail-icon">💳</div>
                <div class="rm-wdv-detail-text">
                    <div class="key"><?php esc_html_e( 'Payment Method', 'reseller-management' ); ?></div>
                    <div class="val">
                        <span class="rm-wdv-method-pill" style="background:<?php echo esc_attr( $ml['bg'] ); ?>;color:<?php echo esc_attr( $ml['text'] ); ?>;">
                            <?php echo esc_html( $ml['icon'] . ' ' . $ml['label'] ); ?>
                        </span>
                    </div>
                </div>
            </div>
            <div class="rm-wdv-detail-item">
                <div class="rm-wdv-detail-icon">📱</div>
                <div class="rm-wdv-detail-text">
                    <div class="key"><?php esc_html_e( 'Account Number', 'reseller-management' ); ?></div>
                    <div class="val" style="font-family:monospace;"><?php echo esc_html( (string) $view_wd->account_details ); ?></div>
                </div>
            </div>
            <div class="rm-wdv-detail-item">
                <div class="rm-wdv-detail-icon">🕐</div>
                <div class="rm-wdv-detail-text">
                    <div class="key"><?php esc_html_e( 'Requested At', 'reseller-management' ); ?></div>
                    <div class="val"><?php echo esc_html( date( 'd M Y, h:i A', strtotime( (string) $view_wd->created_at ) ) ); ?></div>
                </div>
            </div>
            <div class="rm-wdv-detail-item">
                <div class="rm-wdv-detail-icon">📋</div>
                <div class="rm-wdv-detail-text">
                    <div class="key"><?php esc_html_e( 'Status', 'reseller-management' ); ?></div>
                    <div class="val">
                        <span class="rm-wdv-status-pill" style="background:<?php echo esc_attr( $st['bg'] ); ?>;color:<?php echo esc_attr( $st['text'] ); ?>;">
                            <?php echo esc_html( $st['label'] ); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="rm-wdv-footer">
            <div class="rm-wdv-footer-id">
                Withdrawal ID: <strong>#<?php echo esc_html( (string) $view_wd->id ); ?></strong>
            </div>
        </div>
    </div>
</div>

<?php
    return; // stop rendering rest of list template
endif;
/* ── END detail view ──────────────────────────────────────── */
?>

<style>
/* ── Page header ─────────────────────────────── */
.rm-wd-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 20px;
}
.rm-wd-header h3 {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--rm-text, #1e293b);
}
.rm-wd-count {
    font-size: 0.8rem;
    font-weight: 600;
    color: #64748b;
    background: #f1f5f9;
    padding: 4px 14px;
    border-radius: 20px;
}

/* ── Filter bar ──────────────────────────────── */
.rm-wd-filters {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 18px;
    background: #fff;
    border: 1px solid #e8edf3;
    border-radius: 14px;
    padding: 16px 18px;
    box-shadow: 0 1px 6px rgba(0,0,0,0.04);
}
.rm-wd-filter-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
    flex: 1;
    min-width: 160px;
}
.rm-wd-filter-group label {
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #94a3b8;
}
.rm-wd-filter-group input,
.rm-wd-filter-group select {
    border: 1.5px solid #e2e8f0;
    border-radius: 8px;
    padding: 8px 12px;
    font-size: 0.85rem;
    color: #1e293b;
    outline: none;
    transition: border-color 0.2s;
    background: #f8fafc;
}
.rm-wd-filter-group input:focus,
.rm-wd-filter-group select:focus { border-color: #6366f1; background: #fff; }
.rm-wd-filter-reset {
    align-self: flex-end;
    background: #f1f5f9;
    border: none;
    border-radius: 8px;
    padding: 9px 16px;
    font-size: 0.8rem;
    font-weight: 600;
    color: #475569;
    cursor: pointer;
    transition: background 0.18s;
    white-space: nowrap;
}
.rm-wd-filter-reset:hover { background: #e2e8f0; }

/* ── Table wrapper ───────────────────────────── */
.rm-wd-table-wrap {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    border: 1px solid #e8edf3;
    overflow: hidden;
}
.rm-wd-table {
    width: 100%;
    border-collapse: collapse;
}
.rm-wd-table thead th {
    background: #f8fafc;
    padding: 12px 16px;
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.7px;
    color: #64748b;
    text-align: left;
    border-bottom: 1px solid #e8edf3;
    white-space: nowrap;
}
.rm-wd-table tbody tr {
    border-bottom: 1px solid #f1f5f9;
    transition: background 0.12s;
}
.rm-wd-table tbody tr:last-child { border-bottom: none; }
.rm-wd-table tbody tr:hover { background: #fafbff; }
.rm-wd-table tbody tr.is-hidden { display: none; }
.rm-wd-table tbody td {
    padding: 13px 16px;
    font-size: 0.86rem;
    color: #334155;
    vertical-align: middle;
}
.rm-wd-serial { font-weight: 700; color: #cbd5e1; font-size: 0.78rem; }
.rm-wd-txn    { font-family: monospace; font-size: 0.82rem; color: #6366f1; font-weight: 700; letter-spacing: 0.5px; }
.rm-wd-amount { font-weight: 800; color: #0f172a; font-size: 0.97rem; }
.rm-wd-date   { font-size: 0.8rem; color: #64748b; white-space: nowrap; }

.rm-wd-method-badge {
    display: inline-block;
    border-radius: 20px;
    padding: 3px 11px;
    font-size: 0.71rem;
    font-weight: 700;
    letter-spacing: 0.4px;
    text-transform: uppercase;
}
.rm-wd-status-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    border-radius: 20px;
    padding: 4px 12px;
    font-size: 0.73rem;
    font-weight: 700;
}
.rm-wd-status-badge::before {
    content: '';
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: currentColor;
    opacity: 0.7;
}
.rm-wd-view-btn {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: linear-gradient(135deg, #6366f1, #4f46e5);
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 7px 14px;
    font-size: 0.78rem;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    transition: opacity 0.2s, transform 0.1s;
}
.rm-wd-view-btn:hover { opacity: 0.88; transform: translateY(-1px); color: #fff; }

/* ── No-results ──────────────────────────────── */
.rm-wd-no-results {
    display: none;
    text-align: center;
    padding: 40px 24px;
    color: #94a3b8;
    font-size: 0.9rem;
    font-weight: 600;
}

/* ── Pagination ──────────────────────────────── */
.rm-wd-pagination {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    margin-top: 20px;
}
.rm-wd-page-info {
    font-size: 0.8rem;
    color: #64748b;
    font-weight: 600;
}
.rm-wd-page-btns {
    display: flex;
    gap: 6px;
}
.rm-wd-page-btn {
    min-width: 36px;
    height: 36px;
    border-radius: 9px;
    border: 1.5px solid #e2e8f0;
    background: #fff;
    color: #475569;
    font-size: 0.82rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.18s;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0 10px;
}
.rm-wd-page-btn:hover          { border-color: #6366f1; color: #6366f1; background: #f5f3ff; }
.rm-wd-page-btn.is-active      { background: #6366f1; border-color: #6366f1; color: #fff; }
.rm-wd-page-btn:disabled       { opacity: 0.4; cursor: default; }
.rm-wd-page-btn:disabled:hover { border-color: #e2e8f0; color: #475569; background: #fff; }
</style>

<?php
/* Encode all data for JS */
$js_rows = [];
foreach ( $all_withdrawals as $i => $wd ) {
    $sk = strtolower( (string) $wd->status );
    $st = $status_styles[ $sk ] ?? $status_styles['pending'];
    $mk = strtolower( (string) $wd->payment_method );
    $ml = $method_labels[ $mk ] ?? [ 'bg' => '#f1f5f9', 'text' => '#475569', 'label' => ucfirst( $mk ), 'icon' => '?' ];
    $js_rows[] = [
        'id'        => (int) $wd->id,
        'serial'    => $i + 1,
        'txn_id'    => (string) $wd->txn_id,
        'amount'    => number_format( (float) $wd->amount, 2 ),
        'method'    => $ml['label'],
        'method_bg' => $ml['bg'],
        'method_txt'=> $ml['text'],
        'details'   => (string) $wd->account_details,
        'date'      => date( 'd M Y, h:i A', strtotime( (string) $wd->created_at ) ),
        'date_raw'  => date( 'Y-m-d', strtotime( (string) $wd->created_at ) ),
        'status'    => $st['label'],
        'status_bg' => $st['bg'],
        'status_txt'=> $st['text'],
        'view_url'  => esc_url( add_query_arg( [ 'tab' => 'account', 'subtab' => 'withdrawals', 'wd_id' => (int) $wd->id ], $base_url ) ),
    ];
}
?>

<!-- Page header -->
<div class="rm-wd-header">
    <h3><?php esc_html_e( 'Withdrawal Requests', 'reseller-management' ); ?></h3>
    <span class="rm-wd-count" id="rm-wd-total-count"><?php echo count( $all_withdrawals ); ?> <?php esc_html_e( 'total', 'reseller-management' ); ?></span>
</div>

<!-- Filter bar -->
<div class="rm-wd-filters">
    <div class="rm-wd-filter-group">
        <label for="rm-wd-f-txn"><?php esc_html_e( 'Transaction ID', 'reseller-management' ); ?></label>
        <input type="text" id="rm-wd-f-txn" placeholder="TXN-XXXXXXXX">
    </div>
    <div class="rm-wd-filter-group">
        <label for="rm-wd-f-from"><?php esc_html_e( 'Date From', 'reseller-management' ); ?></label>
        <input type="date" id="rm-wd-f-from">
    </div>
    <div class="rm-wd-filter-group">
        <label for="rm-wd-f-to"><?php esc_html_e( 'Date To', 'reseller-management' ); ?></label>
        <input type="date" id="rm-wd-f-to">
    </div>
    <div class="rm-wd-filter-group">
        <label for="rm-wd-f-status"><?php esc_html_e( 'Status', 'reseller-management' ); ?></label>
        <select id="rm-wd-f-status">
            <option value=""><?php esc_html_e( 'All Statuses', 'reseller-management' ); ?></option>
            <option value="Pending">Pending</option>
            <option value="Approved">Approved</option>
            <option value="Rejected">Rejected</option>
        </select>
    </div>
    <button class="rm-wd-filter-reset" id="rm-wd-reset">↺ <?php esc_html_e( 'Reset', 'reseller-management' ); ?></button>
</div>

<!-- Table -->
<div class="rm-wd-table-wrap">
    <table class="rm-wd-table" id="rm-wd-table">
        <thead>
            <tr>
                <th>#</th>
                <th><?php esc_html_e( 'Transaction ID', 'reseller-management' ); ?></th>
                <th><?php esc_html_e( 'Amount', 'reseller-management' ); ?></th>
                <th><?php esc_html_e( 'Requested At', 'reseller-management' ); ?></th>
                <th><?php esc_html_e( 'Status', 'reseller-management' ); ?></th>
                <th><?php esc_html_e( 'Action', 'reseller-management' ); ?></th>
            </tr>
        </thead>
        <tbody id="rm-wd-tbody"></tbody>
    </table>
    <div class="rm-wd-no-results" id="rm-wd-no-results">
        <?php esc_html_e( 'No withdrawals match your filters.', 'reseller-management' ); ?>
    </div>
</div>

<!-- Pagination -->
<div class="rm-wd-pagination" id="rm-wd-pagination">
    <span class="rm-wd-page-info" id="rm-wd-page-info"></span>
    <div class="rm-wd-page-btns" id="rm-wd-page-btns"></div>
</div>

<script>
(function ($) {
    var PER_PAGE   = 20;
    var allRows    = <?php echo wp_json_encode( $js_rows ); ?>;
    var filtered   = allRows.slice();
    var currentPage = 1;

    /* ── Render ─────────────────────────────────────────── */
    function renderTable() {
        var start   = ( currentPage - 1 ) * PER_PAGE;
        var end     = start + PER_PAGE;
        var pageRows= filtered.slice( start, end );
        var $tbody  = $( '#rm-wd-tbody' );
        var html    = '';

        if ( filtered.length === 0 ) {
            $( '#rm-wd-no-results' ).show();
            $( '#rm-wd-table' ).hide();
            $( '#rm-wd-pagination' ).hide();
            $( '#rm-wd-total-count' ).text( '0 total' );
            return;
        }

        $( '#rm-wd-no-results' ).hide();
        $( '#rm-wd-table' ).show();
        $( '#rm-wd-pagination' ).show();
        $( '#rm-wd-total-count' ).text( filtered.length + ' total' );

        pageRows.forEach( function ( row ) {
            html += '<tr>';
            html += '<td class="rm-wd-serial">' + row.serial + '</td>';
            html += '<td class="rm-wd-txn">'    + row.txn_id + '</td>';
            html += '<td class="rm-wd-amount">৳ ' + row.amount + '</td>';
            html += '<td class="rm-wd-date">'   + row.date   + '</td>';
            html += '<td><span class="rm-wd-status-badge" style="background:' + row.status_bg + ';color:' + row.status_txt + ';">' + row.status + '</span></td>';
            html += '<td><a class="rm-wd-view-btn" href="' + row.view_url + '">👁 View</a></td>';
            html += '</tr>';
        } );

        $tbody.html( html );
        renderPagination();
    }

    /* ── Pagination ─────────────────────────────────────── */
    function renderPagination() {
        var total     = filtered.length;
        var totalPages= Math.ceil( total / PER_PAGE );
        var start     = Math.min( ( currentPage - 1 ) * PER_PAGE + 1, total );
        var end       = Math.min( currentPage * PER_PAGE, total );

        $( '#rm-wd-page-info' ).text( 'Showing ' + start + '–' + end + ' of ' + total );

        var $btns = $( '#rm-wd-page-btns' );
        var btns  = '';

        // Previous
        btns += '<button class="rm-wd-page-btn" id="rm-wd-prev" ' + ( currentPage <= 1 ? 'disabled' : '' ) + '>‹</button>';

        // Page numbers (show up to 7 around current)
        var range = pageRange( currentPage, totalPages );
        range.forEach( function ( p ) {
            if ( p === '…' ) {
                btns += '<button class="rm-wd-page-btn" disabled>…</button>';
            } else {
                btns += '<button class="rm-wd-page-btn ' + ( p === currentPage ? 'is-active' : '' ) + '" data-page="' + p + '">' + p + '</button>';
            }
        } );

        // Next
        btns += '<button class="rm-wd-page-btn" id="rm-wd-next" ' + ( currentPage >= totalPages ? 'disabled' : '' ) + '>›</button>';

        $btns.html( btns );
    }

    function pageRange( current, total ) {
        if ( total <= 7 ) {
            var arr = [];
            for ( var i = 1; i <= total; i++ ) arr.push(i);
            return arr;
        }
        var result = [ 1 ];
        if ( current > 3 ) result.push( '…' );
        var lo = Math.max( 2, current - 1 );
        var hi = Math.min( total - 1, current + 1 );
        for ( var j = lo; j <= hi; j++ ) result.push( j );
        if ( current < total - 2 ) result.push( '…' );
        result.push( total );
        return result;
    }

    /* ── Filter logic ───────────────────────────────────── */
    function applyFilters() {
        var txn    = $( '#rm-wd-f-txn' ).val().toUpperCase().trim();
        var from   = $( '#rm-wd-f-from' ).val();
        var to     = $( '#rm-wd-f-to' ).val();
        var status = $( '#rm-wd-f-status' ).val();

        filtered = allRows.filter( function ( row ) {
            if ( txn    && row.txn_id.toUpperCase().indexOf( txn ) === -1 )   return false;
            if ( status && row.status !== status )                              return false;
            if ( from   && row.date_raw < from )                               return false;
            if ( to     && row.date_raw > to )                                 return false;
            return true;
        } );

        currentPage = 1;
        renderTable();
    }

    /* ── Events ─────────────────────────────────────────── */
    $( '#rm-wd-f-txn, #rm-wd-f-from, #rm-wd-f-to, #rm-wd-f-status' ).on( 'input change', applyFilters );

    $( '#rm-wd-reset' ).on( 'click', function () {
        $( '#rm-wd-f-txn' ).val( '' );
        $( '#rm-wd-f-from' ).val( '' );
        $( '#rm-wd-f-to' ).val( '' );
        $( '#rm-wd-f-status' ).val( '' );
        applyFilters();
    } );

    $( document ).on( 'click', '.rm-wd-page-btn[data-page]', function () {
        currentPage = parseInt( $( this ).data( 'page' ) );
        renderTable();
    } );
    $( document ).on( 'click', '#rm-wd-prev', function () {
        if ( currentPage > 1 ) { currentPage--; renderTable(); }
    } );
    $( document ).on( 'click', '#rm-wd-next', function () {
        var totalPages = Math.ceil( filtered.length / PER_PAGE );
        if ( currentPage < totalPages ) { currentPage++; renderTable(); }
    } );

    /* ── Init ───────────────────────────────────────────── */
    renderTable();

})(jQuery);
</script>
