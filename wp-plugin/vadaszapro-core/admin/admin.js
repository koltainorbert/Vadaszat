/**
 * VadászApró – Admin JS
 * Color picker, media picker, sidebar interakció, toast
 */
(function ($) {
    "use strict";

    /* ── Color picker init ────────────────────────────────────── */
    $(function () {
        if ($.fn.wpColorPicker) {
            $(".va-color-input").each(function () {
                var $input = $(this);

                // Parse initial rgba → separate rgb + alpha
                var initVal = $input.val().trim();
                var initAlpha = 1;
                var initRgb   = null;
                var m = initVal.match(/^rgba?\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*(?:,\s*([\d.]+))?\s*\)/i);
                if (m) {
                    initRgb   = { r: +m[1], g: +m[2], b: +m[3] };
                    initAlpha = m[4] !== undefined ? +m[4] : 1;
                    // Show hex to iris so it doesn't choke on rgba
                    $input.val(vaRgbToHex(initRgb.r, initRgb.g, initRgb.b));
                }

                $input.wpColorPicker({
                    palettes: ['#ff2020','#ff7a22','#e5b843','#36d487','#57b0ff','#c75cff','#ffffff','#000000'],
                    width: 270,
                    change: function (event, ui) {
                        var c = ui.color._rgba;
                        $input.data('va-rgb', { r: Math.round(c[0]), g: Math.round(c[1]), b: Math.round(c[2]) });
                        vaCommitRgba($input);
                    }
                });

                // After iris wraps the input, inject alpha slider + fix position
                setTimeout(function () {
                    var $container = $input.closest('.wp-picker-container');
                    if (!$container.length) return;

                    // Store initial rgb from hex that iris may have set
                    if (!initRgb) {
                        var hex = $input.val().replace('#', '');
                        if (hex.length === 3) hex = hex[0]+hex[0]+hex[1]+hex[1]+hex[2]+hex[2];
                        initRgb = {
                            r: parseInt(hex.substr(0,2),16)||255,
                            g: parseInt(hex.substr(2,2),16)||255,
                            b: parseInt(hex.substr(4,2),16)||255
                        };
                    }
                    $input.data('va-rgb',   initRgb);
                    $input.data('va-alpha', initAlpha);

                    // Alpha slider row inside the iris picker
                    var pct  = Math.round(initAlpha * 100);
                    var $row = $('<div class="va-alpha-row">' +
                        '<span class="va-alpha-label">Átlátszóság</span>' +
                        '<div class="va-alpha-track">' +
                            '<div class="va-alpha-fill"></div>' +
                            '<input type="range" class="va-alpha-range" min="0" max="100" step="1" value="' + pct + '">' +
                        '</div>' +
                        '<span class="va-alpha-num">' + pct + '%</span>' +
                    '</div>');
                    $container.find('.iris-picker').append($row);
                    vaUpdateAlphaFill($row.find('.va-alpha-range'), initRgb);

                    $row.find('.va-alpha-range').on('input change', function () {
                        var a = $(this).val() / 100;
                        $(this).closest('.va-alpha-row').find('.va-alpha-num').text(Math.round(a*100) + '%');
                        $input.data('va-alpha', a);
                        var rgb = $input.data('va-rgb') || initRgb;
                        vaUpdateAlphaFill($(this), rgb);
                        vaCommitRgba($input);
                    });

                    // Reposition picker so it stays within viewport
                    $container.find('.wp-color-result').on('click', function () {
                        setTimeout(function () {
                            var $picker = $container.find('.iris-picker');
                            if (!$picker.is(':visible')) return;
                            var rect = $container[0].getBoundingClientRect();
                            var pW = $picker.outerWidth() || 270;
                            var pH = $picker.outerHeight() || 300;
                            var winW = window.innerWidth;
                            var winH = window.innerHeight;
                            var left = rect.left;
                            var top  = rect.bottom + 4;
                            if (left + pW > winW - 16) { left = winW - pW - 16; }
                            if (left < 10) { left = 10; }
                            if (top + pH > winH - 16) { top = rect.top - pH - 4; }
                            if (top < 10) { top = 10; }
                            $picker.css({ position: 'fixed', top: top + 'px', left: left + 'px' });
                        }, 30);
                    });

                    // Restore alpha display on open
                    $container.find('.wp-color-result').on('click', function () {
                        var a = $input.data('va-alpha');
                        if (a === undefined) a = 1;
                        var pct2 = Math.round(a * 100);
                        $row.find('.va-alpha-range').val(pct2);
                        $row.find('.va-alpha-num').text(pct2 + '%');
                        var rgb2 = $input.data('va-rgb') || initRgb;
                        vaUpdateAlphaFill($row.find('.va-alpha-range'), rgb2);
                    });
                }, 50);
            });
        }

        /* ── Media picker ─────────────────────────────────────── */
        $(document).on("click", ".va-media-btn", function (e) {
            e.preventDefault();
            var btn     = $(this);
            var target  = btn.data("target");
            var preview = btn.data("preview");

            var frame = wp.media({
                title:    "Kép kiválasztása",
                button:   { text: "Kiválaszt" },
                multiple: false,
                library:  { type: "image" }
            });

            frame.on("select", function () {
                var att = frame.state().get("selection").first().toJSON();
                $("#" + target).val(att.url);
                if (preview) {
                    var $p = $("#" + preview);
                    if ($p.length) {
                        $p.attr("src", att.url).show();
                    } else {
                        btn.closest(".va-media-field").find(".va-media-preview").attr("src", att.url).show();
                    }
                }
            });

            frame.open();
        });

        /* ── Toast stack ──────────────────────────────────────── */
        if (!$(".va-admin-toast-stack").length) {
            $("body").append("<div class=\"va-admin-toast-stack\" id=\"va-toast-stack\"></div>");
        }

        /* ── Mentés után toast ────────────────────────────────── */
        var $notice = $(".updated, .settings-error");
        if ($notice.length) {
            var msg  = $notice.find("p").text().trim() || "Beállítások mentve.";
            var type = ($notice.hasClass("settings-error") && $notice.hasClass("error")) ? "error" : "success";
            vaAdminToast(msg, type);
        }

        /* ── Sidebar aktív elem ───────────────────────────────── */
        var currentPage    = new URLSearchParams(window.location.search).get("page") || "";
        var currentPostType = new URLSearchParams(window.location.search).get("post_type") || "";
        $("#va-sidebar .va-sb-item").each(function () {
            var href      = $(this).attr("href") || "";
            var urlParams = new URLSearchParams(href.split("?")[1] || "");
            var itemPage  = urlParams.get("page") || "";
            var itemPT    = urlParams.get("post_type") || "";
            if ((itemPage && itemPage === currentPage) || (itemPT && itemPT === currentPostType)) {
                $(this).addClass("active");
            }
        });
    });

    /* ── Color picker helpers ─────────────────────────────────── */
    function vaRgbToHex(r, g, b) {
        return '#' + [r, g, b].map(function (v) {
            return ('0' + Math.max(0, Math.min(255, Math.round(v))).toString(16)).slice(-2);
        }).join('');
    }

    function vaCommitRgba($input) {
        var rgb   = $input.data('va-rgb');
        var alpha = $input.data('va-alpha');
        if (!rgb) return;
        if (alpha === undefined) alpha = 1;
        var val = alpha >= 1
            ? vaRgbToHex(rgb.r, rgb.g, rgb.b)
            : 'rgba(' + rgb.r + ',' + rgb.g + ',' + rgb.b + ',' + (Math.round(alpha * 100) / 100) + ')';
        // Write back into the actual form field (wp-color-picker = original input)
        $input.val(val);
    }

    function vaUpdateAlphaFill($range, rgb) {
        var $fill = $range.siblings('.va-alpha-fill');
        $fill.css('background',
            'linear-gradient(to right, rgba(' + rgb.r + ',' + rgb.g + ',' + rgb.b + ',0) 0%, ' +
            'rgba(' + rgb.r + ',' + rgb.g + ',' + rgb.b + ',1) 100%)');
    }

    /* ── Toast helper ─────────────────────────────────────────── */
    window.vaAdminToast = function (msg, type) {
        type = type || "success";
        var $toast = $("<div class=\"va-admin-toast va-admin-toast--" + type + "\">" + msg + "</div>");
        var $stack = $("#va-toast-stack");
        if (!$stack.length) { $stack = $("<div id=\"va-toast-stack\" class=\"va-admin-toast-stack\"></div>").appendTo("body"); }
        $stack.append($toast);
        requestAnimationFrame(function () { $toast.addClass("show"); });
        setTimeout(function () {
            $toast.removeClass("show");
            setTimeout(function () { $toast.remove(); }, 350);
        }, 3500);
    };

}(jQuery));