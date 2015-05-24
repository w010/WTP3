

// to rewrite in template code
//Socialhelper_ajax_url = '';
// is this used? we use Social.ajax_url


if (!Social) var Social = {

    DEV: false,

        // base url for cached interactions (results, views etc)
    	// generate and set it's value in plugin
    	ajax_url: '',
        // base url for non-cached actions
    	ajax_url_nocache: '',
    	// ajax_url: '/?type=944',
    	base_url: '',

	init: function()	{
		if (!$('.social'))  return;
		//console.log('social init!');
        this.base_url = $("base").attr("href"); // czy tak mozna? czy to jest pewne rozwiazanie?
		Socialhelper.init();
	},

    /**
     * Send non-cached ajax action
     * @param string action - action name7
     * @param array params - params for ajax url data
     * @param string containerAnimate - selector of element to animate can be passed. if not - default parent of caller is animated
     * @param dom element caller - button which called action
     * @param function successFunc
     */
	callAction: function(action, params, containerAnimate, caller, successFunc)    {
        var trigger = $(caller).parent();   // button wrap. usually it's the same as containerAnimate, but may be different.
		containerAnimate = containerAnimate ? $(containerAnimate) : $(trigger);
		//console.log(params);
		if (containerAnimate)
			Socialhelper.animationStart(containerAnimate);
		var data = {tx_wsocial_pi1: {ajaxType: 'action', action: action, params: params} };

		// add (or modify) request data for some actions
		data = Socialhelper.processRequestDataForAction(action, data);

		var conf = {
			//data: {tx_wsocial_pi1: {ajaxType: 'action', action: action, params: params} },
			data: data,

			successCallback: function(response) {
				//console.log('success callback for action!');
				//console.log(successFunc);

				try {
					var result = $.parseJSON(response);
					if (successFunc)    successFunc(result, caller);    // custom success function may be passed to call here
					//console.log(response, 'response');
					//console.log(result, 'result');
					//console.log(containerAnimate, 'containerAnimate');
					//console.log(result.sys_msg, 'sys_msg');
					// insert notice into alert
					if (result.notice)  $('#social_notice').show().find('.notice').html(result.notice);
					else                $('#social_notice').hide();
					$('#social_notice').attr('class', 'alert alert-dismissable alert-info');
					if (result.noticeLevel==0)     $('#social_notice').attr('class', 'alert alert-dismissable alert-success');
					if (result.noticeLevel==1)     $('#social_notice').attr('class', 'alert alert-dismissable alert-info');
					if (result.noticeLevel==2)     $('#social_notice').attr('class', 'alert alert-dismissable alert-warning');
					if (result.noticeLevel==3  ||  (!result.res && !result.noticeLevel))     $('#social_notice').attr('class', 'alert alert-dismissable alert-danger');
					if (!result.notice && !result.res)  $('#social_notice').show().find('.notice').html('unknown error, res false, no notice');
					// insert debugs
					if (result.debug)   $.each(result.debug, function(i, val){    Socialhelper.debugLog( "Ajax: " + val);  });
					if (result.errors)  $.each(result.errors, function(i, val){   Socialhelper.debugLog( "Ajax error: " + val);  });
					if (containerAnimate)   Socialhelper.animationStop(containerAnimate);
                    // replace button
                    // move to successFunc, also make counter update
                    if (result.replaceButton)   { trigger.replaceWith( result.replaceButton ); }
				} catch (e) {
					Socialhelper.handleError( e.message, e );
					Socialhelper.animationStop(containerAnimate);
				}

				Socialhelper.callbackForAction(action, params, result);
				return result;
			}
		};
		Socialhelper.request(conf, null, containerAnimate, true);
	},


	/**
	 * load-more buttons
	 * @param url - pass params as already built url, to enable chash
	 * @param caller - button/anchor js element
	 * @param specifiedContainer - can be set to load data into that element (and also animate it)
	 * @param replaceContent - don't append new items, replace old
     * @param confOptions - array additional settings
	 */
	getResults: function(url, caller, specifiedContainer, successFunc, replaceContent, confOptions)	{
		//console.log(url, 'URL');
		//console.log(conf, 'CONF');
        var trigger = $(caller).parent();   // button wrap
        var container;
		//  console.log(specifiedContainer, 'specifiedContainer');
		if (specifiedContainer)     container = $(specifiedContainer);
		// get first occurance of container 2 levels up (closest
		else                        container = $(caller).parent().parent().find('> .items-container');
		//else                        var container = $(caller).closest('.items-container');    // why this isn't working?
		//console.log(caller, 'caller - load trigger');
	    if (Social.DEV) console.log(container, 'CONTAINER TO LOAD INTO');
	//	container.css('border', '1px solid red');

		Socialhelper.animationStart(container);
		//$(inner).fadeTo('fast', 0.3);
		var conf = {
			// pass this in url, to make chash
			//data: {ajaxType: 'getResults', controller: controller, offset: offset},
			successCallback: function(response) {
				try {
						// if there are problems with disabling some debug comments, use this.
						//var result = $.parseJSON(response.split('<!-- Parsetime')[0]);
					var result = $.parseJSON(response);
						//console.log(result, 'result');

					// if needed, clear container
					if (replaceContent) $(container).html('');

					// PUT ITEMS INTO CONTAINER
						//$(container).append(result.res);

					// ANIMATED VERSION
						// put new items into dom (temporary container), start animation and then put into right place
                        var temp_container = $('<div class="temp hidden">');
                        temp_container.append( $(result.res) );
                        $(container).append( temp_container );
                        Socialhelper.animateItemsLoad( $(container).find('.temp > .item') );
                        //$(container).append( $(container).find('.temp > .item') );

                        if (confOptions && confOptions.loadMode == 'prepend')
                            $(container).prepend( $(container).find('.temp > *') );
                        else
                            $(container).append( $(container).find('.temp > *') );
                        temp_container.remove();


					if (successFunc)    successFunc(result, container);    // custom success function may be passed to call here

					// move clear tag to the end. caution - exact tag must be specified, not only class - can be more
					$(container).find('> br.clear').appendTo(container);
					// stop animation
					Socialhelper.animationStop(container);
					// insert notices
                        /*if (result.notice)  {
                            $('#social_notice .notice').html(result.notice);
                            $('#social_notice').attr('class', 'alert alert-dismissable alert-success').show();
                        }
                        else    $('#social_notice').hide();*/

					// insert debugs
					if (result.debug)   $.each(result.debug, function(i, val){    Socialhelper.debugLog( "Ajax: " + val);  });
					if (result.errors)  $.each(result.errors, function(i, val){   Socialhelper.debugLog( "Ajax error: " + val);  });
					// set new load onclick - url with changed offset. check if has .load - that means it's not called from outside
					//if (result.ajaxCall_load_newOffset && trigger.hasClass('load'))     $(caller).attr('onclick', result.ajaxCall_load_newOffset.replace('/&quot;/g', '"'));
					if (result.ajaxCall_load_newOffset && trigger.hasClass('load'))     $(caller).attr('onclick', result.ajaxCall_load_newOffset.replace( new RegExp("&quot;", "g"), '"') );
					// remove load button, if there's no more records
					if (!result.res && !result.errors.length)      $(caller).parent().html('<p>Więcej nie ma.</p>');
				} catch (e) {
					Socialhelper.handleError( e.message, e );
					Socialhelper.animationStop(container);
				}
				//console.log(result, 'result of request:');
			}
		};
		Socialhelper.request(conf, url, container, false);
	},


    /**
     * load-more buttons
     * @param caller - button/anchor js element
     * @param controller - controller name
     * @param displayMode - view display mode
     * @param string params - additional params
     * @param specifiedContainer - should be always given, if not result could be unexpected
     * @param function successFunc
     * @param bool replaceContent - don't append new items, replace old
     * @param confOptions - array additional settings. not used for now
     */
    getView: function(caller, controller, displayMode, params, specifiedContainer, successFunc, replaceContent, confOptions) {
        //console.log(successFunc);
        if (!params)            params = '';
        if (!displayMode)       displayMode = controller;
        replaceContent = true;  // i doubt it will ever be needed for anything. getView should always replace whole content, I think. but maybe not in some cases?
        // ten url udaje cached, ma cHash, ale nie zadziala jako cachowany - bo recznie dodajemy parametry. wiec (teoretycznie) zawsze zaciagnie swieze dane.
        // przewaga nad ajax_url jest brak no_cache
        var url = Social.ajax_url + '&no_debug=1&tx_wsocial_pi1[ajaxType]=getView&tx_wsocial_pi1[controller]='+controller+'&tx_wsocial_pi1[displayMode]='+displayMode + params;
        Social.getResults(url, caller, specifiedContainer, successFunc, replaceContent );
    }

};



