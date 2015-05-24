#übergibt das komplette Template-Rendering an TemplaVoilà
page = PAGE
page	{
	typeNum = 0

	10 = USER
	10.userFunc = tx_templavoila_pi1->main_page
	10.disableExplosivePreview = 1
	config.metaCharset = utf-8
	config.additionalHeaders = Content-Type:text/html;charset=utf-8
}

config	{
	#doctype = xhtml_trans
	doctype = html5
	metaCharset = utf-8
	forceCharset = utf-8
	renderCharset = utf-8

	disableAllHeaderCode = 1

	tx_realurl_enable = 1

	# is ok?
	absRefPrefix = http://example.com/
}

xmlprologue = none
xhtml_cleaning = none
htmlTag_setParams = none


tx_directmail_pi1.10 {
	template.file = fileadmin/templates/default/newsletter/plaintext.tmpl
}

