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
			this.$input = this.$el.children('input');
			this.$table = this.$el.children('table');
			this.$tbody = this.$table.children('tbody');
			this.$clone = this.$tbody.children('tr.acf-clone');
			
			
			// get options
			this.o = acf.get_data(this.$el, {
				'min':	0,
				'max':	0
			});
			
			
			// min / max
			this.o.min = parseInt(this.o.min);
			this.o.max = parseInt(this.o.max);
			
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
			
			// vars
			var $button = this.$el.find('> .acf-actions .button');
			
			
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
				
				$button.addClass('disabled');
				
			} else {
				
				$button.removeClass('disabled');
				
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
	   			stop: function(event, ui) {
					
					// render
					self.render();
					
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
			
			// reference
			var self = this;
			
			
			// vars
			var $row = e.$el.closest('.acf-row');
			
			
			// add -open class to show controlls
			$row.addClass('-hover');
			
			
			// confirm
			acf.tooltip.confirm_remove( e.$el, function( result ){
				
				$row.removeClass('-hover');
				
				if( result ) {
					
					self.remove( $row );
					
				}
				
			});
			
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
			'click [data-name="add-layout"]': 			'_open',
			'click [data-name="remove-layout"]': 		'_remove',
			'click [data-name="collapse-layout"]':		'_collapse',
			'mouseenter .acf-fc-layout-handle': 		'_mouseenter'
		},
		
		focus: function(){
			
			// vars
			this.$el = this.$field.find('.acf-flexible-content:first');
			this.$input = this.$el.children('input');
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
			var $button = this.$el.find('> .acf-actions .button');
			
			
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
				
				$button.addClass('disabled');
				
			} else {
				
				$button.removeClass('disabled');
				
			}
			
		},
		
		render_layout_title: function( $layout ){
			
			// vars
			var $input = $layout.children('input');
			var prefix = $input.attr('name').replace('[acf_fc_layout]', '');
			
			
			// ajax data
			var ajaxdata = {
				action: 	'acf/fields/flexible_content/layout_title',
				field_key: 	this.$field.data('key'),
				i: 			$layout.index(),
				layout:		$input.val(),
				value:		acf.serialize( $layout, prefix )
			};
			
			
			// ajax get title HTML
			$.ajax({
		    	url:		acf.get('ajaxurl'),
		    	data:		acf.prepare_for_ajax(ajaxdata),
				dataType:	'html',
				type:		'post',
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
	   			stop: function(event, ui) {
					
					// render
					self.render();
					
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
			var self = this;
			
			
			// vars
			var $popup = $( this.$el.children('.tmpl-popup').html() );
			
			
			// count layouts
			var layouts = {};
			this.$values.children('.layout').each(function(){
				var k = $(this).data('layout');
				layouts[ k ] = layouts[ k ] ? layouts[ k ] + 1 : 1;
			});
			
			
			// modify popup
			$popup.find('a').each(function(){
				
				// vars
				var $a = $(this),
					min = $a.data('min') || 0,
					max = $a.data('max') || 0,
					name = $a.data('layout'),
					count = layouts[name] || 0;
				
				
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
			
			
			// within layout?
			var $layout = null;
			if( e.$el.hasClass('acf-icon') ) {

				$layout = e.$el.closest('.layout');
				$layout.addClass('-open');
				
			}
			
			
			// append
			$('body').append( $popup );
			
			
			// position
			this.position_popup( $popup, e.$el );
			
			
			// events
			var event = function( e, layout ){
				
				// prevent all listeners
				e.preventDefault();
				e.stopImmediatePropagation();
				
				
				// remove events
				$popup.off('click', 'a', event_y);
				$('body').off('click', event_n);
				
				
				// remove tooltip
				$popup.remove();
				
				
				// hide controlls?
				if( $layout !== null ) {
					$layout.removeClass('-open');
				}
			
				
				// callback
				if( layout !== null ) {
					self.add( layout, $layout );
				}
				
			};
			
			var event_y = function( e ){
				event( e, $(this).attr('data-layout') );
			};
			
			var event_n = function( e ){
				event( e, null );
			};
			
			
			// add events
			$popup.on('click', 'a', event_y);
			$('body').on('click', event_n);
			
		},
		
		
		/*
		*  position_popup
		*
		*  This function will position a $popup to another element (button)
		*
		*  @type	function
		*  @date	8/6/17
		*  @since	5.6.0
		*
		*  @param	$popup (element)
		*  @param	$el (element)
		*  @return	n/a
		*/
		
		position_popup: function( $popup, $el ){
			
			// position
			var tolerance = 10;
				target_w = $el.outerWidth(),
				target_h = $el.outerHeight(),
				target_t = $el.offset().top,
				target_l = $el.offset().left,
				popup_w = $popup.outerWidth(),
				popup_h = $popup.outerHeight();
			
			
			// calculate top
			var top = target_t - popup_h,
				left = target_l + (target_w / 2) - (popup_w / 2);
			
			
			// too far top
			if( top - $(window).scrollTop() < tolerance ) {
				
				$popup.addClass('-bottom');
				top = target_t + target_h;
			
			// default
			} else {
				
				$popup.addClass('-top');
				
			}
			
			
			// too far left
			if( left < tolerance ) {
				
				$popup.addClass('-right');
				left = target_l;
			
			
			// too far right
			} else if( (left + popup_w + tolerance) > $(window).width() ) {
				
				$popup.addClass('-left');
				left = target_l + target_w - popup_w;
			
			}
			
			
			// update css
			$popup.css({ 'top': top, 'left': left });
			
		},
		
		_remove: function( e ){ console.log('flex _remove');
			
			// reference
			var self = this;
			
			
			// vars
			var $layout = e.$el.closest('.layout');
			
			
			// add -open class to show controlls
			$layout.addClass('-open');
			
			
			// confirm
			acf.tooltip.confirm_remove( e.$el, function( result ){
				
				if( result ) {
					
					self.remove_layout( $layout );
					
				} else {
					
					$layout.removeClass('-open');
					
				}
				
				
			});
			
		},
		
		remove_layout: function( $layout ){
			
			// reference
			var self = this;
			
			
			// bail early if validation fails
			if( !this.validate_remove( $layout.attr('data-layout') ) ) {
				return;
			}
			
			
			// vars
			var $message = null,
				end_height = 0;
			
			
			// show message if no values
			if( this.count() == 1 ) {
				$message = this.$el.children('.no-value-message');
				end_height = $message.outerHeight();
			}
			
			
			// remove
			acf.remove_el( $layout, function(){
				
				// show message
				if( $message !== null ) {
					$message.show();
				}
				
				
				// update order
				self.render();
			
			
				// trigger change to allow attachment save
				self.$input.trigger('change');
				
				
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
				
				items: '.acf-gallery-attachment',
				forceHelperSize: true,
				forcePlaceholderSize: true,
				scroll: true,
				start: function (event, ui) {
					
					ui.placeholder.html( ui.item.html() );
					ui.placeholder.removeAttr('style');
					
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
		*  get_value
		*
		*  description
		*
		*  @type	function
		*  @date	27/6/17
		*  @since	5.6.0
		*
		*  @param	$post_id (int)
		*  @return	$post_id (int)
		*/
		
		get_value: function(){
			
			// vars
			var value = [];
			
			
			// find and add attachment ids
			this.get_attachments().each(function(){
				
				// vars
				value.push( $(this).data('id') );
				
			});
			
			
			// return
			return value;
			
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
			
			
			// get selected values
			this.get_attachments().each(function(){
				
				
			})
			
			// popup
			var frame = acf.media.popup({
				
				title:		acf._e('gallery', 'select'),
				mode:		'select',
				type:		'',
				field:		this.$field.data('key'),
				multiple:	'add',
				library:	this.o.library,
				mime_types: this.o.mime_types,
				selected:	this.get_value(),
				select: function( attachment, i ) {
					
					// add
					self.set('$field', $field).add_attachment( attachment, i );
					
				}
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
			'<div class="acf-gallery-attachment" data-id="' + data.id + '">',
				'<input type="hidden" value="' + data.id + '" name="' + name + '[]">',
				'<div class="margin" title="">',
					'<div class="thumbnail">',
						'<img src="" alt="">',
					'</div>',
					'<div class="filename"></div>',
				'</div>',
				'<div class="actions">',
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
			var ajaxdata = {
				action		: 'acf/fields/gallery/get_attachment',
				field_key	: this.$field.data('key'),
				id			: id
			};
			
			
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
				ajaxdata.attachment = val;
				
			}
			
			
			// get results
		    var xhr = $.ajax({
		    	url			: acf.get('ajaxurl'),
				dataType	: 'html',
				type		: 'post',
				cache		: false,
				data		: acf.prepare_for_ajax(ajaxdata),
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
			var ajaxdata = {
				action		: 'acf/fields/gallery/get_sort_order',
				field_key	: this.$field.data('key'),
				ids			: [],
				sort		: sort
			};
			
			
			// find and add attachment ids
			this.get_attachments().each(function(){
				
				// vars
				var id = $(this).attr('data-id');
				
				
				// bail early if no id (insert from url)
				if( !id ) return;
				
				
				// append
				ajaxdata.ids.push(id);
				
			});
			
			
			// get results
		    var xhr = $.ajax({
		    	url:		acf.get('ajaxurl'),
				dataType:	'json',
				type:		'post',
				cache:		false,
				data:		acf.prepare_for_ajax(ajaxdata),
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
				ajaxdata = acf.serialize( $form );
			
			
			// validate
			if( $submit.attr('disabled') ) return false;
			
			
			// add attr
			$submit.attr('disabled', 'disabled');
			$submit.before('<i class="acf-loading"></i>');
			
			
			// append AJAX action		
			ajaxdata.action = 'acf/fields/gallery/update_attachment';
			
			
			// ajax
			$.ajax({
				url			: acf.get('ajaxurl'),
				data		: acf.prepare_for_ajax(ajaxdata),
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
			'ready':				'ready',
			'validation_begin': 	'validation_begin',
			'validation_failure': 	'validation_failure'
		},
		
		ready: function(){
			
			// customize wp.media views
			if( acf.isset(window, 'wp', 'media', 'view') ) {
				
				this.customize_Attachment();
			
			}
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
			
		},
		
		customize_Attachment: function(){
			
			// vars
			var AttachmentLibrary = wp.media.view.Attachment.Library;
			
			
			// extend
			wp.media.view.Attachment.Library = AttachmentLibrary.extend({
				
				render: function() {
					
					// vars
					var frame = acf.media.frame();
					var selected = acf.maybe_get(frame, 'acf.selected');
					var id = acf.maybe_get(this, 'model.attributes.id');
					
					
					// select
					if( selected && selected.indexOf(id) > -1 ) {
						
						this.$el.addClass('acf-selected');
						
					}
					
						
					// return
					return AttachmentLibrary.prototype.render.apply( this, arguments );
					
				}
				
			});
			
		}
		
	});
	
	
})(jQuery);

// @codekit-prepend "../js/acf-repeater.js";
// @codekit-prepend "../js/acf-flexible-content.js";
// @codekit-prepend "../js/acf-gallery.js";

