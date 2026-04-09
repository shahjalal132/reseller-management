<?php
/**
 * Account → Transaction Statement sub-template.
 *
 * @package reseller-management
 */

defined( 'ABSPATH' ) || exit;

/* ── Helpers ────────────────────────────────────────────── */
$reseller_id      = get_current_user_id();
$all_transactions = \BOILERPLATE\Inc\Reseller_Finance::get_transactions( $reseller_id );
$rm_balance       = \BOILERPLATE\Inc\Reseller_Helper::get_current_balance( $reseller_id );

/* ── Label maps ─────────────────────────────────────────── */
$type_styles = [
	'credit'            => [ 'bg' => '#f0fdf4', 'text' => '#15803d', 'label' => 'Credit' ],
	'debit'             => [ 'bg' => '#fff1f2', 'text' => '#be123c', 'label' => 'Debit' ],
	'payout'            => [ 'bg' => '#eff6ff', 'text' => '#1d4ed8', 'label' => 'Payout' ],
	'refund'            => [ 'bg' => '#fef2f2', 'text' => '#991b1b', 'label' => 'Refund' ],
	'commission_credit' => [ 'bg' => '#f5f3ff', 'text' => '#6d28d9', 'label' => 'Commission' ],
	'withdrawal_debit'  => [ 'bg' => '#fff7ed', 'text' => '#c2410c', 'label' => 'Withdrawal' ],
	'shipping_debit'    => [ 'bg' => '#fef2f2', 'text' => '#b91c1c', 'label' => 'Shipping Cost' ],
	'cod_deduction'             => [ 'bg' => '#f0f9ff', 'text' => '#0369a1', 'label' => 'COD Fee' ],
	'balance_adjustment_credit' => [ 'bg' => '#ecfdf5', 'text' => '#047857', 'label' => 'Balance credit' ],
	'balance_adjustment_debit'  => [ 'bg' => '#fff1f2', 'text' => '#be123c', 'label' => 'Balance debit' ],
];

/* Current page URL builder */
global $post;
$base_url = ( $post instanceof \WP_Post ) ? get_permalink( $post ) : home_url( '/' );

/* Prepare data & calculate balances */
$total_credits = 0.0;
$total_debits  = 0.0;
$current_temp_balance = $rm_balance;

$js_rows = [];
foreach ( $all_transactions as $tx ) {
	$tk = strtolower( (string) $tx->type );
	$ts = $type_styles[ $tk ] ?? [ 'bg' => '#f1f5f9', 'text' => '#475569', 'label' => ucfirst( str_replace( '_', ' ', $tk ) ) ];
	$amount = (float) $tx->amount;

	if ( $amount > 0 ) {
		$total_credits += $amount;
	} else {
		$total_debits += abs( $amount );
	}
	
	$js_rows[] = [
		'id'              => (int) $tx->id,
		'amount'          => number_format( abs( $amount ), 2 ),
		'is_credit'       => $amount >= 0,
		'type'            => $ts['label'],
		'type_bg'         => $ts['bg'],
		'type_txt'        => $ts['text'],
		'desc'            => (string) $tx->description,
		'order_id'        => (int) $tx->order_id,
		'date'            => date( 'd M Y, h:i A', strtotime( (string) $tx->created_at ) ),
		'date_raw'        => date( 'Y-m-d', strtotime( (string) $tx->created_at ) ),
		'running_balance' => number_format( $current_temp_balance, 2 ),
	];

	$current_temp_balance -= $amount;
}

$fmt = function ( $amount ) {
	return '৳' . number_format( abs( (float) $amount ), 2 );
};
?>

