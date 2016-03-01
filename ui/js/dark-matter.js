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

    $( 'table#dark-matter-blog-domains' ).on( 'click', 'button.delete-domain', function ( e ) {
      var domain_id = $( this ).parents( 'tr' ).data( 'id' );

      var data = {
        'action' : 'dark_matter_del_domain',
        'dm_delete_nonce' : $( 'table#dark-matter-blog-domains' ).data( 'delete-nonce' ),
        'id' : $( this ).parents( 'tr' ).data( 'id' )
      };

      $.post( ajaxurl, data, function ( response ) {
        if ( response.success ) {
          $( 'tr#domain-' + domain_id ).slideUp( 'fast', function () {
            $( this ).remove();
          } );
        }
      } );

      e.preventDefault();
    } );

  } );

} )( jQuery, window );
