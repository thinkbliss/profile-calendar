    $('.accordion li').on('click', function(j) {
        var dropDown = $(this).closest('li').find('p');

        $(this).closest('.accordion').find('p').not(dropDown).slideUp();

        if ($(this).hasClass('active')) {
            $(this).removeClass('active');
        } else {
            $(this).closest('.accordion').find('li.active').removeClass('active');
            $(this).addClass('active');
        }

            dropDown.stop(false, true).slideToggle();

            j.preventDefault();
        });
        $('.insideLink').click(function(event){
            event.stopPropagation();
    });


    $('.overlay-trigger').on( 'click', function( event ) {
        event.preventDefault();
        var overlay = $( this ).data( 'overlay' );
        if (! overlay) {
            console.log( 'You must provide the overlay id in the trigger. (data-overlay="overlay-id").' );
            return;
        }

        var id = '#' + overlay;

        $(id).addClass('overlay-open');
        $('body').addClass('overlay-view');

        $(id).on('click', function(event) {
            // Verify that only the outer wrapper was clicked.
            if (event.target.id == overlay) {
                $(id).removeClass('overlay-open');
                $('body').removeClass('overlay-view');
            }
        });

        $('.overlay-close').on('click', function(event) {
            // Verify that only the outer wrapper was clicked.
                $(id).removeClass('overlay-open');
                $('body').removeClass('overlay-view');
        });

        $(document).keyup( function(event) {
            // Verify that the esc key was pressed.
            if (event.keyCode == 27) {
                $(id).removeClass('overlay-open');
                $('body').removeClass('overlay-view');
            }
        });
    });

    // charity click events
    // $('.column-01').on('click', function(){
    //     window.location = $(this).find("a:first").attr("href");
    //     return false;
    // });




