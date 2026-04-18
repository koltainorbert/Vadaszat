/* jshint esversion: 6 */
(function($){
  'use strict';

  function getToastStack() {
    var stack = document.querySelector('.va-admin-toast-stack');
    if (!stack) {
      stack = document.createElement('div');
      stack.className = 'va-admin-toast-stack';
      document.body.appendChild(stack);
    }
    return stack;
  }

  function pushToast(message, type, title) {
    var stack = getToastStack();
    var kind = type || 'info';
    var ttl = title || (kind === 'success' ? 'Mentve' : (kind === 'error' ? 'Hiba' : 'Informacio'));

    var toast = document.createElement('div');
    toast.className = 'va-admin-toast va-admin-toast--' + kind;
    toast.innerHTML = '<p class="va-admin-toast__title"></p><p class="va-admin-toast__msg"></p>';
    toast.querySelector('.va-admin-toast__title').textContent = ttl;
    toast.querySelector('.va-admin-toast__msg').textContent = message;
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
      }, 260);
    }, 5000);
  }

  function updatePreview(targetId, url) {
    var $wrap = $('#' + targetId + '_preview');
    if (!$wrap.length) return;

    if (url) {
      $wrap.html('<img src="' + url + '" alt="" class="va-media-preview">');
    } else {
      $wrap.empty();
    }
  }

  $(document).on('click', '.va-media-pick', function(e){
    e.preventDefault();
    var targetId = $(this).data('target');
    var $input = $('#' + targetId);
    if (!$input.length || typeof wp === 'undefined' || !wp.media) return;

    var frame = wp.media({
      title: 'Kép kiválasztása',
      library: { type: 'image' },
      button: { text: 'Kiválasztás' },
      multiple: false
    });

    frame.on('select', function(){
      var selection = frame.state().get('selection').first();
      if (!selection) return;
      var attachment = selection.toJSON();
      var url = attachment.url || '';
      $input.val(url).trigger('change');
      updatePreview(targetId, url);
    });

    frame.open();
  });

  $(document).on('click', '.va-media-clear', function(e){
    e.preventDefault();
    var targetId = $(this).data('target');
    var $input = $('#' + targetId);
    if (!$input.length) return;

    $input.val('').trigger('change');
    updatePreview(targetId, '');
  });

  $(document).on('change', '.va-media-input', function(){
    updatePreview($(this).attr('id'), $(this).val());
  });

  $(function(){
    if ($.fn.wpColorPicker) {
      $('.va-color-input').wpColorPicker();
    }

    // Mentes gombra kattintasnal azonnali visszajelzes.
    $(document).on('submit', '.va-admin-wrap form', function() {
      pushToast('Mentes folyamatban...', 'info', 'Vadaszapro Admin');
    });

    // Oldal ujratoltes utan WP notice -> push toast.
    var hasPushedNotice = false;
    $('.va-admin-wrap .notice, .va-admin-wrap .updated, .va-admin-wrap .error').each(function() {
      var $n = $(this);
      var text = $.trim($n.text().replace(/\s+/g, ' '));
      if (!text) return;

      var kind = 'info';
      if ($n.hasClass('notice-error') || $n.hasClass('error')) {
        kind = 'error';
      } else if ($n.hasClass('notice-success') || $n.hasClass('updated')) {
        kind = 'success';
      }

      pushToast(text, kind, kind === 'success' ? 'Sikeres mentes' : (kind === 'error' ? 'Mentési hiba' : 'Vadaszapro Admin'));
      hasPushedNotice = true;
    });

    if (!hasPushedNotice) {
      var params = new URLSearchParams(window.location.search);
      if (params.get('settings-updated') === 'true') {
        pushToast('Beallitasok sikeresen mentve.', 'success', 'Sikeres mentes');
      }
    }
  });
})(jQuery);
