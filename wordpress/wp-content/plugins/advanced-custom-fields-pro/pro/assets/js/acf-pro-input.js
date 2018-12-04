(function($){
	
	var Field = acf.Field.extend({
		
		type: 'repeater',
		wait: '',
		
		events: {
			'click a[data-event="add-row"]': 		'onClickAdd',
			'click a[data-event="remove-row"]': 	'onClickRemove',
			'click a[data-event="collapse-row"]': 	'onClickCollapse',
			'showField':							'onShow',
			'unloadField':							'onUnload',
			'mouseover': 							'onHover',
			'unloadField':							'onUnload'
		},
		
		$control: function(){
			return this.$('.acf-repeater:first');
		},
		
		$table: function(){
			return this.$('table:first');
		},
		
		$tbody: function(){
			return this.$('tbody:first');
		},
		
		$rows: function(){
			return this.$('tbody:first > tr').not('.acf-clone');
		},
		
		$row: function( index ){
			return this.$('tbody:first > tr:eq(' + index + ')');
		},
		
		$clone: function(){
			return this.$('tbody:first > tr.acf-clone');
		},
		
		$actions: function(){
			return this.$('.acf-actions:last');
		},
		
		$button: function(){
			return this.$('.acf-actions:last .button');
		},
		
		getValue: function(){
			return this.$rows().length;
		},
		
		allowRemove: function(){
			var min = parseInt( this.get('min') );
			return ( !min || min < this.val() );
		},
		
		allowAdd: function(){
			var max = parseInt( this.get('max') );
			return ( !max || max > this.val() );
		},
		
		addSortable: function( self ){
			
			// bail early if max 1 row
			if( this.get('max') == 1 ) {
				return;
			}
			
			// add sortable
			this.$tbody().sortable({
				items: '> tr',
				handle: '> td.order',
				forceHelperSize: true,
				forcePlaceholderSize: true,
				scroll: true,
	   			stop: function(event, ui) {
					self.render();
	   			},
	   			update: function(event, ui) {
					self.$input().trigger('change');
		   		}
			});
		},
		
		addCollapsed: function(){
			
			// vars
			var indexes = preference.load( this.get('key') );
			
			// bail early if no collapsed
			if( !indexes ) {
				return false;
			}
			
			// loop
			this.$rows().each(function( i ){
				if( indexes.indexOf(i) > -1 ) {
					$(this).addClass('-collapsed');
				}
			});
		},
		
		addUnscopedEvents: function( self ){
			
			// invalidField
			this.on('invalidField', '.acf-row', function(e){
				var $row = $(this);
				if( self.isCollapsed($row) ) {
					self.expand( $row );
				}
			});
		},
				
		initialize: function(){
			
			// add unscoped events
			this.addUnscopedEvents( this );
			
			// add collapsed
			this.addCollapsed();
			
			// disable clone
			acf.disable( this.$clone(), this.cid );
			
			// render
			this.render();
		},
		
		render: function(){
			
			// update order number
			this.$rows().each(function( i ){
				$(this).find('> .order > span').html( i+1 );
			});
			
			// empty
			if( this.val() == 0 ) {
				this.$control().addClass('-empty');
			} else {
				this.$control().removeClass('-empty');
			}
			
			// max
			if( this.allowAdd() ) {
				this.$button().removeClass('disabled');
			} else {
				this.$button().addClass('disabled');
			}	
		},
		
		validateAdd: function(){
			
			// return true if allowed
			if( this.allowAdd() ) {
				return true;
			}
			
			// vars
			var max = this.get('max');
			var text = acf.__('Maximum rows reached ({max} rows)');
			
			// replace
			text = text.replace('{max}', max);
			
			// add notice
			this.showNotice({
				text: text,
				type: 'warning'
			});
			
			// return
			return false;
		},
		
		onClickAdd: function( e, $el ){
			
			// validate
			if( !this.validateAdd() ) {
				return false;
			}
			
			// add above row
			if( $el.hasClass('acf-icon') ) {
				this.add({
					before: $el.closest('.acf-row')
				});
			
			// default
			} else {
				this.add();
			}
		},
		
		add: function( args ){
			
			// validate
			if( !this.allowAdd() ) {
				return false;
			}
			
			// defaults
			args = acf.parseArgs(args, {
				before: false
			});
			
			// add row
			var $el = acf.duplicate({
				target: this.$clone(),
				append: this.proxy(function( $el, $el2 ){
					
					// append
					if( args.before ) {
						args.before.before( $el2 );
					} else {
						$el.before( $el2 );
					}
					
					// remove clone class
					$el2.removeClass('acf-clone');
					
					// enable
					acf.enable( $el2, this.cid );
					
					// render
					this.render();
				})
			});
			
			// trigger change for validation errors
			this.$input().trigger('change');
			
			// return
			return $el;
		},
		
		validateRemove: function(){
			
			// return true if allowed
			if( this.allowRemove() ) {
				return true;
			}
			
			// vars
			var min = this.get('min');
			var text = acf.__('Minimum rows reached ({min} rows)');
			
			// replace
			text = text.replace('{min}', min);
			
			// add notice
			this.showNotice({
				text: text,
				type: 'warning'
			});
			
			// return
			return false;
		},
		
		onClickRemove: function( e, $el ){
			
			// vars
			var $row = $el.closest('.acf-row');
			
			// add class
			$row.addClass('-hover');
			
			// add tooltip
			var tooltip = acf.newTooltip({
				confirmRemove: true,
				target: $el,
				context: this,
				confirm: function(){
					this.remove( $row );
				},
				cancel: function(){
					$row.removeClass('-hover');
				}
			});
		},

		remove: function( $row ){
			
			// reference
			var self = this;
			
			// remove
			acf.remove({
				target: $row,
				endHeight: 0,
				complete: function(){
					
					// trigger change to allow attachment save
					self.$input().trigger('change');
				
					// render
					self.render();
					
					// sync collapsed order
					//self.sync();
				}
			});
		},
		
		isCollapsed: function( $row ){
			return $row.hasClass('-collapsed');
		},
		
		collapse: function( $row ){
			$row.addClass('-collapsed');
			acf.doAction('hide', $row, 'collapse');
		},
		
		expand: function( $row ){
			$row.removeClass('-collapsed');
			acf.doAction('show', $row, 'collapse');
		},
		
		onClickCollapse: function( e, $el ){
			
			// vars
			var $row = $el.closest('.acf-row');
			var isCollpased = this.isCollapsed( $row );
			
			// shift
			if( e.shiftKey ) {
				$row = this.$rows();
			}
			
			// toggle
			if( isCollpased ) {
				this.expand( $row );
			} else {
				this.collapse( $row );
			}	
		},
		
		onShow: function( e, $el, context ){
			
			// get sub fields
			var fields = acf.getFields({
				is: ':visible',
				parent: this.$el,
			});
			
			// trigger action
			// - ignore context, no need to pass through 'conditional_logic'
			// - this is just for fields like google_map to render itself
			acf.doAction('show_fields', fields);
		},
		
		onUnload: function(){
			
			// vars
			var indexes = [];
			
			// loop
			this.$rows().each(function( i ){
				if( $(this).hasClass('-collapsed') ) {
					indexes.push( i );
				}
			});
			
			// allow null
			indexes = indexes.length ? indexes : null;
			
			// set
			preference.save( this.get('key'), indexes );
		},
		
		onHover: function(){
			
			// add sortable
			this.addSortable( this );
			
			// remove event
			this.off('mouseover');
		}
	});
	
	acf.registerFieldType( Field );
	
	
	// register existing conditions
	acf.registerConditionForFieldType('hasValue', 'repeater');
	acf.registerConditionForFieldType('hasNoValue', 'repeater');
	acf.registerConditionForFieldType('lessThan', 'repeater');
	acf.registerConditionForFieldType('greaterThan', 'repeater');
	
	
	// state
	var preference = new acf.Model({
		
		name: 'this.collapsedRows',
		
		key: function( key, context ){
			
			// vars
			var count = this.get(key+context) || 0;
			
			// update
			count++;
			this.set(key+context, count, true);
			
			// modify fieldKey
			if( count > 1 ) {
				key += '-' + count;
			}
			
			// return
			return key;
		},
		
		load: function( key ){
			
			// vars 
			var key = this.key(key, 'load');
			var data = acf.getPreference(this.name);
			
			// return
			if( data && data[key] ) {
				return data[key]
			} else {
				return false;
			}
		},
		
		save: function( key, value ){
			
			// vars 
			var key = this.key(key, 'save');
			var data = acf.getPreference(this.name) || {};
			
			// delete
			if( value === null ) {
				delete data[ key ];
			
			// append
			} else {
				data[ key ] = value;
			}
			
			// allow null
			if( $.isEmptyObject(data) ) {
				data = null;
			}
			
			// save
			acf.setPreference(this.name, data);
		}
	});
		
})(jQuery);

