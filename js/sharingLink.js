(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.sharingLink = {
    attach: function (context) {
      function copyToClipboard(element) {
        var $temp = $("<input>");
        $('body').append($temp);
        $temp.val($(element).val()).select();
        document.execCommand('copy');
        $temp.remove();
      }

      $('.sharing-link').once().each(function () {
        var container = $(this);
        var input = $('.sharing-link__input', container)[0];

        $('.js-sharing-link-copy-button', container).click(function() {
          copyToClipboard(input);
          var $label = $('.sharing-link__copied-message', container);
          $label.addClass('visible');
          setTimeout(function() {
            $label.removeClass('visible');
          }, 1000);
        });
      });
    }
  };
})(jQuery, Drupal);
