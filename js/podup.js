$(document).ready(function(){
$.facebox.settings.closeImage = 'bower_components/facebox/src/closelabel.png'
$.facebox.settings.loadingImage = 'bower_components/facebox/src/loading.gif'
  $('a[rel*=facebox]').facebox()
  $('#myTable').tablesorter()
  $('[data-toggle="tooltip"]').tooltip()
});

