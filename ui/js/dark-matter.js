( function ( $, window, undefined ) {

  $( document ).ready( function () {

    $( '#dm_new_add_domain' ).click( function ( e ) {
      var data = $( '#dm_add_domain_form' ).serializeArray();

      data.push( {
        'name' : 'action',
        'value' : 'dark_matter_add_domain'
      } );

      console.log( data );

      $.post( ajaxurl, data, function ( response ) {
        console.log( response );

        if ( false === response.success ) {
          alert( response.error_message );
        }
        else {
          var template = wp.template( 'domain-row' );
          var $this = $( template( response.data ) );

          $( '.dark-matter-blog table > tbody' ).append( $this );
          $this.slideDown( 'fast' );
        }
      } );

      e.preventDefault();
    } );

  } );

} )( jQuery, window );
