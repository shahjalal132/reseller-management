<?php
/**
 * Account → Withdrawals sub-template.
 *
 * @package reseller-management
 */

defined( 'ABSPATH' ) || exit;

$reseller_id = get_current_user_id();
$withdrawals = \BOILERPLATE\Inc\Reseller_Finance::get_withdrawals( $reseller_id );

$status_styles = [
    'pending'  => [ 'bg' => '#fff7ed', 'text' => '#c2410c',  'label' => 'Pending' ],
    'approved' => [ 'bg' => '#f0fdf4', 'text' => '#15803d',  'label' => 'Approved' ],
    'rejected' => [ 'bg' => '#fff1f2', 'text' => '#be123c',  'label' => 'Rejected' ],
];
?>

<style>
/* ── Withdrawals Header ───────────────────────────── */
.rm-wd-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 22px;
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
    padding: 4px 12px;
    border-radius: 20px;
}

/* ── Withdrawals Table ────────────────────────────── */
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
    padding: 13px 16px;
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.7px;
    color: #64748b;
    text-align: left;
    border-bottom: 1px solid #e8edf3;
}
.rm-wd-table tbody tr {
    border-bottom: 1px solid #f1f5f9;
    transition: background 0.15s;
}
.rm-wd-table tbody tr:last-child { border-bottom: none; }
.rm-wd-table tbody tr:hover { background: #f8fafc; }
.rm-wd-table tbody td {
    padding: 14px 16px;
    font-size: 0.87rem;
    color: #334155;
    vertical-align: middle;
}
.rm-wd-num {
    font-weight: 700;
    color: #94a3b8;
}
.rm-wd-amount {
    font-weight: 700;
    color: #1e293b;
    font-size: 0.95rem;
}
.rm-wd-method-badge {
    display: inline-block;
    border-radius: 20px;
    padding: 3px 12px;
    font-size: 0.72rem;
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
    font-size: 0.75rem;
    font-weight: 700;
}
.rm-wd-status-badge::before {
    content: '';
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: currentColor;
    opacity: 0.7;
}
.rm-wd-view-btn {
    background: #eff6ff;
    color: #2563eb;
    border: none;
    border-radius: 7px;
    padding: 6px 14px;
    font-size: 0.78rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s;
}
.rm-wd-view-btn:hover { background: #dbeafe; }

/* ── Empty ────────────────────────────────────────── */
.rm-wd-empty {
    text-align: center;
    padding: 64px 24px;
    color: #94a3b8;
}
.rm-wd-empty svg { opacity: 0.35; margin-bottom: 14px; }
.rm-wd-empty p   { font-size: 0.95rem; margin: 0; }

/* ── View Modal ───────────────────────────────────── */
.rm-wd-modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(15,23,42,0.45);
    z-index: 99999;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(2px);
}
.rm-wd-modal-overlay.is-open { display: flex; }
.rm-wd-modal {
    background: #fff;
    border-radius: 18px;
    padding: 32px;
    width: 100%;
    max-width: 460px;
    box-shadow: 0 20px 50px rgba(0,0,0,0.18);
    animation: rmWdModalIn 0.2s ease;
}
@keyframes rmWdModalIn {
    from { opacity: 0; transform: scale(0.96) translateY(8px); }
    to   { opacity: 1; transform: scale(1) translateY(0); }
}
.rm-wd-modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 22px;
}
.rm-wd-modal-header h4 {
    margin: 0;
    font-size: 1.05rem;
    font-weight: 700;
    color: #1e293b;
}
.rm-wd-modal-close {
    background: #f1f5f9;
    border: none;
    border-radius: 50%;
    width: 32px;
    height: 32px;
    font-size: 1rem;
    cursor: pointer;
    color: #64748b;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.2s;
}
.rm-wd-modal-close:hover { background: #e2e8f0; }
.rm-wd-detail-row {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 12px 0;
    border-bottom: 1px solid #f1f5f9;
}
.rm-wd-detail-row:last-child { border-bottom: none; }
.rm-wd-detail-label {
    flex-shrink: 0;
    width: 120px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #94a3b8;
    padding-top: 2px;
}
.rm-wd-detail-value {
    font-size: 0.9rem;
    font-weight: 600;
    color: #1e293b;
    flex: 1;
    word-break: break-word;
}

/* Method badge colours inside modal */
.badge-bkash  { background: #fce4ec; color: #c2185b; }
.badge-nagad  { background: #fff3e0; color: #e65100; }
.badge-rocket { background: #ede7f6; color: #512da8; }
.badge-bank   { background: #e3f2fd; color: #1565c0; }
</style>

<div class="rm-wd-header">
    <h3><?php esc_html_e( 'Withdrawal Requests', 'reseller-management' ); ?></h3>
    <span class="rm-wd-count"><?php echo count( $withdrawals ); ?> <?php esc_html_e( 'total', 'reseller-management' ); ?></span>
</div>

<div class="rm-wd-table-wrap">
<?php if ( empty( $withdrawals ) ) : ?>
    <div class="rm-wd-empty">
        <svg viewBox="0 0 24 24" width="52" height="52" fill="#94a3b8"><path d="M19 13H13v6h-2v-6H5v-2h6V5h2v6h6z" opacity="0"/><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
        <p><?php esc_html_e( 'No withdrawal requests submitted yet.', 'reseller-management' ); ?></p>
    </div>
<?php else : ?>
    <table class="rm-wd-table">
        <thead>
            <tr>
                <th>#</th>
                <th><?php esc_html_e( 'Date', 'reseller-management' ); ?></th>
                <th><?php esc_html_e( 'Amount', 'reseller-management' ); ?></th>
                <th><?php esc_html_e( 'Method', 'reseller-management' ); ?></th>
                <th><?php esc_html_e( 'Status', 'reseller-management' ); ?></th>
                <th><?php esc_html_e( 'Action', 'reseller-management' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ( $withdrawals as $i => $wd ) :
                $status_key = strtolower( (string) $wd->status );
                $st         = $status_styles[ $status_key ] ?? $status_styles['pending'];
                $method_key = strtolower( (string) $wd->payment_method );
                $method_labels = [
                    'bkash'  => [ 'bg' => '#fce4ec', 'text' => '#c2185b', 'label' => 'Bkash' ],
                    'nagad'  => [ 'bg' => '#fff3e0', 'text' => '#e65100', 'label' => 'Nagad' ],
                    'rocket' => [ 'bg' => '#ede7f6', 'text' => '#512da8', 'label' => 'Rocket' ],
                    'bank'   => [ 'bg' => '#e3f2fd', 'text' => '#1565c0', 'label' => 'Bank' ],
                ];
                $ml = $method_labels[ $method_key ] ?? [ 'bg' => '#f1f5f9', 'text' => '#475569', 'label' => ucfirst( $method_key ) ];
                $details_escaped = esc_attr( (string) $wd->account_details );
            ?>
                <tr>
                    <td class="rm-wd-num"><?php echo ( $i + 1 ); ?></td>
                    <td><?php echo esc_html( date( 'd M Y, h:i A', strtotime( (string) $wd->created_at ) ) ); ?></td>
                    <td class="rm-wd-amount"><?php echo wp_kses_post( wc_price( (float) $wd->amount ) ); ?></td>
                    <td>
                        <span class="rm-wd-method-badge" style="background:<?php echo esc_attr( $ml['bg'] ); ?>;color:<?php echo esc_attr( $ml['text'] ); ?>;">
                            <?php echo esc_html( $ml['label'] ); ?>
                        </span>
                    </td>
                    <td>
                        <span class="rm-wd-status-badge" style="background:<?php echo esc_attr( $st['bg'] ); ?>;color:<?php echo esc_attr( $st['text'] ); ?>;">
                            <?php echo esc_html( $st['label'] ); ?>
                        </span>
                    </td>
                    <td>
                        <button class="rm-wd-view-btn"
                            data-amount="<?php echo esc_attr( number_format( (float) $wd->amount, 2 ) ); ?>"
                            data-method="<?php echo esc_attr( $ml['label'] ); ?>"
                            data-method-key="<?php echo esc_attr( $method_key ); ?>"
                            data-details="<?php echo $details_escaped; ?>"
                            data-date="<?php echo esc_attr( date( 'd M Y, h:i A', strtotime( (string) $wd->created_at ) ) ); ?>"
                            data-status="<?php echo esc_attr( $st['label'] ); ?>"
                            data-status-bg="<?php echo esc_attr( $st['bg'] ); ?>"
                            data-status-color="<?php echo esc_attr( $st['text'] ); ?>">
                            👁 <?php esc_html_e( 'View', 'reseller-management' ); ?>
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
</div>

<!-- View Modal -->
<div class="rm-wd-modal-overlay" id="rm-wd-modal">
    <div class="rm-wd-modal">
        <div class="rm-wd-modal-header">
            <h4><?php esc_html_e( 'Withdrawal Details', 'reseller-management' ); ?></h4>
            <button class="rm-wd-modal-close" id="rm-wd-close">✕</button>
        </div>
        <div id="rm-wd-detail-body">
            <div class="rm-wd-detail-row">
                <span class="rm-wd-detail-label"><?php esc_html_e( 'Amount', 'reseller-management' ); ?></span>
                <span class="rm-wd-detail-value" id="rm-wd-d-amount"></span>
            </div>
            <div class="rm-wd-detail-row">
                <span class="rm-wd-detail-label"><?php esc_html_e( 'Method', 'reseller-management' ); ?></span>
                <span class="rm-wd-detail-value" id="rm-wd-d-method"></span>
            </div>
            <div class="rm-wd-detail-row">
                <span class="rm-wd-detail-label"><?php esc_html_e( 'Account Details', 'reseller-management' ); ?></span>
                <span class="rm-wd-detail-value" id="rm-wd-d-details"></span>
            </div>
            <div class="rm-wd-detail-row">
                <span class="rm-wd-detail-label"><?php esc_html_e( 'Requested', 'reseller-management' ); ?></span>
                <span class="rm-wd-detail-value" id="rm-wd-d-date"></span>
            </div>
            <div class="rm-wd-detail-row">
                <span class="rm-wd-detail-label"><?php esc_html_e( 'Status', 'reseller-management' ); ?></span>
                <span class="rm-wd-detail-value" id="rm-wd-d-status"></span>
            </div>
        </div>
    </div>
</div>

<script>
(function ($) {
    var $overlay = $('#rm-wd-modal');

    function closeModal() { $overlay.removeClass('is-open'); }

    $('#rm-wd-close').on('click', closeModal);
    $overlay.on('click', function (e) { if ($(e.target).is($overlay)) closeModal(); });

    $(document).on('click', '.rm-wd-view-btn', function () {
        var $btn = $(this);
        var amount    = $btn.data('amount');
        var method    = $btn.data('method');
        var methodKey = $btn.data('method-key');
        var details   = $btn.data('details');
        var date      = $btn.data('date');
        var status    = $btn.data('status');
        var statusBg  = $btn.data('status-bg');
        var statusClr = $btn.data('status-color');

        $('#rm-wd-d-amount').text('৳ ' + parseFloat(amount).toFixed(2));
        $('#rm-wd-d-method').html('<span class="rm-wd-method-badge badge-' + methodKey + '">' + method + '</span>');
        $('#rm-wd-d-details').text(details || '—');
        $('#rm-wd-d-date').text(date);
        $('#rm-wd-d-status').html(
            '<span class="rm-wd-status-badge" style="background:' + statusBg + ';color:' + statusClr + ';">' + status + '</span>'
        );

        $overlay.addClass('is-open');
    });
})(jQuery);
</script>
