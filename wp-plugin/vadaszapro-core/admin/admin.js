/**
 * VadászApró – Admin JS
 * Color picker, media picker, sidebar interakció, toast
 */
(function ($) {
    "use strict";

    /* ── Color picker init ────────────────────────────────────── */
    $(function () {
        if ($.fn.wpColorPicker) {
            $(".va-color-input").each(function() {
                var $input   = $(this);
                var defColor = $input.attr('data-default-color') || $input.val();

                $input.wpColorPicker({
                    change: function() { syncSwatch(); },
                    clear:  function() { syncSwatch(); }
                });

                // Swatch injektálás a gomb belsejébe
                var $wrap   = $input.closest('.wp-picker-container');
                var $btn    = $wrap.find('.wp-color-result');
                if ($btn.length && !$btn.find('.va-color-swatch').length) {
                    $btn.prepend('<span class="va-color-swatch" aria-hidden="true"></span>');
                }

                function syncSwatch() {
                    var val = ($input.val() || '').trim();
                    var $sw = $wrap.find('.va-color-swatch');
                    if ($sw[0]) {
                        $sw[0].style.setProperty('background-color', val || 'transparent');
                    }
                }

                syncSwatch();

                // Default gomb bal border színe
                var $defBtn = $wrap.find('.wp-picker-default');
                if (defColor && $defBtn[0]) {
                    $defBtn[0].style.setProperty('--va-sw-def', defColor);
                }
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
        /* ── Custom number stepper ────────────────────────────── */
        $(document).on("click", ".va-num-up, .va-num-dn", function () {
            var $btn   = $(this);
            var $input = $btn.closest(".va-num-wrap").find("input[type='number']");
            var step   = parseFloat($input.attr("step") || "1");
            var min    = parseFloat($input.attr("min") != null ? $input.attr("min") : "-Infinity");
            var max    = parseFloat($input.attr("max") != null ? $input.attr("max") : "Infinity");
            var val    = parseFloat($input.val() || "0");
            if ($btn.hasClass("va-num-up")) val = Math.min(max, Math.round((val + step) / step) * step);
            else                             val = Math.max(min, Math.round((val - step) / step) * step);
            // tizedesjegy kezelés
            var decimals = (step.toString().split(".")[1] || "").length;
            $input.val(val.toFixed(decimals)).trigger("change");
        });

    });

    /* ── Toast helper ─────────────────────────────────────────── */

    /* ── Color swatch frissítő ───────────────────────────────── */
    window.vaUpdateSwatch = function($btn, color) {
        var bg = color || ($btn[0] && $btn[0].style && $btn[0].style.backgroundColor);
        if (!bg) return;
        // Üres / transparent esetén töröljük (transparent ::before = nincs csúnya négyzet)
        if (bg === 'rgba(0, 0, 0, 0)' || bg === 'transparent' || bg === '') {
            if ($btn[0]) $btn[0].style.removeProperty('--va-sw');
            return;
        }
        if ($btn[0]) $btn[0].style.setProperty('--va-sw', bg);
    };

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