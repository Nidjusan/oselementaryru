jQuery(document).ready(function ($) {

    //autoscroll to PM position
    $('.wpfpm-content').animate({scrollTop: $('.wpfpm-content').prop("scrollHeight")}, 415);
    $('html, body').animate({scrollTop: ($(".wpforo-profile-content").offset().top - 70)}, 415);


    $('#wpforo-wrap .wpforo-messages-content .wpfpm-left').scroll(function () {
        var scrollHeight = document.getElementById('wpfpm-users-list').scrollHeight;
        var scrollPosition = $(this).height() + Math.ceil($(this).scrollTop());
        if (scrollHeight === scrollPosition && $.active === 0) {
            if (!$('#wpfpm-users-list').data('nomore')) {
                wpforo_load_show();
                $("ul#wpfpm-users-list").append('<li id="wpf-pm-load-more" class="whr"><abbr><i class="fas fa-spinner" aria-hidden="true"></i> ' + wpforo_phrase('Loading older conversations...') + ' </abbr></li>');
                var paged = $('#wpfpm-users-list').data('paged');
                if (!paged) paged = 1;

                $.ajax({
                    type: 'POST',
                    url: wpforo.ajax_url,
                    data: {
                        paged: ++paged,
                        action: 'wpforopm_load_more_folders'
                    }
                }).done(function (response) {
                    try {
                        response = JSON.parse(response);
                        if (response) {
                            if (response.stat === 1) {
                                $("ul#wpfpm-users-list").append(response.html);
                                $('#wpfpm-users-list').data('paged', paged);
                            }
                            if (response.no_more) $('#wpfpm-users-list').data('nomore', 1);
                        }
                        $("li#wpf-pm-load-more").remove();
                        wpforo_load_hide();
                    } catch (e) {
                        console.log(e);
                    }

                });
            }
        }
    });


    //scrolltop lazy load ajax
    $('.wpfpm-content').scroll(function () {
        if ($('.wpfpm-content').scrollTop() == 0) {

            if ($("ul#wpfpm-wrap").has("li#wpf-no-pm").length == 0 && $.active == 0) {

                wpforo_load_show();
                $("ul#wpfpm-wrap").prepend('<li id="wpf-pm-load-more" class="whr"><abbr><i class="fas fa-spinner" aria-hidden="true"></i> ' + wpforo_phrase('Loading older messages...') + ' </abbr></li>');

                var wpfpmli = $("ul#wpfpm-wrap > li[id^='wpfpmid-']");
                if (wpfpmli[0] !== undefined) {
                    var wpforopm_lastid = wpfpmli[0]['id'].replace('wpfpmid-', '');
                    if (wpforopm_lastid !== undefined) {
                        $.ajax({
                            type: 'POST',
                            url: wpforo.ajax_url,
                            data: {
                                wpforopm_lastid: wpforopm_lastid,
                                action: 'wpforopm_load_more'
                            }
                        }).done(function (response) {
                            if (response !== undefined && response) {
                                $("ul#wpfpm-wrap").prepend(response);
                            }

                            if (wpforopm_lastid == 0) {
                                var sc = $("#wpfpm-wrap").offset().top
                            } else {
                                var sc = $("li#wpfpmid-" + wpforopm_lastid).offset().top;
                            }
                            $('#wpfpm-wrap').animate({scrollTop: (sc - $("#wpfpm-wrap").offset().top - 385)}, 415);
                            $('html, body').animate({scrollTop: ($(".wpforo-profile-content").offset().top - 70)}, 415);
                            $("li#wpf-pm-load-more").remove();
                            wpforo_load_hide();
                        });
                    }
                }

            }
        }
    });

    //button tool refresh
    $('#wpfpm-tool-refresh').click(function () {
        wpforo_load_show();

        if ($.active == 0) {

            var wpfpmli = $("ul#wpfpm-wrap > li[id^='wpfpmid-']");
            if (wpfpmli[(wpfpmli.length - 1)] !== undefined) {
                var wpforopm_lastid = wpfpmli[(wpfpmli.length - 1)]['id'].replace('wpfpmid-', '');
            } else {
                var wpforopm_lastid = 0;
            }
            if (wpforopm_lastid !== undefined) {
                $.ajax({
                    type: 'POST',
                    url: wpforo.ajax_url,
                    data: {
                        wpforopm_lastid: wpforopm_lastid,
                        action: 'wpforopm_refresh'
                    }
                }).done(function (response) {
                    if (response !== undefined && response) {
                        $("ul#wpfpm-wrap > li#wpf-no-discussion").hide();
                        $("ul#wpfpm-wrap").append(response);
                    }

                    $('.wpfpm-content').animate({scrollTop: $('.wpfpm-content').prop("scrollHeight")}, 415);
                    $('html, body').animate({scrollTop: ($(".wpforo-profile-content").offset().top - 70)}, 415);
                    wpforo_load_hide();
                });
            }

        }


    });

    //button tool write PM
    $('#wpfpm-tool-write').click(function () {
        wpforo_load_show();
        tinyMCE.activeEditor.setContent('');
        $('html, body').animate({scrollTop: ($(".wpfpm-form-wrapper").offset().top - 330)}, 415);
        tinyMCE.activeEditor.focus();

        wpforo_load_hide();
    });

    //button tool load all
    $('#wpfpm-tool-load-all').click(function () {

        if ($("ul#wpfpm-wrap").has("li#wpf-no-pm").length == 0 && $.active == 0) {

            wpforo_load_show();
            $("ul#wpfpm-wrap").prepend('<li id="wpf-pm-load-more" class="whr"><abbr><i class="fas fa-spinner" aria-hidden="true"></i> ' + wpforo_phrase('Loading older messages...') + ' </abbr></li>');

            var wpfpmli = $("ul#wpfpm-wrap > li[id^='wpfpmid-']");
            if (wpfpmli[0] !== undefined) {
                var wpforopm_lastid = wpfpmli[0]['id'].replace('wpfpmid-', '');
            } else {
                var wpforopm_lastid = 0;
            }
            if (wpforopm_lastid !== undefined) {
                $.ajax({
                    type: 'POST',
                    url: wpforo.ajax_url,
                    data: {
                        wpforopm_lastid: wpforopm_lastid,
                        action: 'wpforopm_load_all'
                    }
                }).done(function (response) {
                    if (response !== undefined && response) {
                        $("ul#wpfpm-wrap > li#wpf-no-discussion").hide();
                        $("ul#wpfpm-wrap").prepend(response);
                    }

                    if (wpforopm_lastid == 0) {
                        var sc = $("#wpfpm-wrap").offset().top
                    } else {
                        var sc = $("li#wpfpmid-" + wpforopm_lastid).offset().top;
                    }
                    $('#wpfpm-wrap').animate({scrollTop: (sc - $("#wpfpm-wrap").offset().top - 385)}, 415);
                    $("li#wpf-pm-load-more").remove();
                    wpforo_load_hide();
                });
            }

        }

    });

    //button tool delete all
    $('#wpfpm-tool-delete-all').click(function () {

        if ($("ul#wpfpm-wrap").has("li#wpf-no-discussion").length == 0 && confirm(wpforo_phrase('Are you sure you want to delete?')) && $.active == 0) {

            wpforo_load_show();

            $.ajax({
                type: 'POST',
                url: wpforo.ajax_url,
                data: {
                    action: 'wpforopm_delete_all'
                }
            }).done(function (response) {
                if (response !== undefined && response) {
                    $("ul#wpfpm-wrap li").remove();
                    $("ul#wpfpm-wrap").append(response);
                }

                $('.wpfpm-content').animate({scrollTop: $('.wpfpm-content').prop("scrollHeight")}, 415);
                $('html, body').animate({scrollTop: ($(".wpforo-profile-content").offset().top - 70)}, 415);
                wpforo_load_hide();
            });

        }

    });


    //	Report
    $("#wpfpm-uli-users-wrap").delegate(".wpfpm-uli-cog-report", 'click', function () {
        wpforo_load_show();

        var userid = $(this).parents('.wpfpm-uli-users, .wpfpm-convr-user').attr('id');
        if (userid !== undefined && userid.length !== 0 && userid !== 0) {
            userid = userid.replace('wpfpmu-', '');

            var form = $("form#wpfpm-report");
            $('#wpfpm-report-userid', form).val(userid);
            wpforo_dialog_show('', form, '45%', '295px');
            $("#wpfpm-report-message", form).focus();
        }

        wpforo_load_hide();
    });

    $(document).on('click', '#wpfpm-report-send', wpfpm_report_send);
    $(document).on('keydown', 'form#wpfpm-report', function (e) {
        if (e.ctrlKey && e.keyCode === 13) {
            wpfpm_report_send();
        }
    });

    function wpfpm_report_send() {
        wpforo_load_show();
        var userid = $('#wpfpm-report-userid').val();
        var messagecontent = $('#wpfpm-report-message').val();

        if (userid !== undefined && userid.length !== 0 && userid !== 0
            && messagecontent !== undefined && messagecontent.length !== 0 && messagecontent !== '') {
            $.ajax({
                type: 'POST',
                url: wpforo.ajax_url,
                data: {
                    userid: userid,
                    reportmsg: messagecontent,
                    action: 'wpforopm_report'
                }
            }).done(function (response) {
                try {
                    response = $.parseJSON(response);
                    wpforo_dialog_hide();
                    wpforo_load_hide();
                    wpforo_notice_show(response);
                } catch (e) {
                    console.log(e);
                }
            });
        } else {
            wpforo_load_hide();
            wpforo_notice_show('Error: please specify report reason.', 'error');
        }

    }

    //	block
    $("#wpfpm-uli-users-wrap").delegate('.wpfpm-uli-cog-block', 'click', function () {
        if (confirm(wpforo_phrase('Are you sure you want to block this user?')) && $.active === 0) {

            wpforo_load_show();

            var wpfpm_avatar_ban = $(this).parents('.wpfpm-uli-users').find('.wpfpm_avatar_ban');
            var tooltip = $(this).parents('.wpfpm-uli-cog-tooltip');
            var userid = $(this).parents('.wpfpm-uli-users, .wpfpm-convr-user').attr('id');
            if (userid !== undefined && userid.length !== 0 && userid !== 0) {
                userid = userid.replace('wpfpmu-', '');
                $.ajax({
                    type: 'POST',
                    url: wpforo.ajax_url,
                    data: {
                        userid: userid,
                        action: 'wpforopm_block_user'
                    }
                }).done(function (response) {
                    try {
                        response = $.parseJSON(response);
                    } catch (e) {
                        console.log(e);
                    }

                    if (response.stat == 1) {
                        tooltip.html(response.html);
                        wpfpm_avatar_ban.fadeToggle(400, "linear");
                    }

                    wpforo_load_hide();
                    wpforo_notice_show(response.notice);
                });
            }

        }
    });

    //	unblock
    $("#wpfpm-uli-users-wrap").delegate('.wpfpm-uli-cog-unblock', 'click', function () {
        if (confirm(wpforo_phrase('Are you sure you want to unblock this user?')) && $.active == 0) {

            wpforo_load_show();

            var wpfpm_avatar_ban = $(this).parents('.wpfpm-uli-users').find('.wpfpm_avatar_ban');
            var tooltip = $(this).parents('.wpfpm-uli-cog-tooltip');
            var userid = $(this).parents('.wpfpm-uli-users, .wpfpm-convr-user').attr('id');
            if (userid !== undefined && userid.length != 0 && userid != 0) {
                userid = userid.replace('wpfpmu-', '');
                $.ajax({
                    type: 'POST',
                    url: wpforo.ajax_url,
                    data: {
                        userid: userid,
                        action: 'wpforopm_unblock_user'
                    }
                }).done(function (response) {
                    try {
                        response = $.parseJSON(response);
                    } catch (e) {
                        console.log(e);
                    }

                    if (response.stat == 1) {
                        tooltip.html(response.html);
                        wpfpm_avatar_ban.fadeToggle(400, "linear");
                    }

                    wpforo_load_hide();
                    wpforo_notice_show(response.notice);
                });
            }

        }
    });

    //  remove contact
    $("#wpfpm-uli-users-wrap").delegate('.wpfpm-uli-cog-delete', 'click', function () {
        if (confirm(wpforo_phrase('Are you sure you want to remove this user from conversation?')) && $.active == 0) {

            wpforo_load_show();

            var wpfpm_uli = $(this).parents('.wpfpm-uli-users, .wpfpm-convr-user');
            var tooltip = $(this).parents('.wpfpm-uli-cog-tooltip');
            var userid = $(this).parents('.wpfpm-uli-users, .wpfpm-convr-user').attr('id');
            if (userid !== undefined && userid.length != 0 && userid != 0) {
                userid = userid.replace('wpfpmu-', '');
                $.ajax({
                    type: 'POST',
                    url: wpforo.ajax_url,
                    data: {
                        userid: userid,
                        action: 'wpforopm_delete_users'
                    }
                }).done(function (response) {
                    try {
                        response = $.parseJSON(response);
                    } catch (e) {
                        console.log(e);
                    }

                    if (response.stat == 1) {
                        wpfpm_uli.fadeOut(400, "linear");
                        if (response.html !== undefined && response.html.length != 0 && response.html)
                            $('.wpfpm-toolbar > .tollbar-top-info').html(response.html);
                        if (response.location !== undefined && response.location.length != 0)
                            window.location.assign(response.location);
                    }

                    wpforo_load_hide();
                    wpforo_notice_show(response.notice);
                });
            }

        }
    });

    //add new peoples to folder
    $("#wpfpm-uli-users-wrap").delegate('.wpfpm-add-user .wpfpm-add-user-go', 'click', function () {
        var input_value = $.trim($('#wpfpm-users').val());
        if (input_value === undefined || input_value.length == 0 || !input_value) return false;

        wpforo_load_show();
        if ($.active == 0) {

            $.ajax({
                type: 'POST',
                url: wpforo.ajax_url,
                data: {
                    wpfpm_users: input_value,
                    action: 'wpforopm_add_users'
                }
            }).done(function (response) {
                try {
                    response = $.parseJSON(response);
                } catch (e) {
                    console.log(e);
                }

                if (response.stat == 1) {
                    if (response.html !== undefined && response.html.length != 0 && response.html)
                        $('.wpfpm-toolbar > .tollbar-top-info').html(response.html);

                    $('.wpfpm-add-user > .wpfpm-udatalist').fadeOut(50, "linear");
                    $('#wpfpm-users').val('');
                    $('.wpfpm-add-user .wpfpm-add-user-go').fadeOut(400, "linear");
                }

                wpforo_load_hide();
                wpforo_notice_show(response.notice);
            });

        }

    });

    // fucusing controller
    $('.wpfpm-udatalist').delegate('#wpfpm-users, #wpfpm-users-datalist', 'keydown', function (e) {
        var keycode = e.which;

        if (keycode == 27) {
            $('#wpfpm-users-datalist').fadeOut(400, "linear");
            $('#wpfpm-users').focus();
            e.preventDefault();
            e.stopPropagation();
        } else if (keycode == 35 && !$('#wpfpm-users').is(':focus')) {
            e.preventDefault();
            e.stopPropagation();
            var li = $('#wpfpm-users-datalist > li');
            $(li[li.length - 1]).focus();
        } else if (keycode == 36 && !$('#wpfpm-users').is(':focus')) {
            e.preventDefault();
            e.stopPropagation();
            var li = $('#wpfpm-users-datalist > li');
            $(li[0]).focus();
        } else if (keycode == 38 || keycode == 40) {
            e.preventDefault();
            e.stopPropagation();

            var li = $('#wpfpm-users-datalist > li');
            var focus_status = false;

            li.each(function (key, val) {
                if ($(val).is(':focus') || $(val).is(':hover')) {
                    if (keycode == 40) {
                        $(li[key + 1]).focus();
                        focus_status = true;
                        return false;
                    } else if (keycode == 38) {
                        $(li[key - 1]).focus();
                        focus_status = true;
                        return false;
                    }
                }
            });

            if (!focus_status) $(li[0]).focus();
        } else {
            $('#wpfpm-users').focus();
        }
    });

    //Search Contact
    var input_old_value = '';
    var search_contact_request = '';
    $('#wpfpm-users').stop().bind('input propertychange', function () {

        if ($.active !== 0) search_contact_request.abort();

        setTimeout(function () {

            var wpfpm_users_datalist = $('#wpfpm-users-datalist');
            var input_value = $.trim($('#wpfpm-users').val());

            var filtered = input_value.replace(/^,+|,+$/, "");
            if (filtered !== input_value) input_value = filtered;

            input_arr = input_value.split(',');
            input_value = input_arr[input_arr.length - 1];
            input_value = input_value.replace(/^@+/, "");
            input_value = input_value.replace(/@+$/, "");

            if (input_value.length === 0) {
                wpfpm_users_datalist.fadeOut(400, "linear");
                $('.wpfpm-add-user .wpfpm-add-user-go').fadeOut(400, "linear");
            }

            if (input_value.length > 1 && input_value !== input_old_value) {
                wpforo_load_show();

                search_contact_request = $.ajax({
                    type: 'POST',
                    url: wpforo.ajax_url,
                    data: {
                        wpfpm_needle: input_value,
                        action: 'wpforopm_search_contact'
                    }
                }).done(function (response) {

                    if (response !== undefined && $.trim(response).length !== 0 && response) {
                        wpfpm_users_datalist.html(response);
                        wpfpm_users_datalist.fadeIn(400, "linear");
                    } else {
                        wpfpm_users_datalist.fadeIn(400, "linear");
                        wpfpm_users_datalist.html('<li>' + wpforo_phrase('User not found') + '</li>');
                    }

                    wpforo_load_hide();
                });

                input_old_value = input_value;
            }

        }, 700);


        wpforo_load_hide();
    });

    //select user and add to list
    $('#wpfpm-users-datalist').delegate('.wpfpm-datalist-user', 'click keydown', function (e) {
        var eType = e.type;
        var keyCode = e.which;

        if (eType == 'keydown' && keyCode == 13) {
            e.preventDefault();
            e.stopPropagation();
            var unicename = $('#wpfpm-users-datalist > li:focus, #wpfpm-users-datalist > li:hover').attr('data-nicename');
        } else if (eType == 'click') {
            var unicename = $(this).attr('data-nicename');
        }

        if (unicename !== undefined) {
            var input_value = $.trim($('#wpfpm-users').val());
            var new_val = '';
            var new_arr = [];

            input_arr = input_value.split(',');
            $.each(input_arr, function (key, value) {
                if (key != (input_arr.length - 1) && $.trim(value).length != 0) {
                    if ($.inArray(value, new_arr) === -1) {
                        new_val += ',' + value;
                        new_arr.push(value);
                    }
                }
            });

            if ($.inArray(unicename, new_arr) === -1) {
                new_val += ',' + unicename;
                new_arr.push(unicename);
            }
            new_val = new_val.replace(/^,+|,+$/, "") + ',';

            $('#wpfpm-users').val(new_val);

            unicename = unicename.replace(/^@+/, "");
            unicename = unicename.replace(/@+$/, "");
            input_old_value = unicename;
            $('#wpfpm-users').focus();
            $('#wpfpm-users-datalist').fadeOut(400, "linear");
            $('.wpfpm-add-user .wpfpm-add-user-go').fadeIn(400, "linear");
        }
    });


    //  Delete folder and included pms
    $("#wpfpm-users-list").delegate('.wpfpm-uli-cog-delete', 'click', function () {
        if (confirm(wpforo_phrase('Are you sure you want to delete this conversation with all messages?')) && $.active == 0) {

            wpforo_load_show();

            var wpfpm_uli = $(this).parents('.wpfpm-uli');
            var tooltip = $(this).parents('.wpfpm-uli-cog-tooltip');
            var folderid = $(this).parents('.wpfpm-uli').attr('id');
            if (folderid !== undefined && folderid.length != 0 && folderid != 0) {
                folderid = folderid.replace('wpfpmf-', '');
                $.ajax({
                    type: 'POST',
                    url: wpforo.ajax_url,
                    data: {
                        wpfpm_folderid: folderid,
                        action: 'wpforopm_delete_folder'
                    }
                }).done(function (response) {
                    try {
                        response = $.parseJSON(response);
                    } catch (e) {
                        console.log(e);
                    }

                    if (response.stat == 1) {
                        wpfpm_uli.fadeOut(400, "linear");
                        if (response.location !== undefined && response.location.length != 0)
                            window.location.assign(response.location);
                    }

                    wpforo_load_hide();
                    wpforo_notice_show(response.notice);
                });
            }

        }
    });

    // hide folder from user folderslist
    $("#wpfpm-users-list").delegate('.wpfpm-uli-cog-hide', 'click', function () {
        if (confirm(wpforo_phrase('Are you sure you want to hide this conversation from your conversation list?')) && $.active == 0) {

            wpforo_load_show();

            var wpfpm_uli = $(this).parents('.wpfpm-uli');
            var tooltip = $(this).parents('.wpfpm-uli-cog-tooltip');
            var folderid = $(this).parents('.wpfpm-uli').attr('id');
            if (folderid !== undefined && folderid.length != 0 && folderid != 0) {
                folderid = folderid.replace('wpfpmf-', '');
                $.ajax({
                    type: 'POST',
                    url: wpforo.ajax_url,
                    data: {
                        wpfpm_folderid: folderid,
                        action: 'wpforopm_hide_folder'
                    }
                }).done(function (response) {
                    try {
                        response = $.parseJSON(response);
                    } catch (e) {
                        console.log(e);
                    }

                    if (response.stat == 1) {
                        wpfpm_uli.fadeOut(400, "linear");
                        if (response.location !== undefined && response.location.length != 0)
                            window.location.assign(response.location);
                    }

                    wpforo_load_hide();
                    wpforo_notice_show(response.notice);
                });
            }

        }
    });


    //members list setting button
    $('#wpfpm-uli-users-wrap').delegate('.wpfpm-uli-users *:not(.wpfpm-add-user-button), .wpfpm-uli-users:not(.wpfpm-add-user-button), wpfpm-uli-cog *, wpfpm-uli-cog', 'click', function (event) {
        event.preventDefault();
        event.stopPropagation();
        var cur_sel = $(this).parents('.wpfpm-uli-users').find('.wpfpm-uli-cog-tooltip');
        if (cur_sel.length == 0) cur_sel = $(this).find('.wpfpm-uli-cog-tooltip');
        if (cur_sel.is(":hidden")) $('.wpfpm-uli-users .wpfpm-uli-cog-tooltip').hide();
        cur_sel.fadeToggle(200, "linear");
    });

    //group members list button
    $('#wpfpm-uli-users-wrap').delegate('#wpfpm-ul-users-tooltip-wrap > i.fa-users', 'click', function (event) {
        event.preventDefault();
        event.stopPropagation();
        var cur_sel = $(this).parents('.tollbar-top-info').find('.wpfpm-ul-users-tooltip');
        if (cur_sel.is(":hidden")) $('.tollbar-top-info .wpfpm-uli-cog .wpfpm-uli-cog-tooltip').hide();
        $('.wpfpm-add-user > .wpfpm-udatalist, #wpfpm-users-list .wpfpm-uli .wpfpm-uli-cog-tooltip').fadeOut(50, "linear");
        cur_sel.fadeToggle(200, "linear");
    });

    //add new People to current folder
    $('#wpfpm-uli-users-wrap').delegate('.wpfpm-ul-users-tooltip li.wpfpm-add-user-button, .wpfpm-ul-users-tooltip li.wpfpm-add-user-button *', 'click', function () {
        $('.wpfpm-ul-users-tooltip, #wpfpm-users-list .wpfpm-uli .wpfpm-uli-cog-tooltip').fadeOut(50, "linear");
        $('.wpfpm-add-user > .wpfpm-udatalist').fadeToggle(200, "linear");
        $('#wpfpm-users').focus();
    });

    //folders list setting button
    $('#wpfpm-users-list').delegate('.wpfpm-uli-cog *, wpfpm-uli-cog', 'click', function (event) {
        event.preventDefault();
        event.stopPropagation();
        var cur_sel = $(this).parents('.wpfpm-uli').find('.wpfpm-uli-cog-tooltip');
        if (cur_sel.length == 0) cur_sel = $(this).find('.wpfpm-uli-cog-tooltip');
        if (cur_sel.is(":hidden")) $('.wpfpm-uli .wpfpm-uli-cog-tooltip').hide();
        $('.wpfpm-add-user > .wpfpm-udatalist, .wpfpm-ul-users-tooltip').fadeOut(50, "linear");
        cur_sel.fadeToggle(200, "linear");
    });

    $(window).on('keydown', document, function (e) {
        var keycode = e.which;
        if (keycode == 27) {
            $('.wpfpm-ul-users-tooltip, .wpfpm-uli-cog-tooltip, #wpfpm-users-list .wpfpm-uli .wpfpm-uli-cog-tooltip, .wpfpm-add-user > .wpfpm-udatalist').fadeOut(50, "linear");
            $("body").removeClass("wpfpm-uli-opened");
        }
    });

    $('#wpfpm-tools, #wpfpm-users-list').delegate('#wpfpm-tool-notification-on, .wpfpm-uli-cog-notification-on', 'click', function () {
        if (confirm(wpforo_phrase('Are you sure you want to turn on email notifications for this conversation?')) && $.active == 0) {

            wpforo_load_show();

            var that = $(this);
            var folderid = $(this).parents('.wpfpm-uli').attr('id');
            if (folderid !== undefined && folderid.length != 0 && folderid != 0) {
                folderid = folderid.replace('wpfpmf-', '');
            } else {
                folderid = 0;
            }

            $.ajax({
                type: 'POST',
                url: wpforo.ajax_url,
                data: {
                    wpfpm_folderid: folderid,
                    action: 'wpforopm_folder_email_notification_on'
                }
            }).done(function (response) {
                try {
                    response = $.parseJSON(response);
                } catch (e) {
                    console.log(e);
                }

                if (response.stat == 1) {
                    if (response.is_current == 1) {
                        $('#wpfpm-tool-notification-on').hide();
                        $('#wpfpm-tool-notification-off').show();
                    }

                    $('#wpfpmf-' + response.folderid + ' .wpfpm-uli-cog-tooltip .wpfpm-uli-cog-notification-on').hide();
                    $('#wpfpmf-' + response.folderid + ' .wpfpm-uli-cog-tooltip .wpfpm-uli-cog-notification-off').show();

                    // that.hide();
                    // that.siblings('#wpfpm-tool-notification-off, .wpfpm-uli-cog-notification-off').show();
                }

                wpforo_load_hide();
                wpforo_notice_show(response.notice);
            });
        }
    });

    $('#wpfpm-tools, #wpfpm-users-list').delegate('#wpfpm-tool-notification-off, .wpfpm-uli-cog-notification-off', 'click', function () {
        if (confirm(wpforo_phrase('Are you sure you want to turn off email notifications for this conversation?')) && $.active == 0) {

            wpforo_load_show();

            var that = $(this);
            var folderid = $(this).parents('.wpfpm-uli').attr('id');
            if (folderid !== undefined && folderid.length != 0 && folderid != 0) {
                folderid = folderid.replace('wpfpmf-', '');
            } else {
                folderid = 0;
            }

            $.ajax({
                type: 'POST',
                url: wpforo.ajax_url,
                data: {
                    wpfpm_folderid: folderid,
                    action: 'wpforopm_folder_email_notification_off'
                }
            }).done(function (response) {
                try {
                    response = $.parseJSON(response);
                } catch (e) {
                    console.log(e);
                }

                if (response.stat == 1) {
                    if (response.is_current == 1) {
                        $('#wpfpm-tool-notification-off').hide();
                        $('#wpfpm-tool-notification-on').show();
                    }

                    $('#wpfpmf-' + response.folderid + ' .wpfpm-uli-cog-tooltip .wpfpm-uli-cog-notification-off').hide();
                    $('#wpfpmf-' + response.folderid + ' .wpfpm-uli-cog-tooltip .wpfpm-uli-cog-notification-on').show();

                    // that.hide();
                    // that.siblings('#wpfpm-tool-notification-on, .wpfpm-uli-cog-notification-on').show();
                }

                wpforo_load_hide();
                wpforo_notice_show(response.notice);
            });
        }
    });

    $(document).on("click", "#wpfpm_left_toggle_button", function () {
        $("body").toggleClass("wpfpm-uli-opened");
    });

});