/* jshint esversion: 6 */
/**
 * VadászApró – Frontend JavaScript
 * Szűrő, watchlist, megtekintés, inline kapcsolat megjelenítés, scroll animáció
 */
(function($) {
  'use strict';

  // ── Megtekintés számláló ─────────────────────────────────
  if (typeof VA_Data !== 'undefined' && VA_Data.post_id) {
    $.post(VA_Data.ajax_url, {
      action: 'va_increment_views',
      post_id: VA_Data.post_id
    });
  }

  // ── Watchlist toggle ─────────────────────────────────────
  $(document).on('click', '.va-card__watchlist', function(e) {
    e.preventDefault();
    var $btn    = $(this);
    var post_id = $btn.data('post-id');

    $.post(VA_Data.ajax_url, {
      action:  'va_toggle_watchlist',
      nonce:   VA_Data.nonce,
      post_id: post_id
    }, function(res) {
      if (res.success) {
        $btn.toggleClass('active', res.data.action === 'added');
        $btn.attr('title', res.data.message);
      }
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

    var data = {
      action:     'va_filter_listings',
      paged:      page,
      keyword:    $('#va-kw').val(),
      category:   $('#va-cat').val(),
      county:     $('#va-county').val(),
      condition:  $('#va-cond').val(),
      min_price:  $('#va-min-price').val(),
      max_price:  $('#va-max-price').val(),
      sort:       $('#va-sort').val(),
      post_type:  $form.data('post-type') || 'va_listing'
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
    va_load_listings(1);
  }

})(jQuery);
