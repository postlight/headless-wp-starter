(function($){
		
	acf.fields.repeater = acf.field.extend({
		
		type: 'repeater',
		$el: null,
		$input: null,
		$table: null,
		$tbody: null,
		$clone: null,
		
		actions: {
			'ready':	'initialize',
			'append':	'initialize',
			'show':		'show'
		},
		
		events: {
			'click a[data-event="add-row"]': 		'_add',
			'click a[data-event="remove-row"]': 	'_remove',
			'click a[data-event="collapse-row"]': 	'_collapse',
			'mouseenter td.order': 					'_mouseenter'
		},
		
		focus: function(){
			
			// vars
			this.$el = this.$field.find('.acf-repeater:first');
			this.$input = this.$field.find('input:first');
			this.$table = this.$field.find('table:first');
			this.$tbody = this.$table.children('tbody');
			this.$clone = this.$tbody.children('tr.acf-clone');
			
			
			// get options
			this.o = acf.get_data( this.$el );
			
			
			// min / max
			this.o.min = this.o.min || 0;
			this.o.max = this.o.max || 0;
			
		},
		
		initialize: function(){
			
			// disable clone
			acf.disable_form( this.$clone, 'repeater' );
						
			
			// render
			this.render();
			
		},
		
		show: function(){
			
			this.$tbody.find('.acf-field:visible').each(function(){
				
				acf.do_action('show_field', $(this));
				
			});
			
		},
		
		count: function(){
			
			return this.$tbody.children().length - 1;
			
		},
		
		render: function(){
			
			// update order numbers
			this.$tbody.children().each(function(i){
				
				$(this).find('> td.order > span').html( i+1 );
				
			});
			
			
			// empty?
			if( this.count() == 0 ) {
			
				this.$el.addClass('-empty');
				
			} else {
			
				this.$el.removeClass('-empty');
				
			}
			
			
			// row limit reached
			if( this.o.max > 0 && this.count() >= this.o.max ) {
				
				this.$el.find('> .acf-actions .button').addClass('disabled');
				
			} else {
				
				this.$el.find('> .acf-actions .button').removeClass('disabled');
				
			}
			
		},
		
		add: function( $tr ){
			
			// defaults
			$tr = $tr || this.$clone;
			
			
			// validate
			if( this.o.max > 0 && this.count() >= this.o.max ) {
			
				alert( acf._e('repeater','max').replace('{max}', this.o.max) );
				return false;
				
			}
			
			
			// reference
			var $field = this.$field;
				
				
			// duplicate
			$el = acf.duplicate( this.$clone );
			
						
			// remove clone class
			$el.removeClass('acf-clone');
			
			
			// enable 
			acf.enable_form( $el, 'repeater' );
			
			
			// move row
			$tr.before( $el );
			
			
			// focus (may have added sub repeater)
			this.doFocus($field);
			
			
			// update order
			this.render();
			
			
			// validation
			acf.validation.remove_error( this.$field );
			
			
			// sync collapsed order
			this.sync();
			
			
			// return
			return $el;
			
		},
		
		remove: function( $tr ){
			
			// reference
			var self = this;
				
			
			// validate
			if( this.count() <= this.o.min ) {
			
				alert( acf._e('repeater','min').replace('{min}', this.o.min) );
				return false;
			}
			
			
			// action for 3rd party customization
			acf.do_action('remove', $tr);
			
			
			// animate out tr
			acf.remove_tr( $tr, function(){
				
				// trigger change to allow attachment save
				self.$input.trigger('change');
			
			
				// render
				self.render();
				
				
				// sync collapsed order
				self.sync();
				
				
				// refersh field (hide/show columns)
				acf.do_action('refresh', self.$field);
				
			});
			
		},
		
		sync: function(){
			
			// vars
			var name = 'collapsed_' + this.$field.data('key'),
				collapsed = [];
			
			
			// populate collapsed value
			this.$tbody.children().each(function( i ){
				
				if( $(this).hasClass('-collapsed') ) {
				
					collapsed.push( i );
					
				}
				
			});
			
			
			// update
			acf.update_user_setting( name, collapsed.join(',') );	
			
		},
		
		
		/*
		*  events
		*
		*  these functions are fired for this fields events
		*
		*  @type	function
		*  @date	17/09/2015
		*  @since	5.2.3
		*
		*  @param	e
		*  @return	n/a
		*/
		
		_mouseenter: function( e ){ //console.log('_mouseenter');
			
			// bail early if already sortable
			if( this.$tbody.hasClass('ui-sortable') ) return;
			
			
			// bail early if max 1 row
			if( this.o.max == 1 ) return;
			
			
			// reference
			var self = this;
			
			
			// add sortable
			this.$tbody.sortable({
				items: '> tr',
				handle: '> td.order',
				forceHelperSize: true,
				forcePlaceholderSize: true,
				scroll: true,
				start: function(event, ui) {
					
					acf.do_action('sortstart', ui.item, ui.placeholder);
					
	   			},
	   			stop: function(event, ui) {
					
					// render
					self.render();
					
					acf.do_action('sortstop', ui.item, ui.placeholder);
					
	   			},
	   			update: function(event, ui) {
		   			
		   			// trigger change
					self.$input.trigger('change');
					
		   		}
	   			
			});
			
		},
		
		_add: function( e ){ //console.log('_add');
			
			// vars
			$row = false;
			
			
			// row add
			if( e.$el.hasClass('acf-icon') ) {
			
				$row = e.$el.closest('.acf-row');
				
			}
			
			
			// add
			this.add( $row );
				
		},
		
		_remove: function( e ){ //console.log('_remove');
			
			this.remove( e.$el.closest('.acf-row') );
			
		},
		
		_collapse: function( e ){ //console.log('_collapse');
			
			// vars
			var $tr = e.$el.closest('.acf-row');
			
			
			// reference
			var $field = this.$field;
			
			
			// open row
			if( $tr.hasClass('-collapsed') ) {
				
				$tr.removeClass('-collapsed');
				
				acf.do_action('show', $tr, 'collapse');
				
			} else {
				
				$tr.addClass('-collapsed');
				
				acf.do_action('hide', $tr, 'collapse');
				
			}
			
			
			// sync
			this.set('$field', $field).sync();
			
			
			// refersh field (hide/show columns)
			acf.do_action('refresh', this.$field);
						
		}
		
	});	
	
})(jQuery);

