/**
 * Add collapseable boxes to our editor screens.
 */
postboxes.add_postbox_toggles(pagenow);

/**
 * The rest of our customizations.
 */
(function($) {

	$('#cptui_select_post_type_submit').hide();
	$('#cptui_select_taxonomy_submit').hide();

	if ('edit' === getParameterByName('action')) {
		// Store our original slug on page load for edit checking.
		var original_slug = $('#name').val();
	}

	// Switch to newly selected post type or taxonomy automatically.
	$('#post_type').on('change',function(){
		$('#cptui_select_post_type').submit();
	});

	$('#taxonomy').on('change',function(){
		$( '#cptui_select_taxonomy' ).submit();
	});

	// Confirm our deletions
	$('#cpt_submit_delete').on('click',function() {
		if ( confirm( cptui_type_data.confirm ) ) {
			return true;
		}
		return false;
	});

	// Toggles help/support accordions.
	$('#support .question').each(function() {
		var tis = $(this), state = false, answer = tis.next('div').slideUp();
		tis.on('click keydown',function(e) {
			// Helps with accessibility and keyboard navigation.
			if(e.type==='keydown' && e.keyCode!==32 && e.keyCode!==13) {
				return;
			}
			e.preventDefault();
			state = !state;
			answer.slideToggle(state);
			tis.toggleClass('active',state);
			tis.attr('aria-expanded', state.toString() );
			tis.focus();
		});
	});

	// Switch spaces for underscores on our slug fields.
	$('#name').on('keyup',function(e){
		var value, original_value;
		value = original_value = $(this).val();
		if ( e.keyCode !== 9 && e.keyCode !== 37 && e.keyCode !== 38 && e.keyCode !== 39 && e.keyCode !== 40 ) {
			value = value.replace(/ /g, "_");
			value = value.toLowerCase();
			value = replaceDiacritics(value);
			value = transliterate(value);
			value = replaceSpecialCharacters(value);
			if ( value !== original_value ) {
				$(this).attr('value', value);
			}
		}

		//Displays a message if slug changes.
		if(undefined != original_slug) {
			var $slugchanged = $('#slugchanged');
			if(value != original_slug) {
				$slugchanged.removeClass('hidemessage');
			} else {
				$slugchanged.addClass('hidemessage');
			}
		}
	});

	// Replace diacritic characters with latin characters.
	function replaceDiacritics(s) {
		var diacritics = [
			/[\300-\306]/g, /[\340-\346]/g,  // A, a
			/[\310-\313]/g, /[\350-\353]/g,  // E, e
			/[\314-\317]/g, /[\354-\357]/g,  // I, i
			/[\322-\330]/g, /[\362-\370]/g,  // O, o
			/[\331-\334]/g, /[\371-\374]/g,  // U, u
			/[\321]/g, /[\361]/g, // N, n
			/[\307]/g, /[\347]/g  // C, c
		];

		var chars = ['A', 'a', 'E', 'e', 'I', 'i', 'O', 'o', 'U', 'u', 'N', 'n', 'C', 'c'];

		for (var i = 0; i < diacritics.length; i++) {
			s = s.replace(diacritics[i], chars[i]);
		}

		return s;
	}

	function replaceSpecialCharacters(s) {

		s = s.replace(/[^a-z0-9\s]/gi, '_');

		return s;
	}

	var cyrillic = {
		"Ё": "YO", "Й": "I", "Ц": "TS", "У": "U", "К": "K", "Е": "E", "Н": "N", "Г": "G", "Ш": "SH", "Щ": "SCH", "З": "Z", "Х": "H", "Ъ": "'", "ё": "yo", "й": "i", "ц": "ts", "у": "u", "к": "k", "е": "e", "н": "n", "г": "g", "ш": "sh", "щ": "sch", "з": "z", "х": "h", "ъ": "'", "Ф": "F", "Ы": "I", "В": "V", "А": "a", "П": "P", "Р": "R", "О": "O", "Л": "L", "Д": "D", "Ж": "ZH", "Э": "E", "ф": "f", "ы": "i", "в": "v", "а": "a", "п": "p", "р": "r", "о": "o", "л": "l", "д": "d", "ж": "zh", "э": "e", "Я": "Ya", "Ч": "CH", "С": "S", "М": "M", "И": "I", "Т": "T", "Ь": "'", "Б": "B", "Ю": "YU", "я": "ya", "ч": "ch", "с": "s", "м": "m", "и": "i", "т": "t", "ь": "'", "б": "b", "ю": "yu"
	};

	function transliterate(word) {
		return word.split('').map(function (char) {
			return cyrillic[char] || char;
		}).join("");
	}

	if ( undefined != wp.media ) {
		var _custom_media = true,
			_orig_send_attachment = wp.media.editor.send.attachment;
	}

	function getParameterByName(name, url) {
		if (!url) url = window.location.href;
		name = name.replace(/[\[\]]/g, "\\$&");
		var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
			results = regex.exec(url);
		if (!results) return null;
		if (!results[2]) return '';
		return decodeURIComponent(results[2].replace(/\+/g, " "));
	}

	$('#cptui_choose_icon').on('click',function(e){
		e.preventDefault();

		var button = $(this);
		var id = jQuery('#menu_icon').attr('id');
		_custom_media = true;
		wp.media.editor.send.attachment = function (props, attachment) {
			if (_custom_media) {
				$("#" + id).val(attachment.url);
			} else {
				return _orig_send_attachment.apply(this, [props, attachment]);
			}
		};

		wp.media.editor.open(button);
		return false;
	});

	$('#togglelabels').on('click',function(e){
		e.preventDefault();
		$('#labels_expand').toggleClass('toggledclosed');
	});
	$('#togglesettings').on('click',function(e) {
		e.preventDefault();
		$('#settings_expand').toggleClass('toggledclosed');
	});
	$('#labels_expand,#settings_expand').on('focus',function(e) {
		if ( $(this).hasClass('toggledclosed') ) {
			$(this).toggleClass('toggledclosed');
		}
	});
	$('#labels_expand legend,#settings_expand legend').on('click',function(e){
		$(this).parent().toggleClass('toggledclosed');
	});
	$('.cptui-help').on('click',function(e){
		e.preventDefault();
	});

	$('.cptui-taxonomy-submit').on('click',function(e){
		if ( $('.cptui-table :checkbox:checked').length == 0 ) {
			e.preventDefault();
			alert( cptui_tax_data.no_associated_type );
		}
	});
})(jQuery);
