
# for reuse
lib.CE = RECORDS
lib.CE.tables = tt_content
lib.CE.dontCheckPid = 1

#lib.CE = CONTENT
#lib.CE.table = tt_content
#lib.CE.select	{
#	pidInList = this
#	where = 1=2
#}

# for pass ce uid in "data" param in fluid
lib.CE_fluid < lib.CE
lib.CE_fluid.source.current = 1


#
# MAIN CONTAINER
# 

lib.content = COA
#lib.content.10 < lib.breadcrumb
lib.content.20 = RECORDS
lib.content.20.source.current = 1
lib.content.20.tables = tt_content
lib.content.20.wrap = <!--TYPO3SEARCH_begin--> | <!--TYPO3SEARCH_end-->

# Other containers

# left - referenced as slided example
lib.contentLeft < lib.content
lib.contentLeft {
	20.source >
	20.source.postUserFunc = thinkopen_at\KbTvContSlide\SlideController->main
	20.source.postUserFunc.field = main-content-left
	20.source.postUserFunc.table = tt_content
}
lib.contentRight < lib.contentLeft
lib.contentRight.source.postUserFunc.field = main-content-right

# top - referenced as non-slided example
lib.contentTop < lib.contentLeft
lib.contentRight.source.postUserFunc.field = main-content-top

#lib.contentBottom < lib.content
#lib.contentHead < lib.content


#[globalVar= GP:tx_ttnews|tt_news > 0 ]
#	lib.content >
#	lib.content < plugin.tt_news
#	lib.content.code >
#	lib.content.code = SINGLE
#[global]



# example optionally insert something before by making it COA array

#temp.libcont < lib.content
#lib.content >
#lib.content = COA
#lib.content.5 < lib.breadcrumb
#lib.content.10 < temp.libcont




lib.main-before = COA
lib.main-after = COA


#
# page logo in header, linked to homepage
#
lib.head-logo < lib.CE
lib.head-logo.source = 3

#
# additional content in div#page-head
#
lib.page-meta = COA
#lib.page-meta.10 < lib.lang-menu
lib.page-meta.15 < lib.searchbox
#temp.lib.page-meta.20 < lib.CE
#temp.lib.page-meta.20 {
#	source = 57
#	wrap = <div id="page-meta-image">|</div>
#	required = 1
#}


#[treeLevel = 1,2,3,4,5]
#lib.page-meta.20 >
#[end]


#
# footer div#page-foot
#
lib.page-foot = COA
lib.page-foot {
	10 = COA
	10	{
		10 < lib.CE
		10.source = 7
	}
	10.wrap = <div class="col-xs-6"> | </div>
	
	#15 < lib.menu-footer

	# copyrights
	20 = COA
	20	{
		10 = TEXT
		10.value = <p class="pull-left"> {date:Y} - WTP </p>
		10.insertData = 1

		20 = TEXT
		20.value = <p class="pull-right"> wolo.pl TYPO3 pack  </p>
		
		#20 < lib.CE
		#20.source = 8
		#20.wrap = <nav> | </nav>

		99 = TEXT
		99.value = <br class="clear">
	}
	20.wrap = <div class="col-xs-6"> | </div>

	#99 = TEXT
	#99.value = <br class="clear">
}



