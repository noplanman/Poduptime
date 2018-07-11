var $table = $('.tablesorter'),
    pagerOptions = {
        container: $(".pager"),
        output: '{startRow} - {endRow} of {totalRows} total',
        removeRows: false,
        cssGoto: '.gotoPage'
    };

$(document).ready(function () {
    $('[data-toggle="tooltip"]').tooltip();
    $('.openBtn').on('click', function () {
        $('#podpull').modal({show: true});
        $('.modal-body').load('/db/pull.php?debug=1&nowrite=1&domain=' + $(this).val(), function () {
        });
    });
    $table
        .tablesorter({
            theme: 'bootstrap',
            headerTemplate: '{content} {icon}',
            widthFixed: true,
            widgets: ['filter', 'saveSort'],
            initialized() {
                $('.table-responsive').css("visibility", "visible").fadeIn('loading');
            }
        })
        .tablesorterPager(pagerOptions);
    $('table').trigger('pageSize', 18);
});
