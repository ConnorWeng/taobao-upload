/*! Sell 2014-04-18 17:20 */
!function(a,b){function c(a,b,c){var d,e,f=b;for(e=(""+a).split("."),d=window[e[0]]===f?1:0;d<e.length;++d)if(c)f=f[e[d]]=f[e[d]]||{};else{var g=f[e[d]];if(!g){f=void 0;break}f=g}return f}function d(){var a=window,b=(a._ap||[],a.g_config&&a.g_config.ap_mods),c=a.location.search&&-1!=a.location.search.indexOf("ap-debug"),d={poc:[.001],cdn:[.01],exit:[.01],jstracker:[.001]},e=function(a,b,d){(c||Math.random()<=b)&&(f+=","+(d?d.join("-min.js,"):a+"/m")+".js")},f="";if(a.g_config){for(var g in d){var h=b&&b[g];e(g,h&&h[0]||d[g][0],h&&h[1])}for(var g in b)d[g]||e(g,b[g][0],b[g][1]);if(f){var i=document.createElement("script");i.type="text/javascript",i.async=!0,i.src=("https:"==document.location.protocol?"https://s":"http://g")+".tbcdn.cn/tb/ap/1.0/??p.js"+f;var j=document.getElementsByTagName("script")[0];j.parentNode.insertBefore(i,j)}a._ap=a._ap||[],a.onerror=function(){a._ap.push(["jstracker","_trackCustom","msg="+(arguments[0]?encodeURIComponent(arguments[0]):"")+"&file="+(arguments[1]?encodeURIComponent(arguments[1]):"")+"&line="+(arguments[2]?encodeURIComponent(arguments[2]):"")])}}}var e=a.DOM,f="Sell",g=b[f]=b[f]||{},h=location.href.indexOf("daily.taobao.net")>=0;g.assetsDomain=h?"assets.daily.taobao.net":"a.tbcdn.cn",g.Config={get:function(a){return c(a,g,!1)},set:function(b,d){var e=c(b,g,!0);return a.mix(e,d,!1)}},g.CallBack={};var i="v2",j=e.query("script"),k=j[j.length-1],l=k.src,m=l.lastIndexOf("/"),n=l.split(i)[0],o=e.attr(k,"data-init"),p=l.substring(n.length,m+1);a.config({packages:[{name:i,path:n,charset:"utf-8"}],map:[[/kissy\/1.2.0\/(?:editor|template|overlay|calendar|component|uibase|dd|menu|button|menubutton)-min.js(.+)$/i,"kissy/1.2.0/??template-min.js,uibase-min.js,component-min.js,dd-min.js,overlay-min.js,calendar-min.js,editor-min.js,menu-min.js,button-min.js,menubutton-min.js$1"]]}),a.use(p+(o||"main"));var q=window.g_config||{};q.loginCfg={redirect_url:"http://www.taobao.com/go/act/sell/loginsuccess.php"},d(k)}(KISSY,this);