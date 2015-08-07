(function($) {
    $(function() {
        var jcarousel = $('.jcarousel').jcarousel();
        console.log(typeof result);
        $('.jcarousel-control-prev')
            .on('jcarouselcontrol:active', function() {
                $(this).removeClass('inactive');
            })
            .on('jcarouselcontrol:inactive', function() {
                $(this).addClass('inactive');
            })
            .jcarouselControl({
                target: '-=1'
            });

        $('.jcarousel-control-next')
            .on('jcarouselcontrol:active', function() {
                $(this).removeClass('inactive');
            })
            .on('jcarouselcontrol:inactive', function() {
                $(this).addClass('inactive');
            })
            .jcarouselControl({
                target: '+=1'
            });

        var setup = function(data) {
            var html = '<ul>';

            $.each(data.items, function() {
                html += '<p>' +
                            '<span class="realName">' + data.response[i].name + '</span>' +
                            '<span class="nickName">' + data.response[i].user_nick + '</span>' +
                        '</p>'
            });

            html += '</ul>';

            // Append items
            jcarousel
                .html(html);

            // Reload carousel
            jcarousel
                .jcarousel('reload');
        };

        $.getJSON('/welldays/testimonials/get', setup);
    });
})(jQuery);
