(function ( $, core ) {

    function picker( selector, args ) {
        $( selector ).select2( args );
    }

    function linkPicker( selector )
    {
        $( selector ).autocomplete({
            source: function( request, response ) {
                $.ajax( {
                    url: ajaxurl,
                    method: 'post',
                    dataType: "json",
                    data: {
                        action: 'wp-link-ajax',
                        _ajax_linking_nonce: core.nonces._ajax_linking_nonce,
                        term: request.term
                    },
                    success: function( data ) {
                        var $return = [];
                        for ( var item in data ) {
                            if (data.hasOwnProperty( item ) ) {
                                item = data[ item ];
                                $return.push( { label: item.title  + ' (' + item.info + ')', value: item.permalink } );
                            }
                        }
                        response( $return );
                    }
                } );
            },
            minLength: 0
        } );
    }

    function colorPicker( selector ) {
        $(selector).wpColorPicker();
    }

    function buildPickers()
    {
        picker(     '.extended-core-select2', {} );
        linkPicker( '.extended-core-link-picker' );
        colorPicker( '.extended-core-color' );
    }

    $(function () {
        buildPickers();
    });

    $( document ).on( 'extended-core-init-pickers', function () {
        buildPickers();
    });

    core.pickers = {};

    // Map functions to Groundhogg object.
    core.pickers.picker = picker;
    core.pickers.color  = colorPicker;
    core.pickers.linkPicker = linkPicker;

})(jQuery, ExtendedCore );