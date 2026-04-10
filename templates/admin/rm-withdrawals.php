<?php
/**
 * Reseller Hub – Withdrawals Admin Page.
 *
 * Variables provided by the render method:
 *   $rm_withdrawals (array)
 */

defined( 'ABSPATH' ) || exit;

$rm_withdrawals = isset( $rm_withdrawals ) ? (array) $rm_withdrawals : [];

$action = sanitize_key( wp_unslash( $_GET['action'] ?? '' ) );
$wd_id  = absint( $_GET['wd_id'] ?? 0 );
$base_url = admin_url( 'admin.php?page=reseller-hub-withdrawals' );

/* ── Inject txn_id if missing ─────────────────────────────────────────── */
foreach ( $rm_withdrawals as &$wd ) {
    if ( ! empty( $wd->transaction_id ) ) {
        $wd->txn_id = (string) $wd->transaction_id;
    } elseif ( empty( $wd->txn_id ) ) {
        $wd->txn_id = 'TXN-' . strtoupper( substr( md5( 'wd' . $wd->id ), 0, 8 ) );
    }
}
unset( $wd );

/* ── Label & Method maps ──────────────────────────────────────────────── */
$status_styles = [
    'pending'   => [ 'bg' => '#fff7ed', 'text' => '#c2410c', 'label' => 'Pending' ],
    'approved'  => [ 'bg' => '#f0fdf4', 'text' => '#15803d', 'label' => 'Approved' ],
    'completed' => [ 'bg' => '#eff6ff', 'text' => '#1d4ed8', 'label' => 'Completed' ],
    'rejected'  => [ 'bg' => '#fff1f2', 'text' => '#be123c', 'label' => 'Rejected' ],
];
$method_labels = [
    'bkash'  => [ 'bg' => '#fce4ec', 'text' => '#c2185b', 'label' => 'Bkash',  'icon' => 'B' ],
    'nagad'  => [ 'bg' => '#fff3e0', 'text' => '#e65100', 'label' => 'Nagad',  'icon' => 'N' ],
    'rocket' => [ 'bg' => '#ede7f6', 'text' => '#512da8', 'label' => 'Rocket', 'icon' => 'R' ],
    'bank'   => [ 'bg' => '#e3f2fd', 'text' => '#1565c0', 'label' => 'Bank',   'icon' => '🏦' ],
];

/* ── Find selected item ────────────────────────────────────────────────── */
$view_wd = null;
if ( $wd_id > 0 && in_array( $action, [ 'view', 'edit' ], true ) ) {
    foreach ( $rm_withdrawals as $wd ) {
        if ( (int) $wd->id === $wd_id ) {
            $view_wd = $wd;
            break;
        }
    }
}

