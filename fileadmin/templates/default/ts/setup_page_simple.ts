page.headerData {
	1 > # YUI
	21 > # RSS for news
	32 > # hi_yui_accordion
	784 > # hi_yui_accordion
	785 > # hi_yui_accordion
	786 > # accordion
	791 > # accordion
	2013 > # hi_yui_accordion
	89 > 
	90 > # IE6 css
	91 >
	667 >
	2010 > 
	2015 >
	2020 > # tabview
	2030 > # tabview
	
	# 1230 > # shadowbox
	
	10000.value = <link rel="stylesheet" type="text/css" href="fileadmin/templates/shared/css/simple.css" /> 
}

page.includeLibs {
	hi_tabs >
}

plugin.tx_templavoila_pi1.childTemplate = simple
page.10 >
page.10 < plugin.tx_templavoila_pi1
page.10.userFunc = tx_templavoila_pi1->main_page

#
# To speed up: remove all unneeded libs...
#
lib.side-menu >
lib.main-side-l >
lib.main-side-r >
lib.main-before >
lib.main-after >
lib.page-meta >
lib.page-foot >
lib.page-foot.5 >
lib.page-foot.7 >

lib.page-menu >
lib.breadcrumb >
lib.page-submenu-1 >
lib.page-submenu-2 >

#
# Delete default wrap
# Add [source] link (@see Global snippets storage)
#
lib.main-content.wrap >
lib.main-content.stdWrap.postCObject = RECORDS
lib.main-content.stdWrap.postCObject {
		tables = tt_content
		source = 4236
}