(function($){
	
	var Field = acf.Field.extend({
		
		type: 'flexible_content',
		wait: '',
		
		events: {
			'click [data-name="add-layout"]': 		'onClickAdd',
			'click [data-name="remove-layout"]': 	'onClickRemove',
			'click [data-name="collapse-layout"]': 	'onClickCollapse',
			'showField':							'onShow',
			'unloadField':							'onUnload',
			'mouseover': 							'onHover'
		},
		
		$control: function(){
			return this.$('.acf-flexible-content:first');
		},
		
		$layoutsWrap: function(){
			return this.$('.acf-flexible-content:first > .values');
		},
		
		$layouts: function(){
			return this.$('.acf-flexible-content:first > .values > .layout');
		},
		
		$layout: function( index ){
			return this.$('.acf-flexible-content:first > .values > .layout:eq(' + index + ')');
		},
		
		$clonesWrap: function(){
			return this.$('.acf-flexible-content:first > .clones');
		},
		
		$clones: function(){
			return this.$('.acf-flexible-content:first > .clones  > .layout');
		},
		
		$clone: function( name ){
			return this.$('.acf-flexible-content:first > .clones  > .layout[data-layout="' + name + '"]');
		},
		
		$actions: function(){
			return this.$('.acf-actions:last');
		},
		
		$button: function(){
			return this.$('.acf-actions:last .button');
		},
		
		$popup: function(){
			return this.$('.tmpl-popup:last');
		},
		
		getPopupHTML: function(){
			
			// vars
			var html = this.$popup().html();
			var $html = $(html);
			
			// count layouts
			var $layouts = this.$layouts();
			var countLayouts = function( name ){
				return $layouts.filter(function(){
					return $(this).data('layout') === name;
				}).length;
			};
						
			// modify popup
			$html.find('[data-layout]').each(function(){
				
				// vars
				var $a = $(this);
				var min = $a.data('min') || 0;
				var max = $a.data('max') || 0;
				var name = $a.data('layout') || '';
				var count = countLayouts( name );
				
				// max
				if( max && count >= max) {
					$a.addClass('disabled');
					return;
				}
				
				// min
				if( min && count < min ) {
					
					// vars
					var required = min - count;
					var title = acf.__('{required} {label} {identifier} required (min {min})');
					var identifier = acf._n('layout', 'layouts', required);
										
					// translate
					title = title.replace('{required}', required);
					title = title.replace('{label}', name); // 5.5.0
					title = title.replace('{identifier}', identifier);
					title = title.replace('{min}', min);
					
					// badge
					$a.append('<span class="badge" title="' + title + '">' + required + '</span>');
				}
			});
			
			// update
			html = $html.outerHTML();
			
			// return
			return html;
		},
		
		getValue: function(){
			return this.$layouts().length;
		},
		
		allowRemove: function(){
			var min = parseInt( this.get('min') );
			return ( !min || min < this.val() );
		},
		
		allowAdd: function(){
			var max = parseInt( this.get('max') );
			return ( !max || max > this.val() );
		},
		
		isFull: function(){
			var max = parseInt( this.get('max') );
			return ( max && this.val() >= max );
		},
		
		addSortable: function( self ){
			
			// bail early if max 1 row
			if( this.get('max') == 1 ) {
				return;
			}
			
			// add sortable
			this.$layoutsWrap().sortable({
				items: '> .layout',
				handle: '> .acf-fc-layout-handle',
				forceHelperSize: true,
				forcePlaceholderSize: true,
				scroll: true,
	   			stop: function(event, ui) {
					self.render();
	   			},
	   			update: function(event, ui) {
		   			self.$input().trigger('change');
		   		}
			});
		},
		
		addCollapsed: function(){
			
			// vars
			var indexes = preference.load( this.get('key') );
			
			// bail early if no collapsed
			if( !indexes ) {
				return false;
			}
			
			// loop
			this.$layouts().each(function( i ){
				if( indexes.indexOf(i) > -1 ) {
					$(this).addClass('-collapsed');
				}
			});
		},
		
		addUnscopedEvents: function( self ){
			
			// invalidField
			this.on('invalidField', '.layout', function(e){
				self.onInvalidField( e, $(this) );
			});
		},
		
		initialize: function(){
			
			// add unscoped events
			this.addUnscopedEvents( this );
			
			// add collapsed
			this.addCollapsed();
			
			// disable clone
			acf.disable( this.$clonesWrap(), this.cid );
						
			// render
			this.render();
		},
		
		render: function(){
			
			// update order number
			this.$layouts().each(function( i ){
				$(this).find('.acf-fc-layout-order:first').html( i+1 );
			});
			
			// empty
			if( this.val() == 0 ) {
				this.$control().addClass('-empty');
			} else {
				this.$control().removeClass('-empty');
			}
			
			// max
			if( this.isFull() ) {
				this.$button().addClass('disabled');
			} else {
				this.$button().removeClass('disabled');
			}
		},
		
		onShow: function( e, $el, context ){
			
			// get sub fields
			var fields = acf.getFields({
				is: ':visible',
				parent: this.$el,
			});
			
			// trigger action
			// - ignore context, no need to pass through 'conditional_logic'
			// - this is just for fields like google_map to render itself
			acf.doAction('show_fields', fields);
		},
		
		validateAdd: function(){
			
			// return true if allowed
			if( this.allowAdd() ) {
				return true;
			}
			
			// vars
			var max = this.get('max');
			var text = acf.__('This field has a limit of {max} {label} {identifier}');
			var identifier = acf._n('layout', 'layouts', max);
			
			// replace
			text = text.replace('{max}', max);
			text = text.replace('{label}', '');
			text = text.replace('{identifier}', identifier);
			
			// add notice
			this.showNotice({
				text: text,
				type: 'warning'
			});
			
			// return
			return false;
		},
		
		onClickAdd: function( e, $el ){
			
			// validate
			if( !this.validateAdd() ) {
				return false;
			}
			
			// within layout
			var $layout = null;
			if( $el.hasClass('acf-icon') ) {
				$layout = $el.closest('.layout');
				$layout.addClass('-hover');
			}
			
			// new popup
			var popup = new Popup({
				target: $el,
				targetConfirm: false,
				text: this.getPopupHTML(),
				context: this,
				confirm: function( e, $el ){
					
					// check disabled
					if( $el.hasClass('disabled') ) {
						return;
					}
					
					// add
					this.add({
						layout: $el.data('layout'),
						before: $layout
					});
				},
				cancel: function(){
					if( $layout ) {
						$layout.removeClass('-hover');
					}
					
				}
			});
			
			// add extra event
			popup.on('click', '[data-layout]', 'onConfirm');
		},
		
		add: function( args ){
			
			// defaults
			args = acf.parseArgs(args, {
				layout: '',
				before: false
			});
			
			// validate
			if( !this.allowAdd() ) {
				return false;
			}
			
			// add row
			var $el = acf.duplicate({
				target: this.$clone( args.layout ),
				append: this.proxy(function( $el, $el2 ){
					
					// append
					if( args.before ) {
						args.before.before( $el2 );
					} else {
						this.$layoutsWrap().append( $el2 );
					}
					
					// enable 
					acf.enable( $el2, this.cid );
					
					// render
					this.render();
				})
			});
			
			// trigger change for validation errors
			this.$input().trigger('change');
			
			// return
			return $el;
		},
		
		validateRemove: function(){
			
			// return true if allowed
			if( this.allowRemove() ) {
				return true;
			}
			
			// vars
			var min = this.get('min');
			var text = acf.__('This field requires at least {min} {label} {identifier}');
			var identifier = acf._n('layout', 'layouts', min);
			
			// replace
			text = text.replace('{min}', min);
			text = text.replace('{label}', '');
			text = text.replace('{identifier}', identifier);
			
			// add notice
			this.showNotice({
				text: text,
				type: 'warning'
			});
			
			// return
			return false;
		},
		
		onClickRemove: function( e, $el ){
			
			// vars
			var $layout = $el.closest('.layout');
			
			// add class
			$layout.addClass('-hover');
			
			// add tooltip
			var tooltip = acf.newTooltip({
				confirmRemove: true,
				target: $el,
				context: this,
				confirm: function(){
					this.removeLayout( $layout );
				},
				cancel: function(){
					$layout.removeClass('-hover');
				}
			});
		},
		
		removeLayout: function( $layout ){
			
			// reference
			var self = this;
			
			// vars
			var endHeight = this.getValue() == 1 ? 60: 0;
			
			// remove
			acf.remove({
				target: $layout,
				endHeight: endHeight,
				complete: function(){
					
					// trigger change to allow attachment save
					self.$input().trigger('change');
				
					// render
					self.render();
				}
			});
		},
		
		onClickCollapse: function( e, $el ){
			
			// vars
			var $layout = $el.closest('.layout');
			
			// toggle
			if( this.isLayoutClosed( $layout ) ) {
				this.openLayout( $layout );
			} else {
				this.closeLayout( $layout );
			}
		},
		
		isLayoutClosed: function( $layout ){
			return $layout.hasClass('-collapsed');
		},
		
		openLayout: function( $layout ){
			$layout.removeClass('-collapsed');
			acf.doAction('show', $layout, 'collapse');
		},
		
		closeLayout: function( $layout ){
			$layout.addClass('-collapsed');
			acf.doAction('hide', $layout, 'collapse');
			
			// render
			// - no change could happen if layout was already closed. Only render when closing
			this.renderLayout( $layout );
		},
		
		renderLayout: function( $layout ){
			
			// vars
			var $input = $layout.children('input');
			var prefix = $input.attr('name').replace('[acf_fc_layout]', '');
			
			// ajax data
			var ajaxData = {
				action: 	'acf/fields/flexible_content/layout_title',
				field_key: 	this.get('key'),
				i: 			$layout.index(),
				layout:		$layout.data('layout'),
				value:		acf.serialize( $layout, prefix )
			};
			
			// ajax
			$.ajax({
		    	url: acf.get('ajaxurl'),
		    	data: acf.prepareForAjax(ajaxData),
				dataType: 'html',
				type: 'post',
				success: function( html ){
					if( html ) {
						$layout.children('.acf-fc-layout-handle').html( html );
					}
				}
			});
		},
		
		onUnload: function(){
			
			// vars
			var indexes = [];
			
			// loop
			this.$layouts().each(function( i ){
				if( $(this).hasClass('-collapsed') ) {
					indexes.push( i );
				}
			});
			
			// allow null
			indexes = indexes.length ? indexes : null;
			
			// set
			preference.save( this.get('key'), indexes );
		},
		
		onInvalidField: function( e, $layout ){
			
			// open if is collapsed
			if( this.isLayoutClosed( $layout ) ) {
				this.openLayout( $layout );
			}
		},
		
		onHover: function(){
			
			// add sortable
			this.addSortable( this );
			
			// remove event
			this.off('mouseover');
		}
						
	});
	
	acf.registerFieldType( Field );
	
	
	
	/**
	*  Popup
	*
	*  description
	*
	*  @date	7/4/18
	*  @since	5.6.9
	*
	*  @param	type $var Description. Default.
	*  @return	type Description.
	*/
	
	var Popup = acf.models.TooltipConfirm.extend({
		
		events: {
			'click [data-layout]': 			'onConfirm',
			'click [data-event="cancel"]':	'onCancel',
		},
		
		render: function(){
			
			// set HTML
			this.html( this.get('text') );
			
			// add class
			this.$el.addClass('acf-fc-popup');
		}		
	});
	
	
	/**
	*  conditions
	*
	*  description
	*
	*  @date	9/4/18
	*  @since	5.6.9
	*
	*  @param	type $var Description. Default.
	*  @return	type Description.
	*/
	
	// register existing conditions
	acf.registerConditionForFieldType('hasValue', 'flexible_content');
	acf.registerConditionForFieldType('hasNoValue', 'flexible_content');
	acf.registerConditionForFieldType('lessThan', 'flexible_content');
	acf.registerConditionForFieldType('greaterThan', 'flexible_content');
	
	
	// state
	var preference = new acf.Model({
		
		name: 'this.collapsedLayouts',
		
		key: function( key, context ){
			
			// vars
			var count = this.get(key+context) || 0;
			
			// update
			count++;
			this.set(key+context, count, true);
			
			// modify fieldKey
			if( count > 1 ) {
				key += '-' + count;
			}
			
			// return
			return key;
		},
		
		load: function( key ){
			
			// vars 
			var key = this.key(key, 'load');
			var data = acf.getPreference(this.name);
			
			// return
			if( data && data[key] ) {
				return data[key]
			} else {
				return false;
			}
		},
		
		save: function( key, value ){
			
			// vars 
			var key = this.key(key, 'save');
			var data = acf.getPreference(this.name) || {};
			
			// delete
			if( value === null ) {
				delete data[ key ];
			
			// append
			} else {
				data[ key ] = value;
			}
			
			// allow null
			if( $.isEmptyObject(data) ) {
				data = null;
			}
			
			// save
			acf.setPreference(this.name, data);
		}
	});
	
})(jQuery);

