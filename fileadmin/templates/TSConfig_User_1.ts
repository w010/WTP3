#
# settings for Default Domain beusergroup


setup.override.edit_wideDocument = 1
setup.override.navFrameResizable = 1
setup.default.navFrameWidth = 300
setup.default.thumbnailsByDefault = 1

options.pageTree.showDomainNameWithTitle = 1
options.pageTree.showNavTitle = 1
options.pageTree.hideFilter = 0

mod.web_list {
   clickTitleMode = edit
   alternateBgColors = 1
   hideTables = static_template,static_currencies,static_taxes,static_markets,static_countries
}

# Domyslnie (prawie ;) wszystkie nowe tresci ukryte
TCAdefaults {
	#pages.hidden = 1
	#tt_content.hidden = 1
	#tt_news.hidden = 1
	#tt_news_cat.hidden = 1
}

options.clearCache.system = 1
options.clearCache.all = 1
