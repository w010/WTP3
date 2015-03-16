#includeLibs.newsImageMarkerFunc = EXT:mynews_ext/class.tx_ttnews_itemMarkerArrayFunc.php

plugin.tt_news {

	# singlePid i backPid - domyślnie na bieżącą stronę
	singlePid.data = TSFE:id
	backPid.data = TSFE:id
	
	recursive = 255
	substitutePagetitle = 1
	listOrderBy = datetime desc
	dontUseBackPid = 1

	usePiBasePagebrowser = 1
	pageBrowser {
		showFirstLast = 0
		showRange = 0
		showResultCount = 0
		maxPages = 10
		alwaysPrev = 0
		pageFloat = center
		dontLinkActivePage = 0
		
		#check these, try to uncomment 
		browseBoxWrap.wrap = <div class="browseBoxWrap">|</div>
		showResultsWrap.wrap = <div class="showResultsWrap">|</div>
		browseLinksWrap.wrap = <div class="browseLinksWrap">|</div>
		showResultsNumbersWrap.wrap = <span class="showResultsNumbersWrap">|</span>
		disabledLinkWrap = <span class="disabledLinkWrap">|</span>
		inactiveLinkWrap = <span class="inactiveLinkWrap">|</span>
		activeLinkWrap = <span class="activeLinkWrap">|</span>
	}

	# categories selection

	catSelectorTargetPid = 56

	# catTextModes: 0-no, 1-not linked, 2-as shortcut, 3-as cat selector
	catTextMode = 3
	catImageMode = 0
	categoryDivider =,
	# categoryDivider_stdWrap = |
	category_stdWrap =
	catmenuHeader_stdWrap.wrap =  
   

	displayLatest {
		subheader_stdWrap.crop = 100 | ...
		subheader_stdWrap.stripHtml = 0
		subheader_stdWrap.ifEmpty.field >

		date_stdWrap.strftime = %e.%m.%Y
			# full content on latest view
		#content_stdWrap.parseFunc < lib.parseFunc_RTE
		#content_stdWrap.crop = 110 | ...&nbsp;
		#content_stdWrap.stripHtml = 0
		#content_stdWrap.ifEmpty.field >
		#content_stdWrap.append < plugin.tt_news.displayList.subheader_stdWrap.append

      	image {
          file {
            maxW = 205m
            maxH = 146m
            minW >
            minH >
            #width = 210c
            #height = 150
          }
          noImage_stdWrap {
			cObject = IMAGE
			cObject.file = fileadmin/templates/shared/images/default.jpg
      	  }
        }
	}
	latestLimit = 5

	displayList {
		#author_stdWrap = #bug w tt_news - nie da sie usunac domyslnego markupu p[bodytext]
		subheader_stdWrap.crop >
		subheader_stdWrap.ifEmpty.field >
		# %e probably doesn't work at win server
		date_stdWrap.strftime = %e.%m.%Y

		# gdy na liscie musimy wyswietlic tresc !
		# content_stdWrap.parseFunc < lib.parseFunc_RTE

		#image.file < tt_news.displayLatest.image.file

		image {
          file {
            maxW = 210m
            maxH = 150
            minW > 
            minH >
            #width = 210
            #height = 150
          }
		  noImage_stdWrap {
			wrap = <span class="default-img">|</span>
			cObject = IMAGE
			cObject.file = fileadmin/templates/default/images/news_default.png
      	  }
   		}
	}

	displaySingle {
		#author_stdWrap.wrap = |
		#subheader_stdWrap.wrap = <p class="subheader">|</p>
		subheader_stdWrap.wrap >

		image {
      	  file {
            maxW = 210
            maxH = 150
            minW >
            minH >
            #width = 210c
            #height = 150
         }
         
         imageLinkWrap {
			# ? wyglada, jakby nie bylo brane pod uwage. czemu? patrz constants
            width = 800m
            height = 600
         }
      }
      date_stdWrap.strftime = %e.%m.%Y
   }

	# co to wlasciwie daje?
	getRelatedCObject {
		10.default.5 >
		10.default.20 >
		10.1.5 >
		10.1.20 >
		10.2.5 >
		10.2.20 >
	}

	newsFiles.icon = 0

	related_stdWrap.wrap = <dl class="ttnews-related">|</dl>
	newsFiles_stdWrap.wrap = <dl class="ttnews-files">|</dl>

	date_stdWrap.strftime = %e.%m.%Y
	noNewsIdMsg_stdWrap.wrap = <div class="noNewsIdMsg"> | </div>
	noNewsToListMsg_stdWrap.wrap = <div class="noNewsToListMsg"> | </div>

	sys_language_mode = strict
	showNewsWithoutDefaultTranslation = 1

	excludeAlreadyDisplayedNews = 1 

	_CSS_DEFAULT_STYLE >  
	
	
	#someCustomFunctionality {
		# value used in hook
	#}
}


plugin.tt_news.code = LIST

[globalVar = GP:tx_ttnews|tt_news > 0]
plugin.tt_news.code >
plugin.tt_news.code = SINGLE
[global]





# moze sie przydac

#[PIDinRootline = 95]

#plugin.tx_ttnewscalendar_pi1 {
#	templateFile = fileadmin/templates/default/html/tx_ttnews_calendar.html
#	newsCategory = 2
#	storePid = 2
#	newsListPid = 23
#	newsSinglePid = 45
#}

#page_ttnewscalendar_month	{
#	10	{
#		templateFile = fileadmin/templates/default/html/tx_ttnews_calendar.html
#		storePid = 2
#		newsListPid = 23
#		newsSinglePid = 45
#	}
#}




# NIE RUSZAĆ
# w - co to takiego?
#lib.pageTreeCatMenu = COA
#lib.pageTreeCatMenu {
#	categorySelection = 16,17,18,19,22
#}

#lib.news_catmenu < plugin.tt_news 
#lib.news_catmenu.displayCatMenu.includeList = 1,2,3,26

#plugin.tx_ttnews {
	# [ryży]: co to za ustawienie?
#	overridePageId = 56
#}