(function($){
	
	var Field = acf.Field.extend({
		
		type: 'gallery',
		
		events: {
			'click .acf-gallery-add':			'onClickAdd',
			'click .acf-gallery-edit':			'onClickEdit',
			'click .acf-gallery-remove':		'onClickRemove',
			'click .acf-gallery-attachment': 	'onClickSelect',
			'click .acf-gallery-close': 		'onClickClose',
			'change .acf-gallery-sort': 		'onChangeSort',
			'click .acf-gallery-update': 		'onUpdate',
			'mouseover': 						'onHover',
			'showField': 						'render'
		},
		
		actions: {
			'validation_begin': 	'onValidationBegin',
			'validation_failure': 	'onValidationFailure',
			'resize':				'onResize'
		},
		
		onValidationBegin: function(){
			acf.disable( this.$sideData(), this.cid );
		},
		
		onValidationFailure: function(){
			acf.enable( this.$sideData(), this.cid );
		},
		
		$control: function(){
			return this.$('.acf-gallery');
		},
		
		$collection: function(){
			return this.$('.acf-gallery-attachments');
		},
		
		$attachments: function(){
			return this.$('.acf-gallery-attachment');
		},
		
		$attachment: function( id ){
			return this.$('.acf-gallery-attachment[data-id="' + id + '"]');
		},
		
		$active: function(){
			return this.$('.acf-gallery-attachment.active');
		},
		
		$main: function(){
			return this.$('.acf-gallery-main');
		},
		
		$side: function(){
			return this.$('.acf-gallery-side');
		},
		
		$sideData: function(){
			return this.$('.acf-gallery-side-data');
		},
		
		isFull: function(){
			var max = parseInt( this.get('max') );
			var count = this.$attachments().length;
			return ( max && count >= max );
		},
		
		getValue: function(){
			
			// vars
			var val = [];
			
			// loop
			this.$attachments().each(function(){
				val.push( $(this).data('id') );
			});
			
			// return
			return val.length ? val : false;
		},
		
		addUnscopedEvents: function( self ){
			
			// invalidField
			this.on('change', '.acf-gallery-side', function(e){
				self.onUpdate( e, $(this) );
			});
		},
		
		addSortable: function( self ){
			
			// add sortable
			this.$collection().sortable({
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
			this.$control().resizable({
				handles: 's',
				minHeight: 200,
				stop: function(event, ui){
					acf.update_user_setting('gallery_height', ui.size.height);
				}
			});
		},
		
		initialize: function(){
			
			// add unscoped events
			this.addUnscopedEvents( this );
			
			// render
			this.render();
		},
		
		render: function(){
			
			// vars
			var $sort = this.$('.acf-gallery-sort');
			var $add = this.$('.acf-gallery-add');
			var count = this.$attachments().length;
			
			// disable add
			if( this.isFull() ) {
				$add.addClass('disabled');
			} else {
				$add.removeClass('disabled');
			}
			
			// disable select
			if( !count ) {
				$sort.addClass('disabled');
			} else {
				$sort.removeClass('disabled');
			}
			
			// resize
			this.resize();
		},
		
		resize: function(){
			
			// vars
			var width = this.$control().width();
			var target = 150;
			var columns = Math.round( width / target );
						
			// max columns = 8
			columns = Math.min(columns, 8);
			
			// update data
			this.$control().attr('data-columns', columns);
		},
		
		onResize: function(){
			this.resize();
		},
		
		openSidebar: function(){
			
			// add class
			this.$control().addClass('-open');
			
			// hide bulk actions
			// should be done with CSS
			//this.$main().find('.acf-gallery-sort').hide();
			
			// vars
			var width = this.$control().width() / 3;
			width = parseInt( width );
			width = Math.max( width, 350 );
			
			// animate
			this.$('.acf-gallery-side-inner').css({ 'width' : width-1 });
			this.$side().animate({ 'width' : width-1 }, 250);
			this.$main().animate({ 'right' : width }, 250);
		},
		
		closeSidebar: function(){
			
			// remove class
			this.$control().removeClass('-open');
			
			// clear selection
			this.$active().removeClass('active');
			
			// disable sidebar
			acf.disable( this.$side() );
			
			// animate
			var $sideData = this.$('.acf-gallery-side-data');
			this.$main().animate({ right: 0 }, 250);
			this.$side().animate({ width: 0 }, 250, function(){
				$sideData.html('');
			});
		},
		
		onClickAdd: function( e, $el ){
			
			// validate
			if( this.isFull() ) {
				this.showNotice({
					text: acf.__('Maximum selection reached'),
					type: 'warning'
				});
				return;
			}
			
			// new frame
			var frame = acf.newMediaPopup({
				mode:			'select',
				title:			acf.__('Add Image to Gallery'),
				field:			this.get('key'),
				multiple:		'add',
				library:		this.get('library'),
				allowedTypes:	this.get('mime_types'),
				selected:		this.val(),
				select:			$.proxy(function( attachment, i ) {
					this.appendAttachment( attachment, i );
				}, this)
			});
		},
		
		appendAttachment: function( attachment, i ){
			
			// vars
			attachment = this.validateAttachment( attachment );
			
			// bail early if is full
			if( this.isFull() ) {
				return;
			}
			
			// bail early if already exists
			if( this.$attachment( attachment.id ).length ) {
				return;
			}
			
			// html
			var html = [
			'<div class="acf-gallery-attachment" data-id="' + attachment.id + '">',
				'<input type="hidden" value="' + attachment.id + '" name="' + this.getInputName() + '[]">',
				'<div class="margin" title="">',
					'<div class="thumbnail">',
						'<img src="" alt="">',
					'</div>',
					'<div class="filename"></div>',
				'</div>',
				'<div class="actions">',
					'<a href="#" class="acf-icon -cancel dark acf-gallery-remove" data-id="' + attachment.id + '"></a>',
				'</div>',
			'</div>'].join('');
			var $html = $(html);
			
			// append
			this.$collection().append( $html );
			
			// move to beginning
			if( this.get('insert') === 'prepend' ) {
				var $before = this.$attachments().eq( i );
				if( $before.length ) {
					$before.before( $html );
				}
			}
			
			// render attachment
			this.renderAttachment( attachment );
			
			// render
			this.render();
			
			// trigger change
			this.$input().trigger('change');
		},
		
		validateAttachment: function( attachment ){
			
			// defaults
			attachment = acf.parseArgs(attachment, {
				id: '',
				url: '',
				alt: '',
				title: '',
				filename: '',
				type: 'image'
			});
			
			// WP attachment
			if( attachment.attributes ) {
				attachment = attachment.attributes;
				
				// preview size
				var url = acf.isget(attachment, 'sizes', 'medium', 'url');
				if( url !== null ) {
					attachment.url = url;
				}
			}
			
			// return
			return attachment;
		},
		
		renderAttachment: function( attachment ){
			
			// vars
			attachment = this.validateAttachment( attachment );
			
			// vars
			var $el = this.$attachment( attachment.id );
			
			// image
			if( attachment.type == 'image' ) {
				
				// remove filename	
				$el.find('.filename').remove();
			
			// other (video)	
			} else {	
				
				// attempt to find attachment thumbnail
				attachment.url = acf.isget(attachment, 'thumb', 'src');
				
				// update filename
				$el.find('.filename').text( attachment.filename );
			}
			
			// default icon
			if( !attachment.url ) {
				attachment.url = acf.get('mimeTypeIcon');
				$el.addClass('-icon');
			}
			
			// update els
		 	$el.find('img').attr({
			 	src:	attachment.url,
			 	alt:	attachment.alt,
			 	title:	attachment.title
			});
		 	
			// update val
		 	acf.val( $el.find('input'), attachment.id );
		},
		
		editAttachment: function( id ){
			
			// new frame
			var frame = acf.newMediaPopup({
				mode:		'edit',
				title:		acf.__('Edit Image'),
				button:		acf.__('Update Image'),
				attachment:	id,
				field:		this.get('key'),
				select:		$.proxy(function( attachment, i ) {
					this.renderAttachment( attachment );
					// todo - render sidebar
				}, this)
			});
		},
		
		onClickEdit: function( e, $el ){
			var id = $el.data('id');
			if( id ) {
				this.editAttachment( id );
			}
		},
		
		removeAttachment: function( id ){
			
			// close sidebar (if open)
			this.closeSidebar();
			
			// remove attachment
			this.$attachment( id ).remove();
			
			// render
			this.render();
			
			// trigger change
			this.$input().trigger('change');
		},
		
		onClickRemove: function( e, $el ){
			
			// prevent event from triggering click on attachment
			e.preventDefault();
			e.stopPropagation();
			
			//remove
			var id = $el.data('id');
			if( id ) {
				this.removeAttachment( id );
			}
		},
		
		selectAttachment: function( id ){
			
			// vars
			var $el = this.$attachment( id );
			
			// bail early if already active
			if( $el.hasClass('active') ) {
				return;
			}
			
			// step 1
			var step1 = this.proxy(function(){
				
				// save any changes in sidebar
				this.$side().find(':focus').trigger('blur');
				
				// clear selection
				this.$active().removeClass('active');
				
				// add selection
				$el.addClass('active');
				
				// open sidebar
				this.openSidebar();
				
				// call step 2
				step2();
			});
			
			// step 2
			var step2 = this.proxy(function(){
				
				// ajax
				var ajaxData = {
					action: 'acf/fields/gallery/get_attachment',
					field_key: this.get('key'),
					id: id
				};
				
				// abort prev ajax call
				if( this.has('xhr') ) {
					this.get('xhr').abort();
				}
				
				// loading
				acf.showLoading( this.$sideData() );
				
				// get HTML
				var xhr = $.ajax({
					url: acf.get('ajaxurl'),
					data: acf.prepareForAjax(ajaxData),
					type: 'post',
					dataType: 'html',
					cache: false,
					success: step3
				});
				
				// update
				this.set('xhr', xhr);
			});
			
			// step 3
			var step3 = this.proxy(function( html ){
				
				// bail early if no html
				if( !html ) {
					return;
				}
				
				// vars
				var $side = this.$sideData();
				
				// render
				$side.html( html );
				
				// remove acf form data
				$side.find('.compat-field-acf-form-data').remove();
				
				// merge tables
				$side.find('> table.form-table > tbody').append( $side.find('> .compat-attachment-fields > tbody > tr') );	
								
				// setup fields
				acf.doAction('append', $side);
			});
			
			// run step 1
			step1();
		},
		
		onClickSelect: function( e, $el ){
			var id = $el.data('id');
			if( id ) {
				this.selectAttachment( id );
			}
		},
		
		onClickClose: function( e, $el ){
			this.closeSidebar();
		},
		
		onChangeSort: function( e, $el ){
			
			// vars
			var val = $el.val();
			
			// validate
			if( !val ) {
				return;
			}
			
			// find ids
			var ids = [];
			this.$attachments().each(function(){
				ids.push( $(this).data('id') );
			});
			
			// step 1
			var step1 = this.proxy(function(){
				
				// vars
				var ajaxData = {
					action: 'acf/fields/gallery/get_sort_order',
					field_key: this.get('key'),
					ids: ids,
					sort: val
				};
				
				
				// get results
			    var xhr = $.ajax({
			    	url:		acf.get('ajaxurl'),
					dataType:	'json',
					type:		'post',
					cache:		false,
					data:		acf.prepareForAjax(ajaxData),
					success:	step2
				});
			});
			
			// step 2
			var step2 = this.proxy(function( json ){
				
				// validate
				if( !acf.isAjaxSuccess(json) ) {
					return;
				}
				
				// reverse order
				json.data.reverse();
				
				// loop
				json.data.map(function(id){
					this.$collection().prepend( this.$attachment(id) );
				}, this);
			});
			
			// call step 1
			step1();
		},
		
		onUpdate: function( e, $el ){
			
			// vars
			var $submit = this.$('.acf-gallery-update');
			
			// validate
			if( $submit.hasClass('disabled') ) {
				return;
			}
			
			// serialize data
			var ajaxData = acf.serialize( this.$sideData() );
			
			// loading
			$submit.addClass('disabled');
			$submit.before('<i class="acf-loading"></i> ');
			
			// append AJAX action		
			ajaxData.action = 'acf/fields/gallery/update_attachment';
			
			// ajax
			$.ajax({
				url: acf.get('ajaxurl'),
				data: acf.prepareForAjax(ajaxData),
				type: 'post',
				dataType: 'json',
				complete: function(){
					$submit.removeClass('disabled');
					$submit.prev('.acf-loading').remove();
				}
			});
		},
		
		onHover: function(){
			
			// add sortable
			this.addSortable( this );
			
			// remove event
			this.off('mouseover');
		}
	});
	
	acf.registerFieldType( Field );
	
	// register existing conditions
	acf.registerConditionForFieldType('hasValue', 'gallery');
	acf.registerConditionForFieldType('hasNoValue', 'gallery');
	acf.registerConditionForFieldType('selectionLessThan', 'gallery');
	acf.registerConditionForFieldType('selectionGreaterThan', 'gallery');
	
})(jQuery);

// @codekit-prepend "../js/acf-field-repeater.js";
// @codekit-prepend "../js/acf-field-flexible-content.js";
// @codekit-prepend "../js/acf-field-gallery.js";