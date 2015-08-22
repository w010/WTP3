
# bootstrap nav
# multilevel menu based on http://codepen.io/ajaypatelaj/pen/prHjD?editors=110

lib.page-menu = COA
#lib.page-menu.20 < lib.searchbox
lib.page-menu.9 = TEXT
lib.page-menu.9.value (
	<div class="containerx">
		<div class="navbar-header">
		   <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
		       <span class="sr-only">Toggle navigation</span>
		       <span class="icon-bar"></span>
		       <span class="icon-bar"></span>
		       <span class="icon-bar"></span>
		   </button>
		   <a class="navbar-brand" href="/">wtp2</a>
		</div>

)
lib.page-menu.11 = TEXT
lib.page-menu.11.value = </div>

lib.page-menu.10 = HMENU
lib.page-menu.10	{
	entryLevel = 0
	wrap = <div class="collapse navbar-collapse"> | </div>
	1 = TMENU
	1 {
		wrap = <ul class="nav navbar-nav level1"> | </ul>
		expAll = 1
		noBlur = 1
		#IProcFunc = user_mainMenuIProcFunc

		NO = 1
		NO {
			ATagTitle.field = abstract // description // subtitle // title
			wrapItemAndSub = <li class="first menuitem-{field:uid}">|</li>  |*|  <li class="middle menuitem-{field:uid}"> | </li>  |*|  <li class="last menuitem-{field:uid}"> | </li>
			wrapItemAndSub.insertData = 1
			#wrap = <span>|<span class="menu-pos-end">&nbsp;</span></span>  |*|  <span>|<span class="menu-pos-end">&nbsp;</span></span>  |*|  <span><span class="menu-pos-end">&nbsp;</span>|</span>
			#stdWrap.htmlSpecialChars = 1

			#stdWrap.wrap = <span class="blink"></span> <span class="subtitle">{field:subtitle}</span>|
			#stdWrap.wrap.insertData = 1
		}
		ACT < .NO
		ACT = 1
		ACT {
			wrapItemAndSub = <li class="first active menuitem-{field:uid}">|</li>  |*|  <li class="middle active menuitem-{field:uid}"> | </li>  |*|  <li class="last active menuitem-{field:uid}"> | </li>
			# ATagParams = class="current"
		}
		IFSUB < .NO
		IFSUB {
			wrapItemAndSub = <li class="first dropdown-submenu menuitem-{field:uid}">|</li>  |*|  <li class="middle dropdown-submenu menuitem-{field:uid}"> | </li>  |*|  <li class="last dropdown-submenu menuitem-{field:uid}"> | </li>
		}
		ACTIFSUB < .ACT
		ACTIFSUB {
			wrapItemAndSub = <li class="first dropdown-submenu active menuitem-{field:uid}">|</li>  |*|  <li class="middle dropdown-submenu active menuitem-{field:uid}"> | </li>  |*|  <li class="last dropdown-submenu active menuitem-{field:uid}"> | </li>
		}
	}
	2 < .1
	2	{
		wrap = <ul class="dropdown-menu level2"> | </ul>
		#IFSUB >
		#ACTIFSUB >
		#NO.wrapItemAndSub = <li>|</li>
	}
	3 < .2
	3   {
		wrap = <ul class="dropdown-menu level3"> | </ul>
	}
}





# alternative versions of menu levels
temp.menutemplates {
2 < .1
	2	{
		wrap = <span class="menuhelper"></span> <div class="level-2-wrap"> <ul class="level-2"> | </ul> </div>
		# make first item always .act
		NO.wrapItemAndSub = <li class="item-first page-{field:uid}">|</li>  |*|  <li class="page-{field:uid}"> | </li>  |*|  <li class="item-last page-{field:uid}"> | </li>
		ACT.wrapItemAndSub = <li class="item-first act page-{field:uid}">|</li>  |*|  <li class="act page-{field:uid}"> | </li>  |*|  <li class="item-last act page-{field:uid}"> | </li>
		IFSUB.wrapItemAndSub = <li class="item-first act dir page-{field:uid}">|</li>  |*|  <li class="dir page-{field:uid}"> | </li>  |*|  <li class="item-last dir page-{field:uid}"> | </li>
		ACTIFSUB.wrapItemAndSub = <li class="item-first act dir page-{field:uid}">|</li>  |*|  <li class="act dir page-{field:uid}"> | </li>  |*|  <li class="item-last act dir page-{field:uid}"> | </li>
	}
	3 < .2
	3	{
		wrap = <ul class="level-3"> | </ul>

		NO.wrapItemAndSub = <li class="page-{field:uid}">|<p>{field:subtitle}</p></li>  
		# NO.wrapItemAndSub = <li>|</li>
		# NO.linkWrap.stdWrap = |<span>{field:subtitle}</span>
		# NO.linkWrap.stdWrap.insertData = 1
		ACT.wrapItemAndSub = <li class="page-{field:uid}">|<p>{field:subtitle}</p></li>  
		IFSUB.wrapItemAndSub = <li class="page-{field:uid}">|<p>{field:subtitle}</p></li>  
		ACTIFSUB.wrapItemAndSub = <li class="page-{field:uid}">|<p>{field:subtitle}</p></li>  
	}
}




