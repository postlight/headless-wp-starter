(function($){
	
	acf.field_group = acf.model.extend({
		
		// vars
		$fields: null,
		$locations: null,
		$options: null,
		
		actions: {
			'ready': 'init'
		},
		
		events: {
			'submit #post':					'submit',
			'click a[href="#"]':			'preventDefault',
			'click .submitdelete': 			'trash',
			'mouseenter .acf-field-list': 	'sortable'
		},
		
		
		/*
		*  init
		*
		*  This function will run on document ready and initialize the module
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	n/a
		*  @return	n/a
		*/
		
		init: function(){
			
			// $el
			this.$fields = $('#acf-field-group-fields');
			this.$locations = $('#acf-field-group-locations');
			this.$options = $('#acf-field-group-options');
			
			
			// disable validation
			acf.validation.active = 0;
		    
		},
		
		
		/*
		*  sortable
		*
		*  This function will add sortable to the feild group list
		*  sortable is added on mouseover to speed up page load
		*
		*  @type	function
		*  @date	28/10/2015
		*  @since	5.3.2
		*
		*  @param	$post_id (int)
		*  @return	$post_id (int)
		*/
		
		sortable: function( e ){
			
			// bail early if already sortable
			if( e.$el.hasClass('ui-sortable') ) {
				
				return;
				
			}
			
			
			// vars
			var self = this;
			
			
			// sortable
			e.$el.sortable({
				handle: '.acf-sortable-handle',
				connectWith: '.acf-field-list',
				start: function(e, ui){
			        ui.placeholder.height( ui.item.height() );
			    },
				update: function(event, ui){
					
					// vars
					var $el = ui.item;
					
					
					// render
					self.render_fields();
					
					
					// actions
					acf.do_action('sortstop', $el);
					
				}
			});
			
		},
		
		
		/*
		*  preventDefault
		*
		*  This helper will preventDefault on all events for empty links
		*
		*  @type	function
		*  @date	18/08/2015
		*  @since	5.2.3
		*
		*  @param	$post_id (int)
		*  @return	$post_id (int)
		*/
		
		preventDefault: function( e ){
			
			e.preventDefault();
			
		},
		
		
		/*
		*  get_selector
		*
		*  This function will return a valid selector for finding a field object
		*
		*  @type	function
		*  @date	15/01/2015
		*  @since	5.1.5
		*
		*  @param	s (string)
		*  @return	(string)
		*/
		
		get_selector: function( s ) {
			
			// defaults
			s = s || '';
			
			
			// vars
			var selector = '.acf-field-object';
			

			// search
			if( s ) {
				
				// append
				selector += '-' + s;
				
				
				// replace underscores (split/join replaces all and is faster than regex!)
				selector = selector.split('_').join('-');
			
			}
			
			
			// return
			return selector;
			
		},
		
		
		/*
		*  render_fields
		*
		*  This function is triggered by a change in field order, and will update the field icon number
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	n/a
		*  @return	n/a
		*/
		
		render_fields: function(){
			
			// reference
			var self = this;
			
			
			// update order numbers
			$('.acf-field-list').each(function(){
				
				// vars
				var $fields = $(this).children('.acf-field-object');
				
				
				// loop over fields
				$fields.each(function( i ){
					
					// update meta
					self.update_field_meta( $(this), 'menu_order', i );
					
					
					// update icon number
					$(this).children('.handle').find('.acf-icon').html( i+1 );
					
				});
				
				
				// show no fields message
				if( !$fields.exists() ){
					
					$(this).children('.no-fields-message').show();
					
				} else {
					
					$(this).children('.no-fields-message').hide();
					
				}
				
			});
			
		},
		
		
		/*
		*  get_field_meta
		*
		*  This function will return an input value for a field
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$el
		*  @param	name
		*  @return	(string)
		*/
		
		get_field_meta: function( $el, name ){
			
			//console.log( 'get_field_meta(%o, %o)', $el, name );
			
			// vars
	    	var $input = $el.find('> .meta > .input-' + name);
	    	
	    	
	    	// bail early if no input
			if( !$input.exists() ) {
				
				//console.log( '- aborted due to no input' );
				return false;
				
			}
			
			
			// return
			return $input.val();
			
		},
		
		
		/*
		*  update_field_meta
		*
		*  This function will update an input value for a field
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$el
		*  @param	name
		*  @param	value
		*  @return	n/a
		*/
		
		update_field_meta: function( $el, name, value ){
			
			//console.log( 'update_field_meta(%o, %o, %o)', $el, name, value );
			
			// vars
	    	var $input = $el.find('> .meta > .input-' + name);
	    	
	    	
	    	// create hidden input if doesn't exist
			if( !$input.exists() ) {
				
				// vars
				var html = $el.find('> .meta > .input-ID').outerHTML();
				
				
				// replcae
				html = acf.str_replace('ID', name, html);
								
				
				// update $input
				$input = $(html);
				
				
				// reset value
				$input.val( value );
				
				
				// append
				$el.children('.meta').append( $input );
				
				//console.log( '- created new input' );
				
			}
			
			
			// bail early if no change
			if( $input.val() == value ) {
				
				//console.log( '- aborted due to no change in input value' );
				return;
			}
			
			
			// update value
			$input.val( value );
			
			
			// bail early if updating save
			if( name == 'save' ) {
				
				//console.log( '- aborted due to name == save' );
				return;
				
			}
			
			
			// meta has changed, update save
			this.save_field( $el, 'meta' );
			
		},
		
		
		/*
		*  delete_field_meta
		*
		*  This function will return an input value for a field
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$el
		*  @param	name
		*  @return	(string)
		*/
		
		delete_field_meta: function( $el, name ){
			
			//console.log( 'delete_field_meta(%o, %o, %o)', $el, name );
			
			// vars
	    	var $input = $el.find('> .meta > .input-' + name);
	    	
	    	
	    	// bail early if not exists
			if( !$input.exists() ) {
			
				//console.log( '- aborted due to no input' );
				return;
				
			}
			
			
			// remove
			$input.remove();
			
			
			// meta has changed, update save
			this.save_field( $el, 'meta' );
			
		},
		
		
		/*
		*  save_field
		*
		*  This function will update the changed input for a given field making sure it is saved on submit
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$el
		*  @return	n/a
		*/
		
		save_field: function( $el, type ){
			
			//console.log('save_field(%o %o)', $el, type);
			
			// defaults
			type = type || 'settings';
			
			
			// vars
			var value = this.get_field_meta( $el, 'save' );
			
			
			// bail early if already 'settings'
			if( value == 'settings' ) {
				
				return;
				
			}
			
			
			// bail early if no change
			if( value == type ) {
				
				return;
				
			}
			
			
			// update meta
			this.update_field_meta( $el, 'save', type );
			
			
			// action for 3rd party customization
			acf.do_action('save_field', $el, type);
			
		},
		
		
		/*
		*  submit
		*
		*  This function is triggered when submitting the form and provides validation prior to posting the data
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	n/a
		*  @return	(boolean)
		*/
		
		submit: function( e ){
			
			// reference
			var self = this;
			
			
			// vars
			var $title = $('#titlewrap #title');
			
			
			// title empty
			if( !$title.val() ) {
				
				// prevent default
				e.preventDefault();
				
				
				// unlock form
				acf.validation.toggle( e.$el, 'unlock' );
				
				
				// alert
				alert( acf._e('title_is_required') );
				
				
				// focus
				$title.focus();
				
			}
			
			
			// close / delete fields
			$('.acf-field-object').each(function(){
				
				// vars
				var save = self.get_field_meta( $(this), 'save'),
					ID = self.get_field_meta( $(this), 'ID'),
					open = $(this).hasClass('open');
				
				
				// close
				if( open ) {
					
					self.close_field( $(this) );
					
				}
				
				
				// remove unnecessary inputs
				if( save == 'settings' ) {
					
					// allow all settings to save (new field, changed field)
					
				} else if( save == 'meta' ) {
					
					$(this).children('.settings').find('[name^="acf_fields[' + ID + ']"]').remove();
					
				} else {
					
					$(this).find('[name^="acf_fields[' + ID + ']"]').remove();
					
				}
				
			});

		},
		
		
		/*
		*  trash
		*
		*  This function is triggered when moving the field group to trash
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	n/a
		*  @return	(boolean)
		*/
		
		trash: function( e ){
			
			var result = confirm( acf._e('move_to_trash') );
			
			if( !result ) {
				
				e.preventDefault();
				
			}
			
		},
		
		
		/*
		*  render_field
		*
		*  This function will update the field's info
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$el
		*  @return	n/a
		*/
		
		render_field: function( $el ){
			
			// vars
			var label = $el.find('.field-label:first').val(),
				name = $el.find('.field-name:first').val(),
				type = $el.find('.field-type:first option:selected').text(),
				required = $el.find('.field-required:first').prop('checked'),
				$handle = $el.children('.handle');
			
			
			// update label
			$handle.find('.li-field-label strong a').html( label );
			
			
			// update required
			$handle.find('.li-field-label .acf-required').remove();
			
			if( required ) {
				
				$handle.find('.li-field-label strong').append('<span class="acf-required">*</span>');
				
			}
			
			
			// update name
			$handle.find('.li-field-name').text( name );
			
			
			// update type
			$handle.find('.li-field-type').text( type );
			
			
			// action for 3rd party customization
			acf.do_action('render_field_handle', $el, $handle);
			
		},
		
		
		/*
		*  edit_field
		*
		*  This function is triggered when clicking on a field. It will open / close a fields settings
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$el
		*  @return	n/a
		*/
		
		edit_field: function( $field ){
			
			// toggle
			if( $field.hasClass('open') ) {
			
				this.close_field( $field );
				
			} else {
			
				this.open_field( $field );
				
			}
			
		},
		
		
		/*
		*  open_field
		*
		*  This function will open a fields settings
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$el
		*  @return	n/a
		*/
		
		open_field: function( $el ){
			
			// bail early if already open
			if( $el.hasClass('open') ) {
			
				return false;
				
			}
			
			
			// add class
			$el.addClass('open');
			
			
			// action for 3rd party customization
			acf.do_action('open_field', $el);
			
			
			// animate toggle
			$el.children('.settings').animate({ 'height' : 'toggle' }, 250 );
			
		},
		
		
		/*
		*  close_field
		*
		*  This function will open a fields settings
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$el
		*  @return	n/a
		*/
		
		close_field: function( $el ){
			
			// bail early if already closed
			if( !$el.hasClass('open') ) {
			
				return false;
				
			}
			
			
			// remove class
			$el.removeClass('open');
			
			
			// action for 3rd party customization
			acf.do_action('close_field', $el);
			
			
			// animate toggle
			$el.children('.settings').animate({ 'height' : 'toggle' }, 250 );
			
		},
		
		
		/*
		*  wipe_field
		*
		*  This function will prepare a new field by updating the input names
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$el
		*  @return	n/a
		*/
		
		wipe_field: function( $el ){
			
			// vars
			var id = $el.attr('data-id'),
				key = $el.attr('data-key'),
				new_id = acf.get_uniqid(),
				new_key = 'field_' + new_id;
			
			
			// update attr
			$el.attr('data-id', new_id);
			$el.attr('data-key', new_key);
			$el.attr('data-orig', key);
			
			
			// update hidden inputs
			this.update_field_meta( $el, 'ID', '' );
			this.update_field_meta( $el, 'key', new_key );
			
			
			// update attributes
			$el.find('[id*="' + id + '"]').each(function(){	
			
				$(this).attr('id', $(this).attr('id').replace(id, new_id) );
				
			});
			
			$el.find('[name*="' + id + '"]').each(function(){	
			
				$(this).attr('name', $(this).attr('name').replace(id, new_id) );
				
			});
			
			
			// update key
			$el.find('> .handle .pre-field-key').text( new_key );
			
			
			// remove sortable classes
			$el.find('.ui-sortable').removeClass('ui-sortable');
			
			
			// action for 3rd party customization
			acf.do_action('wipe_field', $el);
			
		},
		
		
		/*
		*  add_field
		*
		*  This function will add a new field to a field list
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$fields
		*  @return	n/a
		*/
		
		add_field: function( $fields ){
			
			// clone tr
			var $el = $( $('#tmpl-acf-field').html() ),
				$label = $el.find('.field-label:first'),
				$name = $el.find('.field-name:first');
			
			
			// update names
			this.wipe_field( $el );
			
			
			// append to table
			$fields.append( $el );
			
			
			// clear name
			$label.val('');
			$name.val('');
			
			
			// focus after form has dropped down
			setTimeout(function(){
			
	        	$label.focus();
	        	
	        }, 251);
			
			
			// update order numbers
			this.render_fields();
			
			
			// trigger append
			acf.do_action('append', $el);
			
			
			// open up form
			this.edit_field( $el );
			
			
			// action for 3rd party customization
			acf.do_action('add_field', $el);
			
		},
		
		
		/*
		*  duplicate_field
		*
		*  This function will duplicate a field
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$el
		*  @return	$el2
		*/
		
		duplicate_field: function( $el ){
			
			// allow acf to modify DOM
			acf.do_action('before_duplicate', $el);
			
			
			// vars
			var $el2 = $el.clone(),
				$label = $el2.find('.field-label:first'),
				$name = $el2.find('.field-name:first');
			
			
			// remove JS functionality
			acf.do_action('remove', $el2);
			
			
			// update names
			this.wipe_field( $el2 );
			
			
			// allow acf to modify DOM
			acf.do_action('after_duplicate', $el, $el2);
			
			
			// append to table
			$el.after( $el2 );
			
			
			// trigger append
			acf.do_action('append', $el2);
			
			
			// focus after form has dropped down
			setTimeout(function(){
			
	        	$label.focus();
	        	
	        }, 251);
	        
			
			// update order numbers
			this.render_fields();
			
			
			// open up form
			if( $el.hasClass('open') ) {
			
				this.close_field( $el );
				
			} else {
			
				this.open_field( $el2 );
				
			}
			
			
			// update new_field label / name
			var label = $label.val(),
				name = $name.val(),
				end = name.split('_').pop(),
				copy = acf._e('copy');
			
			
			// look at last word
			if( end.indexOf(copy) === 0 ) {
				
				var i = end.replace(copy, '') * 1;
					i = i ? i+1 : 2;
				
				// replace
				label = label.replace( end, copy + i );
				name = name.replace( end, copy + i );
				
			} else {
				
				label += ' (' + copy + ')';
				name += '_' + copy;
				
			}
			
			
			$label.val( label );
			$name.val( name );
			
			
			// save field
			this.save_field( $el2 );
			
			
			// render field
			this.render_field( $el2 );
			
			
			// action for 3rd party customization
			acf.do_action('duplicate_field', $el2);
			
			
			// return
			return $el2;
			
		},
		
		
		/*
		*  move_field
		*
		*  This function will launch a popup to move a field to another field group
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$field
		*  @return	n/a
		*/
		
		move_field: function( $field ){
			
			// reference
			var self = this;
			
			
			// AJAX data
			var ajax_data = acf.prepare_for_ajax({
				action:		'acf/field_group/move_field',
				field_id:	this.get_field_meta( $field, 'ID' )
			});
			
			
			// vars
			var warning = false;
			
			
			// validate
			if( !ajax_data.field_id ) {
				
				// Case: field not saved to DB
				warning = true;
				
			} else if( this.get_field_meta( $field, 'save' ) == 'settings' ) {
				
				// Case: field's settings have changed
				warning = true;
				
			} else {
				
				// Case: sub field's settings have changed
				$field.find('.acf-field-object').each(function(){
					
					if( !self.get_field_meta( $(this), 'ID' ) ) {
						
						// Case: field not saved to DB
						warning = true;
						return false;
						
					} else if( self.get_field_meta( $(this), 'save' ) == 'settings' ) {
						
						// Case: field's settings have changed
						warning = true;
						
					}
					
				});
				
			}
			
			
			// bail early if can't move
			if( warning ) {
				
				alert( acf._e('move_field_warning') );
				return;
				
			}
			
			
			// open popup
			acf.open_popup({
				title	: acf._e('move_field'),
				loading	: true,
				height	: 145
			});
			
			
			// get HTML
			$.ajax({
				url: acf.get('ajaxurl'),
				data: ajax_data,
				type: 'post',
				dataType: 'html',
				success: function(html){
				
					self.move_field_confirm( $field, html );
					
				}
			});
			
		},
		
		
		/*
		*  move_field_confirm
		*
		*  This function will move a field to another field group
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$post_id (int)
		*  @return	$post_id (int)
		*/
		
		move_field_confirm: function( $field, html ){
			
			// reference
			var self = this;
			
			
			// update popup
			acf.update_popup({
				content : html
			});
			
			
			// AJAX data
			var ajax_data = acf.prepare_for_ajax({
				'action'			: 'acf/field_group/move_field',
				'field_id'			: this.get_field_meta($field, 'ID'),
				'field_group_id'	: 0
			});
			
			
			// submit form
			$('#acf-move-field-form').on('submit', function(){

				ajax_data.field_group_id = $(this).find('select').val();
				
				
				// get HTML
				$.ajax({
					url: acf.get('ajaxurl'),
					data: ajax_data,
					type: 'post',
					dataType: 'html',
					success: function(html){
					
						acf.update_popup({
							content : html
						});
						
						
						// remove the field without actually deleting it
						self.remove_field( $field );
						
					}
				});
				
				return false;
				
			});
			
		},
		
		
		/*
		*  delete_field
		*
		*  This function will delete a field
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$el
		*  @param	animation
		*  @return	n/a
		*/
		
		delete_field: function( $el, animation ){
			
			// defaults
			animation = animation || true;
			
			
			// vars
			var id = this.get_field_meta($el, 'ID');
			
			
			// add to remove list
			if( id ) {
				
				var $input = $('#_acf_delete_fields');
				$input.val( $input.val() + '|' + id );	
				
			}
			
			
			// action for 3rd party customization
			acf.do_action('delete_field', $el);
			
			
			// bail early if no animation
			if( animation ) {
				
				this.remove_field( $el );
				
			}
						
		},
		
		
		/*
		*  remove_field
		*
		*  This function will visualy remove a field
		*
		*  @type	function
		*  @date	24/10/2014
		*  @since	5.0.9
		*
		*  @param	$el
		*  @param	animation
		*  @return	n/a
		*/
		
		remove_field: function( $el ){
			
			// reference
			var self = this;
			
			
			// vars
			var $field_list	= $el.closest('.acf-field-list');
			
			
			// set layout
			$el.css({
				height		: $el.height(),
				width		: $el.width(),
				position	: 'absolute'
			});
			
			
			// wrap field
			$el.wrap( '<div class="temp-field-wrap" style="height:' + $el.height() + 'px"></div>' );
			
			
			// fade $el
			$el.animate({ opacity : 0 }, 250);
			
			
			// close field
			var end_height = 0,
				$show = false;
			
			
			if( !$field_list.children('.acf-field-object').length ) {
			
				$show = $field_list.children('.no-fields-message');
				end_height = $show.outerHeight();
				
			}
			
			$el.parent('.temp-field-wrap').animate({ height : end_height }, 250, function(){
				
				// show another element
				if( $show ) {
				
					$show.show();
					
				}
				
				
				// action for 3rd party customization 
				acf.do_action('remove', $(this));
				
				
				// remove $el
				$(this).remove();
				
				
				// render fields becuase they have changed
				self.render_fields();
				
			});
						
		},
		
		
		/*
		*  change_field_type
		*
		*  This function will update the field's settings based on the new field type
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$select
		*  @return	n/a
		*/
		
		change_field_type: function( $select ){
			
			// vars
			var $tbody		= $select.closest('tbody'),
				$el			= $tbody.closest('.acf-field-object'),
				$parent		= $el.parent().closest('.acf-field-object'),
				
				key			= $el.attr('data-key'),
				old_type	= $el.attr('data-type'),
				new_type	= $select.val();
				
			
			// update class
			$el.removeClass( 'acf-field-object-' + acf.str_replace('_', '-', old_type) );
			$el.addClass( 'acf-field-object-' + acf.str_replace('_', '-', new_type) );
			
			
			// update atts
			$el.attr('data-type', new_type);
			$el.data('type', new_type);
			
			
			// abort XHR if this field is already loading AJAX data
			if( $el.data('xhr') ) {
			
				$el.data('xhr').abort();
				
			}
			
			
			// get settings
			var $settings = $tbody.children('.acf-field[data-setting="' + old_type + '"]'),
				html = '';
			
			
			// populate settings html
			$settings.each(function(){
				
				html += $(this).outerHTML();
				
			});
			
			
			// remove settings
			$settings.remove();
			
			
			// save field settings html
			acf.update( key + '_settings_' + old_type, html );
			
			
			// render field
			this.render_field( $el );
			
			
			// show field options if they already exist
			html = acf.get( key + '_settings_' + new_type );
			
			if( html ) {
				
				// append settings
				$tbody.children('.acf-field[data-name="conditional_logic"]').before( html );
				
				
				// remove field settings html
				acf.update( key + '_settings_' + new_type, '' );
				
				
				// trigger event
				acf.do_action('change_field_type', $el);
				
				
				// return
				return;
			}
			
			
			// add loading
			var $tr = $('<tr class="acf-field"><td class="acf-label"></td><td class="acf-input"><div class="acf-loading"></div></td></tr>');
			
			
			// add $tr
			$tbody.children('.acf-field[data-name="conditional_logic"]').before( $tr );
			
			
			var ajax_data = {
				action		: 'acf/field_group/render_field_settings',
				nonce		: acf.o.nonce,
				parent		: acf.o.post_id,
				field_group	: acf.o.post_id,
				prefix		: $select.attr('name').replace('[type]', ''),
				type		: new_type
			};
			
			
			// parent
			if( $parent.exists() ) {
				
				ajax_data.parent = this.get_field_meta( $parent, 'ID' );
				
			}
			
			
			// ajax
			var xhr = $.ajax({
				url: acf.o.ajaxurl,
				data: ajax_data,
				type: 'post',
				dataType: 'html',
				success: function( html ){
					
					// bail early if no html
					if( !html ) {
					
						return;
						
					}
					
					
					// vars
					var $new_tr = $(html);
					
					
					// replace
					$tr.after( $new_tr );
					
					
					// trigger event
					acf.do_action('append', $new_tr);
					acf.do_action('change_field_type', $el);

					
				},
				complete : function(){
					
					// this function will also be triggered by $el.data('xhr').abort();
					$tr.remove();
					
				}
			});
			
			
			// update el data
			$el.data('xhr', xhr);
			
		},
		
		/*
		*  change_field_label
		*
		*  This function is triggered when changing the field's label
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$el
		*  @return	n/a
		*/
		
		change_field_label: function( $el ) {
			
			// vars
			var $label = $el.find('.field-label:first'),
				$name = $el.find('.field-name:first'),
				type = $el.attr('data-type');
				
			
			// render name
			if( $name.val() == '' ) {
				
				// vars
				var s = $label.val();
				
				
				// sanitize
				s = acf.str_sanitize(s);
				
				
				// update name
				$name.val( s ).trigger('change');
				
			}
			
			
			// render field
			this.render_field( $el );
			
			
			// action for 3rd party customization
			acf.do_action('change_field_label', $el);
			
		},
		
		/*
		*  change_field_name
		*
		*  This function is triggered when changing the field's name
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$el
		*  @return	n/a
		*/
		
		change_field_name: function( $el ) {
			
			// vars
			var $name = $el.find('.field-name:first');
			
			if( $name.val().substr(0, 6) === 'field_' ) {
				
				alert( acf._e('field_name_start') );
				
				setTimeout(function(){
					
					$name.focus();
					
				}, 1);
				
			}
			
			
			// action for 3rd party customization
			acf.do_action('change_field_name', $el);
			
		}
		
	});
	
	
	/*
	*  field
	*
	*  This model will handle field events
	*
	*  @type	function
	*  @date	19/08/2015
	*  @since	5.2.3
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	acf.field_group.field = acf.model.extend({
		
		events: {
			'click .edit-field':		'edit',
			'click .duplicate-field':	'duplicate',
			'click .move-field':		'move',
			'click .delete-field':		'delete',
			'click .add-field':			'add',
			
			'change .field-type':		'change_type',
			'blur .field-label':		'change_label',
			'blur .field-name':			'change_name',
			
			'keyup .field-label':				'render',
			'keyup .field-name':				'render',
			'change .field-required':			'render',
			
			'change .acf-field-object input':		'save',
			'change .acf-field-object textarea':	'save',
			'change .acf-field-object select':		'save'
		},
		
		event: function( e ){
			
			// append $field
			e.$field = e.$el.closest('.acf-field-object');
			
			
			// return
			return e;
			
		},
		
		
		/*
		*  events
		*
		*  description
		*
		*  @type	function
		*  @date	19/08/2015
		*  @since	5.2.3
		*
		*  @param	$post_id (int)
		*  @return	$post_id (int)
		*/
		
		edit: function( e ){
			
			acf.field_group.edit_field( e.$field );
				
		},
		
		duplicate: function( e ){
			
			acf.field_group.duplicate_field( e.$field );
				
		},
		
		move: function( e ){
			
			acf.field_group.move_field( e.$field );
				
		},
		
		delete: function( e ){
			
			acf.field_group.delete_field( e.$field );
				
		},
		
		add: function( e ){
			
			var $list = e.$el.closest('.acf-field-list-wrap').children('.acf-field-list');
			
			acf.field_group.add_field( $list );
				
		},
		
		change_type: function( e ){
			
			acf.field_group.change_field_type( e.$el );
			
		},
		
		change_label: function( e ){
			
			acf.field_group.change_field_label( e.$field );
			
		},
		
		change_name: function( e ){
			
			acf.field_group.change_field_name( e.$field );
			
		},
		
		render: function( e ){
			
			acf.field_group.render_field( e.$field );
				
		},
		
		save: function( e ){
			
			acf.field_group.save_field( e.$field );
				
		}
		
	});
	
	
	/*
	*  conditions
	*
	*  This model will handle conditional logic events
	*
	*  @type	function
	*  @date	19/08/2015
	*  @since	5.2.3
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	acf.field_group.conditional_logic = acf.model.extend({
		
		actions: {
			'open_field':			'render_field',
			'change_field_label':	'render_fields',
			'change_field_type':	'render_fields'
		},
		
		events: {
			'click .add-conditional-rule':			'add_rule',
			'click .add-conditional-group':			'add_group',
			'click .remove-conditional-rule':		'remove_rule',
			'change .conditional-toggle':			'change_toggle',
			'change .conditional-rule-param':		'change_param'
		},
		
		
		/*
		*  render_fields
		*
		*  description
		*
		*  @type	function
		*  @date	19/08/2015
		*  @since	5.2.3
		*
		*  @param	$post_id (int)
		*  @return	$post_id (int)
		*/
		
		render_fields: function(){
			
			var self = this;
			
			$('.acf-field-object.open').each(function(){
					
				self.render_field( $(this) );
				
			});	
			
		},
		
		
		/*
		*  render_field
		*
		*  This function will render the conditional logic fields for a given field
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$field
		*  @return	n/a
		*/
		
		render_field: function( $field ){
			
			// reference
			var self = this;
			
			
			// vars
			var key = $field.attr('data-key');
			var $lists = $field.parents('.acf-field-list');
			var $tr = $field.find('.acf-field-setting-conditional_logic:last');
				
			
			// choices
			var choices	= [];
			
			
			// loop over ancestor lists
			$.each( $lists, function( i ){
				
				// vars
				var group = (i == 0) ? acf._e('sibling_fields') : acf._e('parent_fields');
				
				
				// loop over fields
				$(this).children('.acf-field-object').each(function(){
					
					// vars
					var $this_field	= $(this),
						this_key	= $this_field.attr('data-key'),
						this_type	= $this_field.attr('data-type'),
						this_label	= $this_field.find('.field-label:first').val();
					
					
					// validate
					if( $.inArray(this_type, ['select', 'checkbox', 'true_false', 'radio', 'button_group']) === -1 ) {
						
						return;
						
					} else if( this_key == key ) {
						
						return;
						
					}
										
					
					// add this field to available triggers
					choices.push({
						value:	this_key,
						label:	this_label,
						group:	group
					});
					
				});
				
			});
				
			
			// empty?
			if( !choices.length ) {
				
				choices.push({
					value: '',
					label: acf._e('no_fields')
				});
				
			}
			
			
			// create select fields
			$tr.find('.rule').each(function(){
				
				self.render_rule( $(this), choices );
				
			});
			
		},
		
		
		/*
		*  populate_triggers
		*
		*  description
		*
		*  @type	function
		*  @date	22/08/2015
		*  @since	5.2.3
		*
		*  @param	$post_id (int)
		*  @return	$post_id (int)
		*/
		
		render_rule: function( $tr, triggers ) {
			
			// vars
			var $trigger	= $tr.find('.conditional-rule-param'),
				$value		= $tr.find('.conditional-rule-value');
				
				
			// populate triggers
			if( triggers ) {
				
				acf.render_select( $trigger, triggers );
				
			}
			
			
			// vars
			var $field		= $('.acf-field-object[data-key="' + $trigger.val() + '"]'),
				field_type	= $field.attr('data-type'),
				choices		= [];
			
			
			// populate choices
			if( field_type == "true_false" ) {
				
				choices.push({
					'value': 1,
					'label': acf._e('checked')
				});
			
			// select				
			} else if( field_type == "select" || field_type == "checkbox" || field_type == "radio" || field_type == "button_group" ) {
				
				// vars
				var lines = $field.find('.acf-field[data-name="choices"] textarea').val().split("\n");	
				
				$.each(lines, function(i, line){
					
					// explode
					line = line.split(':');
					
					
					// default label to value
					line[1] = line[1] || line[0];
					
					
					// append					
					choices.push({
						'value': $.trim( line[0] ),
						'label': $.trim( line[1] )
					});
					
				});
				
				
				// allow null
				var $allow_null = $field.find('.acf-field[data-name="allow_null"]');
				
				if( $allow_null.exists() ) {
					
					if( $allow_null.find('input:checked').val() == '1' ) {
						
						choices.unshift({
							'value': '',
							'label': acf._e('null')
						});
						
					}
					
				}
				
			}
			
			
			// update select
			acf.render_select( $value, choices );
			
		},
		
		
		/*
		*  change_toggle
		*
		*  This function is triggered by changing the 'Conditional Logic' radio button
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$input
		*  @return	n/a
		*/
		
		change_toggle: function( e ){
			
			// vars
			var $input = e.$el,
				checked = e.$el.prop('checked'),
				$td = $input.closest('.acf-input');
				
			
			if( checked ) {
				
				$td.find('.rule-groups').show();
				$td.find('.rule-groups').find('[name]').prop('disabled', false);
			
			} else {
				
				$td.find('.rule-groups').hide();
				$td.find('.rule-groups').find('[name]').prop('disabled', true);
			
			}
			
		},
		
		
		/*
		*  change_trigger
		*
		*  This function is triggered by changing a 'Conditional Logic' trigger
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$select
		*  @return	n/a
		*/
		
		change_param: function( e ){
			
			// vars
			var $rule = e.$el.closest('.rule');
			
			
			// render		
			this.render_rule( $rule );
			
		},
		
		
		/*
		*  add_rule
		*
		*  This function will add a new rule below the specified $tr
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$tr
		*  @return	n/a
		*/
		
		add_rule: function( e ){
			
			// vars
			var $tr = e.$el.closest('tr');
			
			
			// duplicate
			$tr2 = acf.duplicate( $tr );
			
			
			// save field
			$tr2.find('select:first').trigger('change');
						
		},
		
		
		/*
		*  remove_rule
		*
		*  This function will remove the $tr and potentially the group
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$tr
		*  @return	n/a
		*/
		
		remove_rule: function( e ){
			
			// vars
			var $tr = e.$el.closest('tr');

			
			// save field
			$tr.find('select:first').trigger('change');
			
			
			if( $tr.siblings('tr').length == 0 ) {
				
				// remove group
				$tr.closest('.rule-group').remove();
				
			}
			
			
			// remove tr
			$tr.remove();
				
			
		},
		
		
		/*
		*  add_group
		*
		*  This function will add a new rule group to the given $groups container
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$tr
		*  @return	n/a
		*/
		
		add_group: function( e ){
			
			// vars
			var $groups = e.$el.closest('.rule-groups'),
				$group = $groups.find('.rule-group:last');
			
			
			// duplicate
			$group2 = acf.duplicate( $group );
			
			
			// update h4
			$group2.find('h4').text( acf._e('or') );
			
			
			// remove all tr's except the first one
			$group2.find('tr:not(:first)').remove();
			
			
			// save field
			$group2.find('select:first').trigger('change');
			
		}
		
	});
	
	
	/*
	*  locations
	*
	*  This model will handle location rule events
	*
	*  @type	function
	*  @date	19/08/2015
	*  @since	5.2.3
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	acf.field_group.locations = acf.model.extend({
		
		events: {
			'click .add-location-rule':			'add_rule',
			'click .add-location-group':		'add_group',
			'click .remove-location-rule':		'remove_rule',
			'change .refresh-location-rule':	'change_rule'
		},
		
		
		/*
		*  add_rule
		*
		*  This function will add a new rule below the specified $tr
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$tr
		*  @return	n/a
		*/
		
		add_rule: function( e ){
			
			// vars
			var $tr = e.$el.closest('tr');
			
			
			// duplicate
			$tr2 = acf.duplicate( $tr );
			
			
			// action
			//acf.do_action('add_location_rule', $tr2);
			
		},
		
		
		/*
		*  remove_rule
		*
		*  This function will remove the $tr and potentially the group
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$tr
		*  @return	n/a
		*/
		
		remove_rule: function( e ){
			
			// vars
			var $tr = e.$el.closest('tr');
			
			
			// action
			//acf.do_action('remove_location_rule', $tr);
			
			
			// remove
			if( $tr.siblings('tr').length == 0 ) {
				
				// remove group
				$tr.closest('.rule-group').remove();
				
			} else {
				
				// remove tr
				$tr.remove();
			
			}
			
		},
		
		
		/*
		*  add_group
		*
		*  This function will add a new rule group to the given $groups container
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$tr
		*  @return	n/a
		*/
		
		add_group: function( e ){
			
			// vars
			var $groups = e.$el.closest('.rule-groups'),
				$group = $groups.find('.rule-group:last');
			
			
			// duplicate
			$group2 = acf.duplicate( $group );
			
			
			// update h4
			$group2.find('h4').text( acf._e('or') );
			
			
			// remove all tr's except the first one
			$group2.find('tr:not(:first)').remove();
			
			
			// vars
			//var $tr = $group2.find('tr');
			
			
			// action
			//acf.do_action('add_location_rule', $tr);
			
		},
		
		
		/*
		*  change_rule
		*
		*  This function is triggered when changing a location rule trigger
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$select
		*  @return	n/a
		*/
		
		change_rule: function( e ){
				
			// vars
			var $rule = e.$el.closest('tr');
			var $group = $rule.closest('.rule-group');
			var prefix = $rule.find('td.param select').attr('name').replace('[param]', '');
			
			
			// ajax data
			var ajaxdata = {
				action: 'acf/field_group/render_location_rule',
				rule: 	acf.serialize( $rule, prefix ),
			};
			
			
			// append to data
			ajaxdata.rule.id = $rule.attr('data-id');
			ajaxdata.rule.group = $group.attr('data-id');
			
			
			// ajax
			$.ajax({
				url: acf.get('ajaxurl'),
				data: acf.prepare_for_ajax(ajaxdata),
				type: 'post',
				dataType: 'html',
				success: function( html ){
					
					// bail early if no html
					if( !html ) return;
					
					
					// update
					$rule.replaceWith( html );
					
					
					// action
					//acf.do_action('change_location_rule', $rule);
	
				}
			});
			
		}
	});
	
	
	/*
	*  field
	*
	*  This model sets up many of the field's interactions
	*
	*  @type	function
	*  @date	21/02/2014
	*  @since	3.5.1
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	acf.field_group.field_object = acf.model.extend({
		
		// vars
		type:		'',
		o:			{},
		$field:		null,
		$settings:	null,
		
		tag: function( tag ) {
			
			// vars
			var type = this.type;
			
			
			// explode, add 'field' and implode
			// - open 			=> open_field
			// - change_type	=> change_field_type
			var tags = tag.split('_');
			tags.splice(1, 0, 'field');
			tag = tags.join('_');
			
			
			// add type
			if( type ) {
				tag += '/type=' + type;
			}
			
			
			// return
			return tag;
						
		},
		
		selector: function(){
			
			// vars
			var selector = '.acf-field-object';
			var type = this.type;
			

			// add type
			if( type ) {
				selector += '-' + type;
				selector = acf.str_replace('_', '-', selector);
			}
			
			
			// return
			return selector;
			
		},
		
		_add_action: function( name, callback ) {
			
			// vars
			var model = this;
			
			
			// add action
			acf.add_action( this.tag(name), function( $field ){
				
				// focus
				model.set('$field', $field);
				
				
				// callback
				model[ callback ].apply(model, arguments);
				
			});
			
		},
		
		_add_filter: function( name, callback ) {
			
			// vars
			var model = this;
			
			
			// add action
			acf.add_filter( this.tag(name), function( $field ){
				
				// focus
				model.set('$field', $field);
				
				
				// callback
				model[ callback ].apply(model, arguments);
				
			});
			
		},
		
		_add_event: function( name, callback ) {
			
			// vars
			var model = this;
			var event = name.substr(0,name.indexOf(' '));
			var selector = name.substr(name.indexOf(' ')+1);
			var context = this.selector();
			
			
			// add event
			$(document).on(event, context + ' ' + selector, function( e ){
				
				// append $el to event object
				e.$el = $(this);
				e.$field = e.$el.closest('.acf-field-object');
				
				
				// focus
				model.set('$field', e.$field);
				
				
				// callback
				model[ callback ].apply(model, [e]);
				
			});
			
		},
		
		_set_$field: function(){
			
			// vars
			this.o = this.$field.data();
			
			
			// els
			this.$settings = this.$field.find('> .settings > table > tbody');
			
			
			// focus
			this.focus();
			
		},
		
		focus: function(){
			
			// do nothing
			
		},
		
		setting: function( name ) {
			
			return this.$settings.find('> .acf-field-setting-' + name);
			
		}
		
	});
	
	
	/*
	*  field
	*
	*  This model fires actions and filters for registered fields
	*
	*  @type	function
	*  @date	21/02/2014
	*  @since	3.5.1
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	acf.field_group.field_objects = acf.model.extend({
		
		actions: {
			'save_field'				: '_save_field',
			'open_field'				: '_open_field',
			'close_field'				: '_close_field',
			'wipe_field'				: '_wipe_field',
			'add_field'					: '_add_field',
			'duplicate_field'			: '_duplicate_field',
			'delete_field'				: '_delete_field',
			'change_field_type'			: '_change_field_type',
			'change_field_label'		: '_change_field_label',
			'change_field_name'			: '_change_field_name',
			'render_field_settings'		: '_render_field_settings'
		},
		
		_save_field: function( $el ){
			
			acf.do_action('save_field/type=' + $el.data('type'), $el);
			
		},
		
		_open_field: function( $el ){
			
			acf.do_action('open_field/type=' + $el.data('type'), $el);
			acf.do_action('render_field_settings', $el);
			
		},
		
		_close_field: function( $el ){
			
			acf.do_action('close_field/type=' + $el.data('type'), $el);
			
		},
		
		_wipe_field: function( $el ){
			
			acf.do_action('wipe_field/type=' + $el.data('type'), $el);
			
		},
		
		_add_field: function( $el ){
			
			acf.do_action('add_field/type=' + $el.data('type'), $el);
			
		},
		
		_duplicate_field: function( $el ){
			
			acf.do_action('duplicate_field/type=' + $el.data('type'), $el);
			
		},
		
		_delete_field: function( $el ){
			
			acf.do_action('delete_field/type=' + $el.data('type'), $el);
			
		},
		
		_change_field_type: function( $el ){
			
			acf.do_action('change_field_type/type=' + $el.data('type'), $el);
			acf.do_action('render_field_settings', $el);
		},
		
		_change_field_label: function( $el ){
			
			acf.do_action('change_field_label/type=' + $el.data('type'), $el);
			
		},
		
		_change_field_name: function( $el ){
			
			acf.do_action('change_field_name/type=' + $el.data('type'), $el);
			
		},
		
		_render_field_settings: function( $el ){
			
			acf.do_action('render_field_settings/type=' + $el.data('type'), $el);
			
		}
		
	});
	
	
	
	/*
	*  Append
	*
	*  This model handles all logic to append fields together
	*
	*  @type	function
	*  @date	12/02/2015
	*  @since	5.5.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	acf.field_group.append = acf.model.extend({
		
		actions: {
			'render_field_settings' : '_render_field_settings'
		},
		
		render: function( $el ){
			
			// vars
			var append = $el.data('append');
			
			
			// find sibling
			$sibling = $el.siblings('[data-name="' + append + '"]');
			
			
			// bail early if no sibling
			if( !$sibling.exists() ) return;
			
			
			// vars
			var $wrap = $sibling.children('.acf-input'),
				$ul = $wrap.children('.acf-hl');
			
			
			// append ul if doesn't exist
			if( !$ul.exists() ) {
				
				$wrap.wrapInner('<ul class="acf-hl"><li></li></ul>');
				
				$ul = $wrap.children('.acf-hl');
				
			}
			
			
			// create $li
			var $li = $('<li></li>').append( $el.children('.acf-input').children() );
			
			
			// append $li
			$ul.append( $li );
			
			
			// update cols
			$ul.attr('data-cols', $ul.children().length );
			
			
			// remove
			$el.remove();
			
		},
		
		_render_field_settings: function( $el ){
			
			// reference
			var self = this;
			
			
			// loop
			$el.find('.acf-field[data-append]').each(function(){
				
				self.render( $(this) );
					
			});
			
		}
	
	});
	
	
	
	/*
	*  Select
	*
	*  This field type requires some extra logic for its settings
	*
	*  @type	function
	*  @date	24/10/13
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	var acf_settings_select = acf.field_group.field_object.extend({
		
		type: 'select',
		
		actions: {
			'render_settings': 'render'
		},
		
		events: {
			'change .acf-field-setting-ui input': 'render'
		},
		
		render: function( $el ){
			
			// ui checked
			if( this.setting('ui input[type="checkbox"]').prop('checked') ) {
			
				this.setting('ajax').show();
			
			// ui not checked
			} else {
			
				this.setting('ajax').hide();
				this.setting('ajax input[type="checkbox"]').prop('checked', false).trigger('change');
				
			}
			
		}		
		
	});
		
	
	/*
	*  Radio
	*
	*  This field type requires some extra logic for its settings
	*
	*  @type	function
	*  @date	24/10/13
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	var acf_settings_radio = acf.field_group.field_object.extend({
		
		type: 'radio',
		
		actions: {
			'render_settings': 'render'
		},
		
		events: {
			'change .acf-field-setting-other_choice input': 'render'
		},
		
		render: function( $el ){
			
			// other_choice checked
			if( this.setting('other_choice input[type="checkbox"]').prop('checked') ) {
			
				this.setting('save_other_choice').show();
			
			// other_choice not checked
			} else {
			
				this.setting('save_other_choice').hide();
				this.setting('save_other_choice input[type="checkbox"]').prop('checked', false).trigger('change');
				
			}
			
		}		
		
	});
	
	
	/*
	*  Radio
	*
	*  This field type requires some extra logic for its settings
	*
	*  @type	function
	*  @date	24/10/13
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	var acf_settings_checkbox = acf.field_group.field_object.extend({
		
		type: 'checkbox',
		
		actions: {
			'render_settings': 'render'
		},
		
		events: {
			'change .acf-field-setting-allow_custom input': 'render'
		},
		
		render: function( $el ){
			
			// other_choice checked
			if( this.setting('allow_custom input[type="checkbox"]').prop('checked') ) {
			
				this.setting('save_custom').show();
			
			// other_choice not checked
			} else {
			
				this.setting('save_custom').hide();
				this.setting('save_custom input[type="checkbox"]').prop('checked', false).trigger('change');
				
			}
			
		}		
		
	});
	
	
	/*
	*  True false
	*
	*  This field type requires some extra logic for its settings
	*
	*  @type	function
	*  @date	24/10/13
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	var acf_settings_true_false = acf.field_group.field_object.extend({
		
		type: 'true_false',
		
		actions: {
			'render_settings': 'render'
		},
		
		events: {
			'change .acf-field-setting-ui input': 'render'
		},
		
		render: function( $el ){
			
			// ui checked
			if( this.setting('ui input[type="checkbox"]').prop('checked') ) {
			
				this.setting('ui_on_text').show();
				this.setting('ui_off_text').show();
			
			// ui not checked
			} else {
			
				this.setting('ui_on_text').hide();
				this.setting('ui_off_text').hide();
				
			}
						
		}
		
	});
		
	
	/*
	*  Date Picker
	*
	*  This field type requires some extra logic for its settings
	*
	*  @type	function
	*  @date	24/10/13
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	var acf_settings_date_picker = acf.field_group.field_object.extend({
		
		type: 'date_picker',
		
		actions: {
			'render_settings': 'render'
		},
		
		events: {
			'change .acf-field-setting-display_format input':	'render',
			'change .acf-field-setting-return_format input':	'render'
		},
		
		render: function( $el ){
			
			this.render_list( this.setting('display_format') );
			this.render_list( this.setting('return_format') );
			
		},
		
		render_list: function( $setting ){
			
			// vars
			var $ul = $setting.find('ul'),
				$radio = $ul.find('input[type="radio"]:checked'),
				$other = $ul.find('input[type="text"]');
			
			
			// display val
			if( $radio.val() != 'other' ) {
			
				$other.val( $radio.val() );
				
			}
			
		}		
		
	});
	
	
	/*
	*  Date Time Picker
	*
	*  This field type requires some extra logic for its settings
	*
	*  @type	function
	*  @date	24/10/13
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	var acf_settings_date_time_picker = acf_settings_date_picker.extend({
		
		type: 'date_time_picker'		
		
	});
	
	
	/*
	*  Time Picker
	*
	*  This field type requires some extra logic for its settings
	*
	*  @type	function
	*  @date	24/10/13
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	var acf_settings_date_time_picker = acf_settings_date_picker.extend({
		
		type: 'time_picker'		
		
	});
	
	
	/*
	*  tab
	*
	*  description
	*
	*  @type	function
	*  @date	12/02/2015
	*  @since	5.1.5
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	var acf_settings_tab = acf.field_group.field_object.extend({
		
		type: 'tab',
		
		actions: {
			'render_settings': 'render'
		},
		
		render: function( $el ){
			
			// clear name
			this.setting('name input').val('').trigger('change');
			
			
			// clear required
			this.setting('required input[type="checkbox"]').prop('checked', false).trigger('change');
			
		}
		
	});
	
	
	/*
	*  message
	*
	*  description
	*
	*  @type	function
	*  @date	12/02/2015
	*  @since	5.1.5
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	var acf_settings_message = acf_settings_tab.extend({
		
		type: 'message'	
		
	});
	
	
	/*
	*  screen
	*
	*  description
	*
	*  @type	function
	*  @date	23/07/2015
	*  @since	5.2.3
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	acf.field_group.screen = acf.model.extend({
		
		actions: {
			'ready': 'ready'
		},
		
		events: {
			'click #acf-field-key-hide': 'toggle'
		},
		
		ready: function(){
			
			// vars
			var $el = $('#adv-settings'),
				$append = $el.find('#acf-append-show-on-screen');
			
			
			// append
			$el.find('.metabox-prefs').append( $append.html() );
			
			
			// move br
			$el.find('.metabox-prefs br').remove();
			
			
			// remove
			$append.remove();
			
			
			// render
			this.render();
			
		},
		
		toggle: function( e ){
			
			// vars
			var val = e.$el.prop('checked') ? 1 : 0;
			
			
			// update user setting
			acf.update_user_setting('show_field_keys', val);
			
			
			// render $fields
			this.render();
			
		},
		
		render: function(){
			
			// vars
			var options = acf.serialize( $('#adv-settings') );
			
			
			// toggle class
			var $fields = acf.field_group.$fields;
			
			
			// show field keys	
			if( options.show_field_keys ) {
			
				$fields.addClass('show-field-keys');
			
			} else {
				
				$fields.removeClass('show-field-keys');
				
			}
			
		}
		
	});
	
	
	/*
	*  sub fields
	*
	*  description
	*
	*  @type	function
	*  @date	31/1/17
	*  @since	5.5.6
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	acf.field_group.sub_fields = acf.model.extend({
		
		actions: {
			'open_field':			'update_field_parent',
			'sortstop':				'update_field_parent',
			'duplicate_field':		'duplicate_field',
			'delete_field':			'delete_field',
			'change_field_type':	'change_field_type'
		},
		
		
    	/*
    	*  fix_conditional_logic
    	*
    	*  This function will update sub field conditional logic rules after duplication
    	*
    	*  @type	function
    	*  @date	10/06/2014
    	*  @since	5.0.0
    	*
    	*  @param	$fields (jquery selection)
    	*  @return	n/a
    	*/
    	
    	fix_conditional_logic : function( $fields ){
	    	
	    	// build refernce
			var ref = {};
			
			$fields.each(function(){
				
				ref[ $(this).attr('data-orig') ] = $(this).attr('data-key');
				
			});
			
			
	    	$fields.find('.conditional-rule-param').each(function(){
		    	
		    	// vars
		    	var key = $(this).val();
		    	
		    	
		    	// bail early if val is not a ref key
		    	if( !(key in ref) ) {
			    	
			    	return;
			    	
		    	}
		    	
		    	
		    	// add option if doesn't yet exist
		    	if( ! $(this).find('option[value="' + ref[key] + '"]').exists() ) {
			    	
			    	$(this).append('<option value="' + ref[key] + '">' + ref[key] + '</option>');
			    	
		    	}
		    	
		    	
		    	// set new val
		    	$(this).val( ref[key] );
		    	
	    	});
	    	
    	},
    	
    	
    	/*
    	*  update_field_parent
    	*
    	*  This function will update field meta such as parent
    	*
    	*  @type	function
    	*  @date	8/04/2014
    	*  @since	5.0.0
    	*
    	*  @param	$el
    	*  @return	n/a
    	*/
    	
    	update_field_parent: function( $el ){
	    	
	    	// bail early if not div.field (flexible content tr)
	    	if( !$el.hasClass('acf-field-object') ) return;
	    	
	    	
	    	// vars
	    	var $parent = $el.parent().closest('.acf-field-object'),
		    	val = acf.get('post_id');
		    
		    
		    // find parent
			if( $parent.exists() ) {
				
				// set as parent ID
				val = acf.field_group.get_field_meta( $parent, 'ID' );
				
				
				// if parent is new, no ID exists
				if( !val ) {
					
					val = acf.field_group.get_field_meta( $parent, 'key' );
					
				}
				
			}
			
			
			// update parent
			acf.field_group.update_field_meta( $el, 'parent', val );
	    	
	    	
	    	// action for 3rd party customization
			acf.do_action('update_field_parent', $el, $parent);
			
    	},
    	
    	
    	/*
    	*  duplicate_field
    	*
    	*  This function is triggered when duplicating a field
    	*
    	*  @type	function
    	*  @date	8/04/2014
    	*  @since	5.0.0
    	*
    	*  @param	$el
    	*  @return	n/a
    	*/
    	
    	duplicate_field: function( $el ) {
	    	
	    	// vars
			var $fields = $el.find('.acf-field-object');
				
			
			// bail early if $fields are empty
			if( !$fields.exists() ) {
				
				return;
				
			}
			
			
			// loop over sub fields
	    	$fields.each(function(){
		    	
		    	// vars
		    	var $parent = $(this).parent().closest('.acf-field-object'),
		    		key = acf.field_group.get_field_meta( $parent, 'key');
		    		
		    	
		    	// wipe field
		    	acf.field_group.wipe_field( $(this) );
		    	
		    	
		    	// update parent
		    	acf.field_group.update_field_meta( $(this), 'parent', key );
		    	
		    	
		    	// save field
		    	acf.field_group.save_field( $(this) );
		    	
		    	
	    	});
	    	
	    	
	    	// fix conditional logic rules
	    	this.fix_conditional_logic( $fields );
	    	
    	},
    	
    	
    	/*
    	*  delete_field
    	*
    	*  This function is triggered when deleting a field
    	*
    	*  @type	function
    	*  @date	8/04/2014
    	*  @since	5.0.0
    	*
    	*  @param	$el
    	*  @return	n/a
    	*/
    	
    	delete_field : function( $el ){
	    	
	    	$el.find('.acf-field-object').each(function(){
		    	
		    	acf.field_group.delete_field( $(this), false );
		    	
	    	});
	    	
    	},
    	
    	
    	/*
    	*  change_field_type
    	*
    	*  This function is triggered when changing a field type
    	*
    	*  @type	function
    	*  @date	7/06/2014
    	*  @since	5.0.0
    	*
    	*  @param	$post_id (int)
    	*  @return	$post_id (int)
    	*/
		
		change_field_type : function( $el ) {
			
			$el.find('.acf-field-object').each(function(){
		    	
		    	acf.field_group.delete_field( $(this), false );
		    	
	    	});
			
		}
		
	});
	
})(jQuery);

// @codekit-prepend "../js/field-group.js";