(function($){
		
	acf.fields.flexible_content = acf.field.extend({
		
		type: 'flexible_content',
		$el: null,
		$input: null,
		$values: null,
		$clones: null,
		
		actions: {
			'ready':	'initialize',
			'append':	'initialize',
			'show':		'show'
		},
		
		events: {
			'click [data-event="add-layout"]': 			'_open',
			'click [data-event="remove-layout"]': 		'_remove',
			'click [data-event="collapse-layout"]':		'_collapse',
			'click .acf-fc-layout-handle':				'_collapse',
			'click .acf-fc-popup a':					'_add',
			'blur .acf-fc-popup .focus':				'_close',
			'mouseenter .acf-fc-layout-handle': 		'_mouseenter'
		},
		
		focus: function(){
			
			// vars
			this.$el = this.$field.find('.acf-flexible-content:first');
			this.$input = this.$el.siblings('input');
			this.$values = this.$el.children('.values');
			this.$clones = this.$el.children('.clones');
			
			
			// get options
			this.o = acf.get_data( this.$el );
			
			
			// min / max
			this.o.min = this.o.min || 0;
			this.o.max = this.o.max || 0;
			
		},
		
		count: function(){
			
			return this.$values.children('.layout').length;
			
		},
		
		initialize: function(){
			
			// disable clone
			acf.disable_form( this.$clones, 'flexible_content' );
						
			
			// render
			this.render();
			
		},
		
		show: function(){
			
			this.$values.find('.acf-field:visible').each(function(){
				
				acf.do_action('show_field', $(this));
				
			});
			
		},
		
		render: function(){
			
			// vars
			var self = this;
			
			
			// update order numbers
			this.$values.children('.layout').each(function( i ){
			
				$(this).find('> .acf-fc-layout-handle .acf-fc-layout-order').html( i+1 );
				
			});
			
			
			// empty?
			if( this.count() == 0 ) {
			
				this.$el.addClass('empty');
				
			} else {
			
				this.$el.removeClass('empty');
				
			}
			
			
			// row limit reached
			if( this.o.max > 0 && this.count() >= this.o.max ) {
				
				this.$el.find('> .acf-actions .button').addClass('disabled');
				
			} else {
				
				this.$el.find('> .acf-actions .button').removeClass('disabled');
				
			}
			
		},
		
		render_layout_title: function( $layout ){
			
			// vars
			var ajax_data = acf.serialize( $layout );
			
			
			// append
			ajax_data = acf.parse_args( ajax_data, {
				action: 	'acf/fields/flexible_content/layout_title',
				field_key: 	this.$field.data('key'),
				i: 			$layout.index(),
				layout:		$layout.data('layout')
			});
			
			
			// prepare
			ajax_data = acf.prepare_for_ajax(ajax_data);
			
			
			// ajax get title HTML
			$.ajax({
		    	url			: acf.get('ajaxurl'),
				dataType	: 'html',
				type		: 'post',
				data		: ajax_data,
				success: function( html ){
					
					// bail early if no html
					if( !html ) return;
					
					
					// update html
					$layout.find('> .acf-fc-layout-handle').html( html );
					
				}
			});
				
		},
			
		validate_add: function( layout ){
			
			// defaults
			layout = layout || '';
			
			
			// vars
			var max = this.o.max,
				count = this.count();
				
			
			// vadiate max
			if( max && count >= max ) {
				
				// vars
				var identifier	= ( max == 1 ) ? 'layout' : 'layouts',
					s 			= acf._e('flexible_content', 'max');
				
				
				// translate
				s = s.replace('{max}', max);
				s = s.replace('{identifier}', acf._e('flexible_content', identifier));
				
				
				// alert
				alert( s );
				
				
				// return
				return false;
				
			}
			
			
			// vadiate max layout
			if( layout ) {
				
				// vars
				var $popup			= $( this.$el.children('.tmpl-popup').html() ),
					$a				= $popup.find('[data-layout="' + layout + '"]'),
					layout_max		= parseInt( $a.attr('data-max') ),
					layout_count	= this.$values.children('.layout[data-layout="' + layout + '"]').length;
				
				
				if( layout_max > 0 && layout_count >= layout_max ) {
					
					// vars
					var identifier	= ( layout_max == 1 ) ? 'layout' : 'layouts',
						s 			= acf._e('flexible_content', 'max_layout');
					
					
					// translate
					s = s.replace('{max}', layout_count);
					s = s.replace('{label}', '"' + $a.text() + '"');
					s = s.replace('{identifier}', acf._e('flexible_content', identifier));
					
					
					// alert
					alert( s );
					
					
					// return
					return false;
				}
				
			}
			
			
			// return
			return true;
			
		},
		
		validate_remove: function( layout ){
			
			// defaults
			layout = layout || '';
			
			
			// vars
			var min = this.o.min,
				count = this.count();
				
				
			// vadiate min
			if( min > 0 && count <= min ) {
				
				// vars
				var identifier	= ( min == 1 ) ? 'layout' : 'layouts',
					s 			= acf._e('flexible_content', 'min') + ', ' + acf._e('flexible_content', 'remove');
				
				
				// translate
				s = s.replace('{min}', min);
				s = s.replace('{identifier}', acf._e('flexible_content', identifier));
				s = s.replace('{layout}', acf._e('flexible_content', 'layout'));
				
				
				// return
				return confirm( s );

			}
			
			
			// vadiate min layout
			if( layout ) {
				
				// vars
				var $popup			= $( this.$el.children('.tmpl-popup').html() ),
					$a				= $popup.find('[data-layout="' + layout + '"]'),
					layout_min		= parseInt( $a.attr('data-min') ),
					layout_count	= this.$values.children('.layout[data-layout="' + layout + '"]').length;
				
				
				if( layout_min > 0 && layout_count <= layout_min ) {
					
					// vars
					var identifier	= ( layout_min == 1 ) ? 'layout' : 'layouts',
						s 			= acf._e('flexible_content', 'min_layout') + ', ' + acf._e('flexible_content', 'remove');
					
					
					// translate
					s = s.replace('{min}', layout_count);
					s = s.replace('{label}', '"' + $a.text() + '"');
					s = s.replace('{identifier}', acf._e('flexible_content', identifier));
					s = s.replace('{layout}', acf._e('flexible_content', 'layout'));
					
					
					// return
					return confirm( s );
					
				}
				
			}
			
			
			// return
			return true;
			
		},
		
		sync: function(){
			
			// vars
			var name = 'collapsed_' + this.$field.data('key'),
				collapsed = [];
			
			
			// populate collapsed value
			this.$values.children('.layout').each(function( i ){
				
				if( $(this).hasClass('-collapsed') ) {
				
					collapsed.push( i );
					
				}
				
			});
			
			
			// update
			acf.update_user_setting( name, collapsed.join(',') );
			
		},
		
		add: function( layout, $before ){
			
			// defaults
			$before = $before || false;
			
					
			// bail early if validation fails
			if( !this.validate_add(layout) ) {
			
				return false;
				
			}
			
			
			// reference
			var $field = this.$field;
			
			
			// vars
			var $clone = this.$clones.children('.layout[data-layout="' + layout + '"]');
			
			
			// duplicate
			$el = acf.duplicate( $clone );
			
			
			// enable 
			acf.enable_form( $el, 'flexible_content' );
			
				
			// hide no values message
			this.$el.children('.no-value-message').hide();
			
			
			// add row
			if( $before ) {
				
				 $before.before( $el );
				 
			} else {
				
				this.$values.append( $el );
				
			}
			
			
			// focus (may have added sub flexible content)
			this.doFocus($field);
			
			
			// update order
			this.render();
			
			
			// validation
			acf.validation.remove_error( this.$field );
			
			
			// sync collapsed order
			this.sync();
			
		},
		
		
		/*
		*  events
		*
		*  these functions are fired for this fields events
		*
		*  @type	function
		*  @date	17/09/2015
		*  @since	5.2.3
		*
		*  @param	e
		*  @return	n/a
		*/
		
		_mouseenter: function( e ){ //console.log('_mouseenter');
			
			// bail early if already sortable
			if( this.$values.hasClass('ui-sortable') ) return;
			
			
			// bail early if max 1 row
			if( this.o.max == 1 ) return;
			
			
			// reference
			var self = this;
			
			
			// sortable
			this.$values.sortable({
				items: '> .layout',
				handle: '> .acf-fc-layout-handle',
				forceHelperSize: true,
				forcePlaceholderSize: true,
				scroll: true,
				start: function(event, ui) {
					
					acf.do_action('sortstart', ui.item, ui.placeholder);
					
	   			},
	   			stop: function(event, ui) {
					
					// render
					self.render();
					
					acf.do_action('sortstop', ui.item, ui.placeholder);
					
	   			},
	   			update: function(event, ui) {
		   			
		   			// trigger change
					self.$input.trigger('change');
					
		   		}
			});
			
		},
		
		_open: function( e ){ //console.log('_open');
			
			// bail early if validation fails
			if( !this.validate_add() ) return false;
			
			
			// reference
			var $values = this.$values;
			
			
			// vars
			var $popup = $( this.$el.children('.tmpl-popup').html() );
			
			
			// modify popup
			$popup.find('a').each(function(){
				
				// vars
				var $a = $(this),
					min = $a.data('min') || 0,
					max = $a.data('max') || 0,
					name = $a.data('layout'),
					count = $values.children('.layout[data-layout="' + name + '"]').length;
				
				
				// max
				if( max && count >= max) {
					
					$a.addClass('disabled');
					return;
					
				}
				
				
				// min
				if( min ) {
					
					// find diff
					var required	= min - count,
						s			= acf._e('flexible_content', 'required'),
						identifier	= ( required == 1 ) ? 'layout' : 'layouts',
				
						
					// translate
					s = s.replace('{required}', required);
					s = s.replace('{min}', min);
					s = s.replace('{label} ', ''); // remove label since 5.5.0
					s = s.replace('{identifier}', acf._e('flexible_content', identifier));
					
					
					// limit reached?
					if( required > 0 ) {
						
						var $badge = $('<span class="badge"></span>').attr('title', s).text(required);
						$a.append( $badge );
						
					}
					
				}
				
			});
			
			
			// add popup
			e.$el.after( $popup );
			
			
			// within layout?
			if( e.$el.closest('.acf-fc-layout-controlls').exists() ) {
				
				$popup.closest('.layout').addClass('-open');
				
			}
			
			
			// vars
			$popup.css({
				'margin-top' : 0 - $popup.height() - e.$el.outerHeight() - 15,
				'margin-left' : ( e.$el.outerWidth() - $popup.width() ) / 2
			});
			
			
			// check distance to top
			var dist_to_top = $popup.offset().top,
				min = ($('#wpadminbar').height() || 0) + 30; // 30px buffer below 'top'
			
			if( dist_to_top < min ) {
				
				$popup.css({
					'margin-top' : 15
				});
				
				$popup.addClass('bottom');
				
			}
			
			
			// focus
			$popup.children('.focus').trigger('focus');
			
		},
		
		_close: function( e ){ //console.log('_close');
			
			var $popup = e.$el.parent(),
				$layout = $popup.closest('.layout');
			
			
			// hide controlls?
			$layout.removeClass('-open');
			
			
			// remove popup
			setTimeout(function(){
				
				$popup.remove();
				
			}, 200);
			
		},
		
		_add: function( e ){ //console.log('_add');
						
			// vars
			var $popup = e.$el.closest('.acf-fc-popup'),
				layout = e.$el.attr('data-layout'),
				$before = false;
			
			
			// move row
			if( $popup.closest('.acf-fc-layout-controlls').exists() ) {
			
				$before = $popup.closest('.layout');
			
			}
			
			
			// add row
			this.add( layout, $before );
			
		},
		
		_remove: function( e ){ //console.log('_remove');
			
			// reference
			var self = this;
			
			
			// vars
			var $layout	= e.$el.closest('.layout');
			
			
			// bail early if validation fails
			if( !this.validate_remove( $layout.attr('data-layout') ) ) {
			
				return;
				
			}
			
			
			// close field
			var end_height = 0,
				$message = this.$el.children('.no-value-message');
			
			if( $layout.siblings('.layout').length == 0 ) {
			
				end_height = $message.outerHeight();
				
			}
			
			
			// action for 3rd party customization
			acf.do_action('remove', $layout);
			
			
			// remove
			acf.remove_el( $layout, function(){
				
				// update order
				self.render();
			
			
				// trigger change to allow attachment save
				self.$input.trigger('change');
				
			
				if( end_height > 0 ) {
				
					$message.show();
					
				}
				
				
				// sync collapsed order
				self.sync();
				
			}, end_height);
			
		},

		_collapse: function( e ){ //console.log('_collapse');
			
			// vars
			var $layout	= e.$el.closest('.layout'),
				collapsed = $layout.hasClass('-collapsed'),
				action = collapsed ? 'show' : 'hide';
			
			
			// render
			// - do this before calling actions to avoif focusing on the wrong field
			this.render_layout_title( $layout );
			
			
			// toggle class
			$layout.toggleClass('-collapsed');
			
			
			// sync collapsed order
			this.sync();
			
			
			// action
			acf.do_action(action, $layout, 'collapse');
			
		}
		
	});	
	

})(jQuery);

