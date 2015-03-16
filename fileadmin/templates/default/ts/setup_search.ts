page.config.index_enable = 1

# domyslnie wszystkie, ale powinno byc nadpisane dla danego drzewa zeby ograniczyc wyniki do niego
# czy ta opcja jest konieczna, zeby search w ogole dzialal?
# plugin.tx_indexedsearch.search.rootPidList = 1,116,91

# dodaje pelna domene, psuje realurl
#plugin.tx_indexedsearch.search.detect_sys_domain_records = 1


plugin.tx_indexedsearch {
  show.rules=0
  tableParams{
    secHead=border=0 cellpadding=0 cellspacing=0 width="100%" class="text"
    searchBox=border=0 cellpadding=0 cellspacing=0 class="text"
    searchRes=border=0 cellpadding=0 cellspacing=0 width="100%" class="text"
  }
  templateFile = fileadmin/templates/default/html/tx_indexedsearch.html
}

### LOCALIZATION
#plugin.tx_indexedsearch._LOCAL_LANG = LLL:EXT:indexed_search/pi/locallang.xml
#plugin.tx_indexedsearch._LOCAL_LANG.ue = LLL:EXT:indexed_search/pi/locallang.xml


plugin.tx_macinasearchbox_pi1 {
	pidSearchpage = 74
	templateFile = fileadmin/templates/default/html/tx_macinasearchbox.html

	_LOCAL_LANG.pl {
		headline < lib.l10n.search.macinabox_headline
	}
	_LOCAL_LANG.en {
		headline < lib.l10n.search.macinabox_headline
	}
	_LOCAL_LANG.fr {
		headline < lib.l10n.search.macinabox_headline
	}

}


lib.searchbox < plugin.tx_macinasearchbox_pi1


### poprawka dla pagebrowser`a w wynikach wyszukiwania <patrz>  setup.ts

#indexed_search: make exception for search results at indexed_search, otherwise Strona1, Strona2 will link to index.php not siteurl
[PIDinRootline = 74]
	config.absRefPrefix >
[end]