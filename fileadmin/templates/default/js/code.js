


/**
* wolo.pl jquery custom select tag / droplist css
*/
(function($){

	$.fn.dropdownCss = function(options)	{
		//$('.dropstyle select').each(function(index) {
		var defSettings = {
			itemsPerPage: 1,
			itemsPerTransition: 1
		};
		var options = $.extend({}, defSettings, options);
		$(this).each(function(index) {
			var currSelect = $(this);
			var newSel = $('<div class="select">')
				.click(function() { if (!newSel.hasClass('disabled')) newSelOptions.fadeOut().toggle();  })	// hide options list
				.css('position', 'relative');

				$('body').click(function(){newSelOptions.fadeOut().hide();});
				$(newSel).click(function(e){
				  e.stopPropagation();
				});

			var newSelinput = $('<input type="hidden">')
				.attr('name', currSelect.attr('name') )
				.addClass(currSelect.attr('class'));
			var newSelOptions = $('<div class="options">');
			var label = $('<div class="label">');


			currSelect.find('option').each(function(index) {
				var currOption = $(this);
				// take label & val from first, replace it later when item is 'selected'
				if (index==0)	{
					label.html( currOption.html() );
					newSelinput.val( currOption.val() );
				}
				if (currOption.attr('selected'))	{
					label.html( currOption.html() );
					newSelinput.val( currOption.val() );
				}
				var newOption = $('<div class="option">')
					.html( currOption.html() )
					.click( function(){
							newSelinput.val( currOption.val() );			// set hidden input value
							currSelect.trigger('change', newSelinput);		// call original select onchange
							label.html( currOption.html() );				// set label
					})
					.appendTo(newSelOptions);
			});
			// sklepujemy gotowy markup
			label.appendTo( newSel );
			newSelOptions.appendTo( newSel );
				newSel.appendTo( currSelect.parent() );
				newSelinput.appendTo( currSelect.parent() );
			currSelect.detach();	// not remove! because we use events from it
			newSelOptions.css('top', label.outerHeight());
		});
	}
})(jQuery);





/**
* wolo.pl jquery custom checkbox / radio css
*/
(function($){

	$.fn.checkradioCss = function(options)	{
		var defSettings = {defaultStyles:false};
		var options = $.extend({}, defSettings, options);
		//var previousName = '';									// name of preceeding input, for radio grouping into one hidden
		
		$(this).each(function(index) {
			var currInput = $(this);
			var currInputType = $(this).prop('type');
			//if (typeof newInput == 'undefined')
			//	var newInput;
			//	console.log(newInput);
			var newCheck = $('<div class="'+currInputType+'">')
				.addClass('input_'+currInput.prop('name').replace(/[\][]/g, ''))
				.click(function() {
					// CHECKBOX CLICK
					if (currInputType=='checkbox')	{
					
						if (!newInput.val())	{
							newCheck.addClass('active');				// set state to active
							newInput.val( currInput.val() );			// set hidden input value
						} else	{
							newCheck.removeClass('active');				// set state to deactive
							newInput.val('');							// unset hidden input value
						}
					}

					// RADIO CLICK
					if (currInputType=='radio')	{
						// uncheck other radios with that name
						$('.input_'+currInput.prop('name').replace(/[\][]/g, '')).each(function(index) {
							$(this).removeClass('active');
						});
						newCheck.addClass('active');				// set state to active
						//newInput.val( currInput.val() );			// set hidden input value
						$('input[type="hidden"][name="'+currInput.prop('name')+'"]').val( currInput.val() );
					}
					
					currInput.trigger('change', newInput);			// call original select onchange
				});
			
			if (options.defaultStyles)	{
				newCheck.css('border', '1px solid red')
				.css('width', '25px')
				.css('height', '25px');
			}

			// make hidden input for every checkbox or every radio group
//			if (currInputType=='checkbox'  ||  (currInputType=='radio' && previousName != currInput.prop('name')))	{
				var newInput = $('<input type="hidden">')
					.attr('name', currInput.attr('name') )
					.addClass(currInput.attr('class'));

				//previousName = currInput.prop('name');
//			}

			
			// sklepujemy gotowy markup
			newCheck.appendTo( currInput.parent() );
			// if exists hidden with that name, remove it - must be only one
			$('input[type="hidden"][name="'+currInput.attr('name')+'"]').remove();
			newInput.appendTo( currInput.parent() );
			// don't remove old input! we use events from it
			currInput.detach();	
		});
	}
})(jQuery);