(function($){
	
	acf.fields.gallery = acf.field.extend({
		
		type: 'gallery',
		$el: null,
		$main: null,
		$side: null,
		$attachments: null,
		$input: null,
		//$attachment: null,
		
		actions: {
			'ready':	'initialize',
			'append':	'initialize',
			'show': 	'resize'
		},
		
		events: {
			'click .acf-gallery-attachment': 		'_select',
			'click .acf-gallery-add':				'_add',
			'click .acf-gallery-remove':			'_remove',
			'click .acf-gallery-close':				'_close',
			'change .acf-gallery-sort':				'_sort',
			'click .acf-gallery-edit':				'_edit',
			'click .acf-gallery-update': 			'_update',
			
			'change .acf-gallery-side input':		'_update',
			'change .acf-gallery-side textarea':	'_update',
			'change .acf-gallery-side select':		'_update'
		},
		
		
		/*
		*  focus
		*
		*  This function will setup variables when focused on a field
		*
		*  @type	function
		*  @date	12/04/2016
		*  @since	5.3.8
		*
		*  @param	n/a
		*  @return	n/a
		*/
		
		focus: function(){
			
			// el
			this.$el = this.$field.find('.acf-gallery:first');
			this.$main = this.$el.children('.acf-gallery-main');
			this.$side = this.$el.children('.acf-gallery-side');
			this.$attachments = this.$main.children('.acf-gallery-attachments');
			this.$input = this.$el.find('input:first');
			
			
			// get options
			this.o = acf.get_data( this.$el );
			
			
			// min / max
			this.o.min = this.o.min || 0;
			this.o.max = this.o.max || 0;
			
		},
		
		
		/*
		*  initialize
		*
		*  This function will initialize the field
		*
		*  @type	function
		*  @date	12/04/2016
		*  @since	5.3.8
		*
		*  @param	n/a
		*  @return	n/a
		*/
		
		initialize: function(){
			
			// reference
			var self = this,
				$field = this.$field;
				
					
			// sortable
			this.$attachments.unbind('sortable').sortable({
				
				items					: '.acf-gallery-attachment',
				forceHelperSize			: true,
				forcePlaceholderSize	: true,
				scroll					: true,
				
				start: function (event, ui) {
					
					ui.placeholder.html( ui.item.html() );
					ui.placeholder.removeAttr('style');
								
					acf.do_action('sortstart', ui.item, ui.placeholder);
					
	   			},
	   			
	   			stop: function (event, ui) {
				
					acf.do_action('sortstop', ui.item, ui.placeholder);
					
	   			}
			});
			
			
			// resizable
			this.$el.unbind('resizable').resizable({
				handles: 's',
				minHeight: 200,
				stop: function(event, ui){
					
					acf.update_user_setting('gallery_height', ui.size.height);
				
				}
			});
			
			
			// resize
			$(window).on('resize', function(){
				
				self.set('$field', $field).resize();
				
			});
			
			
			// render
			this.render();
			
			
			// resize
			this.resize();
					
		},
		
		
		/*
		*  resize
		*
		*  This function will resize the columns
		*
		*  @type	function
		*  @date	20/04/2016
		*  @since	5.3.8
		*
		*  @param	$post_id (int)
		*  @return	$post_id (int)
		*/
		
		resize: function(){
			
			// vars
			var min = 100,
				max = 175,
				columns = 4,
				width = this.$el.width();
			
			
			// get width
			for( var i = 4; i < 20; i++ ) {
			
				var w = width/i;
				
				if( min < w && w < max ) {
				
					columns = i;
					break;
					
				}
				
			}
			
			
			// max columns css is 8
			columns = Math.min(columns, 8);
			
			
			// update data
			this.$el.attr('data-columns', columns);
			
		},
		
		
		/*
		*  render
		*
		*  This function will render classes etc
		*
		*  @type	function
		*  @date	19/04/2016
		*  @since	5.3.8
		*
		*  @param	n/a
		*  @return	n/a
		*/
		
		render: function() {
			
			// vars
			var $select = this.$main.find('.acf-gallery-sort'),
				$a = this.$main.find('.acf-gallery-add');
			
			
			// disable a
			if( this.o.max > 0 && this.count() >= this.o.max ) {
			
				$a.addClass('disabled');
				
			} else {
			
				$a.removeClass('disabled');
				
			}
			
			
			// disable select
			if( !this.count() ) {
			
				$select.addClass('disabled');
				
			} else {
			
				$select.removeClass('disabled');
				
			}
			
		},
		
		
		/*
		*  open_sidebar
		*
		*  This function will open the gallery sidebar
		*
		*  @type	function
		*  @date	19/04/2016
		*  @since	5.3.8
		*
		*  @param	n/a
		*  @return	n/a
		*/
		
		open_sidebar: function(){
			
			// add class
			this.$el.addClass('sidebar-open');
			
			
			// hide bulk actions
			this.$main.find('.acf-gallery-sort').hide();
			
			
			// vars
			var width = this.$el.width() / 3;
			
			
			// set minimum width
			width = parseInt( width );
			width = Math.max( width, 350 );
			
			
			// animate
			this.$side.children('.acf-gallery-side-inner').css({ 'width' : width-1 });
			this.$side.animate({ 'width' : width-1 }, 250);
			this.$main.animate({ 'right' : width }, 250);
						
		},
		
		
		/*
		*  _close
		*
		*  event listener
		*
		*  @type	function
		*  @date	12/04/2016
		*  @since	5.3.8
		*
		*  @param	e (event)
		*  @return	n/a
		*/
		
		_close: function( e ){
			
			this.close_sidebar();
			
		},
		
		
		/*
		*  close_sidebar
		*
		*  This function will open the gallery sidebar
		*
		*  @type	function
		*  @date	19/04/2016
		*  @since	5.3.8
		*
		*  @param	n/a
		*  @return	n/a
		*/
		
		close_sidebar: function(){
			
			// remove class
			this.$el.removeClass('sidebar-open');
			
			
			// vars
			var $select = this.$el.find('.acf-gallery-sort');
			
			
			// clear selection
			this.get_attachment('active').removeClass('active');
			
			
			// disable sidebar
			this.$side.find('input, textarea, select').attr('disabled', 'disabled');
			
			
			// animate
			this.$main.animate({ right: 0 }, 250);
			this.$side.animate({ width: 0 }, 250, function(){
				
				$select.show();
				
				$(this).find('.acf-gallery-side-data').html('');
				
			});
			
		},
		
		
		/*
		*  count
		*
		*  This function will return the number of attachemnts
		*
		*  @type	function
		*  @date	12/04/2016
		*  @since	5.3.8
		*
		*  @param	n/a
		*  @return	n/a
		*/
		
		count: function(){
			
			return this.get_attachments().length;
			
		},
		
		
		/*
		*  get_attachments
		*
		*  description
		*
		*  @type	function
		*  @date	19/04/2016
		*  @since	5.3.8
		*
		*  @param	$post_id (int)
		*  @return	$post_id (int)
		*/
		
		get_attachments: function(){
			
			return this.$attachments.children('.acf-gallery-attachment');
			
		},
		
		
		/*
		*  get_attachment
		*
		*  This function will return an attachment
		*
		*  @type	function
		*  @date	19/04/2016
		*  @since	5.3.8
		*
		*  @param	id (string)
		*  @return	$el
		*/
		
		get_attachment: function( s ){
			
			// defaults
			s = s || 0;
			
			
			// update selector
			if( s === 'active' ) {
				
				s = '.active';
				
			} else {
				
				s = '[data-id="' + s  + '"]';
				
			}
			
			
			// return
			return this.$attachments.children( '.acf-gallery-attachment'+s );
			
		},
		
		
		/*
		*  render_attachment
		*
		*  This functin will render an attachemnt
		*
		*  @type	function
		*  @date	20/04/2016
		*  @since	5.3.8
		*
		*  @param	$post_id (int)
		*  @return	$post_id (int)
		*/
		
		render_attachment: function( data ){
			
			// prepare
			data = this.prepare(data);
			
			
			// vars
			var $attachment = this.get_attachment(data.id),
				$margin = $attachment.find('.margin'),
				$img = $attachment.find('img'),
				$filename = $attachment.find('.filename'),
				$input = $attachment.find('input[type="hidden"]');
			
			
			// thumbnail
			var thumbnail = data.url;
			
			
			// image
			if( data.type == 'image' ) {
				
				// remove filename	
				$filename.remove();
			
			// other (video)	
			} else {	
				
				// attempt to find attachment thumbnail
				thumbnail = acf.maybe_get(data, 'thumb.src');
				
				
				// update filenmae text
				$filename.text( data.filename );
				
			}
			
			
			// default icon
			if( !thumbnail ) {
				
				thumbnail = acf._e('media', 'default_icon');
				$attachment.addClass('-icon');
				
			}
			
			
			// update els
		 	$img.attr({
			 	'src': thumbnail,
			 	'alt': data.alt,
			 	'title': data.title
			});
		 	
		 	
			// update val
		 	acf.val( $input, data.id );
		 				
		},
		
		
		_add: function( e ){
			
			// validate
			if( this.o.max > 0 && this.count() >= this.o.max ) {
			
				acf.validation.add_warning( this.$field, acf._e('gallery', 'max'));
				
				return;
				
			}
			
			
			// reference
			var self = this,
				$field = this.$field;
			
			
			// popup
			var frame = acf.media.popup({
				
				title:		acf._e('gallery', 'select'),
				mode:		'select',
				type:		'',
				field:		this.$field.data('key'),
				multiple:	'add',
				library:	this.o.library,
				mime_types: this.o.mime_types,
				select: function( attachment, i ) {
					
					// add
					self.set('$field', $field).add_attachment( attachment, i );
					
				}
			});
			
			
			// modify DOM
			frame.on('content:activate:browse', function(){
				
				self.render_collection( frame );
				
				frame.content.get().collection.on( 'reset add', function(){
				    
					self.render_collection( frame );
				    
			    });
				
			});
			
		},
		
		
		/*
		*  add_attachment
		*
		*  This function will add an attachment
		*
		*  @type	function
		*  @date	20/04/2016
		*  @since	5.3.8
		*
		*  @param	$post_id (int)
		*  @return	$post_id (int)
		*/
		
		add_attachment: function( data, i ){
			
			// defaults
			i = i || 0;
			
			
			// prepare
			data = this.prepare(data);	
			
			
			// validate
			if( this.o.max > 0 && this.count() >= this.o.max ) return;
			
			
			// is image already in gallery?
			if( this.get_attachment(data.id).exists() ) return;
			
			
			// vars
			var name = this.$el.find('input[type="hidden"]:first').attr('name');

			
			// html
			var html = [
			'<div class="acf-gallery-attachment acf-soh" data-id="' + data.id + '">',
				'<input type="hidden" value="' + data.id + '" name="' + name + '[]">',
				'<div class="margin" title="">',
					'<div class="thumbnail">',
						'<img src="" alt="">',
					'</div>',
					'<div class="filename"></div>',
				'</div>',
				'<div class="actions acf-soh-target">',
					'<a href="#" class="acf-icon -cancel dark acf-gallery-remove" data-id="' + data.id + '"></a>',
				'</div>',
			'</div>'].join('');
			
			var $html = $(html);
			
			
			// append
			this.$attachments.append( $html );
			
			
			// more to beginning
			if( this.o.insert === 'prepend' ) {
				
				// vars
				var $before = this.$attachments.children(':eq('+i+')');
				
				
				// move
				if( $before.exists() ) {
					
					$before.before( $html );
					
				}
				
			}
						
			
			// render data
			this.render_attachment( data );
			
			
			// render
			this.render();	
			
			
			// trigger change
			this.$input.trigger('change');
			
		},
		
		
		/*
		*  _select
		*
		*  event listener
		*
		*  @type	function
		*  @date	12/04/2016
		*  @since	5.3.8
		*
		*  @param	e (event)
		*  @return	n/a
		*/
		
		_select: function( e ){
			
			// vars
			var id = e.$el.data('id');
			
			
			// select
			this.select_attachment(id);
			
		},
		
		
		/*
		*  select_attachment
		*
		*  This function will select an attachment for editing
		*
		*  @type	function
		*  @date	20/04/2016
		*  @since	5.3.8
		*
		*  @param	$post_id (int)
		*  @return	$post_id (int)
		*/
		
		select_attachment: function( id ){
			
			// vars
			var $attachment = this.get_attachment(id);
			
			
			// bail early if already active
			if( $attachment.hasClass('active') ) return;
			
			
			// save any changes in sidebar
			this.$side.find(':focus').trigger('blur');
			
			
			// clear selection
			this.get_attachment('active').removeClass('active');
			
			
			// add selection
			$attachment.addClass('active');
			
			
			// fetch
			this.fetch( id );
			
			
			// open sidebar
			this.open_sidebar();
			
		},
		
		
		/*
		*  prepare
		*
		*  This function will prepare an object of attachment data
		*  selecting a library image vs embed an image via url return different data
		*  this function will keep the 2 consistent
		*
		*  @type	function
		*  @date	12/04/2016
		*  @since	5.3.8
		*
		*  @param	attachment (object)
		*  @return	data (object)
		*/
		
		prepare: function( attachment ) {
			
			// defaults
			attachment = attachment || {};
			
			
			// bail ealry if already valid
			if( attachment._valid ) return attachment;
			
			
			// vars
			var data = {
				id: '',
				url: '',
				alt: '',
				title: '',
				filename: ''
			};
			
			
			// wp image
			if( attachment.id ) {
				
				// update data
				data = attachment.attributes;
				
				
				// maybe get preview size
				data.url = acf.maybe_get(data, 'sizes.medium.url', data.url);
				
			}
			
			
			// valid
			data._valid = true;
			
	    	
	    	// return
	    	return data;
			
		},
		
		
		/*
		*  fetch
		*
		*  This function will fetch the sidebar html to edit an attachment 
		*
		*  @type	function
		*  @date	19/04/2016
		*  @since	5.3.8
		*
		*  @param	n/a
		*  @return	n/a
		*/
		
		fetch: function( id ){
			
			// vars
			var data = acf.prepare_for_ajax({
				action		: 'acf/fields/gallery/get_attachment',
				field_key	: this.$field.data('key'),
				id			: id
			});
			
			
			// abort XHR if this field is already loading AJAX data
			if( this.$el.data('xhr') ) {
			
				this.$el.data('xhr').abort();
				
			}
			
			
			// add custom attachment
			if( typeof id === 'string' && id.indexOf('_') === 0 ) {
				
				// vars
				var val = this.get_attachment(id).find('input[type="hidden"]').val();
				
				
				// parse json
				val = $.parseJSON(val);
				
				
				// append
				data.attachment = val;
				
			}
			
			
			// get results
		    var xhr = $.ajax({
		    	url			: acf.get('ajaxurl'),
				dataType	: 'html',
				type		: 'post',
				cache		: false,
				data		: data,
				context		: this,
				success		: this.fetch_success
			});
			
			
			// update el data
			this.$el.data('xhr', xhr);
			
		},
		
		fetch_success: function( html ){
			
			// bail early if no html
			if( !html ) return;
			
			
			// vars
			var $side = this.$side.find('.acf-gallery-side-data');
			
			
			// render
			$side.html( html );
			
			
			// remove acf form data
			$side.find('.compat-field-acf-form-data').remove();
			
			
			// detach meta tr
			var $tr = $side.find('> .compat-attachment-fields > tbody > tr').detach();
			
			
			// add tr
			$side.find('> table.form-table > tbody').append( $tr );			
			
			
			// remove origional meta table
			$side.find('> .compat-attachment-fields').remove();
			
			
			// setup fields
			acf.do_action('append', $side);
			
		},
		
		
		/*
		*  _sort
		*
		*  event listener
		*
		*  @type	function
		*  @date	12/04/2016
		*  @since	5.3.8
		*
		*  @param	e (event)
		*  @return	n/a
		*/
		
		_sort: function( e ){
			
			// vars
			var sort = e.$el.val();
			
			
			// validate
			if( !sort ) return;
			
			
			// vars
			var data = acf.prepare_for_ajax({
				action		: 'acf/fields/gallery/get_sort_order',
				field_key	: this.$field.data('key'),
				ids			: [],
				sort		: sort
			});
			
			
			// find and add attachment ids
			this.get_attachments().each(function(){
				
				// vars
				var id = $(this).attr('data-id');
				
				
				// bail early if no id (insert from url)
				if( !id ) return;
				
				
				// append
				data.ids.push(id);
				
			});
			
			
			// get results
		    var xhr = $.ajax({
		    	url:		acf.get('ajaxurl'),
				dataType:	'json',
				type:		'post',
				cache:		false,
				data:		data,
				context:	this,
				success:	this._sort_success
			});
		},
		
		_sort_success: function( json ) {
				
			// validate
			if( !acf.is_ajax_success(json) ) return;
			
			
			// reverse order
			json.data.reverse();
			
			
			// loop over json
			for( i in json.data ) {
				
				var id = json.data[ i ],
					$attachment = this.get_attachment(id);
				
				
				// prepend attachment
				this.$attachments.prepend( $attachment );
				
			}
		},
		
		
		/*
		*  _update
		*
		*  event listener
		*
		*  @type	function
		*  @date	12/04/2016
		*  @since	5.3.8
		*
		*  @param	e (event)
		*  @return	n/a
		*/
		
		_update: function(){
			
			// vars
			var $submit = this.$side.find('.acf-gallery-update'),
				$edit = this.$side.find('.acf-gallery-edit'),
				$form = this.$side.find('.acf-gallery-side-data'),
				id = $edit.data('id'),
				data = acf.serialize_form( $form );
			
			
			// validate
			if( $submit.attr('disabled') ) return false;
			
			
			// add attr
			$submit.attr('disabled', 'disabled');
			$submit.before('<i class="acf-loading"></i>');
			
			
			// append AJAX action		
			data.action = 'acf/fields/gallery/update_attachment';
			
			
			// prepare for ajax
			acf.prepare_for_ajax(data);
			
			
			// ajax
			$.ajax({
				url			: acf.get('ajaxurl'),
				data		: data,
				type		: 'post',
				dataType	: 'json',
				complete	: function( json ){
					
					$submit.removeAttr('disabled');
					$submit.prev('.acf-loading').remove();
					
				}
			});
			
		},
		
		
		/*
		*  _remove
		*
		*  event listener
		*
		*  @type	function
		*  @date	12/04/2016
		*  @since	5.3.8
		*
		*  @param	e (event)
		*  @return	n/a
		*/
		
		_remove: function( e ){
			
			// prevent event from triggering click on attachment
			e.stopPropagation();
			
			
			// vars
			var id = e.$el.data('id');
			
			
			// select
			this.remove_attachment(id);
			
		},
		
		
		/*
		*  remove_attachment
		*
		*  This function will remove an attachment
		*
		*  @type	function
		*  @date	20/04/2016
		*  @since	5.3.8
		*
		*  @param	$post_id (int)
		*  @return	$post_id (int)
		*/
		
		remove_attachment: function( id ){
			
			// close sidebar (if open)
			this.close_sidebar();
			
			
			// remove attachment
			this.get_attachment(id).remove();
			
			
			// render (update classes)
			this.render();
			
			
			// trigger change
			this.$input.trigger('change');
			
		},
		
		
		/*
		*  _edit
		*
		*  event listener
		*
		*  @type	function
		*  @date	12/04/2016
		*  @since	5.3.8
		*
		*  @param	e (event)
		*  @return	n/a
		*/
		
		_edit:function( e ){
			
			// vars
			var id = e.$el.data('id');
			
			
			// select
			this.edit_attachment(id);
						
		},
		
		
		/*
		*  edit_attachment
		*
		*  This function will create a WP popup to edit an attachment
		*
		*  @type	function
		*  @date	20/04/2016
		*  @since	5.3.8
		*
		*  @param	$post_id (int)
		*  @return	$post_id (int)
		*/
		
		edit_attachment: function( id ){
			
			// reference
			var self = this,
				$field = this.$field;
			
			
			// popup
			var frame = acf.media.popup({
				mode:		'edit',
				title:		acf._e('image', 'edit'),
				button:		acf._e('image', 'update'),
				attachment:	id,
				select:		function( attachment ){
					
					// render attachment
					self.set('$field', $field).render_attachment( attachment );
					
				 	
				 	// render sidebar
					self.fetch( id );
					
				}
			});
			
		},
		
		
		
		render_collection: function( frame ){
			
			var self = this;
			
			
			// Note: Need to find a differen 'on' event. Now that attachments load custom fields, this function can't rely on a timeout. Instead, hook into a render function foreach item
			
			// set timeout for 0, then it will always run last after the add event
			setTimeout(function(){
			
			
				// vars
				var $content	= frame.content.get().$el
					collection	= frame.content.get().collection || null;
					

				
				if( collection ) {
					
					var i = -1;
					
					collection.each(function( item ){
					
						i++;
						
						var $li = $content.find('.attachments > .attachment:eq(' + i + ')');
						
						
						// if image is already inside the gallery, disable it!
						if( self.get_attachment(item.id).exists() ) {
						
							item.off('selection:single');
							$li.addClass('acf-selected');
							
						}
						
					});
					
				}
			
			}, 10);
			
		}
		
	});
	
	
	/*
	*  acf_gallery_manager
	*
	*  Priveds some global functionality for the gallery field
	*
	*  @type	function
	*  @date	25/11/2015
	*  @since	5.3.2
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	var acf_gallery_manager = acf.model.extend({
		
		actions: {
			'validation_begin': 	'validation_begin',
			'validation_failure': 	'validation_failure'
		},
		
		validation_begin: function(){
			
			// lock all gallery forms
			$('.acf-gallery-side-data').each(function(){
				
				acf.disable_form( $(this), 'gallery' );
				
			});
			
		},
		
		validation_failure: function(){
			
			// lock all gallery forms
			$('.acf-gallery-side-data').each(function(){
				
				acf.enable_form( $(this), 'gallery' );
				
			});
			
		}
		
	});
	
	
})(jQuery);

// @codekit-prepend "../js/acf-repeater.js";
// @codekit-prepend "../js/acf-flexible-content.js";
// @codekit-prepend "../js/acf-gallery.js";

