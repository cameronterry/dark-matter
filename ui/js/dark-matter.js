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

        if ( 'error' === response ) {
          alert( 'Something has gone wrong!' );
        }
        else {
          $( '.dark-matter-blog table > tbody' ).append( response );
          document.getElementById( 'dm_add_domain_form' ).reset();
        }
      } );

      e.preventDefault();
    } );

    $( 'table#dark-matter-blog-domains' ).on( 'click', 'button.delete-domain', function ( e ) {
      var domain_id = $( this ).parents( 'tr' ).data( 'id' );

      var data = {
        'action' : 'dark_matter_del_domain',
        'dm_delete_nonce' : $( 'table#dark-matter-blog-domains' ).data( 'delete-nonce' ),
        'id' : $( this ).parents( 'tr' ).data( 'id' )
      };

      $.post( ajaxurl, data, function ( response ) {
        if ( response.success ) {
          $( 'tr#domain-' + domain_id ).hide().remove();
        }
      } );

      e.preventDefault();
    } );

  } );

} )( jQuery, window );