lib.page-menu2 = HMENU
lib.page-menu2 {
   # excludeUidList = 45
   # special = directory
   # special.value = 796

   wrap = <ul class="menu main">|</ul>

   1 = TMENU
   1 {
      noBlur = 1
      #IProcFunc = user_mainMenuIProcFunc

      #NO.linkWrap = <li id="page-{elementUid}">|</li>
      NO.linkWrap = <li class="item-first page-{elementUid}">|</li> |*| <li class="item-middle page-{elementUid}">|</li> |*| <li class="item-last page-{elementUid}">|</li>
      NO.subst_elementUid = 1
      NO.stdWrap.wrap = <span>|</span>

      ACT < lib.page-menu.1.NO
      ACT = 1
      ACT.subst_elementUid = 1
	  #ACT.linkWrap = <li class="act" id="page-{elementUid}">|</li>
	  ACT.linkWrap = <li class="item-first page-{elementUid} act">|</li> |*| <li class="item-middle page-{elementUid} act">|</li> |*| <li class="item-last page-{elementUid} act">|</li> 
      
      #ACT.ATagParams = class="act"
   }
}




# on homepage always mark this page (hp shortcut in menu)
[treeLevel = 0]
   lib.page-menu.10.alwaysActivePIDlist = 3
[end]





#names: breadcrumb,navigation,location
lib.breadcrumb = HMENU
lib.breadcrumb {
	special = rootline
	special.range = 1|10

	wrap = <div id="breadcrumb">|</div>
	# <ol class="breadcrumb"> - bootstrap

	1 = TMENU
	1 {
		noBlur = 1

		stdWrap.preCObject = TEXT
		stdWrap.preCObject.data = levelfield:0,nav_title // levelfield:0,title
		stdWrap.preCObject.typolink.parameter = 1
		stdWrap.preCObject.wrap = | &nbsp;&raquo;&nbsp;

		NO.allWrap =   |&nbsp;&raquo;&nbsp;|*||*|  |
		NO.ATagTitle.data = field:subtitle // field:abstract // field:title
	}
}


# if needed
temp.lib.breadcrumb.20 < lib.breadcrumb.10
temp.lib.breadcrumb.20 {
	wrap = <div id="breadcrumb-mobi" class="only450">|</div>
	special.range = 1|3
	1.stdWrap >
}




lib.page-submenu-1 = HMENU
lib.page-submenu-1 {
   # excludeUidList = 45
   # special = directory
   # special.value = 29
   
  entryLevel = 1

	1 = TMENU
   	1 {
   
			# stdWrap.preCObject.wrap = | &nbsp;&raquo;&nbsp;
			wrap = <nav><ul class="menu sub">|</ul></nav>
   
   		#expAll = 1
    	noBlur = 1
    	#IProcFunc = user_mainMenuIProcFunc

    	NO.linkWrap = <li class="item-first page-{elementUid}">|</li> |*| <li class="item-middle page-{elementUid}">|</li> |*| <li class="item-last page-{elementUid}">|</li>
    	NO.subst_elementUid = 1
    	NO.stdWrap.wrap = <span>|</span>

    	ACT < .NO
    	ACT = 1
    	ACT.linkWrap = <li class="item-first page-{elementUid} act">|</li> |*| <li class="item-middle page-{elementUid} act">|</li> |*| <li class="item-last page-{elementUid} act">|</li>
    	ACT.subst_elementUid = 1
    	#ACT.ATagParams = class="act"
      
   	}
}


# currently content sitemap option is used for footer menu

lib.menu-footer = COA
#lib.menu-footer.stdWrap.wrap = <nav class="menu-footer"> | </nav>
lib.menu-footer.10 = HMENU
lib.menu-footer.10 {
	# excludeUidList = 45
#	special = directory
#	special.value = 17

	1 = TMENU
    1 {
        # stdWrap.preCObject.wrap = | &nbsp;&raquo;&nbsp;
        #wrap = <nav class="menu-footer"> | </nav>

        #expAll = 1
        noBlur = 1
        #IProcFunc = user_mainMenuIProcFunc

        NO.linkWrap = <span class="item-first page-{elementUid}">|</span> |*| <span class="item-middle page-{elementUid}">|</span> |*| <span class="item-last page-{elementUid}">|</span>
        NO.subst_elementUid = 1
        #NO.stdWrap.wrap = <span>|</span>

        ACT < .NO
        ACT = 1
        ACT.linkWrap = <span class="item-first page-{elementUid} act">|</span> |*| <span class="item-middle page-{elementUid} act">|</span> |*| <span class="item-last page-{elementUid} act">|</span>
        ACT.subst_elementUid = 1
        #ACT.ATagParams = class="act"
    }
}

# social icons
lib.menu-footer.20 = TEXT
lib.menu-footer.20.value (

)




# login / logout links
# @see filmy

temp.page.10 = COA_INT
temp.page.10 {
  ## Login link (shown if FE user is NOT logged in)
  10 = TEXT
  10 {
    value = LOGIN
    typolink {
      parameter = 123 // pid of page with login form
    }
    if.isFalse.data = TSFE:fe_user|user|username
  }
 
  ## Logout link (shown if FE user IS logged in)
  20 < .10
  20.if.negate = 1
  20 {
    value = LOGOUT
    typolink {
      additionalParams = &logintype=logout
    }
  }
}




# snippet:
# display only if has subpages
# try to do side menu with this

temp.x.30 = TEXT
temp.x.30 {
  wrap = <ul><li>|</li></ul>
  data = leveltitle:1
  if {
    isTrue.numRows {
      table = pages
      select {
                # select properties here if necessary
      }
    }
  }
}

