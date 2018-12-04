jQuery( document ).ready( function( $ )
{
	// if it is taxonomy page
	if( adminpage == 'edit-tags-php' )
	{
		// make table rows sortable
		$( '.wp-list-table.tags tbody' ).sortable({
			items: 'tr:not(.inline-edit-row)',
			cursor: 'move',
			axis: 'y',
			containment: 'table.widefat',
			scrollSensitivity: 40,
			stop: function( event, ui ) {
				// array for the ids and positions
				var rows	= new Array([]);

				// show "activity" with spinner
				hsam_taxonomy_sort_activity_spinner( true );

				$( '.wp-list-table.tags tbody tr:not(.inline-edit-row)' ).each( function( i, e ) {
					var rowID	= parseInt( $( e ).attr( 'id' ).substr( 4 ) );
					rows[i]		= rowID;
				} );

				// post rows for sorting
				$.post( ajaxurl, { 'rows' : rows, 'action' : 'get_inline_boxes' }, function(response) {
					// stop activity spinner
					hsam_taxonomy_sort_activity_spinner( false );
				});
			}
		});
	}

	/**
	 * Adds, shows or hides the activity status spinner
 
	 */
	function hsam_taxonomy_sort_activity_spinner( status ) {
		var actions_table	= $( '.tablenav .actions' );

		if( actions_table.find( '.spinner' ).length === 0 && status === true ) {
			// add spinner
			actions_table.prepend( '<div class="spinner" style="display: inline;" />' );
		}
		else if ( status === true ) {
			// show spinner
			actions_table.find( '.spinner' ).show();
		}
		else {
			// hide spinner
			actions_table.find( '.spinner' ).hide();
		}
	}
} );