#
# settings for Content Editor beusergroup
# this is default group for every user and subgroup of Content Admin (uid=3)


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
   hideTables = static_template,static_currencies,static_taxes,static_markets
}

# Domyslnie (prawie ;) wszystkie nowe tresci ukryte
TCAdefaults {
	#pages.hidden = 1
	#tt_content.hidden = 1
	#tt_news.hidden = 1
	#tt_news_cat.hidden = 1
}

[globalVar= ENV:LOCAL=1]
	TCAdefaults {
		pages.hidden = 0
		tt_content.hidden = 0
		tt_news.hidden = 0
		tt_news_cat.hidden = 0
	}
[end]


[PIDupinRootline=275]
admPanel {
	hide = 1
	override {
		preview = 1
		preview.showHiddenPages = 1
		preview.showHiddenRecords = 1
	}
}
[end]