<style>
/* ── Page header ─────────────────────────────── */
.rm-tx-header {
	display: flex;
	align-items: center;
	justify-content: space-between;
	flex-wrap: wrap;
	gap: 12px;
	margin-bottom: 20px;
}
.rm-tx-header h3 {
	margin: 0;
	font-size: 1.1rem;
	font-weight: 700;
	color: var(--rm-text, #1e293b);
}
.rm-tx-count {
	font-size: 0.8rem;
	font-weight: 600;
	color: #64748b;
	background: #f1f5f9;
	padding: 4px 14px;
	border-radius: 20px;
}

/* ── Filter bar ──────────────────────────────── */
.rm-tx-filters {
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
.rm-tx-filter-group {
	display: flex;
	flex-direction: column;
	gap: 5px;
	flex: 1;
	min-width: 160px;
}
.rm-tx-filter-group label {
	font-size: 0.7rem;
	font-weight: 700;
	text-transform: uppercase;
	letter-spacing: 0.5px;
	color: #94a3b8;
}
.rm-tx-filter-group input,
.rm-tx-filter-group select {
	border: 1.5px solid #e2e8f0;
	border-radius: 8px;
	padding: 8px 12px;
	font-size: 0.85rem;
	color: #1e293b;
	outline: none;
	transition: border-color 0.2s;
	background: #f8fafc;
}
.rm-tx-filter-group input:focus,
.rm-tx-filter-group select:focus { border-color: #6366f1; background: #fff; }
.rm-tx-filter-reset {
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
.rm-tx-filter-reset:hover { background: #e2e8f0; }

/* ── Table wrapper ───────────────────────────── */
.rm-tx-table-wrap {
	background: #fff;
	border-radius: 14px;
	box-shadow: 0 2px 12px rgba(0,0,0,0.06);
	border: 1px solid #e8edf3;
	overflow: hidden;
}
.rm-tx-table {
	width: 100%;
	border-collapse: collapse;
}
.rm-tx-table thead th {
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
.rm-tx-table tbody tr {
	border-bottom: 1px solid #f1f5f9;
	transition: background 0.12s;
}
.rm-tx-table tbody tr:last-child { border-bottom: none; }
.rm-tx-table tbody tr:hover { background: #fafbff; }
.rm-tx-table tbody td {
	padding: 13px 16px;
	font-size: 0.86rem;
	color: #334155;
	vertical-align: middle;
}
.rm-tx-id { font-weight: 700; color: #cbd5e1; font-size: 0.78rem; }
.rm-tx-amount { font-weight: 800; font-size: 0.97rem; }
.rm-tx-amount.is-credit { color: #15803d; }
.rm-tx-amount.is-debit { color: #be123c; }
.rm-tx-date { font-size: 0.8rem; color: #64748b; white-space: nowrap; }
.rm-tx-desc { font-size: 0.82rem; color: #475569; }

.rm-tx-type-badge {
	display: inline-flex;
	align-items: center;
	gap: 5px;
	border-radius: 20px;
	padding: 4px 12px;
	font-size: 0.73rem;
	font-weight: 700;
}
.rm-tx-type-badge::before {
	content: '';
	width: 6px;
	height: 6px;
	border-radius: 50%;
	background: currentColor;
	opacity: 0.7;
}

/* ── No-results ──────────────────────────────── */
.rm-tx-no-results {
	display: none;
	text-align: center;
	padding: 40px 24px;
	color: #94a3b8;
	font-size: 0.9rem;
	font-weight: 600;
}

/* ── Pagination ──────────────────────────────── */
.rm-tx-pagination {
	display: flex;
	align-items: center;
	justify-content: space-between;
	flex-wrap: wrap;
	gap: 12px;
	margin-top: 20px;
}
.rm-tx-page-info {
	font-size: 0.8rem;
	color: #64748b;
	font-weight: 600;
}
.rm-tx-page-btns {
	display: flex;
	gap: 6px;
}
.rm-tx-page-btn {
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
.rm-tx-page-btn:hover { border-color: #6366f1; color: #6366f1; background: #f5f3ff; }
.rm-tx-page-btn.is-active { background: #6366f1; border-color: #6366f1; color: #fff; }
.rm-tx-page-btn:disabled { opacity: 0.4; cursor: default; }
/* ── Summary cards ────────────────────────────── */
.rm-tx-summary {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
	gap: 16px;
	margin-bottom: 24px;
}
.rm-tx-card {
	background: #fff;
	border: 1px solid #e8edf3;
	border-radius: 14px;
	padding: 18px;
	display: flex;
	align-items: center;
	gap: 14px;
	box-shadow: 0 1px 6px rgba(0,0,0,0.03);
}
.rm-tx-card-icon {
	width: 42px;
	height: 42px;
	border-radius: 10px;
	display: flex;
	align-items: center;
	justify-content: center;
	flex-shrink: 0;
}
.rm-tx-card-icon svg { width: 22px; height: 22px; }

.rm-tx-card--balance .rm-tx-card-icon { background: #eff6ff; color: #2563eb; }
.rm-tx-card--credits .rm-tx-card-icon { background: #f0fdf4; color: #16a34a; }
.rm-tx-card--debits .rm-tx-card-icon { background: #fff1f2; color: #e11d48; }
.rm-tx-card--count .rm-tx-card-icon { background: #f8fafc; color: #64748b; }

.rm-tx-card-info { display: flex; flex-direction: column; }
.rm-tx-card-info label { font-size: 0.72rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; margin-bottom: 2px; }
.rm-tx-card-info span { font-size: 1.1rem; font-weight: 800; color: #1e293b; }

.rm-tx-amount-bal { font-weight: 600; color: #1e293b; }
</style>

<!-- Page header -->
<div class="rm-tx-header">
	<h3><?php esc_html_e( 'Transaction Statement', 'reseller-management' ); ?></h3>
	<span class="rm-tx-count" id="rm-tx-total-count"><?php echo count( $all_transactions ); ?> <?php esc_html_e( 'total', 'reseller-management' ); ?></span>
</div>

<!-- Summary cards -->
<div class="rm-tx-summary">
	<div class="rm-tx-card rm-tx-card--balance">
		<div class="rm-tx-card-icon">
			<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
		</div>
		<div class="rm-tx-card-info">
			<label><?php esc_html_e( 'Current Balance', 'reseller-management' ); ?></label>
			<span><?php echo esc_html( $fmt( $rm_balance ) ); ?></span>
		</div>
	</div>
	<div class="rm-tx-card rm-tx-card--credits">
		<div class="rm-tx-card-icon">
			<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941"/></svg>
		</div>
		<div class="rm-tx-card-info">
			<label><?php esc_html_e( 'Total Credits', 'reseller-management' ); ?></label>
			<span><?php echo esc_html( $fmt( $total_credits ) ); ?></span>
		</div>
	</div>
	<div class="rm-tx-card rm-tx-card--debits">
		<div class="rm-tx-card-icon">
			<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6 9 12.75l4.286-4.286a11.948 11.948 0 0 1 4.306 6.43l.776 2.898m0 0 3.182-5.511m-3.182 5.51-5.511-3.181"/></svg>
		</div>
		<div class="rm-tx-card-info">
			<label><?php esc_html_e( 'Total Debits', 'reseller-management' ); ?></label>
			<span><?php echo esc_html( $fmt( $total_debits ) ); ?></span>
		</div>
	</div>
	<div class="rm-tx-card rm-tx-card--count">
		<div class="rm-tx-card-icon">
			<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/></svg>
		</div>
		<div class="rm-tx-card-info">
			<label><?php esc_html_e( 'Transactions', 'reseller-management' ); ?></label>
			<span><?php echo count( $all_transactions ); ?></span>
		</div>
	</div>
</div>

<div class="rm-tx-filters">
	<div class="rm-tx-filter-group">
		<label for="rm-tx-f-from"><?php esc_html_e( 'Date From', 'reseller-management' ); ?></label>
		<input type="date" id="rm-tx-f-from">
	</div>
	<div class="rm-tx-filter-group">
		<label for="rm-tx-f-to"><?php esc_html_e( 'Date To', 'reseller-management' ); ?></label>
		<input type="date" id="rm-tx-f-to">
	</div>
	<div class="rm-tx-filter-group">
		<label for="rm-tx-f-type"><?php esc_html_e( 'Type', 'reseller-management' ); ?></label>
		<select id="rm-tx-f-type">
			<option value=""><?php esc_html_e( 'All Types', 'reseller-management' ); ?></option>
			<?php foreach ( $type_styles as $key => $style ) : ?>
				<option value="<?php echo esc_attr( $style['label'] ); ?>"><?php echo esc_html( $style['label'] ); ?></option>
			<?php endforeach; ?>
		</select>
	</div>
	<button class="rm-tx-filter-reset" id="rm-tx-reset">↺ <?php esc_html_e( 'Reset', 'reseller-management' ); ?></button>
</div>

<!-- Table -->
<div class="rm-tx-table-wrap">
	<table class="rm-tx-table" id="rm-tx-table">
		<thead>
			<tr>
				<th>ID</th>
				<th><?php esc_html_e( 'Type', 'reseller-management' ); ?></th>
				<th><?php esc_html_e( 'Description', 'reseller-management' ); ?></th>
				<th><?php esc_html_e( 'Amount', 'reseller-management' ); ?></th>
				<th><?php esc_html_e( 'Balance', 'reseller-management' ); ?></th>
				<th><?php esc_html_e( 'Date', 'reseller-management' ); ?></th>
			</tr>
		</thead>
		<tbody id="rm-tx-tbody"></tbody>
	</table>
	<div class="rm-tx-no-results" id="rm-tx-no-results">
		<?php esc_html_e( 'No transactions match your filters.', 'reseller-management' ); ?>
	</div>
</div>

<!-- Pagination -->
<div class="rm-tx-pagination" id="rm-tx-pagination">
	<span class="rm-tx-page-info" id="rm-tx-page-info"></span>
	<div class="rm-tx-page-btns" id="rm-tx-page-btns"></div>
</div>

<script>
(function ($) {
	var PER_PAGE   = 20;
	var allRows    = <?php echo wp_json_encode( $js_rows ); ?>;
	var filtered   = allRows.slice();
	var currentPage = 1;

	function renderTable() {
		var start   = ( currentPage - 1 ) * PER_PAGE;
		var end     = start + PER_PAGE;
		var pageRows= filtered.slice( start, end );
		var $tbody  = $( '#rm-tx-tbody' );
		var html    = '';

		if ( filtered.length === 0 ) {
			$( '#rm-tx-no-results' ).show();
			$( '#rm-tx-table' ).hide();
			$( '#rm-tx-pagination' ).hide();
			$( '#rm-tx-total-count' ).text( '0 total' );
			return;
		}

		$( '#rm-tx-no-results' ).hide();
		$( '#rm-tx-table' ).show();
		$( '#rm-tx-pagination' ).show();
		$( '#rm-tx-total-count' ).text( filtered.length + ' total' );

		pageRows.forEach( function ( row ) {
			var amtClass = row.is_credit ? 'is-credit' : 'is-debit';
			var amtPrefix = row.is_credit ? '+' : '-';
			
			html += '<tr>';
			html += '<td class="rm-tx-id">#' + row.id + '</td>';
			html += '<td><span class="rm-tx-type-badge" style="background:' + row.type_bg + ';color:' + row.type_txt + ';">' + row.type + '</span></td>';
			html += '<td class="rm-tx-desc">' + row.desc + (row.order_id ? ' (Order #' + row.order_id + ')' : '') + '</td>';
			html += '<td class="rm-tx-amount ' + amtClass + '">' + amtPrefix + ' ৳' + row.amount + '</td>';
			html += '<td class="rm-tx-amount-bal">৳' + row.running_balance + '</td>';
			html += '<td class="rm-tx-date">' + row.date + '</td>';
			html += '</tr>';
		} );

		$tbody.html( html );
		renderPagination();
	}

	function renderPagination() {
		var total     = filtered.length;
		var totalPages= Math.ceil( total / PER_PAGE );
		var start     = Math.min( ( currentPage - 1 ) * PER_PAGE + 1, total );
		var end       = Math.min( currentPage * PER_PAGE, total );

		$( '#rm-tx-page-info' ).text( 'Showing ' + start + '–' + end + ' of ' + total );

		var $btns = $( '#rm-tx-page-btns' );
		var btns  = '';

		btns += '<button class="rm-tx-page-btn" id="rm-tx-prev" ' + ( currentPage <= 1 ? 'disabled' : '' ) + '>‹</button>';

		for ( var i = 1; i <= totalPages; i++ ) {
			if ( i === 1 || i === totalPages || ( i >= currentPage - 2 && i <= currentPage + 2 ) ) {
				btns += '<button class="rm-tx-page-btn ' + ( i === currentPage ? 'is-active' : '' ) + '" data-page="' + i + '">' + i + '</button>';
			} else if ( i === currentPage - 3 || i === currentPage + 3 ) {
				btns += '<button class="rm-tx-page-btn" disabled>…</button>';
			}
		}

		btns += '<button class="rm-tx-page-btn" id="rm-tx-next" ' + ( currentPage >= totalPages ? 'disabled' : '' ) + '>›</button>';

		$btns.html( btns );
	}

	function applyFilters() {
		var from   = $( '#rm-tx-f-from' ).val();
		var to     = $( '#rm-tx-f-to' ).val();
		var type   = $( '#rm-tx-f-type' ).val();

		filtered = allRows.filter( function ( row ) {
			if ( type && row.type !== type ) return false;
			if ( from && row.date_raw < from ) return false;
			if ( to   && row.date_raw > to )   return false;
			return true;
		} );

		currentPage = 1;
		renderTable();
	}

	$( '#rm-tx-f-from, #rm-tx-f-to, #rm-tx-f-type' ).on( 'change', applyFilters );

	$( '#rm-tx-reset' ).on( 'click', function () {
		$( '#rm-tx-f-from, #rm-tx-f-to, #rm-tx-f-type' ).val( '' );
		applyFilters();
	} );

	$( document ).on( 'click', '.rm-tx-page-btn[data-page]', function () {
		currentPage = parseInt( $( this ).data( 'page' ) );
		renderTable();
	} );
	$( document ).on( 'click', '#rm-tx-prev', function () {
		if ( currentPage > 1 ) { currentPage--; renderTable(); }
	} );
	$( document ).on( 'click', '#rm-tx-next', function () {
		var totalPages = Math.ceil( filtered.length / PER_PAGE );
		if ( currentPage < totalPages ) { currentPage++; renderTable(); }
	} );

	renderTable();

})(jQuery);
</script>