// Avoid `console` errors in browsers that lack a console.
(function() {
    var method;
    var noop = function () {};
    var methods = [
        'assert', 'clear', 'count', 'debug', 'dir', 'dirxml', 'error',
        'exception', 'group', 'groupCollapsed', 'groupEnd', 'info', 'log',
        'markTimeline', 'profile', 'profileEnd', 'table', 'time', 'timeEnd',
        'timeStamp', 'trace', 'warn'
    ];
    var length = methods.length;
    var console = (window.console = window.console || {});

    while (length--) {
        method = methods[length];

        // Only stub undefined methods.
        if (!console[method]) {
            console[method] = noop;
        }
    }
}());



if (!wtp) var wtp = {


	init: function()	{
		wtp.checkIfHideAgeSplash();
		//wtp.makeMenuSlidingMarker();	// do it on windowready instead
		wtp.fixFancyboxAnchors();
		wtp.makeMoreButtonEndings();
		jw('select').dropdownCss({x:1});
	},

	
	/**
	*  standard typo3 anchors includes domain and/or page.
	*  for Fancybox open ce we need anchors alone.
	*  this should be before fancybox apply.
	*/
	fixFancyboxAnchors: function()	{
		$( $('.fancybox') ).each(function(index) {
			var anchor = $(this).attr('href').split('#')[1];
			if (anchor)
			$(this).attr('href', "#" + anchor);
		});
	},
	
	fixDownloads: function()	{
		$('.csc-uploads-fileName').each(function(){
			$(this).find('.csc-uploads-description').wrapInner('<a href="'+  $(this).find('a').prop('href')  +'"/>');
		});
	},

	equalizeColumns: function()	{
		var padding = 66+2;
		var col1H = $('.lay-sub-2col .col-1').outerHeight();
		var col2H = $('.lay-sub-2col .col-2').outerHeight();
//		console.log(col2H);
		if (col1H > col2H)	{
			$('.lay-sub-2col .col-2').css('height', col1H-padding);
		}
		else if (col2H > col1H)	{
			$('.lay-sub-2col .col-1').css('height', col2H-padding);
		}
	},

	checkIfHideAgeSplash: function()	{
		// show form layer - because it starts with visible overlay and hidden form to avoid form blink on start
		$('.agesplash-inner').css('display', 'block');
		
		// if confirmed, remove all splash layers
		if ($.cookie("age_confirmed"))	{
			wtp.hideAgeSplash(false, true);
		}
	},


	/**
	*  hide age splash form. can be after submit - with animation,
	*  or on start - without it
	*/
	hideAgeSplash: function(setCookie, noAnimation)	{
		if (setCookie)
			$.cookie("age_confirmed", "1", {
					expires: 30,
					path: "/"
			});

		if (noAnimation)	{
			//console.log('splash - hiding noanim');
			$('.agesplash-overlay').remove();
			$('.agesplash-inner').remove();
			return;
		}

		// hide splash		
		$('.agesplash-overlay').animate({
			opacity: 0
		}, 300, function() {
			$('.agesplash-overlay').remove();
		});
		$('.agesplash-inner').animate({
			opacity: 0
		}, 300, function() {
			$('.agesplash-inner').remove();
		});

	},

	
	/**
	*  jezdzacy punkt pod pozycjami menu, ustawiajacy sie na aktywnej / hover
	*/
	makeMenuSlidingMarker: function()	{
		// okreslamy polozenie markera
		var marker_x = wtp.findMenuMarkerPosition('.act');

		// jesli x=0, znaczy zadna nie jest aktywna lub blad
		if (!marker_x)	marker_x = -20;
		$('#menu-marker').css("display", "block");

		// ustawiamy marker na biezacej
		$('#menu-marker').css("marginLeft", marker_x+"px");

		$('.level-1 > li').hover(
		  function () {
		  	// przesuwamy
		    $('#menu-marker').animate({
				marginLeft:  wtp.findMenuMarkerPosition(this)+"px"
			}, {duration: 500, queue: false}, function() {});

			// kolor hover
			$('#menu-marker').css("backgroundPosition", "0 -11px");
		  },
		  
		  function () {
		  	// wracamy
		   	$('#menu-marker').animate({
				marginLeft:  marker_x
			}, {duration: 500, queue: false}, function() {});


			// zwykly kolor
			$('#menu-marker').css("backgroundPosition", "0 0");
		  }
		);
	},

	
	// szukamy naszego obiektu (pozycji menu) i liczymy szerokosci poprzednich + 0.5 jego (srodek)
	findMenuMarkerPosition: function(obj)	{
		var x = 0;
		var found = false;
		$( $('.level-1 > li') ).each(function(index) {
			// jesli znaleziono wybrany
			if ($(this).is(obj))	{
				// get half its width and exit
				x += $(this).width() / 2;
				found = true;
				return false;
			}
			// szukamy dalej
			x += $(this).outerWidth();// + wtp.menuPositionMargin;
		});
		if (!found)	return 0;
		// korekta przesuniecia - na srodek pozycji
		x -= $('#menu-marker').width() / 2;
		return x;
	},
	
	
	breakLongMenuItems: function(length)	{
		// Line Splitter Function
		// copyright Stephen Chapman, 19th April 2006
		// you may copy this code but please keep the copyright notice as well
		splitLine = function splitLine(st, n) {var b = ''; var s = st;while (s.length > n) {var c = s.substring(0,n);var d = c.lastIndexOf(' ');var e =c.lastIndexOf('\n');if (e != -1) d = e; if (d == -1) d = n; b += c.substring(0,d) + '\n';s = s.substring(d+1);}return b+s;};
		jw( '#menu-main > ul > li > a' ).each(function(index) {
			jw(this).html(   splitLine(jw(this).html(), length).replace(/\n/g,'<br>')    );
		});
	},
	
	
	// dodajemy koncowki do buttonow
	makeMoreButtonEndings: function()	{
		$( '.more' ).each(function(index) {
			$(this).css('position', 'relative');
			var height = $(this).outerHeight();
			$(this).html('<span class="buttontext">'+$(this).html()+'</span><span class="end" style="height:'+height+'px; width:1px; position:absolute; top:0; right:0;"></span>')
		});
	},

	makeMoreButtonFromSubmit: function()	{
		$( '.tx-powermail-pi1 input[type=submit], #subscribe_box input[type=submit]' ).each(function(index) {
			var button = $(this);
			var more = $('<a class="more" href="">'+button.val()+'</a>')
				.click(function(){button.click(); return false;})
				.appendTo(button.parent());
			button.css('display', 'none');
		});
	}
}


/* 1. dom ready (same as $(function(){} ) */
$(document).ready(function() {
    //wtp.init();
	//wtp.initMenu();
});

/* 2. resources loaded page rendered and assume sizes calculated - ready for resizing actions */
$(window).load(function() {
	/*wtp.initHeaders();
	wtp.equalizeProducts();
	wtp.equalizeColumns();*/
	
	/*setTimeout( function() {
			wtp.initHeaders();
		}, 400
		);*/
});


/* there's no such thing */
//$(window).ready(function() {
