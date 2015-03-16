config.pageTitleFirst = 1

# Title is in format [Page title : Title from TS Title]
# But usually level0 title is the same as in TS record, so here we disable it
[treeLevel = 0]
	config.noPageTitle = 1
[treeLevel = 1,2,3,4,5]
	plugin.tx_seobasics.10.stdWrap.stdWrap.append.data = leveltitle:0
[end]


#
# Inherited META keywords, description
#
lib.mergedKeywords = COA
lib.mergedKeywords {
  10 = TEXT
  10.data = fullRootLine:0,keywords
  10.wrap = |,
  10.required = 1
  20 < .10
  20.data = fullRootLine:1,keywords
  30 < .10
  30.data = fullRootLine:2,keywords
  40 < .10
  40.data = fullRootLine:3,keywords
}
lib.mergedDesc = COA
lib.mergedDesc {
  10 = TEXT
  10.data = fullRootLine:0,description
  10.wrap = |
  10.required = 1
  20 < .10
  20.data = fullRootLine:1,description
  30 < .10
  30.data = fullRootLine:2,description
  40 < .10
  40.data = fullRootLine:3,description
}

[globalVar= GP:tx_ttnews|tt_news < 1 ]
page.meta.keywords.cObject < lib.mergedKeywords
page.meta.description.cObject < lib.mergedDesc
[end]
