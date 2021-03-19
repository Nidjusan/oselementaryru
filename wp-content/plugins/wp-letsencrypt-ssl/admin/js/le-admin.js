(function ($) {
  "use strict";

  $(".le-section-title").click(function () {
    var scn = $(this).attr("data-section");
    $(".le-section-title").removeClass("active");
    $(this).addClass("active");

    localStorage.setItem('le-state', scn);

    $(".le-section.active").fadeOut("fast").removeClass("active").promise().done(function () {
      $("." + scn).fadeIn("fast").addClass("active");
    });

  });

  //restore last tab
  if (localStorage.getItem('le-state') !== null) {
    var section = localStorage.getItem('le-state');

    $(".le-section-title[data-section=" + section + "]").click();
  }

  // Since 2.5.0  
  $('.wple-tooltip').each(function () {
    var $this = $(this);

    tippy('.wple-tooltip:not(.bottom)', {
      //content: $this.attr('data-content'),
      placement: 'top',
      onShow(instance) {
        instance.popper.hidden = instance.reference.dataset.tippy ? false : true;
        instance.setContent(instance.reference.dataset.tippy);
      }
      //arrow: false
    });

    tippy('.wple-tooltip.bottom', {
      //content: $this.attr('data-content'),
      placement: 'bottom',
      onShow(instance) {
        instance.popper.hidden = instance.reference.dataset.tippy ? false : true;
        instance.setContent(instance.reference.dataset.tippy);
      }
      //arrow: false
    });
  });

  $(".toggle-debugger").click(function () {
    $(this).find("span").toggleClass("rotate");

    $(".le-debugger").slideToggle('fast');
  });

  //since 4.6.0
  $("#admin-verify-dns").submit(function (e) {
    e.preventDefault();

    var $this = $(this);

    jQuery.ajax({
      method: "POST",
      url: ajaxurl.replace('https', 'http'),
      dataType: "text",
      data: {
        action: 'wple_admin_dnsverify',
        nc: $("#checkdns").val()
      },
      beforeSend: function () {
        $(".dns-notvalid").removeClass("active");
        $this.addClass("buttonrotate");
        $this.find("button").attr("disabled", true);
      },
      error: function () {
        $(".dns-notvalid").removeClass("active");
        $this.removeClass("buttonrotate");
        $this.find("button").removeAttr("disabled");
        alert("Something went wrong! Please try again");
      },
      success: function (response) {
        $this.removeClass("buttonrotate");
        $this.find("button").removeAttr("disabled");

        if (response === '1') {
          $this.find("button").text("Verified");
          setTimeout(function () {
            window.location.href = window.location.href + "&wpleauto=dns";
            exit();
          }, 1000);

          // } else if (response !== 'fail') {
          //   alert("Partially verified. Could not verify " + String(response));
        } else {
          $(".dns-notvalid").addClass("active");
        }
      }
    });

    return false;
  });

  //since 4.7.0
  $("#verify-subdns").click(function (e) {
    e.preventDefault();

    var $this = $(this);

    jQuery.ajax({
      method: "POST",
      url: ajaxurl.replace('https', 'http'),
      dataType: "text",
      data: {
        action: 'wple_admin_dnsverify',
        nc: $this.prev().val()
      },
      beforeSend: function () {
        $(".dns-notvalid").removeClass("active");
        $this.addClass("buttonrotate");
        $this.attr("disabled", true);
      },
      error: function () {
        $(".dns-notvalid").removeClass("active");
        $this.removeClass("buttonrotate");
        $this.removeAttr("disabled");
        alert("Something went wrong! Please try again");
      },
      success: function (response) {
        $this.removeClass("buttonrotate");
        $this.removeAttr("disabled");

        if (response === '1') {
          $this.text("Verified");
          $("#wple-error-popper .wple-error").hide();
          $("#wple-error-popper").fadeIn('fast');
          $("#wple-error-popper .wple-flex img").show();

          setTimeout(function () {
            window.location.href = window.location.href + "&subdir=1&wpleauto=dns";
            exit();
          }, 1000);

          // } else if (response !== 'fail') {
          //   alert("Partially verified. Could not verify " + String(response));
        } else {
          $(".dns-notvalid").addClass("active");
        }
      }
    });

    return false;
  });

  $("#verify-subhttp").click(function (e) {
    e.preventDefault();

    var $this = $(this);

    jQuery.ajax({
      method: "POST",
      url: ajaxurl.replace('https', 'http'),
      dataType: "text",
      data: {
        action: 'wple_admin_httpverify',
        nc: $this.prev().val()
      },
      beforeSend: function () {
        $(".http-notvalid").removeClass("active");
        $this.addClass("buttonrotate");
        $this.attr("disabled", true);
      },
      error: function () {
        $(".http-notvalid").removeClass("active");
        $this.removeClass("buttonrotate");
        $this.removeAttr("disabled");
        alert("Something went wrong! Please try again");
      },
      success: function (response) {
        $this.removeClass("buttonrotate");
        $this.removeAttr("disabled");

        if (response === '1') {
          $this.text("Verified");
          $("#wple-error-popper .wple-error").hide();
          $("#wple-error-popper").fadeIn('fast');
          $("#wple-error-popper .wple-flex img").show();

          setTimeout(function () {
            window.location.href = window.location.href + "&subdir=1&wpleauto=http";
            return false;
          }, 1000);

          // } else if (response !== 'fail') {
          //   alert("Partially verified. Could not verify " + String(response));
        } else {
          $(".http-notvalid").addClass("active");
        }
      }
    });

    return false;
  });

  //since 4.7.1
  $("#singledvssl").click(function (e) {
    //e.preventDefault();

    var flag = 0;
    if ($("input.wple_email").val() == '') {
      flag = 1;
      $("#wple-error-popper .wple-error").text('Email address is required');
      $("#wple-error-popper").fadeIn('slow');
    } else if (!$("input.wple_agree_le").is(":checked") || !$("input.wple_agree_gws").is(":checked")) {
      flag = 1;
      $("#wple-error-popper .wple-error").text('Agree to TOS required');
      $("#wple-error-popper").fadeIn('slow');
    }

    if (flag == 0) {
      $("#wple-error-popper .wple-error").hide();
      $("#wple-error-popper").fadeIn('fast');
      $("#wple-error-popper .wple-flex img").show();
      //$(this).closest(".le-genform").submit();
    } else {
      setTimeout(function () {
        $("#wple-error-popper").fadeOut(500);
      }, 2000);
      return false;
    }

  });

  
/* Premium Code Stripped by Freemius */


  $(".wple_include_www").change(function () {
    if ($(this).is(":checked")) {
      $(".wple-www").addClass("active");
    } else {
      $(".wple-www").removeClass("active");
    }
  });

  $(".single-wildcard-switch").change(function () {
    if ($(this).is(":checked")) {
      $(".single-genform").fadeOut('fast');
      $(".wildcard-genform").fadeIn('fast');
      $(".wple-wc").addClass("active");
    } else {
      $(".wildcard-genform").fadeOut('fast');
      $(".single-genform").fadeIn('fast');
      $(".wple-wc").removeClass("active");
    }
  });

  $(".initplan-switch").change(function () {
    if ($(this).is(":checked")) {
      $(".wplepricingcol.proplan").removeClass("hiddenplan");
      $(".wplepricingcol.firewallplan").addClass("hiddenplan");
    } else {
      $(".wplepricingcol.proplan").addClass("hiddenplan");
      $(".wplepricingcol.firewallplan").removeClass("hiddenplan");
    }
  });

  var wple_scan_complete = () => {

    jQuery.ajax({
      method: "POST",
      url: SCAN.adminajax,
      dataType: "html",
      data: {
        action: 'wple_clearreport',
        nc: $(".wple-scan").attr("data-nc"),
      },
      beforeSend: function () {},
      error: function () {},
      success: function (response) {
        console.log('cleaned');
      }
    });

    setTimeout(function () { //wait to gather data on scanner
      $(".wple-scanbar").css("animation", "none").text("Populating Mixed Content Stats! Please wait...").addClass("complete");

      setTimeout(function () {
        jQuery.ajax({
          method: "GET",
          url: SCAN.adminajax,
          dataType: "json",
          data: {
            action: 'wple_get_scanreports'
          },
          beforeSend: function () {},
          error: function () {
            alert("Error during scan results retrieval. Please reload the page and try again.");
          },
          success: function (response) {
            if (response.length) {

              var table = '<table><thead><th>Mixed Content URL</th><th>Violation</th><th>Source File</th><th>Line Number</th></thead><tbody>';

              $.each(response, (index, rep) => {
                var srcfile = rep.source_file;
                var lnumber = rep.line_number;

                if (typeof srcfile == 'undefined') {
                  srcfile = '';
                }

                if (typeof lnumber == 'undefined') {
                  lnumber = 0;
                }

                table += '<tr><td>' + String(rep.blocked_uri) + '</td><td>' + String(rep.violated_directive) + '</td><td>' + String(srcfile) + '</td><td>' + parseInt(lnumber) + '</td></tr>';
              });

              table += '</tbody></table><h4>"Force HTTPS via WordPress" method of <b>FORCE HTTPS</b> should fix most of these issues. Other img-src or such issues might be coming from widgets, css files. These should be manually identified and fixed. <br>If you found above results helpful, Please take a moment to <a href="https://wordpress.org/support/plugin/wp-letsencrypt-ssl/reviews/#new-post" target="_blank">leave a positive review</a>.</h4><small>Please clear browser cache once after fixing mixed content issues. If you want to scan another url / page, please reload this page.</small>';

              $("#wple-scanner-iframe").fadeOut('fast');
              $("#wple-scanresults").html(table);
              $(".wple-scan").text('COMPLETE');

            } else {
              $(".wple-scan").text('COMPLETE');
              $(".wple-scanbar").text("All good! Mixed content issues not found.").addClass("success");
              $(".wple-frameholder").slideUp('fast');
            }
          }
        });

      }, 10000);

    }, 5000);
  }

  jQuery(".wple-scan").click(function () {
    var $button = $(this);
    $(".wple-frameholder").html('');
    $(this).text('SCANNING').attr("disabled", "disabled");

    jQuery.ajax({
      method: "POST",
      url: SCAN.adminajax,
      dataType: "html",
      data: {
        action: 'wple_start_scanner',
        nc: $button.attr("data-nc"),
      },
      beforeSend: function () {},
      error: function () {
        alert("Could not initiate request! Please try later.");
        $button.text('SCAN').removeAttr("disabled");
      },
      success: function (response) {

        if (response == 'fail') {
          $button.text('SCAN').removeAttr("disabled");
          alert("Please make your .htaccess writable!");
          return false;
        }

        if (response == 'nossl') {
          $button.text('SCAN').removeAttr("disabled");
          alert("Valid SSL Certificate could not be detected on your site! Please install SSL Certificate before checking for mixed content issues.");
          return false;
        }

        $("#wple-scanner-iframe").css("height", "510px");

        var frm = document.createElement("iframe");
        frm.onload = function () {
          wple_scan_complete();
        }
        frm.src = SCAN.base + $(".wple_scanpath").val().replace('http://', '').replace('https://', '');
        frm.width = 500;
        frm.height = 500;
        frm.scrolling = 'no';
        document.getElementsByClassName("wple-frameholder")[0].appendChild(frm);

      }
    });

  });

  /**
   * v5.2.6
   */
  var handler = FS.Checkout.configure({
    plugin_id: '5090',
    plan_id: '10643',
    public_key: 'pk_f6a07c106bf4ef064d9ac4b989e02',
    image: 'https://s3-us-west-2.amazonaws.com/freemius/plugins/5090/icons/766cb1e9dfd1b9436c3fb2c489a667ea.png'
  });

  $('#upgradetocdn').on('click', function (e) {
    handler.open({
      name: 'WP Encryption',
      licenses: 1,
      // You can consume the response for after purchase logic.
      purchaseCompleted: function (response) {
        // The logic here will be executed immediately after the purchase confirmation.                                // alert(response.user.email);
      },
      success: function (response) {
        // The logic here will be executed after the customer closes the checkout, after a successful purchase.                                // alert(response.user.email);
      }
    });
    e.preventDefault();
  });

  $(".have-root-ssh").click(function () {
    $(this).siblings().removeClass("active");
    $(this).addClass("active");

    $(".rootssh-check").fadeOut('fast');
    $(".havessh").fadeIn("fast");
  });

  $(".no-root-ssh").click(function () {
    $(this).siblings().removeClass("active");
    $(this).addClass("active");

    $(".rootssh-check").fadeOut('fast');
    $(".nossh").fadeIn("fast");
  });

  $(".check-root-ssh li").click(function () {
    $(".nocp-ssl-validation").show();
  });

  $("#validate-nocp-ssl").click(function () {
    var $this = $(this);

    jQuery.ajax({
      method: "GET",
      url: ajaxurl,
      dataType: "text",
      data: {
        action: 'wple_validate_ssl'
      },
      beforeSend: function () {
        $this.find("span").show();
        $(".wple-validate-nossl").hide();
      },
      error: function () {
        $this.find("span").hide();
        alert("Could not validate SSL! Please try later.");
      },
      success: function (response) {
        $this.find("span").hide();

        if (response == 1) {
          var currenthref = window.location.href;
          window.location.href = currenthref.substr(0, currenthref.indexOf('&')) + "&success=1";
          return false;
        } else {
          $(".wple-validate-nossl").fadeIn("fast");
        }
      }
    });
  });

  $(".email-certs-switch,.disable-spmode-switch").change(function () {
    var $this = $(this);

    jQuery.ajax({
      method: "POST",
      url: ajaxurl,
      dataType: "text",
      data: {
        action: 'wple_email_certs',
        emailcert: $(".email-certs-switch").is(":checked"),
        spmode: $(".disable-spmode-switch").is(":checked"),
      },
      beforeSend: function () {},
      error: function () {
        alert("Failed to save opt! Please try again");
      },
      success: function (response) {
        alert("Settings Saved!");
      }
    });
  });

  $(".wple-did-review,.wple-later-review").click(function (e) {
    var $this = $(this);
    e.preventDefault();

    jQuery.ajax({
      method: "POST",
      url: ajaxurl,
      dataType: "text",
      data: {
        action: 'wple_review_notice',
        nc: $this.attr("data-nc"),
        choice: $this.attr("data-action")
      },
      beforeSend: function () {},
      error: function () {
        alert("Failed to save! Please try again");
      },
      success: function (response) {
        $(".wple-admin-review").fadeOut('slow');
      }
    });
  });

  $(".wple-mx-ignore").click(function (e) {
    var $this = $(this);
    e.preventDefault();

    jQuery.ajax({
      method: "POST",
      url: ajaxurl,
      dataType: "text",
      data: {
        action: 'wple_mxerror_ignore'
      },
      beforeSend: function () {},
      error: function () {
        //alert("Failed to save! Please try again");
      },
      success: function (response) {
        $(".wple-mx-prom").fadeOut('slow');
      }
    });
  });


})(jQuery);