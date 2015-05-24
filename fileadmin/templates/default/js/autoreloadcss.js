/**
 * Auto reload css on given time
 * version: 0.1
 *
 * WTP2 - wolo.pl Typo3 Pack
 * 2015
 *
 * use:
 * [globalVar= ENV:DEV=1] && [globalVar= GP:autocss > 0 ]
 * page.includeJSFooter.autoreloadcss = fileadmin/templates/default/js/autoreloadcss.js
 * [global]
*/





// from chrome ext

function refreshCss(){
    console.log('refresh css');
    var i,a,s;
    a=document.getElementsByTagName('link');
    i=a.length;
    while(i--){
        s=a[i];
        if(s.rel.toLowerCase().indexOf('stylesheet')>=0&&s.href){
            var h = s.href.replace(/(&|\?)forceReload=\d+/,'');
            s.href = h+(h.indexOf('?')>=0?'&':'?')+'forceReload='+(new Date().valueOf());
        }
    }
}

/*
// other method - also works
 function autoReloadCss() {
 console.log('auto reload css');
 var queryString = '?reload=' + new Date().getTime();
 $('link[rel="stylesheet"]').each(function () {
 this.href = this.href.replace(/\?.*|$/, queryString);
 });
 }
 */



$(document).dblclick(function(e) {
//$(document).click(function(e) {
    refreshCss();
});



var autoReloadCss_time = 5000;




window.setInterval( function() {
        refreshCss();
    }, autoReloadCss_time
);


// non-jquery
//document.getElementsByTagName("link"); for (var i = 0; i < links.length;i++) { var link = links[i]; if (link.rel === "stylesheet") {link.href += "?"; }}

