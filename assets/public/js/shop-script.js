(function ($) {
  'use strict';

  if (typeof rmShop === 'undefined') {
    return;
  }

  function updateCartBadge(count, total) {
    $('.rm-shop-cart-count').text(count || 0);
    if (typeof total !== 'undefined') {
      $('.rm-shop-cart-total-value').text(Number(total).toFixed(0));
    }
  }

  function showMsg($el, message, ok) {
    if (!$el.length) return;
    $el
      .removeClass('is-success is-error')
      .addClass(ok ? 'is-success' : 'is-error')
      .text(message)
      .show();
  }

  // Catalog / simple add / Order Now
  $(document).on('click', '.rm-shop-add-btn:not([disabled])', function (e) {
    if ($(this).is('a')) {
      return;
    }
    e.preventDefault();
    var $btn = $(this);
    var productId = parseInt($btn.data('product-id'), 10);
    var isVariable = String($btn.data('variable')) === '1';
    var redirect = $btn.data('redirect');
    var originalText = $btn.html();
    var qty = 1;

    if (isVariable) {
      var $select = $('.rm-shop-variation-select');
      productId = parseInt($select.val(), 10);
      if (!productId) {
        alert('Please select an option.');
        return;
      }
    }

    var $qty = $btn.closest('.rm-spd, .rm-shop-product-detail, article').find('.rm-shop-qty');
    if ($qty.length) {
      qty = parseInt($qty.val(), 10) || 1;
    }

    $btn.prop('disabled', true);

    $.post(rmShop.ajaxUrl, {
      action: 'reseller_shop_add_to_cart',
      nonce: rmShop.nonce,
      shop_slug: rmShop.shopSlug,
      product_id: productId,
      quantity: qty
    })
      .done(function (res) {
        if (res && res.success) {
          updateCartBadge(res.data.cartCount, res.data.cartTotal);
          $('.rm-spd-float-count').text((res.data.cartCount || 0) + ' items');
          if (redirect === 'checkout' && rmShop.shopUrl) {
            window.location.href = rmShop.shopUrl.replace(/\/?$/, '/') + 'checkout/';
            return;
          }
          if (res.data.message) {
            $btn.text(res.data.message);
            setTimeout(function () {
              $btn.html(originalText);
            }, 1200);
          }
        } else {
          alert((res && res.data && res.data.message) || rmShop.i18n.error);
        }
      })
      .fail(function () {
        alert(rmShop.i18n.error);
      })
      .always(function () {
        $btn.prop('disabled', false);
      });
  });

  // Variation price preview
  $(document).on('change', '.rm-shop-variation-select', function () {
    var price = $(this).find(':selected').data('price');
    if (price !== undefined) {
      $('.rm-shop-detail-price-val, .rm-shop-detail-price span').text(parseFloat(price).toFixed(0));
    }
  });

  // Qty +/-
  $(document).on('click', '.rm-spd-qty-btn', function () {
    var dir = parseInt($(this).data('dir'), 10) || 0;
    var $input = $(this).closest('.rm-spd-qty').find('.rm-shop-qty');
    var val = parseInt($input.val(), 10) || 1;
    val = Math.max(1, val + dir);
    $input.val(val);
  });

  // Gallery thumbs
  $(document).on('click', '.rm-spd-thumb', function () {
    var full = $(this).data('full');
    if (!full) return;
    $('#rm-spd-main-img').attr('src', full);
    $('.rm-spd-thumb').removeClass('is-active');
    $(this).addClass('is-active');
  });

  // Tabs
  $(document).on('click', '.rm-spd-tab', function () {
    var tab = $(this).data('tab');
    $('.rm-spd-tab').removeClass('is-active');
    $(this).addClass('is-active');
    $('.rm-spd-panel').attr('hidden', true).removeClass('is-active');
    $('.rm-spd-panel[data-panel="' + tab + '"]').removeAttr('hidden').addClass('is-active');
  });

  // Wishlist UI only
  $(document).on('click', '.rm-spd-wishlist', function () {
    var $btn = $(this);
    $btn.text('Saved!');
    setTimeout(function () {
      $btn.html('<svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor" aria-hidden="true"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg> Add to Wishlist');
    }, 1200);
  });

  // Cart qty update
  function updateCheckoutTotals() {
    var subtotal = 0;
    $('.rm-chk-table tbody tr').each(function () {
      var $input = $(this).find('.rm-shop-cart-qty');
      var price = parseFloat($input.data('price')) || 0;
      var qty = parseInt($input.val(), 10) || 0;
      var line = price * qty;
      subtotal += line;
      $(this).find('.rm-chk-line-total').text('৳ ' + Math.round(line));
    });
    var shipping = parseFloat($('#rm-chk-shipping-charge').val()) || 0;
    $('#rm-chk-subtotal').text(Math.round(subtotal));
    $('#rm-chk-shipping').text(Math.round(shipping));
    $('#rm-chk-payable').text(Math.round(subtotal + shipping));
    $('.rm-shop-cart-total-value').text(Math.round(subtotal));
    $('.rm-chk').attr('data-subtotal', subtotal);
  }

  $(document).on('change', '.rm-shop-cart-qty', function () {
    var productId = $(this).data('product-id');
    var quantity = parseInt($(this).val(), 10) || 1;

    $.post(rmShop.ajaxUrl, {
      action: 'reseller_shop_update_cart',
      nonce: rmShop.nonce,
      shop_slug: rmShop.shopSlug,
      product_id: productId,
      quantity: quantity
    }).done(function (res) {
      if (res && res.success) {
        updateCartBadge(res.data.cartCount, res.data.cartTotal);
        $('.rm-shop-cart-total-value').text(Math.round(parseFloat(res.data.cartTotal) || 0));
        $('.rm-spd-float-count').text((res.data.cartCount || 0) + ' items');
        if ($('.rm-chk-table').length) {
          updateCheckoutTotals();
        } else {
          window.location.reload();
        }
      }
    });
  });

  $(document).on('click', '.rm-chk-qty-btn', function () {
    var dir = parseInt($(this).data('dir'), 10) || 0;
    var productId = $(this).data('product-id');
    var $input = $('.rm-shop-cart-qty[data-product-id="' + productId + '"]');
    var val = Math.max(1, (parseInt($input.val(), 10) || 1) + dir);
    $input.val(val).trigger('change');
  });

  $(document).on('click', '.rm-shop-remove-item', function () {
    var productId = $(this).data('product-id');
    $.post(rmShop.ajaxUrl, {
      action: 'reseller_shop_remove_cart_item',
      nonce: rmShop.nonce,
      shop_slug: rmShop.shopSlug,
      product_id: productId,
      quantity: 0
    }).done(function () {
      window.location.reload();
    });
  });

  // Area → shipping
  $(document).on('change', '#rm-chk-area', function () {
    var $opt = $(this).find(':selected');
    var charge = parseFloat($opt.data('charge')) || 0;
    var title = $(this).val() || '';
    $('#rm-chk-shipping-charge').val(charge);
    $('#rm-chk-district').val(title);
    updateCheckoutTotals();
  });

  // Coupon (UI only)
  $(document).on('click', '#rm-chk-coupon-apply', function () {
    var code = $.trim($('#rm-chk-coupon-input').val());
    var $msg = $('.rm-chk-coupon-msg');
    if (!code) {
      $msg.text('Please enter a coupon code.');
      return;
    }
    $msg.text('Invalid coupon code.');
  });

  // District → thana (legacy cart forms)
  var thanas = window.rmShopThanas || {};
  $('#rm-shop-district').on('change', function () {
    var district = $(this).val();
    var $thana = $('#rm-shop-thana');
    $thana.empty().append($('<option/>').val('').text('Select thana'));
    (thanas[district] || []).forEach(function (name) {
      $thana.append($('<option/>').val(name).text(name));
    });
  });

  // Checkout
  $('#rm-shop-checkout-form').on('submit', function (e) {
    e.preventDefault();
    var $form = $(this);
    var $btn = $('#rm-shop-place-order');
    var $resp = $form.find('.rm-form-response');
    var btnHtml = $btn.html();

    $btn.prop('disabled', true).addClass('is-loading').html(
      '<span class="rm-chk-spinner" aria-hidden="true"></span> Placing Order…'
    );

    $.post(rmShop.ajaxUrl, {
      action: 'reseller_shop_place_order',
      nonce: rmShop.nonce,
      shop_slug: rmShop.shopSlug,
      customer_name: $form.find('[name="customer_name"]').val(),
      customer_phone: $form.find('[name="customer_phone"]').val(),
      customer_address: $form.find('[name="customer_address"]').val(),
      shipping_area: $form.find('[name="shipping_area"]').val(),
      shipping_charge: $form.find('[name="shipping_charge"]').val(),
      district: $form.find('[name="district"]').val(),
      thana: $form.find('[name="thana"]').val(),
      order_notes: $form.find('[name="order_notes"]').val()
    })
      .done(function (res) {
        if (res && res.success) {
          showMsg($resp, res.data.message, true);
          updateCartBadge(0, 0);
          $btn.html('<span class="rm-chk-spinner" aria-hidden="true"></span> Redirecting…');
          var redirect = res.data.redirect || res.data.thank_you_url;
          if (redirect) {
            window.location.href = redirect;
            return;
          }
          setTimeout(function () {
            window.location.href = rmShop.shopUrl;
          }, 1500);
          return;
        }
        showMsg($resp, (res && res.data && res.data.message) || rmShop.i18n.error, false);
        $btn.prop('disabled', false).removeClass('is-loading').html(btnHtml);
      })
      .fail(function () {
        showMsg($resp, rmShop.i18n.error, false);
        $btn.prop('disabled', false).removeClass('is-loading').html(btnHtml);
      });
  });
})(jQuery);
