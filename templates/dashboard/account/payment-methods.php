<?php
/**
 * Account → Payment Methods sub-template.
 *
 * @package reseller-management
 */

defined( 'ABSPATH' ) || exit;

$reseller_id     = get_current_user_id();
$payment_methods = \BOILERPLATE\Inc\Reseller_Helper::get_payment_methods( $reseller_id );

$method_colors = [
    'bkash'  => [ 'bg' => '#fce4ec', 'text' => '#c2185b', 'label' => 'Bkash' ],
    'nagad'  => [ 'bg' => '#fff3e0', 'text' => '#e65100', 'label' => 'Nagad' ],
    'rocket' => [ 'bg' => '#ede7f6', 'text' => '#512da8', 'label' => 'Rocket' ],
];
?>

<style>
.rm-pm-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 24px;
}
.rm-pm-header h3 {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--rm-text, #1e293b);
}
.rm-pm-add-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: linear-gradient(135deg, #10b981, #059669);
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 9px 18px;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition: opacity 0.2s, transform 0.1s;
}
.rm-pm-add-btn:hover { opacity: 0.9; transform: translateY(-1px); }

/* Method Cards Grid */
.rm-pm-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 16px;
}
.rm-pm-card {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    border: 1px solid #e8edf3;
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    transition: box-shadow 0.2s, transform 0.2s;
}
.rm-pm-card:hover { box-shadow: 0 6px 20px rgba(0,0,0,0.1); transform: translateY(-2px); }
.rm-pm-card-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.rm-pm-badge {
    display: inline-block;
    border-radius: 20px;
    padding: 4px 14px;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}
