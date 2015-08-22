plugin.tt_news {
	# cat=plugin.tt_news/file/1; type=file[html,htm,tmpl,txt]; label= Template File: HTML-template file for display of news. See EXT:tt_news/pi/tt_news_v2_template.html for an example
	file.templateFile = fileadmin/templates/default/html/tx_ttnews.html
	
	# cat=plugin.tt_news/links/3; type=text; label= Starting Point (pid_list): The PID of the page (or comma separated list pf PIDs) where your news articles are located.
	pid_list = 10
	
	# cat=plugin.tt_news/links/5; type=int+; label= SinglePid: The PID of the page on which single news items should be displayed (since tt_news v1.6.0 this var is called 'singlePid'. The old var-name 'PIDitemDisplay' does not work anymore).
	singlePid = 11

	# cat=plugin.tt_news/links/9; type=int+; label= BackPid: The PID of the page to go back to from single news item display screen.
	backPid = 
	
	# cat=plugin.tt_news/links/7; type=text; label= Archive Link parameter: The PID of the page with a LIST element that displays only archived news items. This link appears at the bottom of the 'LATEST template and it is also used for the links in the AMENU (see: http://typo3.org/documentation/document-library/doc_core_tsref/typolink/ section 'parameter' for more information)
	archiveTypoLink.parameter =
	
	# cat=plugin.tt_news//10; type=int+; label= datetime Days to Archive: If this is set, elements are automatically in the archive, after the given number of days has passed. Set this to '0' if you want to disable this feature.
	datetimeDaysToArchive = 0
	
	# cat=plugin.tt_news//20; type=int+; label= List Limit: max items in LIST template.
	limit = 10
	
	# cat=plugin.tt_news//30; type=int+; label= Latest Limit: max news items in LATEST template.
	latestLimit = 3
	
	# cat=plugin.tt_news//40; type=int+; label= Category Text mode: posible values are: 0 = don't display, 1 = display but no link, 2 = link to category shortcut, 3 = act as category selector.
	catTextMode = 0
	
	# cat=plugin.tt_news//50; type=int+; label= Category Image mode: same values as catTextMode.
	catImageMode = 0
	
	# cat=plugin.tt_news/enable/1; type=boolean; label= Use human readable dates: This enables the use of the GETvars 'year' and 'month' for the archive links instead of the non-readable 'pS', 'pL' and 'arc'.
	useHRDates = 0
	
	# cat=plugin.tt_news/enable/2; type=boolean; label= Use Multipage Single View: Enable this if you want to divide the news SINGLE view to multiple pages.
	useMultiPageSingleView = 0
	
	# cat=plugin.tt_news/enable/3; type=boolean; label= Use bidirectional relations: If this is enabled the SINGLE view shows the the relations of news in both directions. The relation which points back to the source record will be inserted automatically.
	useBidirectionalRelations = 1
	
	# cat=plugin.tt_news/enable/4; type=boolean; label= Use Pages as related news: If this is enabled the SINGLE view shows also relations to pages.
	usePagesRelations = 0
  	
  	# cat=plugin.tt_news/enable/5; type=boolean; label= Use subcategories: Enable this if news should also be selected for display if they are a member of a subcategory of the selected category. Works only if categoryMode is 1 (="Show items with selected categories").
	useSubCategories = 0
 	
 	# cat=plugin.tt_news/enable/6; type=boolean; label= Display subcategories: If this is enabled the subcategories of the categories that are assigned to a news record will also be displayed in news records in the FrontEnd. If displayed categories in a news record are subcategories they will be wrapped with "subCategoryTitleItem_stdWrap" (titles) or "subCategoryImgItem_stdWrap" (images).
	displaySubCategories = 0
  	
  	# cat=plugin.tt_news/enable/7; type=boolean; label= show related news by category: If this is enbaled the SINGLE view shows a list of news with the same category as the current news record.
	showRelatedNewsByCategory = 0
   
    # cat=plugin.tt_news/enable/8; type=boolean; label= Allow Caching: Allow caching of displayed news? If you want your news being indexed by the indexed-search this has to be enabled.
	allowCaching = 1
   
    # cat=plugin.tt_news/enable/9; type=boolean; label= Use singlePid from category: If this is enabled tt_news uses the singlePid from the first assigned category.
	useSPidFromCategory = 1
   
    # cat=plugin.tt_news/enable/10; type=boolean; label= Show category rootline: Enable this to show the category rootline in the SINGLE or LIST view.
	showCatRootline = 0
	
	# cat=plugin.tt_news/dims/110; type=int+; label= single-image max Width: Max width for an image displayed in SINGLE template
	singleMaxW = 550
   
    # cat=plugin.tt_news/dims/120; type=int+; label= single-image max height: Max height for an image displayed in SINGLE template
	singleMaxH = 550
   
    # cat=plugin.tt_news/dims/130; type=int+; label= latest-image max width: Max width for an image displayed in LATEST template
	latestMaxW = 125
	
    # cat=plugin.tt_news/dims/140; type=int+; label= latest-image max height: Max height for an image displayed in LATEST template
	latestMaxH = 300
	
	# cat=plugin.tt_news/dims/150; type=int+; label= list-image max width: Max width for an image displayed in LIST template
	listMaxW = 150
	
	# cat=plugin.tt_news/dims/160; type=int+; label= list-image max height: Max height for an image displayed in LIST template
	listMaxH = 300


  displayXML { 
    # rss091_tmplFile = EXT:tt_news/res/rss_0_91.tmpl 
    # rdf_tmplFile = EXT:tt_news/res/rdf.tmpl 
    # atom03_tmplFile = EXT:tt_news/res/atom_0_3.tmpl 
    # atom1_tmplFile = EXT:tt_news/res/atom_1_0.tmpl 
    rss2_tmplFile = EXT:tt_news/res/rss_2.tmpl 
    # possibile values: rss091 / rss2 / rdf / atom03 / atom1  
    xmlFormat = rss2 
    xmlTitle = wiadomości
    xmlLink = http://dev.xxx.pl
    xmlDesc = Wiadomości 
    xmlLang = pl
    xmlLimit = 10
    xmlIcon = typo3conf/ext/tt_news/res/tt_news_article.gif 
    title_stdWrap.htmlSpecialChars = 1 
    title_stdWrap.htmlSpecialChars.preserveEntities = 1 
    subheader_stdWrap.stripHtml = 1 
    subheader_stdWrap.htmlSpecialChars = 1 
    subheader_stdWrap.htmlSpecialChars.preserveEntities = 1 
    subheader_stdWrap.crop = 100 | ... | 1 
    subheader_stdWrap.ifEmpty.field = bodytext 
    xmlLastBuildDate = 1

    #dontInsertSiteUrl = 1
    xmlCaching = 0
  } 
  
}  



