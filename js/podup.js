var $table = $('.tablesorter'),
  pagerOptions = {
  container: $(".pager"),
  output: '{startRow} - {endRow}',
  removeRows: true,
  cssGoto: '.gotoPage'
};

$table
  .tablesorter({
    theme: 'default',
    headerTemplate : '{content} {icon}', // new in v2.7. Needed to add the bootstrap icon!
    widthFixed: true,
    widgets: ['zebra', 'filter', 'saveSort','resizable', 'columns'],
  })
  .tablesorterPager(pagerOptions);

$('table').trigger('pageSize', 20);

$(document).ready(function(){
$.facebox.settings.closeImage = 'bower_components/facebox/src/closelabel.png'
$.facebox.settings.loadingImage = 'bower_components/facebox/src/loading.gif'
  $('a[rel*=facebox]').facebox()
  $('[data-toggle="tooltip"]').tooltip()
  $(function () {
    $('[data-toggle="popover"]').popover()
  })
  $('.popover-dismiss').popover({
    trigger: 'focus'
  })
  $('.openBtn').on('click',function(){
    $('#podpull').modal({show:true});
    $('.modal-body').load('/db/pull.php?debug=1&nowrite=1&domain='+$(this).val(),function(){
    });
  });
});