/* ── Render Edit or View Screen ────────────────────────────────────────── */
if ( $view_wd ) :
    $u          = get_user_by( 'id', $view_wd->reseller_id );
    $user_name  = $u ? $u->display_name : 'Unknown';
    $user_phone = (string) get_user_meta( $view_wd->reseller_id, '_reseller_phone', true );

    $sk = strtolower( (string) $view_wd->status );
    $st = $status_styles[ $sk ] ?? $status_styles['pending'];
    $mk = strtolower( (string) $view_wd->payment_method );
    $ml = $method_labels[ $mk ] ?? [ 'bg' => '#f1f5f9', 'text' => '#475569', 'label' => ucfirst( $mk ), 'icon' => '?' ];

    if ( 'view' === $action ) : ?>
        <style>
        .rm-wdv-wrap { max-width: 720px; margin: 20px auto; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        .rm-wdv-back-btn { display: inline-flex; align-items: center; gap: 6px; color: #64748b; font-size: 0.85rem; font-weight: 600; text-decoration: none; margin-bottom: 22px; transition: color 0.2s; }
        .rm-wdv-back-btn:hover { color: #1e293b; }
        .rm-wdv-card { background: #fff; border-radius: 20px; box-shadow: 0 4px 24px rgba(0,0,0,0.08); border: 1px solid #e8edf3; overflow: hidden; }
        .rm-wdv-card-header { background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%); padding: 28px 32px; display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; }
        .rm-wdv-card-header-left h2 { margin: 0 0 6px; font-size: 1.45rem; font-weight: 800; color: #fff; letter-spacing: -0.3px; }
        .rm-wdv-card-header-left p { margin: 0; font-size: 0.8rem; color: rgba(255,255,255,0.55); font-weight: 500; }
        .rm-wdv-status-pill { display: inline-flex; align-items: center; gap: 7px; border-radius: 30px; padding: 7px 18px; font-size: 0.8rem; font-weight: 700; letter-spacing: 0.3px; backdrop-filter: blur(4px); }
        .rm-wdv-amount-hero { text-align: center; padding: 32px 32px 24px; border-bottom: 1px solid #f1f5f9; }
        .rm-wdv-amount-hero .label { font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #94a3b8; margin-bottom: 8px; }
        .rm-wdv-amount-hero .amount { font-size: 2.8rem; font-weight: 900; color: #0f172a; letter-spacing: -1px; line-height: 1; }
        .rm-wdv-amount-hero .currency { font-size: 1.4rem; font-weight: 700; color: #64748b; margin-right: 4px; }
        .rm-wdv-details { padding: 8px 32px 28px; }
        .rm-wdv-detail-item { display: flex; align-items: flex-start; padding: 16px 0; border-bottom: 1px solid #f8fafc; gap: 16px; }
        .rm-wdv-detail-item:last-child { border-bottom: none; }
        .rm-wdv-detail-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0; background: #f8fafc; }
        .rm-wdv-detail-text { flex: 1; }
        .rm-wdv-detail-text .key { font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.6px; color: #94a3b8; margin-bottom: 4px; }
        .rm-wdv-detail-text .val { font-size: 0.95rem; font-weight: 700; color: #1e293b; }
        .rm-wdv-method-pill { display: inline-flex; align-items: center; gap: 6px; border-radius: 20px; padding: 4px 14px; font-size: 0.8rem; font-weight: 700; }
        .rm-wdv-footer { background: #f8fafc; padding: 16px 32px; display: flex; align-items: center; justify-content: space-between; border-top: 1px solid #e8edf3; }
        .rm-wdv-actions { display: flex; gap: 10px; }
        .rm-btn { padding: 8px 18px; font-size: 0.8rem; font-weight: 600; border-radius: 8px; cursor: pointer; text-decoration: none; border: none; display: inline-flex; align-items: center; gap: 5px; transition: opacity 0.2s; }
        .rm-btn:hover { opacity: 0.85; color: #fff; }
        .rm-btn-dark { background: #0f172a; color: #fff; }
        .rm-btn-primary { background: #6366f1; color: #fff; }
        </style>
        <div class="rm-wdv-wrap">
            <a href="<?php echo esc_url( $base_url ); ?>" class="rm-wdv-back-btn">← Back to Withdrawals</a>
            <div class="rm-wdv-card" id="rm-wdv-print-area">
                <div class="rm-wdv-card-header">
                    <div class="rm-wdv-card-header-left">
                        <h2>Withdrawal Detail</h2>
                        <p><?php echo esc_html( (string) $view_wd->txn_id ); ?></p>
                    </div>
                    <span class="rm-wdv-status-pill" style="background:<?php echo esc_attr( $st['bg'] ); ?>;color:<?php echo esc_attr( $st['text'] ); ?>;">
                        <?php echo esc_html( $st['label'] ); ?>
                    </span>
                </div>
                <div class="rm-wdv-amount-hero">
                    <div class="label">Requested Amount</div>
                    <div class="amount"><span class="currency">৳</span><?php echo esc_html( number_format( (float) $view_wd->amount, 2 ) ); ?></div>
                </div>
                <div class="rm-wdv-details">
                    <div class="rm-wdv-detail-item">
                        <div class="rm-wdv-detail-icon">👤</div>
                        <div class="rm-wdv-detail-text">
                            <div class="key">Reseller Info</div>
                            <div class="val">
                                <?php echo esc_html( $user_name ); ?> 
                                <?php if ( $user_phone ) : ?><span style="color:#64748b;font-size:0.85rem;font-weight:500;">(<?php echo esc_html( $user_phone ); ?>)</span><?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="rm-wdv-detail-item">
                        <div class="rm-wdv-detail-icon">💳</div>
                        <div class="rm-wdv-detail-text">
                            <div class="key">Payment Method</div>
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
                            <div class="key">Account Number / Details</div>
                            <div class="val" style="font-family:monospace;"><?php echo esc_html( (string) $view_wd->account_details ); ?></div>
                        </div>
                    </div>
                    <?php if ( ! empty( $view_wd->note ) ) : ?>
                    <div class="rm-wdv-detail-item">
                        <div class="rm-wdv-detail-icon">📝</div>
                        <div class="rm-wdv-detail-text">
                            <div class="key">Note</div>
                            <div class="val" style="font-weight:500;"><?php echo nl2br( esc_html( (string) $view_wd->note ) ); ?></div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="rm-wdv-detail-item">
                        <div class="rm-wdv-detail-icon">🕐</div>
                        <div class="rm-wdv-detail-text">
                            <div class="key">Requested At</div>
                            <div class="val"><?php echo esc_html( date( 'd M Y, h:i A', strtotime( (string) $view_wd->created_at ) ) ); ?></div>
                        </div>
                    </div>
                </div>
                <div class="rm-wdv-footer">
                    <span style="font-size:0.75rem;color:#94a3b8;font-weight:600;">ID: <strong>#<?php echo esc_html( (string) $view_wd->id ); ?></strong></span>
                </div>
            </div>
        </div>
    <?php
    elseif ( 'edit' === $action ) : ?>
        <style>
        .rm-form-wrap { max-width: 600px; margin: 20px auto; background: #fff; padding: 32px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #e8edf3; }
        .rm-form-wrap h2 { margin: 0 0 24px; font-size: 1.5rem; color: #1e293b; }
        .rm-form-group { margin-bottom: 20px; }
        .rm-form-group label { display: block; margin-bottom: 6px; font-size: 0.85rem; font-weight: 700; color: #475569; }
        .rm-form-group input, .rm-form-group select, .rm-form-group textarea { width: 100%; border: 1.5px solid #e2e8f0; border-radius: 8px; padding: 10px 14px; font-size: 0.95rem; color: #1e293b; transition: all 0.2s; box-sizing: border-box; }
        .rm-form-group input:focus, .rm-form-group select:focus, .rm-form-group textarea:focus { border-color: #6366f1; outline: none; }
        .rm-form-btn-group { display: flex; gap: 12px; margin-top: 30px; }
        .rm-btn { padding: 10px 24px; font-size: 0.9rem; font-weight: 600; border-radius: 8px; cursor: pointer; border: none; text-decoration: none; }
        .rm-btn-save { background: #6366f1; color: #fff; }
        .rm-btn-save:hover { background: #4f46e5; }
        .rm-btn-cancel { background: #f1f5f9; color: #475569; }
        .rm-btn-cancel:hover { background: #e2e8f0; }
        </style>
        <div class="rm-form-wrap">
            <h2>Edit Withdrawal #<?php echo esc_html( (string) $view_wd->id ); ?></h2>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <?php wp_nonce_field( 'rm_edit_withdrawal_' . $view_wd->id ); ?>
                <input type="hidden" name="action" value="rm_edit_withdrawal">
                <input type="hidden" name="wd_id" value="<?php echo esc_attr( $view_wd->id ); ?>">

                <div class="rm-form-group">
                    <label>Amount (৳)</label>
                    <input type="number" step="0.01" name="amount" value="<?php echo esc_attr( $view_wd->amount ); ?>" required>
                </div>
                <div class="rm-form-group">
                    <label>Payment Method</label>
                    <input type="text" name="payment_method" value="<?php echo esc_attr( $view_wd->payment_method ); ?>" required>
                </div>
                <div class="rm-form-group">
                    <label>Account Details</label>
                    <textarea name="account_details" rows="3" required><?php echo esc_textarea( $view_wd->account_details ); ?></textarea>
                </div>
                <div class="rm-form-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="pending" <?php selected( $view_wd->status, 'pending' ); ?>>Pending</option>
                        <option value="approved" <?php selected( $view_wd->status, 'approved' ); ?>>Approved</option>
                        <option value="completed" <?php selected( $view_wd->status, 'completed' ); ?>>Completed</option>
                        <option value="rejected" <?php selected( $view_wd->status, 'rejected' ); ?>>Rejected</option>
                    </select>
                </div>
                <div class="rm-form-group">
                    <label>Admin Note (Optional)</label>
                    <textarea name="note" rows="3"><?php echo esc_textarea( $view_wd->note ); ?></textarea>
                </div>

                <div class="rm-form-btn-group">
                    <button type="submit" class="rm-btn rm-btn-save">Save Changes</button>
                    <a href="<?php echo esc_url( $base_url ); ?>" class="rm-btn rm-btn-cancel">Cancel</a>
                </div>
            </form>
        </div>
    <?php endif;
    // Early exit
    return;
endif;

/* ── Render List Screen (Data Grid) ────────────────────────────────────── */
$js_rows = [];
foreach ( $rm_withdrawals as $i => $wd ) {
    $u = get_user_by( 'id', $wd->reseller_id );
    $user_name = $u ? $u->display_name : 'Unknown';
    $user_username = $u ? $u->user_login : '';
    $user_phone = (string) get_user_meta( $wd->reseller_id, '_reseller_phone', true );

    $sk = strtolower( (string) $wd->status );
    $st = $status_styles[ $sk ] ?? $status_styles['pending'];
    $mk = strtolower( (string) $wd->payment_method );
    $ml = $method_labels[ $mk ] ?? [ 'bg' => '#f1f5f9', 'text' => '#475569', 'label' => ucfirst( $mk ), 'icon' => '?' ];

    $js_rows[] = [
        'id'            => (int) $wd->id,
        'serial'        => $i + 1,
        'txn_id'        => (string) $wd->txn_id,
        'amount'        => number_format( (float) $wd->amount, 2 ),
        'method'        => $ml['label'],
        'method_bg'     => $ml['bg'],
        'method_txt'    => $ml['text'],
        'details'       => (string) $wd->account_details,
        'date'          => date( 'd M Y, h:i A', strtotime( (string) $wd->created_at ) ),
        'date_raw'      => date( 'Y-m-d', strtotime( (string) $wd->created_at ) ),
        'status'        => $sk, // raw status
        'status_html'   => $st['label'],
        'user_name'     => $user_name,
        'user_username' => $user_username,
        'user_phone'    => $user_phone,
        'view_url'      => esc_url( add_query_arg( [ 'action' => 'view', 'wd_id' => $wd->id ], $base_url ) ),
        'edit_url'      => esc_url( add_query_arg( [ 'action' => 'edit', 'wd_id' => $wd->id ], $base_url ) ),
        'delete_url'    => wp_nonce_url( admin_url( 'admin-post.php?action=rm_delete_withdrawal&wd_id=' . $wd->id ), 'rm_delete_withdrawal_' . $wd->id ),
    ];
}
?>

<div class="rm-admin-wd">
    <div class="rm-page-header">
        <h1 class="rm-page-title"><?php esc_html_e( 'Withdrawal Requests', 'reseller-management' ); ?></h1>
    </div>

    <div class="rm-users-table-wrap">
        <!-- Filter bar -->
        <div class="rm-search-bar-row">
            <div class="rm-wd-filters">
                <div class="rm-wd-filter-group rm-wd-filter-group--search">
                    <label><?php esc_html_e( 'Search', 'reseller-management' ); ?></label>
                    <input type="text" id="rm-wd-f-search" placeholder="<?php esc_attr_e( 'TXN, name, phone, username', 'reseller-management' ); ?>">
                </div>
                <div class="rm-wd-filter-group">
                    <label><?php esc_html_e( 'Date From', 'reseller-management' ); ?></label>
                    <input type="date" id="rm-wd-f-from">
                </div>
                <div class="rm-wd-filter-group">
                    <label><?php esc_html_e( 'Date To', 'reseller-management' ); ?></label>
                    <input type="date" id="rm-wd-f-to">
                </div>
                <div class="rm-wd-filter-group">
                    <label><?php esc_html_e( 'Status', 'reseller-management' ); ?></label>
                    <select id="rm-wd-f-status">
                        <option value=""><?php esc_html_e( 'All Statuses', 'reseller-management' ); ?></option>
                        <option value="pending"><?php esc_html_e( 'Pending', 'reseller-management' ); ?></option>
                        <option value="approved"><?php esc_html_e( 'Approved', 'reseller-management' ); ?></option>
                        <option value="completed"><?php esc_html_e( 'Completed', 'reseller-management' ); ?></option>
                        <option value="rejected"><?php esc_html_e( 'Rejected', 'reseller-management' ); ?></option>
                    </select>
                </div>
                <button class="rm-wd-filter-reset" id="rm-wd-reset"><?php esc_html_e( 'Reset', 'reseller-management' ); ?></button>
            </div>
        </div>

        <!-- Table -->
        <div class="rm-table-responsive">
            <div class="rm-wd-table-wrap">
                <table class="rm-users-table rm-withdrawals-table" id="rm-wd-table">
            <thead>
                <tr>
                    <th style="width:5%;">#</th>
                    <th style="width:18%;">Reseller</th>
                    <th style="width:12%;">Transaction ID</th>
                    <th style="width:10%;">Amount</th>
                    <th style="width:15%;">Method & Details</th>
                    <th style="width:12%;">Requested At</th>
                    <th style="width:13%;">Status</th>
                    <th style="width:15%;">Actions</th>
                </tr>
            </thead>
            <tbody id="rm-wd-tbody"></tbody>
        </table>
        <div class="rm-wd-no-results" id="rm-wd-no-results">
            <p><?php esc_html_e( 'No withdrawals match your filters.', 'reseller-management' ); ?></p>
        </div>
    </div>
</div>

        <!-- Pagination -->
        <div class="rm-pagination rm-wd-pagination" id="rm-wd-pagination">
            <span class="rm-pagination-info rm-wd-page-info" id="rm-wd-page-info"></span>
            <div class="rm-pagination-links rm-wd-page-btns" id="rm-wd-page-btns"></div>
        </div>
    </div>
</div>

<style>
/* Base UI */
.rm-admin-wd { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }

/* Filter Bar */
.rm-wd-filters { display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end; width: 100%; }
.rm-wd-filter-group { display: flex; flex-direction: column; gap: 5px; flex: 1; min-width: 150px; }
.rm-wd-filter-group--search { flex: 2; min-width: 260px; }
.rm-wd-filter-group label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: #9ca3af; }
.rm-wd-filter-group input, .rm-wd-filter-group select { border: 1.5px solid #e5e7eb; border-radius: 8px; padding: 9px 12px; font-size: 13px; color: #374151; outline: none; background: #fff; min-height: 38px; box-shadow: none; }
.rm-wd-filter-group input:focus, .rm-wd-filter-group select:focus { border-color: #005f5a; box-shadow: 0 0 0 3px rgba(0,95,90,.09); }
.rm-wd-filter-reset { background: #fff; border: 1.5px solid #e5e7eb; border-radius: 8px; padding: 9px 16px; font-size: 13px; font-weight: 600; color: #6b7280; cursor: pointer; transition: all .18s; white-space: nowrap; min-height: 38px; }
.rm-wd-filter-reset:hover { border-color: #9ca3af; color: #374151; }

/* Table enhancements */
.rm-wd-table-wrap { background: #fff; margin-bottom: 0; }
#rm-wd-table { border: none; box-shadow: none; table-layout: auto; min-width: 980px; }
#rm-wd-table th { white-space: nowrap; }
#rm-wd-table td { vertical-align: middle; }
.rm-wd-no-results { display: none; text-align: center; padding: 38px 20px; color: #9ca3af; font-size: 14px; font-weight: 500; background: #fff; }
.rm-wd-no-results p { margin: 0; }

/* Cell Content */
.rm-wd-user-name { font-weight: 600; color: #111827; font-size: 14px; }
.rm-wd-user-meta { font-size: 12px; color: #6b7280; display: block; margin-top: 2px; }
.rm-wd-txn { font-family: monospace; font-size: 12px; color: #374151; font-weight: 600; }
.rm-wd-amount { font-weight: 700; color: #005f5a; font-size: 14px; }
.rm-wd-method { font-size: 12.5px; font-weight: 600; color: #111827; display: block; }
.rm-wd-method-details { font-family: monospace; font-size: 12px; color: #6b7280; }

/* Select Dropdown for Status */
.rm-wd-status-select { font-size: 12px; padding: 6px 10px; border-radius: 8px; border: 1.5px solid #e5e7eb; color: #374151; font-weight: 600; outline: none; background: #fff; cursor: pointer; transition: all .18s; min-height: 34px; }
.rm-wd-status-select:focus { border-color: #005f5a; box-shadow: 0 0 0 3px rgba(0,95,90,.08); }
.rm-wd-status-select.is-pending { background: #fff7ed; color: #c2410c; border-color: #fdba74; }
.rm-wd-status-select.is-approved { background: #f0fdf4; color: #15803d; border-color: #86efac; }
.rm-wd-status-select.is-completed { background: #eff6ff; color: #1d4ed8; border-color: #93c5fd; }
.rm-wd-status-select.is-rejected { background: #fff1f2; color: #be123c; border-color: #fda4af; }

/* Action Buttons */
.rm-wd-acts { display: flex; gap: 6px; align-items: center; }
.rm-wd-btn { display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 34px; border-radius: 8px; font-size: 0; text-decoration: none; border: 1.5px solid #e5e7eb; cursor: pointer; transition: all .18s; color: #9ca3af; background: #fff; }
.rm-wd-btn-view:hover { border-color: #005f5a; color: #005f5a; background: #f0faf9; }
.rm-wd-btn-edit.is-edit:hover { border-color: #3b82f6; color: #3b82f6; background: #eff6ff; }
.rm-wd-btn-del:hover { border-color: #ef4444; color: #ef4444; background: #fef2f2; }
.spinner { margin: 0; float: none; display: none; vertical-align: middle; }
.spinner.is-active { display: inline-block; }

/* Pagination */
.rm-wd-pagination { margin-top: 0; }
.rm-wd-page-info { font-size: 13px; color: #6b7280; font-weight: 500; }
.rm-wd-page-btns { display: flex; gap: 4px; }
.rm-wd-page-btn { min-width: 34px; height: 34px; border-radius: 8px; border: 1.5px solid #e5e7eb; background: #fff; color: #374151; font-size: 13px; font-weight: 500; cursor: pointer; transition: all .18s; display: inline-flex; align-items: center; justify-content: center; padding: 0 8px; }
.rm-wd-page-btn:hover { border-color: #005f5a; color: #005f5a; background: #f0faf9; }
.rm-wd-page-btn.is-active { background: #005f5a; border-color: #005f5a; color: #fff; font-weight: 700; }
.rm-wd-page-btn:disabled { opacity: 0.4; cursor: default; }
.rm-wd-page-btn:disabled:hover { background: #fff; border-color: #e5e7eb; color: #374151; }

@media (max-width: 782px) {
  .rm-admin-wd .rm-table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
  .rm-admin-wd .rm-wd-table-wrap { min-width: 980px; }
  .rm-admin-wd #rm-wd-table th,
  .rm-admin-wd #rm-wd-table td { white-space: nowrap; }

  .rm-wd-filters { flex-direction: column; align-items: stretch; }
  .rm-wd-filter-group { width: 100%; min-width: 0; }
  .rm-wd-filter-group--search { min-width: 0; }
  .rm-wd-filter-group input, .rm-wd-filter-group select { width: 100%; min-height: 38px; }
  .rm-wd-filter-reset { width: 100%; justify-content: center; min-height: 38px; align-self: stretch; }
  
  .rm-wdv-card-header { flex-direction: column; padding: 20px; }
  .rm-wdv-amount-hero .amount { font-size: 2rem; }
  .rm-wdv-details { padding: 8px 20px 20px; }
  .rm-wdv-footer { padding: 16px 20px; flex-direction: column; gap: 10px; text-align: center; }
  
  .rm-wd-pagination { flex-direction: column; align-items: center; text-align: center; }
  .rm-wd-page-btns { flex-wrap: wrap; justify-content: center; }

  .rm-form-wrap { padding: 20px; margin: 10px; }
  .rm-form-btn-group { flex-direction: column; }
  .rm-btn { width: 100%; justify-content: center; }
}

@media (max-width: 480px) {
  .rm-admin-wd .rm-wd-header .wp-heading-inline { font-size: 20px; line-height: 1.3; }
  .rm-admin-wd { margin-right: 0; }
}
</style>

<script>
jQuery(document).ready(function ($) {
    var PER_PAGE    = 20;
    var allRows     = <?php echo wp_json_encode( $js_rows ); ?>;
    var filtered    = allRows.slice();
    var currentPage = 1;

    // Helper to get status class
    function getStatusClass(st) {
        return 'is-' + st;
    }

    /* ── Render Table ──────────────────────────────────────────────── */
    function renderTable() {
        var start    = ( currentPage - 1 ) * PER_PAGE;
        var end      = start + PER_PAGE;
        var pageRows = filtered.slice( start, end );
        var $tbody   = $( '#rm-wd-tbody' );
        var html     = '';

        if ( filtered.length === 0 ) {
            $( '#rm-wd-no-results' ).show();
            $( '#rm-wd-table' ).hide();
            $( '#rm-wd-pagination' ).hide();
            return;
        }

        $( '#rm-wd-no-results' ).hide();
        $( '#rm-wd-table' ).show();
        $( '#rm-wd-pagination' ).show();

        pageRows.forEach( function ( row ) {
            html += '<tr data-wd-id="' + row.id + '">';
            html += '<td>' + row.serial + '</td>';
            html += '<td>';
            html +=   '<span class="rm-wd-user-name">' + row.user_name + '</span>';
            html +=   '<span class="rm-wd-user-meta">' + row.user_phone + (row.user_username ? ' (@' + row.user_username + ')' : '') + '</span>';
            html += '</td>';
            html += '<td class="rm-wd-txn">' + row.txn_id + '</td>';
            html += '<td class="rm-wd-amount">৳ ' + row.amount + '</td>';
            html += '<td>';
            html +=   '<span class="rm-wd-method">' + row.method + '</span>';
            html +=   '<span class="rm-wd-method-details">' + row.details + '</span>';
            html += '</td>';
            html += '<td style="font-size:0.8rem;color:#646970;">' + row.date + '</td>';
            
            // Status Dropdown
            var selPending   = (row.status === 'pending')   ? 'selected' : '';
            var selApproved  = (row.status === 'approved')  ? 'selected' : '';
            var selCompleted = (row.status === 'completed') ? 'selected' : '';
            var selRejected  = (row.status === 'rejected')  ? 'selected' : '';
            html += '<td>';
            html +=   '<select class="rm-wd-status-select ' + getStatusClass(row.status) + '" data-id="' + row.id + '">';
            html +=     '<option value="pending" ' + selPending + '>Pending</option>';
            html +=     '<option value="approved" ' + selApproved + '>Approved</option>';
            html +=     '<option value="completed" ' + selCompleted + '>Completed</option>';
            html +=     '<option value="rejected" ' + selRejected + '>Rejected</option>';
            html +=   '</select>';
            html +=   '<span class="spinner" id="spinner-' + row.id + '"></span>';
            html += '</td>';

            html += '<td><div class="rm-wd-acts">';
            html +=   '<a href="' + row.view_url + '" class="rm-wd-btn rm-wd-btn-view" title="View"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg></a>';
            html +=   '<a href="' + row.edit_url + '" class="rm-wd-btn rm-wd-btn-edit is-edit" title="Edit"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg></a>';
            html +=   '<a href="' + row.delete_url + '" class="rm-wd-btn rm-wd-btn-del" onclick="return confirm(\'Delete this withdrawal?\');" title="Delete"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg></a>';
            html += '</div></td>';
            html += '</tr>';
        } );

        $tbody.html( html );
        renderPagination();
    }

    /* ── Pagination ────────────────────────────────────────────────── */
    function renderPagination() {
        var total      = filtered.length;
        var totalPages = Math.ceil( total / PER_PAGE );
        var start      = Math.min( ( currentPage - 1 ) * PER_PAGE + 1, total );
        var end        = Math.min( currentPage * PER_PAGE, total );

        $( '#rm-wd-page-info' ).text( 'Showing ' + start + '–' + end + ' of ' + total );

        var $btns = $( '#rm-wd-page-btns' );
        var btns  = '';

        btns += '<button class="rm-wd-page-btn" id="rm-wd-prev" ' + ( currentPage <= 1 ? 'disabled' : '' ) + '>‹</button>';

        var range = pageRange( currentPage, totalPages );
        range.forEach( function ( p ) {
            if ( p === '…' ) {
                btns += '<button class="rm-wd-page-btn" disabled>…</button>';
            } else {
                btns += '<button class="rm-wd-page-btn ' + ( p === currentPage ? 'is-active' : '' ) + '" data-page="' + p + '">' + p + '</button>';
            }
        } );

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

    /* ── Filter logic ──────────────────────────────────────────────── */
    function applyFilters() {
        var searchPattern = $( '#rm-wd-f-search' ).val().toUpperCase().trim();
        var from          = $( '#rm-wd-f-from' ).val();
        var to            = $( '#rm-wd-f-to' ).val();
        var statusFilter  = $( '#rm-wd-f-status' ).val();

        filtered = allRows.filter( function ( row ) {
            if ( searchPattern ) {
                var combined = (row.txn_id + ' ' + row.user_name + ' ' + row.user_phone + ' ' + row.user_username).toUpperCase();
                if ( combined.indexOf( searchPattern ) === -1 ) return false;
            }
            if ( statusFilter && row.status !== statusFilter ) return false;
            if ( from && row.date_raw < from ) return false;
            if ( to && row.date_raw > to ) return false;
            return true;
        } );

        currentPage = 1;
        renderTable();
    }

    $( '#rm-wd-f-search, #rm-wd-f-from, #rm-wd-f-to, #rm-wd-f-status' ).on( 'input change', applyFilters );

    $( '#rm-wd-reset' ).on( 'click', function () {
        $( '#rm-wd-f-search' ).val( '' );
        $( '#rm-wd-f-from' ).val( '' );
        $( '#rm-wd-f-to' ).val( '' );
        $( '#rm-wd-f-status' ).val( '' );
        applyFilters();
    } );

    /* ── Pagination Events ─────────────────────────────────────────── */
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

    /* ── AJAX Status Update ────────────────────────────────────────── */
    $( document ).on( 'change', '.rm-wd-status-select', function () {
        var $select = $(this);
        var wd_id   = $select.data('id');
        var status  = $select.val();
        var $spin   = $('#spinner-' + wd_id);

        $select.removeClass('is-pending is-approved is-rejected is-completed').addClass(getStatusClass(status));
        $select.prop('disabled', true);
        $spin.addClass('is-active');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'rm_admin_update_withdrawal_status',
                nonce: '<?php echo wp_create_nonce( 'rm_admin_nonce' ); ?>',
                wd_id: wd_id,
                status: status
            },
            success: function(res) {
                $spin.removeClass('is-active');
                $select.prop('disabled', false);

                if (!res.success) {
                    alert(res.data || 'Failed to update status.');
                } else {
                    // Update main array so filters/pagination preserve state
                    for (var i = 0; i < allRows.length; i++) {
                        if (allRows[i].id === wd_id) {
                            allRows[i].status = status;
                            break;
                        }
                    }
                }
            },
            error: function() {
                $spin.removeClass('is-active');
                $select.prop('disabled', false);
                alert('Network error while updating status.');
            }
        });
    });

    /* ── Init ──────────────────────────────────────────────────────── */
    renderTable();

});
</script>
