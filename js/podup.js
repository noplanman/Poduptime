$('.tablesorter-bootstrap').tablesorter()
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

