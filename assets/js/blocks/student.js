( function( blocks, element ) {
    var el = element.createElement;
    blocks.registerBlockType( 'teqcidb/student', {
        title: 'Student',
        icon: 'database',
        category: 'widgets',
        edit: function() {
            return el( 'p', {}, 'Student Output' );
        },
        save: function() {
            return null;
        }
    } );
} )( window.wp.blocks, window.wp.element );
