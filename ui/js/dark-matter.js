( function ( $, window, undefined ) {

  $( document ).ready( function () {

    $( '#dm_new_add_domain' ).click( function ( e ) {
      var data = $( '#dm_add_domain_form' ).serializeArray();

      data['action'] = 'dark_matter_add_domain';
      data['nonce'] = $( '#dm_new_nonce' ).val();

      $.post( ajaxurl, data, function ( response ) {
        if ( false === data.success ) {
          alert( data.error_message );
        }
        else {
          var template = wp.template( 'domain-row' );
        }
      } );

      e.preventDefault();
    } );

  } );

} )( jQuery, window );
