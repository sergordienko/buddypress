;( function( $ ){

	$( document ).ready( function(){

		enableSortableFieldOptions( );

		if ( $.isFunction( $.fn.tabs ) ) $tabs = $( '#tabs' ).tabs({
			activate: function( event, ui ){
				history.pushState( null, null, ui.newPanel.selector ); 
			}
		});

		$( '.datebox' ).datepicker();

		$( '#content-tabs' ).removeClass( 'hide' );

		$( document ).on( 'change', '#fieldtype', function(){

			var value = $( this ).val();

			$( this ).siblings( '#multi-options' ).hide( );

			$( '#options_more' ).html( '' );

			var type = logOptgroupType( this.id );

			$( 'input[name="type"]' ).val( type );

			$( 'input[id^="default_option"]' ).prop( 'checked', false );

			if( type == 'multi' )
			{
				$( '#multi-options' ).show( );

				if( value == 'checkbox' || value == 'multiselectbox' )
				{
					clearDefaultOption( $( 'input[id^=default_option]' ), 'checkbox' );
				}
				else
				{
					clearDefaultOption( $( 'input[id^=default_option]' ), 'radio' );
				}
			}

			$( 'input[id^="default_option"]' ).prop( 'checked', false );
		});

		$( document ).on( 'change', 'input[id^="default_option"][type="radio"], .option-label input[type="radio"]', function(){

			$( 'input[id^="default_option"][type="radio"], .option-label input[type="radio"]' ).prop( 'checked', false );

			$( this ).prop( 'checked', true );

		});

		$( document ).on( 'click', '#add-option', function( e ){

			e.preventDefault( );

			var type = 'checkbox';

			var $bp_option = $( '.bp-option' ).eq( 0 ).clone( true );

			$bp_option.append( '<div class="delete-button"><a href="#" class="delete">Delete</a></div>' );

			$bp_option.find( ':text' ).each( function( i, input ){ $( input ).val( '' ); });

			$bp_option.find( ':checkbox,:radio' ).each( function( i, input ){ 

				$( input ).prop( 'checked', false ); 

				if( $( input ).is( ':checkbox' ) ) type = 'checkbox';

				else if( $( input ).is( ':radio' ) ) type = 'radio';
			});

			$( '#options_more' ).append( $bp_option );

			regenerateName( $( '.bp-option' ), 0 );

			clearDefaultOption( $( 'input[id^=default_option]' ), type );

		});

		$( document ).on( 'click', '.bp-option .delete', function( e ){

			e.preventDefault();

			$( this ).parents( '.bp-option' ).remove();

			regenerateName( $( '.bp-option' ), 0 );

		});

		$( 'fieldset.field-group' ).sortable({
			cursor: 'move',
			opacity: 0.7,
			items: 'fieldset',
			tolerance: 'pointer',
			update: function( ){
				$.post( ajaxurl, {
					action: 'group_reorder_fields',
					'cookie': encodeURIComponent( document.cookie ),
					'_wpnonce_reorder_fields': $( 'input#_wpnonce_reorder_fields' ).val( ),
					'field_order': $( this ).sortable( 'toArray' )
				},
				function( ) { } );
			}
		});
	});

	function clearDefaultOption( $object, type )
	{
		$object.each( function( i, e ){ 
			$( e ).replaceWith( '<input type="' + type + '" id="default_option[' + (i+1) + ']" name="default_option[' + (i+1) + ']" value="' + $( e ).val() + '" />' );
		});
	}

	function logOptgroupType( id )
	{
	    var elt = $( '#' + id )[ 0 ];

	    return $( elt.options[ elt.selectedIndex ] ).closest( 'optgroup' ).data( 'type' );
	}

	function replaceUrlParam( url, paramName, paramValue )
	{
	    var pattern = new RegExp( '(\\?|\\&)('+paramName+'=).*?(&|$)' );
	    
	    var newUrl = url;
	    
	    if( url.search( pattern ) >= 0 )
	    {
	        newUrl = url.replace( pattern, '$1$2' + paramValue + '$3' );
	    }
	    else
	    {
	        newUrl = newUrl + ( newUrl.indexOf( '?' ) > 0 ? '&' : '?' ) + paramName + '=' + paramValue;
	    }

	    return newUrl
	}

	function regenerateName( $objects, $index )
	{
		$objects.each( function( i, element ){
			replaceNum( $( element ), 'name', i, $index );
			replaceNum( $( element ), 'id', i, $index );
			replaceNum( $( element ), 'for', i, $index );
		});
	}

	function replaceNum( $object, attr, num, index )
	{
		$object.find( '[' + attr + ']' ).each( function( i, element ){
			var name = $( element ).attr( attr );
			var re = /\[(.?)\]/g;
			var result = name.replace( re, '[' + ( num + 1 ) + ']' );
			$( element ).attr( attr, result );
		});
	}

	function enableSortableFieldOptions( )
	{
		$( '.bp-options-box' ).sortable({
			cursor: 'move',
			items: 'div.sortable',
			tolerance: 'intersect',
			axis: 'y',
			stop: function( event, ui ){ regenerateName( $( '.bp-option' ), 0 ); }
		});

		$( '.sortable, .sortable span' ).css( 'cursor', 'move' );
	}

	function destroySortableFieldOptions( )
	{
		$( '.bp-options-box' ).sortable( 'destroy' );
		$( '.sortable, .sortable span' ).css( 'cursor', 'default' );
	}

})( jQuery );