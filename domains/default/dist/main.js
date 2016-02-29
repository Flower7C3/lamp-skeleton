/**
 * Created by bkwiatek on 15.09.2015.
 */
function tagFilter(tag) {
    $('.bootstrap-table .search input').val(tag).trigger('drop');
}
function createLoader() {
    $('#main').addClass('loading');
}
function removeLoader() {
    $('#main').removeClass('loading');
}
function searchOnPage(e) {
    if (e.which === 27) {
        $('.bootstrap-table .search input').val('').focus();
    }
}

function startTime() {
    var today = new Date();
    var h = today.getHours();
    var m = today.getMinutes();
    var s = today.getSeconds();
    m = checkTime(m);
    s = checkTime(s);
    $('.current-time').text(h + ":" + m + ":" + s);
    $('#current-time').val('@' + parseInt(today.getTime() / 1000));
    var t = setTimeout(function () {
        startTime()
    }, 500);
}

function checkTime(i) {
    if (i < 10) {
        i = "0" + i
    }
    return i;
}


var tag = decodeURIComponent(window.location.hash.replace('#', '').trim());
$(function () {
    startTime();
    if (tag) {
        setTimeout(function () {
            tagFilter(tag);
        }, 500);
    } else {
        removeLoader();
    }
    $('.datepicker').datepicker({
        todayBtn: true,
        language: "pl",
        orientation: "bottom right",
        calendarWeeks: true,
        todayHighlight: true
    });
    $(document)
        .on('contextmenu', '.bootstrap-table tr', function (e) {
            var id = $(this).data('id');
            $('.context-menu').removeClass('visible');
            $('#context-menu-id-' + id).addClass('visible').css({
                top: e.pageY - $('body').offset().top,
                left: e.pageX,
            });
            return false;
        })
        .on('click', function (e) {
            if (e.which === 1) {
                $('.context-menu').removeClass('visible');
            }
        })
        .on('click', '[data-copy]', function (e) {
            element = $(this).data('copy');
            $element = $(element);
            $element.focus().select();
            var msg, type;
            try {
                var successful = document.execCommand('copy');
                msg = (successful ? ('Copy data success: <b>' + $element.val() + '</b>') : 'Sorry, can\'t copy data.');
                type = successful ? 'success' : 'danger';
            } catch (err) {
                msg = 'Oops, unable to copy.';
                type = 'info';
            }
            $.bootstrapGrowl(msg, {
                type: type
            });
        })
        .on('click', 'a[data-tag]', function () {
            createLoader();
            var tag = $(this).data('tag');
            tagFilter(tag);
        })
        .on('click', '.bootstrap-table .search .close', function () {
            createLoader();
            tagFilter('');
        })
        .on('keyup.search', searchOnPage)
        .on('keyup drop', '.bootstrap-table .search input', function (e) {
            createLoader();
            if (e.which === 13) {
                $('.bootstrap-table .search input').change();
            } else if (e.which === 27) {
                tagFilter('');
            } else {
                var tag = $(this).val().trim();
                var $button = $('<a>').addClass('fa fa-times close').attr('href', 'javascript://undefined');
                $('nav li.active').removeClass('active');
                if (tag) {
                    $('nav li [data-tag="' + tag + '"]').closest('ul').closest('li').addClass('active');
                    $('nav li [data-tag="' + tag + '"]').closest('li').addClass('active');
                    window.location.hash = tag;
                    if ($('.bootstrap-table .search .close').length === 0) {
                        $('.bootstrap-table .search input').after($button);
                    }
                } else if (!tag) {
                    window.location.hash = "";
                    $('.bootstrap-table .search .close').remove();
                }
            }
        }).on('sort.bs.table', function () {
        createLoader();
    }).on('pre-body.bs.table', function () {
        createLoader();
    }).on('post-body.bs.table', function () {
        removeLoader();
    });
});
