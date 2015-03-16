sitemap = PAGE
sitemap {
    typeNum = 200
    config {
        renderCharset = utf-8
        disableAllHeaderCode = 1
        additionalHeaders = Content-type:text/xml
        no_cache = 1
        xhtml_cleaning = 0
    }
    10 >
    10 < plugin.tx_weeaargooglesitemap_pi1

    # slash is important
    10.domain = https://example.com/

    10.tt_news.single_page {
        #wydarzenia
        1 = 43
        1.pid_list = 109
        #news
        2 = 8
        2.pid_list = 9
    }

    10.tt_news {
        disabledParameter = day,month,year
    }
}

[globalVar= ENV:DEV=1]
sitemap.10.domain = http://dev.example.com/
[end]