if (!Socialhelper) var Socialhelper = {

	init: function()	{
		//if (Socialhelper_ajax_url)
		//	this.ajax_url = Socialhelper_ajax_url;
		if (!Social.ajax_url)     Socialhelper.handleError( "ERROR: ajax url not set. Use Social.ajax_url = 'url' in plugin code" );


		// make close buttons hide their parent on click
		$('.social .close').click(function(){   $(this).parent().hide();    });

		// TODO: to nie moze tu byc, ten onclick musi byc inline, bo ajax
		$('.social .button-hide').click(function(){   $(this).parent().parent().hide();    });

		$('.tx-wsocial-pi1 .debugdata').draggable().dblclick(function(){   $(this).css('height', 'auto'); });
	},


    request: function(conf, url, boxToAnimate, nocache)	{
        //console.log(conf, 'request conf');
        //console.log(url, 'custom request url');
        //console.log(Social.base_url, 'Social.base_url');
        //console.log(Social.ajax_url, 'Social.ajax_url');
        //console.log(this.base_url + (url?url:this.ajax_url) + '&no_debug=1', 'URL_FINAL!!!');
		var request = $.ajax({
			type:       "GET",
			//url:              'http://badanie.local/'+ url?url:this.ajax_url,
			//url:              this.base_url + url ? url : this.ajax_url,
			url:            Social.base_url + (url ? url : Social.ajax_url) + '&no_debug=1' + (nocache ? '&no_cache=1' : ''),
			//                  data: 'tx_wsocial_pi1[month_ajax]='+month,
			//                  data: { id : menuId },
			data:           conf.data ? conf.data : {}
			//                  dataType: "json",
			//                  dataType: "html"
			//success:
		})

		// this default method is not used, is overwritten in conf.successCallback
		.done(conf.successCallback ? conf.successCallback : function(res) {
			console.info('done!');
			if(parseInt(res)!=0)  {  // if no errors
				Socialhelper.animationStop(boxToAnimate);
			}
		})
		.fail(function( jqXHR, textStatus ) {
			console.error( "Request failed: " + textStatus );
			Socialhelper.handleError( "Request failed: " + textStatus );
		});
	},


        //
        // REQUEST HELPERS

	/**
	 * @param action
	 * @param data
	 * @returns {*}
	 */
	processRequestDataForAction: function(action, data) {
		switch (action) {
			case 'postArticle':
				// w taki sposób możemy tu dodawać parametry dla jakiegoś action
				//data.tx_wsocial_pi1["params"]["contentx"] = $('textarea#article_post_content').val();
			break;

		}
		return data;
	},

	callbackForAction: function (action, params, result)    {

	},


        //
        // ANIMATION

	animationStart: function(container)	{
		container.addClass('loading')
		.animate({
			opacity: 0.6
		}, 200, function() {
			// Animation complete.
		});
	},

	animationStop: function(container)	{
		container.removeClass('loading')
		.animate({
			opacity: 1
		}, 200, function() {
			// Animation complete.
		});
	},

	animateItemsLoad: function(items)   {
		$(items).each(function()	{
			$(this).css('opacity', 0).css('margin-left', '300px').animate({
				opacity: 1,
				'marginLeft': '0px'
			}, {
				duration: 300,
				specialEasing: {
				  opacity: "easeOutQuint",
				  marginLeft: "easeOutExpo"
				},
				complete: function() {
					// Animation complete.
					$(this).css('opacity', '').css('margin-left', '');
				}
			});
		});
	},



    // modal dialog
    openModal: function(caller, controller, content, className)   {
        if (!className)     className = 'modal_'+controller;

        //if (!$('.'+className))  {
            var modal = $('<div class="modal fade in '+className+'">')
            var dialog = $('<div class="modal-dialog">').appendTo(modal)
            var container = $('<div class="modal-content">').appendTo(dialog);

            $(caller).parent().prepend( modal );

            if (!content)       {
                Social.getView(caller, controller, '', '', container, function() {

                }, false, {});
            }
        /*}
        else    {
            var modal = $('.'+className);
        }*/

        $(modal).modal('show');
        return modal;
    },


        //
        // ARTICLE COMMENT

	showArticleCommentForm: function(caller, uid)    {
		var article = $(caller).parents('.item');
		if ( $(article).find('.commentform')[0] )  return false;
		var form = $('<div class="commentform clear">');
		var input = $('<textarea id="commentinput-'+uid+'" class="form-control">');
		var submit = $('<button id="commentsubmit-'+uid+'">').html( $(caller).html() );
		$(submit).click(function(){ Socialhelper.submitCommentform('#commentinput-'+uid, uid, form, caller);  });
		article.find('> .info').append( form.append(input).append(submit) );
		// scroll to input
		$('html, body').animate({   scrollTop: $('#commentinput-'+uid).offset().top - 100    }, 900);
		$($(article).find('.commentform textarea')).focus();
	},

	submitCommentform: function (textarea, articleUid, form, caller) {
        // tutaj caller nie jest tym, ktory submituje form, tylko nadal ten, ktory wywolal create form
        // czy to jest nam tu potrzebne?
		var el = $(textarea);
		var content = el.val();
		//console.log ('posting comment'); 		//console.log ('uid: '+articleUid); 		//console.log ('content: '+content);

			// definiujemy, co ma sie stac po udanym submicie
            // to powinno byc w osobnej metodzie, tak jak pozostale, dla spojnosci
			var successFunc = function(result)   {
					// console.log('SUBMIT SUCCESS. trying to reload comments');
				// jesli pozytywnie, pobieramy nowa liste komentarzy i nadpisujemy
				if (result && result.res) {
					//var container = $(caller).parent().parent().parent().find('> .items-container');    // for some reason it doesn't work
					var container = $(caller).parent().parent().find(' .items-container');

					// update licznika komentarzy po success
					var loadSuccessFunc = function(result, container)    {
						var counter = $(container).parent().find(' .counter');
						counter.html( container.find(' .item').length );
					}
					// pobieramy cala liste komentarzy i zastepujemy stare. getResults sam je pobiera i umieszcza gdzie wskazemy
					Social.getResults(result.reloadUrl, caller, container, loadSuccessFunc, true );
                    // clear input
                    $(form).find('textarea').val('');
				}
			}
		// wysylamy action
		Social.callAction('submitComment', {content: content, articleUid: articleUid}, form, caller, successFunc);

		// ? dodatkowo animujemy przesuniecie napisanego wlasnie tekstu w kierunku miejsca docelowego ?
	},



        //
        // MESSAGES

    /**
    * opens a conversation with selected user. it downloads messages list with header
    * @param caller
    * @param userUid
    * @returns {boolean}
    */
   	openConversation: function(caller, userUid)    {
        //console.log('openConversation');
        //console.log(userUid);
        // jesli przelaczamy zdalnie (np z new message form, to bedzie inny caller - nie mozemy sie na tym opierac, wiec znajdujemy go
        var userSelector = $('.peopleMsgList .person-'+userUid);
        // przelaczamy usera na liscie
        userSelector.parent().parent().find('.person').removeClass('active');
        userSelector.addClass('active');

        var successFunc = function()    {
            // scroll messages container to bottom
            $('#msg_messages .items-container').stop().animate({
              scrollTop: $('#msg_messages .items-container')[0].scrollHeight
            }, 800);
            // scroll viewport to input & focus
            $('html, body').animate({   scrollBottom: $('#message_input_content').offset().bottom - 500    }, 900);
            $('#message_input_content').focus();
        };

        // zaciagamy caly widok messages dla takiego usera
        Social.getView(caller, 'messages', 'messages', '&tx_wsocial_pi1[userUid]='+userUid, '#msg_messages', successFunc);
   	},

    /**
     * actually we use the form already existing there, but we add recipient selector
     * @param caller
     * @param uid
     * @returns {boolean}
     */
	showNewMessageForm: function(caller)    {

        $('.view.messages').removeClass('hidden');
		if ( $('#msg_messages').find('#newmessageform')[0] )  return false;

        //var input_content = $('#message_input_content');
		var form = $('<div id="newmessageform" class="clear">');
        var input_recipient_label = $('<span>Adresat: </span>');
        var input_recipient = $('<input type="text" id="message_input_recipient" class="form-control">')
            .autocomplete({
                //source: ["Adriana","Alessandra","Behati","Candice","Doutzen","Erin","Gisele"]
                source: function (request, response) {
                    $.get(Social.ajax_url+'&no_debug=1', {
                        tx_wsocial_pi1:{
                            ajaxType: 'autocomplete',
                            query: request.term,
                            for: 'peopleForNewConversation'
                        }
                    }, function (data) {
                        var result = $.parseJSON(data);
                        //console.log(result);
                        //response(result.res);
                        response( $.map( result.res, function( item ) {
                                return  {
                                        label: item.username, value: item.uid+' / '+item.username
                                }
                        }));
                    });
                },
                minLength: 2
            });

        // clear current conversation
        $('#msg_messages .load, #msg_messages .items-container').html('');

        // change submit onclick to other method which gets neccessery data before request
		$('#message_send').attr('onclick', "");
		$('#message_send').click( function(){  Socialhelper.submitNewMessageForm(this);  } );

        form.append(input_recipient_label, input_recipient).insertBefore('#msg_messages .items-container');

		// scroll to input
		$('html, body').animate({   scrollTop: $('#msg_messages').offset().top - 100    }, 900);
		$( 'textarea#message_input_content').focus();
	},


	submitNewMessageForm: function (caller) {
        // wysyla request z wiadomoscia i na success przelacza na nowa rozmowe

        var userName = $('#message_input_recipient').val();
        var userUid = userName.split(' / ')[0];
        console.log(userName);
        console.log(userUid);
        var successFunc = function(result)   {
        	//console.log('SUBMIT SUCCESS. if res, reload user list and switch to new conversation');
            //console.log(result);
        	// jesli pozytywnie:
            if (result && result.res) {
                // clear input
                $('#message_input_content').val('');
                // reload user list
                var reloadUsersSuccessFunc = function(result, container) {
                    console.log('users reload success. open conversation');
                    // and switch to this conversation
                    Socialhelper.openConversation(caller, userUid);
                };

                // todo: czemu to jest tak, a nie getView?
                //Socialhelper.getView(caller, 'peopleMsgList', 'peopleMsgList', '', '#msg_people', reloadUsersSuccessFunc);

                var url = Social.ajax_url + '&tx_wsocial_pi1[ajaxType]=getView&tx_wsocial_pi1[controller]=peopleMsgList&tx_wsocial_pi1[displayMode]=peopleMsgList';
                Social.getResults(url, caller, '#msg_people', reloadUsersSuccessFunc, true );
            }
        }
        // we pass userName instead of uid, but it's in "NN - name" format so can be intvaled
        // later the autocomplete should be reworked to set uid
        Socialhelper.submitMessageForm(caller, userName, successFunc)
    },

	submitMessageForm: function (caller, userUid, successFunc) {
		var el = $('#message_input_content');
		var content = el.val();
		var form = $('.view.messages'); // just to have find scope, its not really a form
		console.log ('posting message');

			// co ma sie stac po udanym submicie
            // to powinno byc w osobnej metodzie, tak jak pozostale, dla spojnosci
			successFunc = successFunc ? successFunc : function(result)   {

				// jesli pozytywnie, pobieramy wpis i dodajemy na koncu
				if (result && result.res) {
                    var container = $(caller).parent().parent().find('.items-container');

                    // pobieramy wpis
                    Social.getResults(result.reloadUrl, caller, container, null, false );
                    // scroll to
                    $(container).stop().animate({
                      scrollTop: $(container)[0].scrollHeight
                    }, 800);

                    // clear input after success
                    $(form).find('textarea').val('');
				}
			}
		// wysylamy action
		Social.callAction('sendMessage', {content: content, userUid: userUid}, '#message_input_content', caller, successFunc);

		// ? dodatkowo animujemy przesuniecie napisanego wlasnie tekstu w kierunku miejsca docelowego ?
	},

    submitUserSearchForm: function (caller) {
    		var el = $('#user_search_query');
    		var query = el.val();
    		var form = $('.view.messages'); // just to have find scope, its not really a form
    		//console.log ('searching for '+query);
    		// wysylamy action
    		//Social.getResults('sendMessage', {content: content, userUid: userUid}, '#message_input_content', caller, successFunc);
            var url = Social.ajax_url + '&no_debug=1&tx_wsocial_pi1[ajaxType]=getResults&tx_wsocial_pi1[controller]=userSearch&tx_wsocial_pi1[displayMode]=userSearch&tx_wsocial_pi1[query]='+query;
            Social.getResults(url, null, $('.userSearch .items-container'), null, true);
    	},


        // OTHER CALLBACKS

    // after join action
    updateGroupCounter: function(result, caller)    {
        var counter = $(caller).parent().parent().parent().find('.count.members span');
        if (result.res && result.sys_msg == 'JOINED')
            counter.html( parseInt(counter.html()) + 1 );
        if (result.res && result.sys_msg == 'LEFT')
            counter.html( parseInt(counter.html()) - 1 );
    },


    // after post action
    successPostArticle: function(result, caller)   {
        if (result.res && result.reloadUrl) {
            // clear input
            $('#article_post_content').val('');
            // get fresh article list
            Social.getResults(result.reloadUrl, caller, '.newArticleContainer > .items-container',
                function() {
                    // after getresults replace proper loadbutton, because when called external it doesn't replace itself
                    // czy to da sie jakos zalatwic, zeby jednak sobie poradzil?
                    if (result.ajaxCall_load_newOffset)         $('.newArticleContainer > .load a').attr('onclick', result.ajaxCall_load_newOffset);
                }, true );
        }
    },

    // after add friend action
    successAddFriend: function(result, caller)   {
        if (result.res && result.reloadUrl) {
            // get fresh friends list
            Social.getResults(result.reloadUrl, caller, '.newUserContainer > .items-container',
                function() {
                    // after getresults replace proper loadbutton, because when called external it doesn't replace itself
                    // czy to da sie jakos zalatwic, zeby jednak sobie poradzil?
                    if (result.ajaxCall_load_newOffset)         $('.newUserContainer > .load a').attr('onclick', result.ajaxCall_load_newOffset);
                }, true );
        }
    },


    successCreateGroup: function(result, caller)    {
        if (result.res) {
            // close modal
            $('.modal_groupAddForm').modal('hide');
            // get fresh my groups list
            Social.getView(caller, 'groupList', 'myGroups', '', '.groupList.myGroups', function(){});
        }
    },


        //
        // TECH

	handleError: function(msg, e)	{
        if (e)
            console.log (e.getStack());
		Socialhelper.debugLog('JS/AJAX ERROR: <span style="color: red;">'+msg+'</span>');
	},

	debugLog: function(data)	{
		$('.tx-wsocial-pi1 .debugdata')
			.append('<p>'+data+'</p>');
            // scroll to bottom
        if ($('.tx-wsocial-pi1 .debugdata')[0])
            $('.tx-wsocial-pi1 .debugdata').stop().animate({
              scrollTop: $('.tx-wsocial-pi1 .debugdata')[0].scrollHeight
            }, 300);
	}
};

