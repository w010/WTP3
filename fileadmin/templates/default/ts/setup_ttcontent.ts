# is this working as expected in 6?
lib.stdheader.10.setCurrent.htmlSpecialChars = 0
lib.stdheader.10.setCurrent.wrap = <span> | </span>

tt_content {

	# 1,5,6,10,11,12,20,21

	#stdWrap.dataWrap =
	# usuń linki <a href> do lokalizowanych content-elementów
	stdWrap.prepend.dataWrap >

  # HTML5
	stdWrap.innerWrap.cObject.default {
		10.value = <section id="c{field:uid}"
		30.value = >|</section>
	}

	# 1-szy frame: był invisible (nieprzydatny, jest space-after). Odpowiednie zmiany naniesione w  TCEFORM.tt_content.section_frame.altLabels
	## gron: od t3v4.3 nie dziala 
	# stdWrap.innerWrap.cObject.1.value = <div class="csc-frame csc-frame-space-after">|</div>

	# rulerAfter
	stdWrap.innerWrap.cObject.6.15.value = csc-default
	stdWrap.innerWrap.cObject.6.30.value = >|</section><div class="separator"></div>

	# Zmiana: Indent > layout-1
	stdWrap.innerWrap.cObject.10.15.value = csc-default frame-layout-1
	stdWrap.innerWrap.cObject.10.30.value = >|</section>

	# Zmiana: Indent 33/66 > layout-2
	stdWrap.innerWrap.cObject.11.15.value = csc-default frame-layout-2
	stdWrap.innerWrap.cObject.11.30.value = >|</section>


	# Zmiana: Indent 66/33 > 3cols
	stdWrap.innerWrap.cObject.12.15.value = csc-default frame-3cols
	stdWrap.innerWrap.cObject.12.30.value = >|</section>

	# Frame 1 > na 3cols + rulerAfter
	stdWrap.innerWrap.cObject.20.15.value = csc-default frame-3cols frame-3cols-sep
	stdWrap.innerWrap.cObject.20.30.value = >|</section><div class="separator"></div>

	# 2col
	stdWrap.innerWrap.cObject.30 < tt_content.stdWrap.innerWrap.cObject.default
	stdWrap.innerWrap.cObject.30.15.value = csc-default frame-2cols
	stdWrap.innerWrap.cObject.30.30.value = >|</section>
	# 2col w/separator
	stdWrap.innerWrap.cObject.31 < tt_content.stdWrap.innerWrap.cObject.default
	stdWrap.innerWrap.cObject.31.15.value = csc-default frame-2cols frame-2cols-sep
	stdWrap.innerWrap.cObject.31.30.value = >|</section><div class="separator"></div>



	# usuń wrapy z parametrem ID ( nowe od typo3v4.3 )
	#stdWrap.innerWrap.cObject.default >



	# 2-gi frame: bylo frame-2 przelaczam na No wrap
	stdWrap.innerWrap.cObject.21.10 =
	stdWrap.innerWrap.cObject.21.15 =
	stdWrap.innerWrap.cObject.21.20 =
	stdWrap.innerWrap.cObject.21.30 =

}



# zoom icon overlay to images

#tt_content.image.20.rendering.simple	{
#	imageStdWrap.dataWrap = <div class="csc-textpic-imagewrap csc-textpic-single-image" style="width:{register:totalwidth}px;"> | <div class="iconoverlay"></div> </div>
#	imageStdWrapNoWidth.wrap = <div class="csc-textpic-imagewrap csc-textpic-single-image"> | <div class="iconoverlay"></div> </div>
#}

# uncomment when needed. check in constants before
#tt_content.image.20 {
#	maxW = 960
#}

tt_content.menu	{

	20 {
		# "Menu of these pages"
		default {
			1 {
				noBlur = 1

				NO = 1
				NO.wrapItemAndSub = <li>|</li> |*||*| <li class="last">|</li>

				ACT < .NO
				ACT.wrapItemAndSub = <li class="act">|</li> |*||*| <li class="last act">|</li>

				CUR < .ACT
			}
		}

		# "Menu of subpages to these pages"
		1 {
			special = directory
			wrap = <ul class="csc-menu csc-menu-1">|</ul>
			
			1 {
				NO = 1
				NO.wrapItemAndSub = <li>|</li> |*||*| <li class="last">|</li>

				ACT < .NO
				ACT.wrapItemAndSub = <li class="act">|</li> |*||*| <li class="last act">|</li>

				CUR < .ACT
				
				#expAll = 0
			}
			2 < .1
			3 < .1
			4 < .1
			5 < .1
			6 < .1
			7 < .1
		}
		
		# "Sitemap - liststyle"
		2 {
			1 {
				NO = 1
				NO.wrapItemAndSub = <li>|</li> |*||*| <li class="last">|</li>

				ACT < .NO
				ACT.wrapItemAndSub = <li class="act">|</li> |*||*| <li class="last act">|</li>

				CUR < .ACT
			}
			2 < .1
			3 < .1
			4 < .1
			5 < .1
			6 < .1
			7 < .1
		}



		# "Menu of subpages to these pages (with abstract)"
		4 {
			wrap = <ul class="csc-menu csc-menu-4">|</ul>
			1	{
				NO = 1
				NO {
					#wrapItemAndSub >
					linkWrap = <li>|</li> |*||*| <li class="last">|</li>
					temp_____after {
						#data = field : abstract // field : description // field : subtitle
						#required = 1
						#htmlSpecialChars = 1
						wrap = <dd>|</dd> |*||*| <dd class="last">|</dd>
					}
					ATagTitle.field = description // title
				}
				ACT < .NO
				ACT {
					linkWrap = <li class="act">|</li> |*||*| <li class="last act">|</li>
					# after.wrap = <dd class="act">|</dd> |*||*| <dd class="last act">|</li>
				}
				CUR < .ACT
			}
		}
	}
}





