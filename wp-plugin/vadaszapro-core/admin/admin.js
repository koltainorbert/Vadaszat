/**
 * VadászApró – Admin JS
 * Color picker, media picker, sidebar interakció, toast
 */
(function ($) {
    "use strict";

    /* ── Custom VA Color Picker ──────────────────────────────── */
    $(function () {
        $(".va-color-input").each(function () {
            var $hidden = $(this).hide();
            var rawVal  = $hidden.val().trim();

            // rgba / rgb parse
            var alpha = 1, hex = '#000000';
            var m = rawVal.match(/^rgba?\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*(?:,\s*([\d.]+))?\s*\)/i);
            if (m) {
                hex   = vaRgbToHex(+m[1], +m[2], +m[3]);
                alpha = m[4] !== undefined ? parseFloat(m[4]) : 1;
            } else if (/^#[0-9a-f]{3,8}$/i.test(rawVal)) {
                hex = rawVal.length === 4
                    ? '#' + rawVal[1]+rawVal[1]+rawVal[2]+rawVal[2]+rawVal[3]+rawVal[3]
                    : rawVal.slice(0,7);
            }

            var pct = Math.round(alpha * 100);

            // UI: swatch gomb + rejtett native picker + alpha slider
            var $wrap = $(
                '<div class="va-cpick">' +
                  '<div class="va-cpick__row">' +
                    '<label class="va-cpick__btn">' +
                      '<span class="va-cpick__swatch"></span>' +
                      '<input type="color" class="va-cpick__wheel">' +
                    '</label>' +
                    '<input type="text" class="va-cpick__hex" maxlength="25" spellcheck="false">' +
                  '</div>' +
                  '<div class="va-cpick__alpha-wrap">' +
                    '<span class="va-cpick__alpha-label">Átlátszóság</span>' +
                    '<div class="va-cpick__alpha-track">' +
                      '<div class="va-cpick__alpha-fill"></div>' +
                      '<input type="range" class="va-cpick__alpha-range" min="0" max="100" step="1" value="'+pct+'">' +
                    '</div>' +
                    '<span class="va-cpick__alpha-num">'+pct+'%</span>' +
                  '</div>' +
                '</div>'
            );
            $hidden.after($wrap);

            var $wheel = $wrap.find('.va-cpick__wheel');
            var $aRange= $wrap.find('.va-cpick__alpha-range');
            var $aFill = $wrap.find('.va-cpick__alpha-fill');
            var $aNum  = $wrap.find('.va-cpick__alpha-num');
            var $hexIn = $wrap.find('.va-cpick__hex');
            var $swatch= $wrap.find('.va-cpick__swatch');

            function syncUI(h, a) {
                $wheel.val(h);
                $swatch.css('background', h);
                var pct2 = Math.round(a * 100);
                $aRange.val(pct2);
                $aNum.text(pct2 + '%');
                var rgb = vaHexToRgb(h);
                $aFill.css('background',
                    'linear-gradient(to right,rgba('+rgb.r+','+rgb.g+','+rgb.b+',0),rgba('+rgb.r+','+rgb.g+','+rgb.b+',1))');
                var display = a >= 1 ? h : 'rgba('+rgb.r+','+rgb.g+','+rgb.b+','+Math.round(a*100)/100+')';
                $hexIn.val(display);
                $hidden.val(display);
            }
            syncUI(hex, alpha);

            $wheel.on('input', function () {
                hex = $(this).val();
                syncUI(hex, $aRange.val() / 100);
            });

            $aRange.on('input', function () {
                syncUI(hex, $(this).val() / 100);
            });

            $hexIn.on('change blur', function () {
                var v = $(this).val().trim();
                var pm = v.match(/^rgba?\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*(?:,\s*([\d.]+))?\s*\)/i);
                if (pm) {
                    hex   = vaRgbToHex(+pm[1], +pm[2], +pm[3]);
                    alpha = pm[4] !== undefined ? parseFloat(pm[4]) : 1;
                } else if (/^#[0-9a-f]{3,8}$/i.test(v)) {
                    hex   = v.length === 4 ? '#'+v[1]+v[1]+v[2]+v[2]+v[3]+v[3] : v.slice(0,7);
                    alpha = 1;
                }
                syncUI(hex, alpha);
            });
        });

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

    /* ── Color math helpers ─────────────────────────────────── */
    function vaHsvToRgb(h, s, v) {
        var i = Math.floor(h / 60) % 6, f = h/60 - Math.floor(h/60);
        var p = v*(1-s), q = v*(1-f*s), t = v*(1-(1-f)*s);
        var r,g,b;
        switch(i){case 0:r=v;g=t;b=p;break;case 1:r=q;g=v;b=p;break;case 2:r=p;g=v;b=t;break;case 3:r=p;g=q;b=v;break;case 4:r=t;g=p;b=v;break;default:r=v;g=p;b=q;}
        return {r:Math.round(r*255),g:Math.round(g*255),b:Math.round(b*255)};
    }
    function vaRgbToHsv(r,g,b) {
        r/=255;g/=255;b/=255;
        var max=Math.max(r,g,b),min=Math.min(r,g,b),d=max-min,h,s=max?d/max:0,v=max;
        if(!d){h=0;}else if(max===r){h=(g-b)/d+(g<b?6:0);}else if(max===g){h=(b-r)/d+2;}else{h=(r-g)/d+4;}
        return {h:h/6*360,s:s,v:v};
    }
    function vaRgbToHex(r, g, b) {
        return '#' + [r,g,b].map(function(v){return ('0'+Math.max(0,Math.min(255,Math.round(v))).toString(16)).slice(-2);}).join('');
    }
    function vaHexToRgb(hex) {
        hex=hex.replace('#','');
        if(hex.length===3)hex=hex[0]+hex[0]+hex[1]+hex[1]+hex[2]+hex[2];
        return{r:parseInt(hex.substr(0,2),16)||0,g:parseInt(hex.substr(2,2),16)||0,b:parseInt(hex.substr(4,2),16)||0};
    }
    function vaCpickParse(val) {
        var m=val.match(/^rgba?\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*(?:,\s*([\d.]+))?\s*\)/i);
        var r,g,b,a=1;
        if(m){r=+m[1];g=+m[2];b=+m[3];a=m[4]!==undefined?parseFloat(m[4]):1;}
        else if(/^#[0-9a-f]{3,8}$/i.test(val)){var rgb=vaHexToRgb(val);r=rgb.r;g=rgb.g;b=rgb.b;}
        else return null;
        var hsv=vaRgbToHsv(r,g,b);
        return{h:hsv.h,s:hsv.s,v:hsv.v,a:a};
    }

    // (legacy stub)
    function vaCommitRgba() {}



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