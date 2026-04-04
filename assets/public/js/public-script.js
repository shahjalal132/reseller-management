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
        rmPublic.profit_data.reverse().forEach(function(item) {
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
    $('.rm-sidebar-toggle').on('click', function() {
      $('.rm-dashboard-app').toggleClass('sidebar-collapsed');
    });

    submitAjaxForm({
      selector: "#rm-registration-form",
      action: "reseller_register_user",
      resetOnSuccess: true,
      beforeSubmit: function ($form, $response) {
        var password = $form.find('input[name="password"]').val();
        var confirmPassword = $form.find('input[name="confirm_password"]').val();

        if (password !== confirmPassword) {
          renderResponse($response, "Passwords do not match.", false);
          return false;
        }

        return true;
      },
      onSuccess: function () {
        $("#rm-registration-form")
          .find('input[type="file"]')
          .val("");
      },
    });

    submitAjaxForm({
      selector: "#rm-create-order-form",
      action: "reseller_create_order",
      resetOnSuccess: true,
      onSuccess: function () {
        window.setTimeout(function () {
          window.location.reload();
        }, 800);
      },
    });

    submitAjaxForm({
      selector: "#rm-withdrawal-form",
      action: "reseller_request_withdrawal",
      resetOnSuccess: true,
      onSuccess: function () {
        window.setTimeout(function () {
          window.location.reload();
        }, 800);
      },
    });

    submitAjaxForm({
      selector: "#rm-profile-form",
      action: "reseller_update_profile",
    });

    submitAjaxForm({
      selector: "#rm-password-form",
      action: "reseller_change_password",
      resetOnSuccess: true,
      beforeSubmit: function ($form, $response) {
        var password = $form.find('input[name="password"]').val();
        var confirmPassword = $form.find('input[name="confirm_password"]').val();

        if (password !== confirmPassword) {
          renderResponse($response, "Passwords do not match.", false);
          return false;
        }

        return true;
      },
      onSuccess: function () {
        window.setTimeout(function () {
          window.location.reload();
        }, 1200);
      },
    });
  });
})(jQuery);
