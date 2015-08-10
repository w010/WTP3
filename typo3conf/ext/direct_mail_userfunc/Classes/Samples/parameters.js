/**
 * Sample code to show a parameter dialog with ExtJS
 *
 * $Id$
 */
Ext.onReady(function(){
	var win;
	var button = Ext.get('params-btn');
	
	button.on('click', function(){
		Ext.Msg.prompt(
			"Parameters for myRecipientList",
			"How many items do you want in your list?",
			function(btn, text) {
				if (btn == 'ok'){
					updateParameters(text);
				}
			},
			this, false, dmuf_parameters.value
		);
	});
});    			