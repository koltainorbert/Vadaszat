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

                // Ha rgba → kivesszük az alpha értéket, hexet adunk az irisnek
                var initVal   = $input.val().trim();
                var initAlpha = 1;
                var initRgb   = null;
                var m = initVal.match(/^rgba?\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*(?:,\s*([\d.]+))?\s*\)/i);
                if (m) {
                    initRgb   = { r: +m[1], g: +m[2], b: +m[3] };
                    initAlpha = m[4] !== undefined ? parseFloat(m[4]) : 1;
                    $input.val(vaRgbToHex(initRgb.r, initRgb.g, initRgb.b));
                }

                $input.wpColorPicker({
                    palettes: ['#ff2020','#ff7a22','#e5b843','#36d487','#57b0ff','#c75cff','#ffffff','#000000'],
                    width: 260,
                    change: function (event, ui) {
                        var c = ui.color._rgba;
                        $input.data('va-rgb', { r: Math.round(c[0]), g: Math.round(c[1]), b: Math.round(c[2]) });
                        vaUpdateAlphaFill($input.data('va-$range'), $input.data('va-rgb'));
                        vaCommitRgba($input);
                    }
                });

                setTimeout(function () {
                    var $container = $input.closest('.wp-picker-container');
                    if (!$container.length) return;

                    // Init rgb from hex if rgba nem volt
                    if (!initRgb) {
                        var hex = $input.val().replace('#','');
                        if (hex.length === 3) hex = hex[0]+hex[0]+hex[1]+hex[1]+hex[2]+hex[2];
                        initRgb = {
                            r: parseInt(hex.substr(0,2),16)||0,
                            g: parseInt(hex.substr(2,2),16)||0,
                            b: parseInt(hex.substr(4,2),16)||0
                        };
                    }
                    $input.data('va-rgb',   initRgb);
                    $input.data('va-alpha', initAlpha);

                    // Alpha sort a palette-container UTÁN szúrjuk be
                    var pct  = Math.round(initAlpha * 100);
                    var $row = $('<div class="va-alpha-row">' +
                        '<span class="va-alpha-label">Átlátszóság</span>' +
                        '<div class="va-alpha-track">' +
                            '<div class="va-alpha-fill"></div>' +
                            '<input type="range" class="va-alpha-range" min="0" max="100" step="1" value="' + pct + '">' +
                            '<div class="va-alpha-thumb"></div>' +
                        '</div>' +
                        '<span class="va-alpha-num">' + pct + '%</span>' +
                    '</div>');

                    var $palette = $container.find('.iris-palette-container');
                    if ($palette.length) { $row.insertAfter($palette); }
                    else                 { $container.find('.iris-picker').append($row); }

                    var $range = $row.find('.va-alpha-range');
                    $input.data('va-$range', $range);
                    vaUpdateAlphaFill($range, initRgb);
                    vaUpdateAlphaThumb($range);

                    $range.on('input', function () {
                        var a   = $(this).val() / 100;
                        $row.find('.va-alpha-num').text(Math.round(a * 100) + '%');
                        $input.data('va-alpha', a);
                        vaUpdateAlphaFill($(this), $input.data('va-rgb') || initRgb);
                        vaUpdateAlphaThumb($(this));
                        vaCommitRgba($input);
                    });

                    // Frissítés minden megnyitáskor
                    $container.find('.wp-color-result').on('click', function () {
                        setTimeout(function () {
                            var a2   = $input.data('va-alpha');
                            if (a2 === undefined) a2 = 1;
                            $range.val(Math.round(a2 * 100));
                            $row.find('.va-alpha-num').text(Math.round(a2 * 100) + '%');
                            vaUpdateAlphaFill($range, $input.data('va-rgb') || initRgb);
                            vaUpdateAlphaThumb($range);
                        }, 20);
                    });
                }, 80);
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
        alpha = Math.round(alpha * 100) / 100;
        var val = alpha >= 1
            ? vaRgbToHex(rgb.r, rgb.g, rgb.b)
            : 'rgba(' + rgb.r + ',' + rgb.g + ',' + rgb.b + ',' + alpha + ')';
        $input.val(val);
    }

    function vaUpdateAlphaFill($range, rgb) {
        if (!$range || !$range.length || !rgb) return;
        $range.siblings('.va-alpha-fill').css('background',
            'linear-gradient(to right,rgba(' + rgb.r + ',' + rgb.g + ',' + rgb.b + ',0) 0%,' +
            'rgba(' + rgb.r + ',' + rgb.g + ',' + rgb.b + ',1) 100%)');
    }

    function vaUpdateAlphaThumb($range) {
        if (!$range || !$range.length) return;
        var pct = $range.val() + '%';
        $range.siblings('.va-alpha-thumb').css('left', pct);
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