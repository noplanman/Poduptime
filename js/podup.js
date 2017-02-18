$(document).ready(function(){
$.facebox.settings.closeImage = 'bower_components/facebox/src/closelabel.png'
$.facebox.settings.loadingImage = 'bower_components/facebox/src/loading.gif'
  $('a[rel*=facebox]').facebox()
  $('.tablesorter-bootstrap').tablesorter()
  $('[data-toggle="tooltip"]').tooltip()
  $(function () {
    $('[data-toggle="popover"]').popover()
  })
  $('.popover-dismiss').popover({
    trigger: 'focus'
  })
});