.rm-pm-type-tag {
    font-size: 0.72rem;
    font-weight: 600;
    color: #64748b;
    background: #f1f5f9;
    border-radius: 6px;
    padding: 3px 10px;
    text-transform: capitalize;
}
.rm-pm-number {
    font-size: 1.15rem;
    font-weight: 700;
    color: #1e293b;
    letter-spacing: 1px;
}
.rm-pm-number small {
    display: block;
    font-size: 0.7rem;
    font-weight: 500;
    color: #94a3b8;
    margin-bottom: 2px;
    letter-spacing: 0;
}
.rm-pm-actions {
    display: flex;
    gap: 8px;
    margin-top: 4px;
}
.rm-pm-btn-edit, .rm-pm-btn-delete {
    flex: 1;
    border: none;
    border-radius: 8px;
    padding: 7px;
    font-size: 0.78rem;
    font-weight: 600;
    cursor: pointer;
    transition: opacity 0.2s;
}
.rm-pm-btn-edit   { background: #eff6ff; color: #2563eb; }
.rm-pm-btn-delete { background: #fff1f2; color: #e11d48; }
.rm-pm-btn-edit:hover, .rm-pm-btn-delete:hover { opacity: 0.75; }

/* Empty State */
.rm-pm-empty {
    text-align: center;
    padding: 56px 24px;
    color: #94a3b8;
}
.rm-pm-empty svg { margin-bottom: 12px; opacity: 0.4; }
.rm-pm-empty p { font-size: 0.95rem; margin: 0; }

/* Modal */
.rm-pm-modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(15,23,42,0.45);
    z-index: 99999;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(2px);
}
.rm-pm-modal-overlay.is-open { display: flex; }
.rm-pm-modal {
    background: #fff;
    border-radius: 18px;
    padding: 32px;
    width: 100%;
    max-width: 440px;
    box-shadow: 0 20px 50px rgba(0,0,0,0.18);
    animation: rmModalIn 0.2s ease;
}
@keyframes rmModalIn {
    from { opacity: 0; transform: scale(0.95) translateY(10px); }
    to   { opacity: 1; transform: scale(1) translateY(0); }
}
.rm-pm-modal-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 22px;
}
.rm-pm-modal-field {
    margin-bottom: 16px;
}
.rm-pm-modal-field label {
    display: block;
    font-size: 0.8rem;
    font-weight: 600;
    color: #475569;
    margin-bottom: 6px;
}
.rm-pm-modal-field select,
.rm-pm-modal-field input {
    width: 100%;
    border: 1.5px solid #e2e8f0;
    border-radius: 8px;
    padding: 9px 12px;
    font-size: 0.9rem;
    color: #1e293b;
    outline: none;
    transition: border-color 0.2s;
    box-sizing: border-box;
}
.rm-pm-modal-field select:focus,
.rm-pm-modal-field input:focus { border-color: #10b981; }
.rm-pm-modal-actions {
    display: flex;
    gap: 10px;
    margin-top: 22px;
}
.rm-pm-modal-save {
    flex: 1;
    background: linear-gradient(135deg, #10b981, #059669);
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 10px;
    font-weight: 700;
    font-size: 0.9rem;
    cursor: pointer;
    transition: opacity 0.2s;
}
.rm-pm-modal-save:hover { opacity: 0.9; }
.rm-pm-modal-cancel {
    flex: 1;
    background: #f1f5f9;
    color: #475569;
    border: none;
    border-radius: 8px;
    padding: 10px;
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    transition: background 0.2s;
}
.rm-pm-modal-cancel:hover { background: #e2e8f0; }
.rm-pm-modal-response {
    font-size: 0.82rem;
    margin-top: 10px;
    min-height: 18px;
    text-align: center;
    font-weight: 600;
}
.rm-pm-modal-response.is-success { color: #10b981; }
.rm-pm-modal-response.is-error   { color: #e11d48; }
</style>

<div class="rm-pm-header">
    <h3><?php esc_html_e( 'Payment Methods', 'reseller-management' ); ?></h3>
    <button class="rm-pm-add-btn" id="rm-pm-open-add">
        <svg viewBox="0 0 24 24" width="15" height="15" fill="currentColor"><path d="M19 13H13v6h-2v-6H5v-2h6V5h2v6h6z"/></svg>
        <?php esc_html_e( 'Add Payment Method', 'reseller-management' ); ?>
    </button>
</div>

<?php if ( empty( $payment_methods ) ) : ?>
    <div class="rm-pm-empty">
        <svg viewBox="0 0 24 24" width="48" height="48" fill="#94a3b8"><path d="M20 4H4c-1.11 0-2 .89-2 2v12c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z"/></svg>
        <p><?php esc_html_e( 'No payment methods added yet. Click "Add Payment Method" to get started.', 'reseller-management' ); ?></p>
    </div>
<?php else : ?>
    <div class="rm-pm-grid">
        <?php foreach ( $payment_methods as $method ) :
            $key    = strtolower( (string) $method->method_name );
            $colors = $method_colors[ $key ] ?? [ 'bg' => '#f1f5f9', 'text' => '#475569', 'label' => ucfirst( $key ) ];
            ?>
            <div class="rm-pm-card"
                 data-id="<?php echo esc_attr( (string) $method->id ); ?>"
                 data-method="<?php echo esc_attr( $key ); ?>"
                 data-number="<?php echo esc_attr( (string) $method->number ); ?>"
                 data-type="<?php echo esc_attr( (string) $method->type ); ?>">
                <div class="rm-pm-card-top">
                    <span class="rm-pm-badge" style="background:<?php echo esc_attr( $colors['bg'] ); ?>;color:<?php echo esc_attr( $colors['text'] ); ?>;">
                        <?php echo esc_html( $colors['label'] ); ?>
                    </span>
                    <span class="rm-pm-type-tag"><?php echo esc_html( ucfirst( (string) $method->type ) ); ?></span>
                </div>
                <div class="rm-pm-number">
                    <small><?php esc_html_e( 'Account Number', 'reseller-management' ); ?></small>
                    <?php echo esc_html( (string) $method->number ); ?>
                </div>
                <div class="rm-pm-actions">
                    <button class="rm-pm-btn-edit" data-id="<?php echo esc_attr( (string) $method->id ); ?>">✏️ <?php esc_html_e( 'Edit', 'reseller-management' ); ?></button>
                    <button class="rm-pm-btn-delete" data-id="<?php echo esc_attr( (string) $method->id ); ?>">🗑 <?php esc_html_e( 'Delete', 'reseller-management' ); ?></button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Add / Edit Modal -->
<div class="rm-pm-modal-overlay" id="rm-pm-modal">
    <div class="rm-pm-modal">
        <h4 class="rm-pm-modal-title" id="rm-pm-modal-title"><?php esc_html_e( 'Add Payment Method', 'reseller-management' ); ?></h4>
        <input type="hidden" id="rm-pm-id" value="">

        <div class="rm-pm-modal-field">
            <label for="rm-pm-method-name"><?php esc_html_e( 'Method Name', 'reseller-management' ); ?></label>
            <select id="rm-pm-method-name">
                <option value=""><?php esc_html_e( '— Select Method —', 'reseller-management' ); ?></option>
                <option value="bkash">Bkash</option>
                <option value="nagad">Nagad</option>
                <option value="rocket">Rocket</option>
            </select>
        </div>

        <div class="rm-pm-modal-field">
            <label for="rm-pm-number"><?php esc_html_e( 'Account Number', 'reseller-management' ); ?></label>
            <input type="text" id="rm-pm-number" placeholder="01XXXXXXXXX">
        </div>

        <div class="rm-pm-modal-field">
            <label for="rm-pm-type"><?php esc_html_e( 'Account Type', 'reseller-management' ); ?></label>
            <select id="rm-pm-type">
                <option value="personal"><?php esc_html_e( 'Personal', 'reseller-management' ); ?></option>
                <option value="agent"><?php esc_html_e( 'Agent', 'reseller-management' ); ?></option>
            </select>
        </div>

        <div class="rm-pm-modal-response" id="rm-pm-response"></div>

        <div class="rm-pm-modal-actions">
            <button class="rm-pm-modal-save" id="rm-pm-save"><?php esc_html_e( 'Save', 'reseller-management' ); ?></button>
            <button class="rm-pm-modal-cancel" id="rm-pm-cancel"><?php esc_html_e( 'Cancel', 'reseller-management' ); ?></button>
        </div>
    </div>
</div>

<script>
(function ($) {
    var $modal   = $('#rm-pm-modal');
    var $title   = $('#rm-pm-modal-title');
    var $idField = $('#rm-pm-id');
    var $resp    = $('#rm-pm-response');

    function openModal(isEdit, cardData) {
        $modal.addClass('is-open');
        $resp.text('').removeClass('is-success is-error');
        if (isEdit && cardData) {
            $title.text('<?php esc_html_e( 'Edit Payment Method', 'reseller-management' ); ?>');
            $idField.val(cardData.id);
            $('#rm-pm-method-name').val(cardData.method);
            $('#rm-pm-number').val(cardData.number);
            $('#rm-pm-type').val(cardData.type);
        } else {
            $title.text('<?php esc_html_e( 'Add Payment Method', 'reseller-management' ); ?>');
            $idField.val('');
            $('#rm-pm-method-name').val('');
            $('#rm-pm-number').val('');
            $('#rm-pm-type').val('personal');
        }
    }

    function closeModal() { $modal.removeClass('is-open'); }

    $('#rm-pm-open-add').on('click', function () { openModal(false); });
    $('#rm-pm-cancel').on('click', closeModal);
    $modal.on('click', function (e) { if ($(e.target).is($modal)) closeModal(); });

    $(document).on('click', '.rm-pm-btn-edit', function () {
        var $card = $(this).closest('.rm-pm-card');
        openModal(true, {
            id:     $card.data('id'),
            method: $card.data('method'),
            number: $card.data('number'),
            type:   $card.data('type')
        });
    });

    $(document).on('click', '.rm-pm-btn-delete', function () {
        if (!confirm('<?php esc_html_e( 'Delete this payment method?', 'reseller-management' ); ?>')) return;
        var id = $(this).data('id');
        var $card = $(this).closest('.rm-pm-card');

        $.post(rmPublic.ajaxUrl, {
            action: 'reseller_delete_payment_method',
            nonce:  rmPublic.nonce,
            id:     id
        }, function (response) {
            if (response.success) {
                $card.remove();
                if ($('.rm-pm-card').length === 0) {
                    location.reload();
                }
            } else {
                alert(response.data || '<?php esc_html_e( 'Could not delete payment method.', 'reseller-management' ); ?>');
            }
        });
    });

    $('#rm-pm-save').on('click', function () {
        var method = $('#rm-pm-method-name').val();
        var number = $.trim($('#rm-pm-number').val());
        var type   = $('#rm-pm-type').val();
        var id     = $('#rm-pm-id').val();

        if (!method || !number || !type) {
            $resp.text('<?php esc_html_e( 'Please fill in all fields.', 'reseller-management' ); ?>').removeClass('is-success').addClass('is-error');
            return;
        }

        var $btn = $(this).prop('disabled', true).text('<?php esc_html_e( 'Saving…', 'reseller-management' ); ?>');

        $.post(rmPublic.ajaxUrl, {
            action:      'reseller_save_payment_method',
            nonce:       rmPublic.nonce,
            id:          id,
            method_name: method,
            number:      number,
            type:        type
        }, function (response) {
            if (response.success) {
                $resp.text(response.data).removeClass('is-error').addClass('is-success');
                setTimeout(function () { location.reload(); }, 900);
            } else {
                $resp.text(response.data || '<?php esc_html_e( 'Failed.', 'reseller-management' ); ?>').removeClass('is-success').addClass('is-error');
                $btn.prop('disabled', false).text('<?php esc_html_e( 'Save', 'reseller-management' ); ?>');
            }
        });
    });
})(jQuery);
</script>
