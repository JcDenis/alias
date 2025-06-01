/*global $, dotclear */
'use strict';

dotclear.ready(() => {
  // DOM ready and content loaded

  $('#alias-list').sortable();
  $('#alias-form').on('submit', () => {
    const order = [];
    $('#alias-list tr td input.position').each(function () {
      order.push(this.name.replace(/^order\[([^\]]+)\]$/, '$1'));
    });
    $('input[name=alias_order]')[0].value = order.join(',');
    return true;
  });
  $('#alias-list tr td input.position').hide();
  $('#alias-list tr td.handle').addClass('handler');
});
