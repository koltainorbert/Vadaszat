/* jshint esversion: 6 */
/**
 * VadászApró – Frontend JavaScript
 * Szűrő, watchlist, megtekintés, inline kapcsolat megjelenítés, scroll animáció
 */
(function($) {
  'use strict';

  document.documentElement.classList.add('va-js');

  function va_toast(message, type) {
    var kind = type || 'success';
    var stack = document.querySelector('.va-toast-stack');
    if (!stack) {
      stack = document.createElement('div');
      stack.className = 'va-toast-stack';
      document.body.appendChild(stack);
    }

    var toast = document.createElement('div');
    toast.className = 'va-toast va-toast--' + kind;
    toast.innerHTML = '<div class="va-toast__title">' + (kind === 'error' ? 'Hiba' : 'Kedvencek') + '</div>'
      + '<div class="va-toast__msg"></div>';
    toast.querySelector('.va-toast__msg').textContent = message;
    stack.appendChild(toast);

    requestAnimationFrame(function() {
      toast.classList.add('is-visible');
    });

    setTimeout(function() {
      toast.classList.remove('is-visible');
      setTimeout(function() {
        if (toast.parentNode) {
          toast.parentNode.removeChild(toast);
        }
      }, 250);
    }, 5000);
  }

  // ── Megtekintés számláló ─────────────────────────────────
  if (typeof VA_Data !== 'undefined' && VA_Data.post_id) {
    $.post(VA_Data.ajax_url, {
      action: 'va_increment_views',
      post_id: VA_Data.post_id
    });
  }

  // ── Ár csúszka (range slider) ────────────────────────────
  function va_format_price(val) {
    return parseInt(val).toLocaleString('hu-HU');
  }
  function va_update_range() {
    var $min  = $('#va-min-price');
    var $max  = $('#va-max-price');
    if (!$min.length) return;
    var minV  = parseInt($min.val());
    var maxV  = parseInt($max.val());
    var total = parseInt($min.attr('max')) || 50000000;
    // csere ha min > max
    if (minV > maxV) {
      var tmp = minV; minV = maxV; maxV = tmp;
      $min.val(minV); $max.val(maxV);
    }
    $('#va-min-price-display').text(va_format_price(minV));
    $('#va-max-price-display').text(va_format_price(maxV));
    // fill sáv
    var left  = (minV / total) * 100;
    var right = 100 - (maxV / total) * 100;
    $('#va-range-fill').css({ left: left + '%', right: right + '%' });
  }
  $(document).on('input', '#va-min-price, #va-max-price', function() {
    va_update_range();
    clearTimeout(filterTimeout);
    filterTimeout = setTimeout(function() { va_load_listings(1); }, 400);
  });
  va_update_range();

  // ── Watchlist toggle ─────────────────────────────────────
  $(document).on('click', '.va-card__watchlist', function(e) {
    e.preventDefault();
    e.stopPropagation();
    var $btn    = $(this);
    var post_id = $btn.data('post-id');
    var nonce   = $btn.data('nonce') || (typeof VA_Data !== 'undefined' ? VA_Data.nonce : '');
    var ajaxUrl = $btn.data('ajax-url') || (typeof VA_Data !== 'undefined' ? VA_Data.ajax_url : '');

    if (!post_id || !nonce || !ajaxUrl) {
      va_toast('A kedvencek mentése most nem elérhető.', 'error');
      return;
    }

    if ($btn.data('busy')) {
      return;
    }
    $btn.data('busy', true);

    $.post(ajaxUrl, {
      action:  'va_toggle_watchlist',
      nonce:   nonce,
      post_id: post_id
    }, function(res) {
      if (res.success) {
        $btn.toggleClass('active', res.data.action === 'added');
        $btn.attr('title', res.data.message);
        va_toast(res.data.message || 'Kedvencek frissítve.', 'success');
      } else {
        va_toast((res.data && res.data.message) ? res.data.message : 'Nem sikerült menteni a kedvencekbe.', 'error');
      }
    }).fail(function() {
      va_toast('Hálózati hiba. Próbáld újra.', 'error');
    }).always(function() {
      $btn.data('busy', false);
    });
  });

  // ── Hirdetések AJAX szűrő ────────────────────────────────
  var filterTimeout;

  function va_load_listings(page) {
    page = page || 1;
    var $form   = $('#va-filter-form');
    var $results = $('#va-listing-results');
    var $loader  = $('#va-listing-loader');

    if (!$form.length) return;

    $loader.show();
    $results.css('opacity', 0.4);

    var $minR     = $('#va-min-price');
    var $maxR     = $('#va-max-price');
    var maxLimit  = parseInt($minR.attr('max') || 50000000);
    var minVal    = $minR.length ? parseInt($minR.val()) : 0;
    var maxVal    = $maxR.length ? parseInt($maxR.val()) : 0;

    var data = {
      action:     'va_filter_listings',
      paged:      page,
      keyword:    $('#va-kw').val(),
      category:   $('#va-cat').val(),
      county:     $('#va-county').val(),
      condition:  $('#va-cond').val(),
      min_price:  minVal > 0 ? minVal : 0,
      max_price:  (maxVal > 0 && maxVal < maxLimit) ? maxVal : 0,
      sort:       $('#va-sort').val(),
      post_type:  $form.data('post-type') || 'va_listing',
      author_id:  $form.data('author-id') || 0
    };

    $.post(VA_Data.ajax_url, data, function(res) {
      $loader.hide();
      $results.css('opacity', 1);

      if (res.success) {
        $results.html(res.data.html);
        va_build_pagination(res.data.max_pages, page);
        va_count_update(res.data.total);
        va_init_animate();
      }
    });
  }

  // filter esemény
  $(document).on('change', '#va-cat, #va-county, #va-cond, #va-sort', function() {
    va_load_listings(1);
  });

  $(document).on('input', '#va-kw, #va-min-price, #va-max-price', function() {
    clearTimeout(filterTimeout);
    filterTimeout = setTimeout(function() { va_load_listings(1); }, 450);
  });

  $(document).on('click', '#va-filter-reset', function() {
    $('#va-filter-form')[0].reset();
    va_load_listings(1);
  });

  // ── Pagination build ─────────────────────────────────────
  function va_build_pagination(maxPages, currentPage) {
    var $pag = $('#va-pagination');
    if (!$pag.length || maxPages <= 1) { $pag.empty(); return; }

    var html = '';
    if (currentPage > 1) html += '<button class="va-page-btn" data-page="' + (currentPage - 1) + '">⟨</button>';
    for (var i = 1; i <= maxPages; i++) {
      html += '<button class="va-page-btn' + (i === currentPage ? ' active' : '') + '" data-page="' + i + '">' + i + '</button>';
    }
    if (currentPage < maxPages) html += '<button class="va-page-btn" data-page="' + (currentPage + 1) + '">⟩</button>';

    $pag.html(html);
  }

  $(document).on('click', '.va-page-btn', function() {
    va_load_listings(parseInt($(this).data('page')));
    $('html,body').animate({ scrollTop: $('#va-listing-results').offset().top - 80 }, 300);
  });

  function va_count_update(total) {
    $('#va-results-count').text(total + ' hirdetés');
  }

  // ── Nézet váltó (rács / lista) ───────────────────────────
  $('#va-view-grid').on('click', function() {
    $('#va-listing-results').removeClass('va-grid--list');
    $('#va-view-grid').addClass('active');
    $('#va-view-list').removeClass('active');
    localStorage.setItem('va_view', 'grid');
  });
  $('#va-view-list').on('click', function() {
    $('#va-listing-results').addClass('va-grid--list');
    $('#va-view-list').addClass('active');
    $('#va-view-grid').removeClass('active');
    localStorage.setItem('va_view', 'list');
  });
  // Mentett nézet visszaállítása
  if (localStorage.getItem('va_view') === 'list') {
    $('#va-listing-results').addClass('va-grid--list');
    $('#va-view-list').addClass('active');
    $('#va-view-grid').removeClass('active');
  } else {
    $('#va-view-grid').addClass('active');
  }

  // ── Galéria kapcsoló (detail oldal) ─────────────────────
  $(document).on('click', '.va-listing-detail__thumb', function() {
    var src = $(this).data('src');
    $('.va-listing-detail__main-img').attr('src', src);
    $('.va-listing-detail__thumb').removeClass('active');
    $(this).addClass('active');
  });

  // ── Kapcsolat megjelenítés (telefonszám) ─────────────────
  $(document).on('click', '.va-contact-box__show-btn', function() {
    var phone = $(this).data('phone');
    $(this).replaceWith('<a href="tel:' + phone + '" class="va-contact-box__phone">📞 ' + phone + '</a>');
  });

  // ── Dashboard tab navigáció ──────────────────────────────
  $(document).on('click', '.va-dashboard__nav-item', function(e) {
    e.preventDefault();
    var tab = $(this).data('tab');
    $('.va-dashboard__nav-item').removeClass('active');
    $(this).addClass('active');
    $('.va-dashboard__section').removeClass('active');
    $('#va-tab-' + tab).addClass('active');
    history.pushState(null, '', '#' + tab);
  });

  // URL hash alapú tab nyitás
  var hash = window.location.hash.replace('#', '');
  if (hash && $('.va-dashboard__nav-item[data-tab="' + hash + '"]').length) {
    $('.va-dashboard__nav-item[data-tab="' + hash + '"]').trigger('click');
  }

  // ── Scroll animáció ──────────────────────────────────────
  function va_init_animate() {
    var observer = new IntersectionObserver(function(entries) {
      entries.forEach(function(entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.1 });

    document.querySelectorAll('.va-animate').forEach(function(el) {
      observer.observe(el);
    });
  }
  va_init_animate();

  // ── Oldal betöltésekor szűrő init ────────────────────────
  if ($('#va-filter-form').length && $('#va-listing-results').length) {
    if (typeof VA_Data !== 'undefined') {
      if (VA_Data.initial_s)          { $('#va-kw').val(VA_Data.initial_s); }
      if (parseInt(VA_Data.initial_cat) > 0)        { $('#va-cat').val(VA_Data.initial_cat); }
      if (VA_Data.initial_author_id)  { $('#va-filter-form').data('author-id', VA_Data.initial_author_id); }
      if (VA_Data.initial_post_type)  { $('#va-filter-form').data('post-type', VA_Data.initial_post_type); }
    }
    va_load_listings(1);
  }

})(jQuery);
