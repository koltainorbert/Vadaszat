/* jshint esversion: 6 */
(function($){
  'use strict';

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
})(jQuery);