#tt_content.menu.20.1.1 = TMENU

tt_content.menu.20.1.1.expAll = 1
#tt_content.menu.20.1.1.NO.allWrap = <p class="csc-mysitemap csc-mysitemap-level1"><span>|</span></p>

tt_content.menu.20.1.2 < tt_content.menu.20.1.1
#tt_content.menu.20.1.2.NO.allWrap = <p class="csc-mysitemap csc-mysitemap-level2"><span>|</span></p>
tt_content.menu.20.1.2 {
	#itemArrayProcFunc = user_itemArrayProcFuncCat
	#IProcFunc = user_IProcFuncCat
}

tt_content.menu.20.1.3 < tt_content.menu.20.1.1
#tt_content.menu.20.1.3.NO.allWrap = <p class="csc-mysitemap csc-mysitemap-level3"><span>|</span></p>
tt_content.menu.20.1.3 {
	#itemArrayProcFunc = user_itemArrayProcFuncCat
	#IProcFunc = user_IProcFuncCat
}

tt_content.menu.20.1.4 < tt_content.menu.20.1.1
#tt_content.menu.20.1.4.NO.allWrap = <p class="csc-mysitemap csc-mysitemap-level4"><span>|</span></p> 
tt_content.menu.20.1.4 {
	#itemArrayProcFunc = user_itemArrayProcFuncCat
	#IProcFunc = user_IProcFuncCat
}

tt_content.menu.20.1.5 < tt_content.menu.20.1.1
#tt_content.menu.20.1.5.NO.allWrap = <p class="csc-mysitemap csc-mysitemap-level5"><span>|</span></p>





tt_content.uploads {
	20 {
		#outerWrap = <div class="csc-uploads">|</div>
		
		#linkProc.iconCObject.file.import.override = fileadmin/templates/default/images/fileicons/
		linkProc.iconCObject.file	{
			import = fileadmin/templates/default/images/fileicons/
			import.override = fileadmin/templates/default/images/fileicons/
			width = 18
		}
		linkProc.icon.path = fileadmin/templates/default/images/fileicons/
		linkProc.icon.widthAttribute = 18

		itemRendering {
			20.1	{
				wrap = <p class="linkedLabel">|</p>
			}
		}
	}

	# previous
	temp.20 {
		outerWrap = <div class="csc-uploads">|</div>
		itemRendering {
			wrap = <div class="row-odd tr-first">|</div> |*| <div class="row-even">|</div> || <div class="row-odd">|</div> |*|

			10.wrap = <span class="csc-uploads-icon">|</span>

			20.wrap > 
			20.1 {
				data = register:description
				wrap = <span class="file-descr">|</span>
			}
			20.2 {
				data = register:linkedLabel
				wrap >
				dataWrap = <span class="file-label" onclick="pageTracker._trackEvent('Files', 'download', 'page-{TSFE:id}')">|</span>
			}

			30.wrap = <span class="file-size">|</span>
		}
	}
}


# Better, cleaner filelist
temp.tt_content.uploads {
	20 {
		outerWrap = <div class="csc-uploads">|</div>
		itemRendering {
			wrap = <div class="row-odd tr-first">|</div> |*| <div class="row-even">|</div> || <div class="row-odd">|</div> |*|

			10.wrap = <span class="csc-uploads-icon">|</span>

			20.wrap > 
			20.1 {
				data = register:description
				wrap = <span class="file-descr">|</span>
			}
			20.2 {
				data = register:linkedLabel
				wrap >
				dataWrap = <span class="file-label" onclick="pageTracker._trackEvent('Files', 'download', 'page-{TSFE:id}')">|</span>
			}

			30.wrap = <span class="file-size">|</span>
		}
	}
}

# remove class bodytext
# lib.parseFunc_RTE.nonTypoTagStdWrap.encapsLines.addAttributes.P.class >

