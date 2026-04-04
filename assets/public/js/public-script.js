(function ($) {
  function renderResponse($container, message, isSuccess) {
    if (!$container.length) {
      return;
    }

    $container
      .removeClass("is-error is-success")
      .addClass(isSuccess ? "is-success" : "is-error")
      .text(message);
  }

  function submitAjaxForm(config) {
    var $form = $(config.selector);
    if (!$form.length) {
      return;
    }

    $form.on("submit", function (event) {
      var formData;
      var $response = $form.find(".rm-form-response");

      event.preventDefault();

      if (typeof config.beforeSubmit === "function") {
        if (!config.beforeSubmit($form, $response)) {
          return;
        }
      }

      formData = new FormData(this);
      formData.append("action", config.action);
      formData.append("nonce", rmPublic.nonce);

      $.ajax({
        url: rmPublic.ajaxUrl,
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
          renderResponse(
            $response,
            response.data || "Request completed successfully.",
            !!response.success
          );

          if (response.success && config.resetOnSuccess) {
            $form[0].reset();
          }

          if (response.success && typeof config.onSuccess === "function") {
            config.onSuccess(response);
          }
        },
        error: function (xhr) {
          var response = xhr.responseJSON || {};
          renderResponse(
            $response,
            response.data || "Something went wrong. Please try again.",
            false
          );
        },
      });
    });
  }

  $(function () {
    // Profit Chart
    var profitCtx = document.getElementById('rm-profit-canvas');
    if (profitCtx) {
      var profitLabels = [];
      var profitValues = [];

      if (rmPublic.profit_data && rmPublic.profit_data.length > 0) {
        rmPublic.profit_data.reverse().forEach(function (item) {
          profitLabels.push(item.month_key);
          profitValues.push(parseFloat(item.total));
        });
      } else {
        // Fallback for empty data
        profitLabels = ['No Data'];
        profitValues = [0];
      }

      new Chart(profitCtx, {
        type: 'bar',
        data: {
          labels: profitLabels,
          datasets: [{
            label: 'Profit',
            data: profitValues,
            backgroundColor: '#004d40',
            borderRadius: 4
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { display: false } },
          scales: {
            y: { beginAtZero: true, ticks: { stepSize: 100 } },
            x: { grid: { display: false } }
          }
        }
      });
    }

    // Order Count Chart
    var orderCtx = document.getElementById('rm-order-count-canvas');
    if (orderCtx) {
      new Chart(orderCtx, {
        type: 'doughnut',
        data: {
          labels: ['Completed', 'Pending', 'Cancelled'],
          datasets: [{
            data: [
              rmPublic.order_stats.completed || 0,
              rmPublic.order_stats.pending || 0,
              rmPublic.order_stats.cancelled || 0
            ],
            backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
            borderWidth: 0,
            cutout: '70%'
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { display: false } }
        }
      });
    }

    // Sidebar Toggle
    $('.rm-sidebar-toggle').on('click', function () {
      $('.rm-dashboard-app').toggleClass('sidebar-collapsed');
    });

    // Sidebar Accordion Toggle
    $('.rm-nav-item-wrapper.has-children .rm-nav-link').on('click', function (e) {
      var $wrapper = $(this).closest('.rm-nav-item-wrapper');
      var $chevron = $(e.target).closest('.rm-nav-chevron');

      if ($chevron.length) {
        e.preventDefault();
        $wrapper.toggleClass('is-expanded');
      }
    });

    // Advanced Order Page Logic
    var $newOrderForm = $('#rm-create-order-form-advanced');
    if ($newOrderForm.length) {
      var orderItems = window.rmOrderPrefilledItems || [];
      var districtData = rmPublic.locations.districts || [];
      var thanaData = rmPublic.locations.thanas || {};

      // Initialize Districts
      var $districtSelect = $('#rm-order-district');
      var selectedDistrict = $districtSelect.val();

      districtData.forEach(function (district) {
        if ($districtSelect.find('option[value="' + district + '"]').length === 0) {
          $districtSelect.append('<option value="' + district + '">' + district + '</option>');
        }
      });

      // If editing, trigger thana load and render items
      if (selectedDistrict) {
        loadThanas(selectedDistrict);
      }

      if (orderItems.length > 0) {
        renderOrderItems();
      }

      // Handle Thana updates
      $districtSelect.on('change', function () {
        loadThanas($(this).val());
      });

      function loadThanas(district) {
        var $thanaSelect = $('#rm-order-thana');
        var currentThana = $thanaSelect.val();
        $thanaSelect.empty().append('<option value="">Search Sub City...</option>');

        if (thanaData[district]) {
          thanaData[district].forEach(function (thana) {
            var selected = (thana === currentThana) ? 'selected' : '';
            $thanaSelect.append('<option value="' + thana + '" ' + selected + '>' + thana + '</option>');
          });
        }
        // If currentThana exists but not in thanaData list for this district, add it back as selected
        if (currentThana && $thanaSelect.find('option[value="' + currentThana + '"]').length === 0) {
          $thanaSelect.append('<option value="' + currentThana + '" selected>' + currentThana + '</option>');
        }
      }

      // Product Search
      var $searchInput = $('#rm-product-search-input');
      var $searchResults = $('#rm-product-search-results');
      var searchTimeout;

      $searchInput.on('input', function () {
        var query = $(this).val();
        clearTimeout(searchTimeout);

        if (query.length < 2) {
          $searchResults.hide();
          return;
        }

        searchTimeout = setTimeout(function () {
          $.ajax({
            url: rmPublic.ajaxUrl,
            type: 'GET',
            data: {
              action: 'reseller_search_products',
              nonce: rmPublic.nonce,
              q: query
            },
            success: function (response) {
              if (response.success && response.data.length > 0) {
                var html = '';
                response.data.forEach(function (product) {
                  html += '<div class="rm-search-result-item" data-product=\'' + JSON.stringify(product) + '\'>';
                  html += '<img src="' + product.image + '" alt="" width="30">';
                  html += '<span>' + product.text + (product.sku ? ' (' + product.sku + ')' : '') + '</span>';
                  html += '<strong>' + product.price + '</strong>';
                  html += '</div>';
                });
                $searchResults.html(html).show();
              } else {
                $searchResults.hide();
              }
            }
          });
        }, 300);
      });

      // Add product from search
      $(document).on('click', '.rm-search-result-item', function () {
        var product = $(this).data('product');
        addProductToTable(product);
        $searchResults.hide();
        $searchInput.val('');
      });

      function addProductToTable(product) {
        var existing = orderItems.find(item => item.id === product.id);
        if (existing) {
          existing.quantity++;
        } else {
          orderItems.push({
            id: product.id,
            name: product.text,
            image: product.image,
            price: parseFloat(product.price),
            resale_price: parseFloat(product.recommended_price || product.price),
            recommended_price: parseFloat(product.recommended_price || product.price),
            quantity: 1,
            variants: product.variants || []
          });
        }
        renderOrderItems();
      }

      function renderOrderItems() {
        var $body = $('#rm-order-items-body');
        if (orderItems.length === 0) {
          $body.html('<tr class="rm-no-items"><td colspan="8">No products added yet.</td></tr>');
        } else {
          var html = '';
          orderItems.forEach((item, index) => {
            html += '<tr data-index="' + index + '">';
            html += '<td>' + (index + 1) + '</td>';
            html += '<td><div class="rm-item-product"><img src="' + item.image + '" alt=""><span>' + item.name + '</span></div></td>';

            // Variant select
            html += '<td>';
            if (item.variants.length > 0) {
              html += '<select class="rm-item-variant">';
              item.variants.forEach(v => {
                var label = Object.values(v.attributes).join(', ');
                var selected = (item.selected_variant == v.id) ? 'selected' : '';
                html += '<option value="' + v.id + '" data-price="' + v.price + '" data-recommended="' + v.recommended_price + '" ' + selected + '>' + label + '</option>';
              });
              html += '</select>';
            } else {
              html += '-';
            }
            html += '</td>';

            html += '<td><input type="number" class="rm-item-qty" value="' + item.quantity + '" min="1"></td>';
            html += '<td>' + item.price + '</td>';
            html += '<td><input type="number" class="rm-item-resale" value="' + item.resale_price + '"></td>';
            html += '<td>' + (item.resale_price * item.quantity).toFixed(2) + '</td>';
            html += '<td><button class="rm-item-remove">🗑</button></td>';
            html += '</tr>';
          });
          $body.html(html);
        }
        calculateTotals();
      }

      // Handle variant change
      $(document).on('change', '.rm-item-variant', function () {
        var $row = $(this).closest('tr');
        var index = $row.data('index');
        var variantId = $(this).val();
        var $opt = $(this).find('option:selected');

        orderItems[index].selected_variant = variantId;
        orderItems[index].price = parseFloat($opt.data('price'));
        orderItems[index].resale_price = parseFloat($opt.data('recommended'));

        renderOrderItems();
      });

      // Handle item updates
      $(document).on('input', '.rm-item-qty', function () {
        var index = $(this).closest('tr').data('index');
        orderItems[index].quantity = parseInt($(this).val()) || 1;
        renderOrderItems();
      });

      $(document).on('input', '.rm-item-resale', function () {
        var index = $(this).closest('tr').data('index');
        orderItems[index].resale_price = parseFloat($(this).val()) || 0;
        calculateTotals(); // Just update totals, don't re-render unless needed for subtotal column
        $(this).closest('tr').find('td:nth-child(7)').text((orderItems[index].resale_price * orderItems[index].quantity).toFixed(2));
      });

      $(document).on('click', '.rm-item-remove', function () {
        var index = $(this).closest('tr').data('index');
        orderItems.splice(index, 1);
        renderOrderItems();
      });

      function calculateTotals() {
        var total = 0;
        var baseTotal = 0;
        orderItems.forEach(item => {
          total += item.resale_price * item.quantity;
          baseTotal += item.price * item.quantity;
        });

        var shipping = parseFloat($('#rm-shipping-charge').val()) || 0;
        var discount = parseFloat($('#rm-discount').val()) || 0;
        var paid = parseFloat($('#rm-paid-amount').val()) || 0;

        var payable = total + shipping - discount;
        var due = payable - paid;
        var profit = (total - baseTotal) - discount;

        $('#rm-summary-total').text(total.toFixed(2));
        $('#rm-summary-payable').text(payable.toFixed(2));
        $('#rm-summary-due').text(due.toFixed(2));
        $('#rm-summary-profit').text(profit.toFixed(2));
      }

      $('#rm-shipping-charge, #rm-discount, #rm-paid-amount').on('input', calculateTotals);

      // Submit Order
      $('#rm-submit-order-advanced').on('click', function () {
        if (orderItems.length === 0) {
          alert('Please add at least one product.');
          return;
        }

        var isEdit = $('input[name="is_edit"]').val() === '1';
        var orderId = $('input[name="order_id"]').val();

        var data = {
          action: isEdit ? 'reseller_update_order' : 'reseller_create_order',
          nonce: rmPublic.nonce,
          order_id: orderId,
          customer_name: $('input[name="customer_name"]').val(),
          customer_phone: $('input[name="customer_phone"]').val(),
          customer_address: $('textarea[name="customer_address"]').val(),
          district: $('#rm-order-district').val(),
          thana: $('#rm-order-thana').val(),
          order_notes: $('textarea[name="order_notes"]').val(),
          shipping_charge: $('#rm-shipping-charge').val(),
          discount: $('#rm-discount').val(),
          paid_amount: $('#rm-paid-amount').val(),
          items: orderItems.map(item => ({
            product_id: item.id,
            quantity: item.quantity,
            resale_price: item.resale_price
          }))
        };

        var $btn = $('#rm-submit-order-advanced');
        $btn.prop('disabled', true).text('Submitting...');

        $.ajax({
          url: rmPublic.ajaxUrl,
          type: 'POST',
          data: data,
          success: function (rawResponse) {
            var response;
            try {
              response = (typeof rawResponse === 'object') ? rawResponse : JSON.parse(rawResponse);
            } catch (e) {
              var $response = $('.rm-order-actions .rm-form-response').first();
              renderResponse($response, 'Technical Error: Invalid server response.', false);
              $btn.prop('disabled', false).text(isEdit ? 'Update Order' : 'Submit');
              return;
            }

            if (response.success) {
              var $response = $('.rm-order-actions .rm-form-response').first();
              renderResponse($response, response.data, true);
              $btn.text(isEdit ? 'Order Updated!' : 'Order Created!');

              setTimeout(function () {
                var url = new URL(window.location.href);
                url.searchParams.set('success', '1');
                window.location.href = url.toString();
              }, 1500);
            } else {
              var errorMsg = (typeof response.data === 'string') ? response.data : (response.data.message || 'Failed to process order.');
              var $response = $('.rm-order-actions .rm-form-response').first();
              renderResponse($response, errorMsg, false);
              $btn.prop('disabled', false).text(isEdit ? 'Update Order' : 'Submit');
            }
          },
          error: function (xhr) {
            var $response = $('.rm-order-actions .rm-form-response').first();
            var errorText = xhr.responseText ? xhr.responseText.substring(0, 100) : 'Service unavailable';
            renderResponse($response, 'Error: ' + errorText, false);
            $btn.prop('disabled', false).text('Submit');
          }
        });
      });
    }

    // Action Dropdown Toggle
    $(document).on('click', '.rm-btn-action-trigger', function (e) {
      e.stopPropagation();
      $('.rm-action-dropdown-menu').not($(this).next()).removeClass('is-active');
      $(this).next('.rm-action-dropdown-menu').toggleClass('is-active');
    });

    $(document).on('click', function () {
      $('.rm-action-dropdown-menu').removeClass('is-active');
    });

    // Handle Order Status Update
    $(document).on('click', '.rm-action-dropdown-menu .rm-dropdown-item[data-status]', function (e) {
      e.preventDefault();
      var $item = $(this);
      var orderId = $item.data('order-id');
      var newStatus = $item.data('status');
      var $container = $item.closest('.rm-action-dropdown-container');
      var $trigger = $container.find('.rm-btn-action-trigger');

      var statusNames = {
        'pending': 'Pending',
        'processing': 'New',
        'on-hold': 'Confirmed',
        'completed': 'Completed',
        'cancelled': 'Cancel',
        'refunded': 'Returned',
        'failed': 'Failed'
      };

      var newStatusLabel = statusNames[newStatus] || newStatus;

      if (!confirm('Are you sure you want to change the order status to ' + newStatusLabel + '?')) {
        return;
      }

      var $row = $item.closest('tr');
      var $statusBadge = $row.find('.rm-status-badge');
      var currentStatusClass = $statusBadge.attr('class').match(/status-\S+/);
      var currentStatus = currentStatusClass ? currentStatusClass[0].replace('status-', '') : '';

      $trigger.prop('disabled', true).css('opacity', '0.5');

      $.ajax({
        url: rmPublic.ajaxUrl,
        type: 'POST',
        data: {
          action: 'reseller_update_order_status',
          nonce: rmPublic.nonce,
          order_id: orderId,
          status: newStatus
        },
        success: function (response) {
          if (response.success) {
            // Update the badge
            $statusBadge.removeClass(function (index, className) {
              return (className.match(/(^|\s)status-\S+/g) || []).join(' ');
            });
            $statusBadge.addClass('status-' + newStatus);
            $statusBadge.text(newStatusLabel);

            // Close the dropdown
            $('.rm-action-dropdown-menu').removeClass('is-active');

            // Enable the trigger button
            $trigger.prop('disabled', false).css('opacity', '1');

            // Optionally show a non-intrusive toast instead of an alert
            // But we can stick to alert if the user likes it, or just use a toast if available.
            // Let's just use alert as before.
            alert(response.data || 'Status updated successfully.');

            // Update stat counters
            var oldStatCard = $('.rm-order-stat-card[href*="' + currentStatus + '"] .rm-stat-count');
            var newStatCard = $('.rm-order-stat-card[href*="' + newStatus + '"] .rm-stat-count');

            // Mapping complex statuses back to their specific keys for counters
            var oldStatusKey = currentStatus;
            var newStatusKey = newStatus;

            if (currentStatus === 'processing') oldStatusKey = 'new';
            if (currentStatus === 'on-hold') oldStatusKey = 'confirmed';
            if (currentStatus === 'completed') oldStatusKey = 'delivered';
            if (currentStatus === 'refunded') oldStatusKey = 'returned';
            if (currentStatus === 'cancelled') oldStatusKey = 'cancel';
            if (currentStatus === 'failed') oldStatusKey = 'incomplete';

            if (newStatus === 'processing') newStatusKey = 'new';
            if (newStatus === 'on-hold') newStatusKey = 'confirmed';
            if (newStatus === 'completed') newStatusKey = 'delivered';
            if (newStatus === 'refunded') newStatusKey = 'returned';
            if (newStatus === 'cancelled') newStatusKey = 'cancel';
            if (newStatus === 'failed') newStatusKey = 'incomplete';

            var $oldStatEl = $('.rm-order-stat-card[href$="subtab=' + oldStatusKey + '"] .rm-stat-count, .rm-order-stat-card[href$="subtab=new"] .rm-stat-count').filter(function () { return $(this).closest('a').attr('href').indexOf('subtab=' + oldStatusKey) > -1; });
            var $newStatEl = $('.rm-order-stat-card[href$="subtab=' + newStatusKey + '"] .rm-stat-count, .rm-order-stat-card[href$="subtab=new"] .rm-stat-count').filter(function () { return $(this).closest('a').attr('href').indexOf('subtab=' + newStatusKey) > -1; });

            if ($oldStatEl.length) $oldStatEl.text(Math.max(0, parseInt($oldStatEl.text() || 0) - 1));
            if ($newStatEl.length) $newStatEl.text(parseInt($newStatEl.text() || 0) + 1);

          } else {
            alert(response.data || 'Failed to update status.');
            $trigger.prop('disabled', false).css('opacity', '1');
          }
        },
        error: function () {
          alert('An error occurred. Please try again.');
          $trigger.prop('disabled', false).css('opacity', '1');
        }
      });
    });
  });
})(jQuery);
