/*
Copyright 2012, KISSY UI Library v1.20
MIT Licensed
build time: Oct 8 16:26
*/
KISSY.add("uibase/align",function(e,h,b,d){function i(j){for(var c=j.ownerDocument.body,a=b.css(j,"position"),e="fixed"==a||"absolute"==a,j=j.parentNode;j&&j!=c;j=j.parentNode)if(a=b.css(j,"position"),e=e&&"static"==a,"visible"!=b.css(j,"overflow")&&(!e||"fixed"==a||"absolute"==a||"relative"==a))return j;return null}function c(j){for(var c in j)if(0===c.indexOf("fail"))return!0;return!1}function a(j){var a=j.offset,l=j.node,d=j.points,k,f=this.get("el"),a=a||[0,0];k=f.offset();l=r(l,d[0]);d=r(f,d[1]);
d=[d.left-l.left,d.top-l.top];k={left:k.left-d[0]+ +a[0],top:k.top-d[1]+ +a[1]};a:{a=k;k=this.get("el");f={};d={width:k.outerWidth(),height:k.outerHeight()};l=e.clone(d);if(!e.isEmptyObject(j.overflow)){for(var f={left:0,right:Infinity,top:0,bottom:Infinity},m=k[0];m=i(m);){var g=m.clientWidth;if(!h.ie||0!==g){var g=m.clientLeft,s=m.clientTop,p=b.offset(m);p.left+=g;p.top+=s;f.top=Math.max(f.top,p.top);f.right=Math.min(f.right,p.left+m.clientWidth);f.bottom=Math.min(f.bottom,p.top+m.clientHeight);
f.left=Math.max(f.left,p.left)}}m=b.scrollLeft();g=b.scrollTop();f.left=Math.max(f.left,m);f.top=Math.max(f.top,g);f.right=Math.min(f.right,m+b.viewportWidth());f.bottom=Math.min(f.bottom,g+b.viewportHeight());f=0<=f.top&&0<=f.left&&f.bottom>f.top&&f.right>f.left?f:null;j=j.overflow||{};m={};a.left<f.left&&j.adjustX&&(a.left=f.left,m.adjustX=1);a.left<f.left&&a.left+l.width>f.right&&j.resizeWidth&&(l.width-=a.left+l.width-f.right,m.resizeWidth=1);a.left+l.width>f.right&&j.adjustX&&(a.left=Math.max(f.right-
l.width,f.left),m.adjustX=1);j.failX&&(m.failX=a.left<f.left||a.left+l.width>f.right);a.top<f.top&&j.adjustY&&(a.top=f.top,m.adjustY=1);a.top>=f.top&&a.top+l.height>f.bottom&&j.resizeHeight&&(l.height-=a.top+l.height-f.bottom,m.resizeHeight=1);a.top+l.height>f.bottom&&j.adjustY&&(a.top=Math.max(f.bottom-l.height,f.top),m.adjustY=1);j.failY&&(m.failY=a.top<f.top||a.top+l.height>f.bottom);f=m;if(c(f)){j=f;break a}}this.set("x",a.left);this.set("y",a.top);if(l.width!=d.width||l.height!=d.height)k.width(l.width),
k.height(l.height);j=f}return j}function g(a,c,d){var k=[];e.each(a,function(a){k.push(a.replace(c,function(a){return d[a]}))});return k}function k(){}function r(a,c){var k=c.charAt(0),e=c.charAt(1),g,f,m,h;a?(a=d.one(a),g=a.offset(),f=a.outerWidth(),m=a.outerHeight()):(g={left:b.scrollLeft(),top:b.scrollTop()},f=b.viewportWidth(),m=b.viewportHeight());h=g.left;g=g.top;"c"===k?g+=m/2:"b"===k&&(g+=m);"c"===e?h+=f/2:"r"===e&&(h+=f);return{left:h,top:g}}k.ATTRS={align:{}};k.prototype={_uiSetAlign:function(a){e.isPlainObject(a)&&
this.align(a.node,a.points,a.offset,a.overflow)},align:function(j,k,d,b){var h={},b=e.clone(b||{}),d=d&&[].concat(d)||[0,0];b.failX&&(h.failX=1);b.failY&&(h.failY=1);var f=a.call(this,{node:j,points:k,offset:d,overflow:h});if(c(f)&&(f.failX&&(k=g(k,/[lr]/ig,{l:"r",r:"l"}),d[0]=-d[0]),f.failY))k=g(k,/[tb]/ig,{t:"b",b:"t"}),f=d,f[1]=-f[1],d=f;f=a.call(this,{node:j,points:k,offset:d,overflow:h});c(f)&&(delete b.failX,delete b.failY,a.call(this,{node:j,points:k,offset:d,overflow:b}))},center:function(a){this.set("align",
{node:a,points:["cc","cc"],offset:[0,0]})}};return k},{requires:["ua","dom","node"]});
KISSY.add("uibase/base",function(e,h,b){function d(a){return a.charAt(0).toUpperCase()+a.substring(1)}function i(d){h.apply(this,arguments);for(var g=d,j=this.constructor;j;){if(g&&g[a]&&j.HTML_PARSER&&(g[a]=b.one(g[a]))){var q=g[a],l=j.HTML_PARSER,i=void 0,n=void 0;for(i in l)l.hasOwnProperty(i)&&(n=l[i],e.isFunction(n)?this.__set(i,n.call(this,q)):e.isString(n)?this.__set(i,q.one(n)):e.isArray(n)&&n[0]&&this.__set(i,q.all(n[0])))}j=j.superclass&&j.superclass.constructor}c(this,"initializer","constructor");
d&&d.autoRender&&this.render()}function c(a,c,d){for(var b=a.constructor,e=[],g,h,f,m;b;){m=[];if(f=b.__ks_exts)for(var i=0;i<f.length;i++)if(g=f[i])"constructor"!=d&&(g=g.prototype.hasOwnProperty(d)?g.prototype[d]:null),g&&m.push(g);b.prototype.hasOwnProperty(c)&&(h=b.prototype[c])&&m.push(h);m.length&&e.push.apply(e,m.reverse());b=b.superclass&&b.superclass.constructor}for(i=e.length-1;0<=i;i--)e[i]&&e[i].call(a)}var a="srcNode",g=function(){};i.HTML_PARSER={};i.ATTRS={rendered:{value:!1},created:{value:!1}};
e.extend(i,h,{create:function(){this.get("created")||(this._createDom(),this.fire("createDom"),c(this,"createDom","__createDom"),this.fire("afterCreateDom"),this.set("created",!0))},render:function(){this.get("rendered")||(this.create(),this._renderUI(),this.fire("renderUI"),c(this,"renderUI","__renderUI"),this.fire("afterRenderUI"),this._bindUI(),this.fire("bindUI"),c(this,"bindUI","__bindUI"),this.fire("afterBindUI"),this._syncUI(),this.fire("syncUI"),c(this,"syncUI","__syncUI"),this.fire("afterSyncUI"),
this.set("rendered",!0))},_createDom:g,_renderUI:g,renderUI:g,_bindUI:function(){var a=this,c=a.__attrs,b,g;for(b in c)c.hasOwnProperty(b)&&(g="_uiSet"+d(b),a[g]&&function(c,b){a.on("after"+d(c)+"Change",function(c){a[b](c.newVal,c)})}(b,g))},bindUI:g,_syncUI:function(){var a=this.__attrs,c;for(c in a)if(a.hasOwnProperty(c)){var b="_uiSet"+d(c);if(this[b]&&!1!==a[c].sync&&void 0!==this.get(c))this[b](this.get(c))}},syncUI:g,destroy:function(){for(var a=this.constructor,c,b,d;a;){a.prototype.hasOwnProperty("destructor")&&
a.prototype.destructor.apply(this);if(c=a.__ks_exts)for(d=c.length-1;0<=d;d--)(b=c[d]&&c[d].prototype.__destructor)&&b.apply(this);a=a.superclass&&a.superclass.constructor}this.fire("destroy");this.detach()}});i.create=function(a,c,b,d){function g(){i.apply(this,arguments)}e.isArray(a)&&(d=b,b=c,c=a,a=i);a=a||i;e.isObject(c)&&(d=b,b=c,c=[]);e.extend(g,a,b,d);if(c){g.__ks_exts=c;var h={},a=c.concat(g);e.each(a,function(a){a&&e.each(["ATTRS","HTML_PARSER"],function(c){if(a[c]){h[c]=h[c]||{};e.mix(h[c],
a[c],true,void 0,true)}})});e.each(h,function(a,c){g[c]=a});var n={};e.each(a,function(a){if(a){var a=a.prototype,c;for(c in a)a.hasOwnProperty(c)&&(n[c]=a[c])}});e.each(n,function(a,c){g.prototype[c]=a})}return g};return i},{requires:["base","node"]});
KISSY.add("uibase/box",function(){function e(){}e.ATTRS={html:{view:!0,sync:!1},width:{view:!0},height:{view:!0},elCls:{view:!0},elStyle:{view:!0},elAttrs:{view:!0},elBefore:{view:!0},el:{view:!0},render:{view:!0},visibleMode:{value:"display",view:!0},visible:{view:!0},srcNode:{view:!0}};e.HTML_PARSER={el:function(e){this.decorateInternal&&this.decorateInternal(e);return e}};e.prototype={_uiSetVisible:function(e){this.fire(e?"show":"hide")},show:function(){this.render();this.set("visible",!0)},hide:function(){this.set("visible",
!1)}};return e});
KISSY.add("uibase/boxrender",function(e,h){function b(){}function d(c,a,b,d,e,h){a=a||{};b&&(a.width=b);d&&(a.height=d);var b="",i;for(i in a)a.hasOwnProperty(i)&&(b+=i+":"+a[i]+";");var a="",l;for(l in h)h.hasOwnProperty(l)&&(a+=" "+l+"='"+h[l]+"' ");return"<"+e+(b?" style='"+b+"' ":"")+a+(c?" class='"+c+"' ":"")+"></"+e+">"}var i=e.all;b.ATTRS={el:{setter:function(c){return i(c)}},elCls:{},elStyle:{},width:{},height:{},elTagName:{value:"div"},elAttrs:{},elBefore:{},render:{},html:{sync:!1},visible:{},
visibleMode:{}};b.construct=d;b.HTML_PARSER={html:function(c){return c.html()}};b.prototype={__renderUI:function(){if(this.__boxRenderNew){var c=this.get("render"),a=this.get("el"),b=this.get("elBefore");b?a.insertBefore(b):c?a.appendTo(c):a.appendTo("body")}},__createDom:function(){var c=this.get("el");c||(this.__boxRenderNew=!0,c=new h(d(this.get("elCls"),this.get("elStyle"),this.get("width"),this.get("height"),this.get("elTagName"),this.get("elAttrs"))),this.set("el",c),this.get("html")&&c.html(this.get("html")))},
_uiSetElAttrs:function(c){this.get("el").attr(c)},_uiSetElCls:function(c){this.get("el").addClass(c)},_uiSetElStyle:function(c){this.get("el").css(c)},_uiSetWidth:function(c){this.get("el").width(c)},_uiSetHeight:function(c){this.get("el").height(c)},_uiSetHtml:function(c){this.get("el").html(c)},_uiSetVisible:function(c){var a=this.get("el");"visibility"==this.get("visibleMode")?a.css("visibility",c?"visible":"hidden"):a.css("display",c?"":"none")},show:function(){this.render();this.set("visible",
!0)},hide:function(){this.set("visible",!1)},__destructor:function(){var c=this.get("el");c&&(c.detach(),c.remove())}};return b},{requires:["node"]});KISSY.add("uibase/close",function(){function e(){}e.ATTRS={closable:{view:!0},closeAction:{value:"hide"}};var h={hide:"hide",destroy:"destroy"};e.prototype={__bindUI:function(){var b=this,d=b.get("view").get("closeBtn");d&&d.on("click",function(d){b[h[b.get("closeAction")]||"hide"]();d.preventDefault()})}};return e});
KISSY.add("uibase/closerender",function(e,h){function b(){}b.ATTRS={closable:{value:!0},closeBtn:{}};b.HTML_PARSER={closeBtn:function(b){return b.one("."+this.get("prefixCls")+"ext-close")}};b.prototype={_uiSetClosable:function(b){var e=this.get("closeBtn");e&&(b?e.css("display",""):e.css("display","none"))},__renderUI:function(){var b=this.get("closeBtn"),e=this.get("el");!b&&e&&(b=(new h("<a tabindex='0' href='javascript:void(\"\u5173\u95ed\")' role='button' class='"+this.get("prefixCls")+"ext-close'><span class='"+
this.get("prefixCls")+"ext-close-x'>\u5173\u95ed</span></a>")).appendTo(e),this.set("closeBtn",b))},__destructor:function(){var b=this.get("closeBtn");b&&b.detach()}};return b},{requires:["node"]});
KISSY.add("uibase/constrain",function(e,h,b){function d(){}function i(c){var a;if(!c)return a;var d=this.get("el");!0!==c?(c=b.one(c),a=c.offset(),e.mix(a,{maxLeft:a.left+c.outerWidth()-d.outerWidth(),maxTop:a.top+c.outerHeight()-d.outerHeight()})):(c=document.documentElement.clientWidth,a={left:h.scrollLeft(),top:h.scrollTop()},e.mix(a,{maxLeft:a.left+c-d.outerWidth(),maxTop:a.top+h.viewportHeight()-d.outerHeight()}));return a}d.ATTRS={constrain:{value:!1}};d.prototype={__renderUI:function(){var c=
this,a=c.getAttrs(),b=a.x,a=a.y,d=b.setter,e=a.setter;b.setter=function(a){var b=d&&d.call(c,a);void 0===b&&(b=a);if(!c.get("constrain"))return b;a=i.call(c,c.get("constrain"));return Math.min(Math.max(b,a.left),a.maxLeft)};a.setter=function(a){var b=e&&e.call(c,a);void 0===b&&(b=a);if(!c.get("constrain"))return b;a=i.call(c,c.get("constrain"));return Math.min(Math.max(b,a.top),a.maxTop)};c.addAttr("x",b);c.addAttr("y",a)}};return d},{requires:["dom","node"]});
KISSY.add("uibase/contentbox",function(){function e(){}e.ATTRS={content:{view:!0,sync:!1},contentEl:{view:!0},contentElAttrs:{view:!0},contentElStyle:{view:!0},contentTagName:{view:!0}};e.prototype={};return e});
KISSY.add("uibase/contentboxrender",function(e,h,b){function d(){}function i(a,c){var b=a.get("contentEl");b.html("");c&&b.append(c)}d.ATTRS={contentEl:{},contentElAttrs:{},contentElCls:{value:""},contentElStyle:{},contentTagName:{value:"div"},content:{sync:!1}};d.HTML_PARSER={content:function(a){return a.html()}};var c=b.construct;d.prototype={__renderUI:function(){},__createDom:function(){var a,b;a=this.get("el");var d=e.makeArray(a[0].childNodes);a=(new h(c(this.get("prefixCls")+"contentbox "+
this.get("contentElCls"),this.get("contentElStyle"),void 0,void 0,this.get("contentTagName"),this.get("contentElAttrs")))).appendTo(a);this.set("contentEl",a);if(d.length)for(b=0;b<d.length;b++)a.append(d[b]);else(b=this.get("content"))&&i(this,b)},_uiSetContentElCls:function(a){this.get("contentEl").addClass(a)},_uiSetContentElAttrs:function(a){this.get("contentEl").attr(a)},_uiSetContentElStyle:function(a){this.get("contentEl").css(a)},_uiSetContent:function(a){i(this,a)}};return d},{requires:["node",
"./boxrender"]});
KISSY.add("uibase/drag",function(e){function h(){}h.ATTRS={handlers:{value:[]},draggable:{value:!0}};h.prototype={_uiSetHandlers:function(b){b&&0<b.length&&this.__drag&&this.__drag.set("handlers",b)},__bindUI:function(){var b=e.require("dd/draggable"),d=this.get("el");this.get("draggable")&&b&&(this.__drag=new b({node:d}))},_uiSetDraggable:function(b){var d=this.__drag;d&&(b?(d.detach("drag"),d.on("drag",this._dragExtAction,this)):d.detach("drag"))},_dragExtAction:function(b){this.set("xy",[b.left,
b.top])},__destructor:function(){var b=this.__drag;b&&b.destroy()}};return h});KISSY.add("uibase/loading",function(){function e(){}e.prototype={loading:function(){this.get("view").loading()},unloading:function(){this.get("view").unloading()}};return e});
KISSY.add("uibase/loadingrender",function(e,h){function b(){}b.prototype={loading:function(){this._loadingExtEl||(this._loadingExtEl=(new h("<div class='"+this.get("prefixCls")+"ext-loading' style='position: absolute;border: none;width: 100%;top: 0;left: 0;z-index: 99999;height:100%;*height: expression(this.parentNode.offsetHeight);'/>")).appendTo(this.get("el")));this._loadingExtEl.show()},unloading:function(){var b=this._loadingExtEl;b&&b.hide()}};return b},{requires:["node"]});
KISSY.add("uibase/mask",function(){function e(){}e.ATTRS={mask:{value:!1}};e.prototype={_uiSetMask:function(e){e?(this.on("show",this.get("view")._maskExtShow,this.get("view")),this.on("hide",this.get("view")._maskExtHide,this.get("view"))):(this.detach("show",this.get("view")._maskExtShow,this.get("view")),this.detach("hide",this.get("view")._maskExtHide,this.get("view")))},__destructor:function(){this.get("mask")&&this.get("view")._maskExtHide()}};return e},{requires:["ua"]});
KISSY.add("uibase/maskrender",function(e,h,b){function d(){return k?l.width()+r:"100%"}function i(){return k?l.height()+r:"100%"}function c(){g=j("<div class='"+this.get("prefixCls")+"ext-mask'/>").prependTo("body");g.css({position:k?"absolute":"fixed",left:0,top:0,width:d(),height:i()});k&&(o=j("<iframe style='position:absolute;left:0px;top:0px;background:red;width:"+d()+";height:"+i()+";filter:alpha(opacity=0);z-index:-1;'/>").insertBefore(g));g.unselectable();g.on("mousedown",function(a){a.preventDefault()})}
function a(){}var g,k=6===h.ie,r="px",j=b.all,q=j(window),l=j(document),o,n=0,f=e.throttle(function(){var a={width:d(),height:i()};g.css(a);o&&o.css(a)},50);a.prototype={_maskExtShow:function(){g||c.call(this);var a={"z-index":this.get("zIndex")-1},b={display:""};g.css(a);o&&o.css(a);n++;if(1==n&&(g.css(b),o&&o.css(b),k))q.on("resize scroll",f)},_maskExtHide:function(){n--;0>=n&&(n=0);if(!n){var a={display:"none"};g&&g.css(a);o&&o.css(a);k&&q.detach("resize scroll",f)}}};return a},{requires:["ua",
"node"]});KISSY.add("uibase/position",function(e){function h(){}h.ATTRS={x:{view:!0},y:{view:!0},xy:{setter:function(b){var d=e.makeArray(b);d.length&&(d[0]&&this.set("x",d[0]),d[1]&&this.set("y",d[1]));return b},getter:function(){return[this.get("x"),this.get("y")]}},zIndex:{view:!0}};h.prototype={move:function(b,d){e.isArray(b)&&(d=b[1],b=b[0]);this.set("xy",[b,d])}};return h});
KISSY.add("uibase/positionrender",function(){function e(){}e.ATTRS={x:{valueFn:function(){return this.get("el")&&this.get("el").offset().left}},y:{valueFn:function(){return this.get("el")&&this.get("el").offset().top}},zIndex:{value:9999}};e.prototype={__renderUI:function(){this.get("el").addClass(this.get("prefixCls")+"ext-position")},_uiSetZIndex:function(e){this.get("el").css("z-index",e)},_uiSetX:function(e){this.get("el").offset({left:e})},_uiSetY:function(e){this.get("el").offset({top:e})}};
return e});KISSY.add("uibase/resize",function(e){function h(){}h.ATTRS={resize:{value:{}}};h.prototype={__destructor:function(){this.resizer&&this.resizer.destroy()},_uiSetResize:function(b){var d=e.require("resizable");d&&(this.resizer&&this.resizer.destroy(),b.node=this.get("el"),b.autoRender=!0,b.handlers&&(this.resizer=new d(b)))}};return h});
KISSY.add("uibase/shimrender",function(e,h){function b(){}b.ATTRS={shim:{value:!0}};b.prototype={_uiSetShim:function(b){var e=this.get("el");b&&!this.__shimEl?(this.__shimEl=new h("<iframe style='position: absolute;border: none;width: expression(this.parentNode.offsetWidth);top: 0;opacity: 0;filter: alpha(opacity=0);left: 0;z-index: -1;height: expression(this.parentNode.offsetHeight);'/>"),e.prepend(this.__shimEl)):!b&&this.__shimEl&&(this.__shimEl.remove(),delete this.__shimEl)}};return b},{requires:["node"]});
KISSY.add("uibase/stdmod",function(){function e(){}e.ATTRS={header:{view:!0},body:{view:!0},footer:{view:!0},bodyStyle:{view:!0},footerStyle:{view:!0},headerStyle:{view:!0},headerContent:{view:!0},bodyContent:{view:!0},footerContent:{view:!0}};e.prototype={};return e});
KISSY.add("uibase/stdmodrender",function(e,h){function b(){}function d(b,a){var e=b.get("contentEl"),d=b.get(a);d||(d=(new h("<div class='"+b.get("prefixCls")+i+a+"'/>")).appendTo(e),b.set(a,d))}var i="stdmod-";b.ATTRS={header:{},body:{},footer:{},bodyStyle:{},footerStyle:{},headerStyle:{},headerContent:{},bodyContent:{},footerContent:{}};b.HTML_PARSER={header:function(b){return b.one("."+this.get("prefixCls")+i+"header")},body:function(b){return b.one("."+this.get("prefixCls")+i+"body")},footer:function(b){return b.one("."+
this.get("prefixCls")+i+"footer")}};b.prototype={_setStdModContent:function(b,a){e.isString(a)?this.get(b).html(a):(this.get(b).html(""),this.get(b).append(a))},_uiSetBodyStyle:function(b){this.get("body").css(b)},_uiSetHeaderStyle:function(b){this.get("header").css(b)},_uiSetFooterStyle:function(b){this.get("footer").css(b)},_uiSetBodyContent:function(b){this._setStdModContent("body",b)},_uiSetHeaderContent:function(b){this._setStdModContent("header",b)},_uiSetFooterContent:function(b){this._setStdModContent("footer",
b)},__renderUI:function(){d(this,"header");d(this,"body");d(this,"footer")}};return b},{requires:["node"]});KISSY.add("uibase",function(e,h,b,d,i,c,a,g,k,r,j,q,l,o,n,f,m,t,s,p,u){c.Render=a;q.Render=l;o.Render=n;f.Render=m;p.Render=u;d.Render=i;k.Render=r;e.mix(h,{Align:b,Box:d,Close:c,Contrain:g,Contentbox:k,Drag:j,Loading:q,Mask:o,Position:f,Shim:{Render:t},Resize:s,StdMod:p});return e.UIBase=h},{requires:"uibase/base,uibase/align,uibase/box,uibase/boxrender,uibase/close,uibase/closerender,uibase/constrain,uibase/contentbox,uibase/contentboxrender,uibase/drag,uibase/loading,uibase/loadingrender,uibase/mask,uibase/maskrender,uibase/position,uibase/positionrender,uibase/shimrender,uibase/resize,uibase/stdmod,uibase/stdmodrender".split(",")});