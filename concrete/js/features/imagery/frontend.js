(()=>{var e={4062:()=>{$("[data-gallery-lightbox=true]").magnificPopup({type:"image",gallery:{enabled:!0},image:{titleSrc:function(e){var t=$("<div />"),n=e.el.attr("data-caption");t.append(n);var i=e.el.attr("data-download-link");if(i.length){var a=$("<a></a>");a.attr("href",i),a.attr("target","_blank"),a.attr("class","ms-2"),a.html("Download"),t.append(a)}return t.html()}}})},9703:()=>{$("a[data-concrete-link-lightbox=image],a[data-concrete5-link-lightbox=image]").each((function(){$(this).magnificPopup({type:"image",removalDelay:500,callbacks:{beforeOpen:function(){this.st.image.markup=this.st.image.markup.replace("mfp-figure","mfp-figure mfp-with-anim"),this.st.mainClass="mfp-zoom-in"}},closeOnContentClick:!0,midClick:!0})})),$("a[data-concrete-link-lightbox=iframe],a[data-concrete5-link-lightbox=iframe]").each((function(){var e=$(this),t=500,n=400;$(this).attr("data-concrete-link-lightbox-width")&&$(this).attr("data-concrete-link-lightbox-height")?(t=$(this).attr("data-concrete-link-lightbox-width"),n=$(this).attr("data-concrete-link-lightbox-height")):$(this).attr("data-concrete5-link-lightbox-width")&&$(this).attr("data-concrete5-link-lightbox-height")&&(t=$(this).attr("data-concrete5-link-lightbox-width"),n=$(this).attr("data-concrete5-link-lightbox-height")),e.magnificPopup({type:"iframe",callbacks:{beforeOpen:function(){this.st.iframe.markup=this.st.iframe.markup.replace("mfp-figure","mfp-figure mfp-with-anim"),this.st.mainClass="mfp-zoom-in";var e=$.magnificPopup.instance;$(e.contentContainer).css("maxWidth",t+"px").css("maxHeight",n+"px")}},iframe:{patterns:{website:{index:"",src:"%id%"}}},closeOnContentClick:!0,midClick:!0})}))},5547:()=>{
/*! ResponsiveSlides.js v1.55
 * http://responsiveslides.com
 * http://viljamis.com
 *
 * Copyright (c) 2011-2012 @viljamis
 * Available under the MIT license
 */
!function(e,t,n){e.fn.responsiveSlides=function(i){var a=e.extend({auto:!0,speed:500,timeout:4e3,pager:!1,nav:!1,random:!1,pause:!1,pauseControls:!0,prevText:"Previous",nextText:"Next",maxwidth:"",navContainer:"",manualControls:"",namespace:"rslides",before:e.noop,after:e.noop},i);return this.each((function(){n++;var o,r,s,l,c,d,u=e(this),p=0,f=u.children(),m=f.length,g=parseFloat(a.speed),h=parseFloat(a.timeout),v=parseFloat(a.maxwidth),C=a.namespace,y=C+n,b=C+"_nav "+y+"_nav",w=C+"_here",x=y+"_on",k=y+"_s",I=e("<ul class='"+C+"_tabs "+y+"_tabs' />"),_={float:"left",position:"relative",opacity:1,zIndex:2},T={float:"none",position:"absolute",opacity:0,zIndex:1},P=function(){var e,t=(document.body||document.documentElement).style,n="transition";if("string"==typeof t[n])return!0;for(o=["Moz","Webkit","Khtml","O","ms"],n=n.charAt(0).toUpperCase()+n.substr(1),e=0;e<o.length;e++)if("string"==typeof t[o[e]+n])return!0;return!1}(),S=function(t){a.before(t),P?(f.removeClass(x).css(T).eq(t).addClass(x).css(_),p=t,setTimeout((function(){a.after(t)}),g)):f.stop().fadeOut(g,(function(){e(this).removeClass(x).css(T).css("opacity",1)})).eq(t).fadeIn(g,(function(){e(this).addClass(x).css(_),a.after(t),p=t}))};if(a.random&&(f.sort((function(){return Math.round(Math.random())-.5})),u.empty().append(f)),f.each((function(e){this.id=k+e})),u.addClass(C+" "+y),i&&i.maxwidth&&u.css("max-width",v),f.hide().css(T).eq(0).addClass(x).css(_).show(),P&&f.show().css({"-webkit-transition":"opacity "+g+"ms ease-in-out","-moz-transition":"opacity "+g+"ms ease-in-out","-o-transition":"opacity "+g+"ms ease-in-out",transition:"opacity "+g+"ms ease-in-out"}),f.length>1){if(h<g+100)return;if(a.pager&&!a.manualControls){var O=[];f.each((function(e){var t=e+1;O+="<li><a href='#' class='"+k+t+"'>"+t+"</a></li>"})),I.append(O),i.navContainer?e(a.navContainer).append(I):u.after(I)}if(a.manualControls&&(I=e(a.manualControls)).addClass(C+"_tabs "+y+"_tabs"),(a.pager||a.manualControls)&&I.find("li").each((function(t){e(this).addClass(k+(t+1))})),(a.pager||a.manualControls)&&(d=I.find("a"),r=function(e){d.closest("li").removeClass(w).eq(e).addClass(w)}),a.auto&&(s=function(){c=setInterval((function(){f.stop(!0,!0);var e=p+1<m?p+1:0;(a.pager||a.manualControls)&&r(e),S(e)}),h)})(),l=function(){a.auto&&(clearInterval(c),s())},a.pause&&u.hover((function(){clearInterval(c)}),(function(){l()})),(a.pager||a.manualControls)&&(d.bind("click",(function(t){t.preventDefault(),a.pauseControls||l();var n=d.index(this);p===n||e("."+x).queue("fx").length||(r(n),S(n))})).eq(0).closest("li").addClass(w),a.pauseControls&&d.hover((function(){clearInterval(c)}),(function(){l()}))),a.nav){var z="<a href='#' class='"+b+" prev'>"+a.prevText+"</a><a href='#' class='"+b+" next'>"+a.nextText+"</a>";i.navContainer?e(a.navContainer).append(z):u.after(z);var E=e("."+y+"_nav"),M=E.filter(".prev");E.bind("click",(function(t){t.preventDefault();var n=e("."+x);if(!n.queue("fx").length){var i=f.index(n),o=i-1,s=i+1<m?p+1:0;S(e(this)[0]===M[0]?o:s),(a.pager||a.manualControls)&&r(e(this)[0]===M[0]?o:s),a.pauseControls||l()}})),a.pauseControls&&E.hover((function(){clearInterval(c)}),(function(){l()}))}}if(void 0===document.body.style.maxWidth&&i.maxwidth){var B=function(){u.css("width","100%"),u.width()>v&&u.css("width",v)};B(),e(t).bind("resize",(function(){B()}))}}))}}(jQuery,window,0)},8119:(e,t,n)=>{var i,a,o;a=[n(1669)],i=function(e){var t,n,i,a,o,r,s="Close",l="BeforeClose",c="AfterClose",d="BeforeAppend",u="MarkupParse",p="Open",f="Change",m="mfp",g="."+m,h="mfp-ready",v="mfp-removing",C="mfp-prevent-close",y=function(){},b=!!window.jQuery,w=e(window),x=function(e,n){t.ev.on(m+e+g,n)},k=function(t,n,i,a){var o=document.createElement("div");return o.className="mfp-"+t,i&&(o.innerHTML=i),a?n&&n.appendChild(o):(o=e(o),n&&o.appendTo(n)),o},I=function(n,i){t.ev.triggerHandler(m+n,i),t.st.callbacks&&(n=n.charAt(0).toLowerCase()+n.slice(1),t.st.callbacks[n]&&t.st.callbacks[n].apply(t,e.isArray(i)?i:[i]))},_=function(n){return n===r&&t.currTemplate.closeBtn||(t.currTemplate.closeBtn=e(t.st.closeMarkup.replace("%title%",t.st.tClose)),r=n),t.currTemplate.closeBtn},T=function(){e.magnificPopup.instance||((t=new y).init(),e.magnificPopup.instance=t)},P=function(){var e=document.createElement("p").style,t=["ms","O","Moz","Webkit"];if(void 0!==e.transition)return!0;for(;t.length;)if(t.pop()+"Transition"in e)return!0;return!1};y.prototype={constructor:y,init:function(){var n=navigator.appVersion;t.isLowIE=t.isIE8=document.all&&!document.addEventListener,t.isAndroid=/android/gi.test(n),t.isIOS=/iphone|ipad|ipod/gi.test(n),t.supportsTransition=P(),t.probablyMobile=t.isAndroid||t.isIOS||/(Opera Mini)|Kindle|webOS|BlackBerry|(Opera Mobi)|(Windows Phone)|IEMobile/i.test(navigator.userAgent),i=e(document),t.popupsCache={}},open:function(n){var a;if(!1===n.isObj){t.items=n.items.toArray(),t.index=0;var r,s=n.items;for(a=0;a<s.length;a++)if((r=s[a]).parsed&&(r=r.el[0]),r===n.el[0]){t.index=a;break}}else t.items=e.isArray(n.items)?n.items:[n.items],t.index=n.index||0;if(!t.isOpen){t.types=[],o="",n.mainEl&&n.mainEl.length?t.ev=n.mainEl.eq(0):t.ev=i,n.key?(t.popupsCache[n.key]||(t.popupsCache[n.key]={}),t.currTemplate=t.popupsCache[n.key]):t.currTemplate={},t.st=e.extend(!0,{},e.magnificPopup.defaults,n),t.fixedContentPos="auto"===t.st.fixedContentPos?!t.probablyMobile:t.st.fixedContentPos,t.st.modal&&(t.st.closeOnContentClick=!1,t.st.closeOnBgClick=!1,t.st.showCloseBtn=!1,t.st.enableEscapeKey=!1),t.bgOverlay||(t.bgOverlay=k("bg").on("click"+g,(function(){t.close()})),t.wrap=k("wrap").attr("tabindex",-1).on("click"+g,(function(e){t._checkIfClose(e.target)&&t.close()})),t.container=k("container",t.wrap)),t.contentContainer=k("content"),t.st.preloader&&(t.preloader=k("preloader",t.container,t.st.tLoading));var l=e.magnificPopup.modules;for(a=0;a<l.length;a++){var c=l[a];c=c.charAt(0).toUpperCase()+c.slice(1),t["init"+c].call(t)}I("BeforeOpen"),t.st.showCloseBtn&&(t.st.closeBtnInside?(x(u,(function(e,t,n,i){n.close_replaceWith=_(i.type)})),o+=" mfp-close-btn-in"):t.wrap.append(_())),t.st.alignTop&&(o+=" mfp-align-top"),t.fixedContentPos?t.wrap.css({overflow:t.st.overflowY,overflowX:"hidden",overflowY:t.st.overflowY}):t.wrap.css({top:w.scrollTop(),position:"absolute"}),(!1===t.st.fixedBgPos||"auto"===t.st.fixedBgPos&&!t.fixedContentPos)&&t.bgOverlay.css({height:i.height(),position:"absolute"}),t.st.enableEscapeKey&&i.on("keyup"+g,(function(e){27===e.keyCode&&t.close()})),w.on("resize"+g,(function(){t.updateSize()})),t.st.closeOnContentClick||(o+=" mfp-auto-cursor"),o&&t.wrap.addClass(o);var d=t.wH=w.height(),f={};if(t.fixedContentPos&&t._hasScrollBar(d)){var m=t._getScrollbarSize();m&&(f.marginRight=m)}t.fixedContentPos&&(t.isIE7?e("body, html").css("overflow","hidden"):f.overflow="hidden");var v=t.st.mainClass;return t.isIE7&&(v+=" mfp-ie7"),v&&t._addClassToMFP(v),t.updateItemHTML(),I("BuildControls"),e("html").css(f),t.bgOverlay.add(t.wrap).prependTo(t.st.prependTo||e(document.body)),t._lastFocusedEl=document.activeElement,setTimeout((function(){t.content?(t._addClassToMFP(h),t._setFocus()):t.bgOverlay.addClass(h),i.on("focusin"+g,t._onFocusIn)}),16),t.isOpen=!0,t.updateSize(d),I(p),n}t.updateItemHTML()},close:function(){t.isOpen&&(I(l),t.isOpen=!1,t.st.removalDelay&&!t.isLowIE&&t.supportsTransition?(t._addClassToMFP(v),setTimeout((function(){t._close()}),t.st.removalDelay)):t._close())},_close:function(){I(s);var n=v+" "+h+" ";if(t.bgOverlay.detach(),t.wrap.detach(),t.container.empty(),t.st.mainClass&&(n+=t.st.mainClass+" "),t._removeClassFromMFP(n),t.fixedContentPos){var a={marginRight:""};t.isIE7?e("body, html").css("overflow",""):a.overflow="",e("html").css(a)}i.off("keyup"+g+" focusin"+g),t.ev.off(g),t.wrap.attr("class","mfp-wrap").removeAttr("style"),t.bgOverlay.attr("class","mfp-bg"),t.container.attr("class","mfp-container"),!t.st.showCloseBtn||t.st.closeBtnInside&&!0!==t.currTemplate[t.currItem.type]||t.currTemplate.closeBtn&&t.currTemplate.closeBtn.detach(),t.st.autoFocusLast&&t._lastFocusedEl&&e(t._lastFocusedEl).focus(),t.currItem=null,t.content=null,t.currTemplate=null,t.prevHeight=0,I(c)},updateSize:function(e){if(t.isIOS){var n=document.documentElement.clientWidth/window.innerWidth,i=window.innerHeight*n;t.wrap.css("height",i),t.wH=i}else t.wH=e||w.height();t.fixedContentPos||t.wrap.css("height",t.wH),I("Resize")},updateItemHTML:function(){var n=t.items[t.index];t.contentContainer.detach(),t.content&&t.content.detach(),n.parsed||(n=t.parseEl(t.index));var i=n.type;if(I("BeforeChange",[t.currItem?t.currItem.type:"",i]),t.currItem=n,!t.currTemplate[i]){var o=!!t.st[i]&&t.st[i].markup;I("FirstMarkupParse",o),t.currTemplate[i]=!o||e(o)}a&&a!==n.type&&t.container.removeClass("mfp-"+a+"-holder");var r=t["get"+i.charAt(0).toUpperCase()+i.slice(1)](n,t.currTemplate[i]);t.appendContent(r,i),n.preloaded=!0,I(f,n),a=n.type,t.container.prepend(t.contentContainer),I("AfterChange")},appendContent:function(e,n){t.content=e,e?t.st.showCloseBtn&&t.st.closeBtnInside&&!0===t.currTemplate[n]?t.content.find(".mfp-close").length||t.content.append(_()):t.content=e:t.content="",I(d),t.container.addClass("mfp-"+n+"-holder"),t.contentContainer.append(t.content)},parseEl:function(n){var i,a=t.items[n];if(a.tagName?a={el:e(a)}:(i=a.type,a={data:a,src:a.src}),a.el){for(var o=t.types,r=0;r<o.length;r++)if(a.el.hasClass("mfp-"+o[r])){i=o[r];break}a.src=a.el.attr("data-mfp-src"),a.src||(a.src=a.el.attr("href"))}return a.type=i||t.st.type||"inline",a.index=n,a.parsed=!0,t.items[n]=a,I("ElementParse",a),t.items[n]},addGroup:function(e,n){var i=function(i){i.mfpEl=this,t._openClick(i,e,n)};n||(n={});var a="click.magnificPopup";n.mainEl=e,n.items?(n.isObj=!0,e.off(a).on(a,i)):(n.isObj=!1,n.delegate?e.off(a).on(a,n.delegate,i):(n.items=e,e.off(a).on(a,i)))},_openClick:function(n,i,a){if((void 0!==a.midClick?a.midClick:e.magnificPopup.defaults.midClick)||!(2===n.which||n.ctrlKey||n.metaKey||n.altKey||n.shiftKey)){var o=void 0!==a.disableOn?a.disableOn:e.magnificPopup.defaults.disableOn;if(o)if(e.isFunction(o)){if(!o.call(t))return!0}else if(w.width()<o)return!0;n.type&&(n.preventDefault(),t.isOpen&&n.stopPropagation()),a.el=e(n.mfpEl),a.delegate&&(a.items=i.find(a.delegate)),t.open(a)}},updateStatus:function(e,i){if(t.preloader){n!==e&&t.container.removeClass("mfp-s-"+n),i||"loading"!==e||(i=t.st.tLoading);var a={status:e,text:i};I("UpdateStatus",a),e=a.status,i=a.text,t.preloader.html(i),t.preloader.find("a").on("click",(function(e){e.stopImmediatePropagation()})),t.container.addClass("mfp-s-"+e),n=e}},_checkIfClose:function(n){if(!e(n).hasClass(C)){var i=t.st.closeOnContentClick,a=t.st.closeOnBgClick;if(i&&a)return!0;if(!t.content||e(n).hasClass("mfp-close")||t.preloader&&n===t.preloader[0])return!0;if(n===t.content[0]||e.contains(t.content[0],n)){if(i)return!0}else if(a&&e.contains(document,n))return!0;return!1}},_addClassToMFP:function(e){t.bgOverlay.addClass(e),t.wrap.addClass(e)},_removeClassFromMFP:function(e){this.bgOverlay.removeClass(e),t.wrap.removeClass(e)},_hasScrollBar:function(e){return(t.isIE7?i.height():document.body.scrollHeight)>(e||w.height())},_setFocus:function(){(t.st.focus?t.content.find(t.st.focus).eq(0):t.wrap).focus()},_onFocusIn:function(n){if(n.target!==t.wrap[0]&&!e.contains(t.wrap[0],n.target))return t._setFocus(),!1},_parseMarkup:function(t,n,i){var a;i.data&&(n=e.extend(i.data,n)),I(u,[t,n,i]),e.each(n,(function(n,i){if(void 0===i||!1===i)return!0;if((a=n.split("_")).length>1){var o=t.find(g+"-"+a[0]);if(o.length>0){var r=a[1];"replaceWith"===r?o[0]!==i[0]&&o.replaceWith(i):"img"===r?o.is("img")?o.attr("src",i):o.replaceWith(e("<img>").attr("src",i).attr("class",o.attr("class"))):o.attr(a[1],i)}}else t.find(g+"-"+n).html(i)}))},_getScrollbarSize:function(){if(void 0===t.scrollbarSize){var e=document.createElement("div");e.style.cssText="width: 99px; height: 99px; overflow: scroll; position: absolute; top: -9999px;",document.body.appendChild(e),t.scrollbarSize=e.offsetWidth-e.clientWidth,document.body.removeChild(e)}return t.scrollbarSize}},e.magnificPopup={instance:null,proto:y.prototype,modules:[],open:function(t,n){return T(),(t=t?e.extend(!0,{},t):{}).isObj=!0,t.index=n||0,this.instance.open(t)},close:function(){return e.magnificPopup.instance&&e.magnificPopup.instance.close()},registerModule:function(t,n){n.options&&(e.magnificPopup.defaults[t]=n.options),e.extend(this.proto,n.proto),this.modules.push(t)},defaults:{disableOn:0,key:null,midClick:!1,mainClass:"",preloader:!0,focus:"",closeOnContentClick:!1,closeOnBgClick:!0,closeBtnInside:!0,showCloseBtn:!0,enableEscapeKey:!0,modal:!1,alignTop:!1,removalDelay:0,prependTo:null,fixedContentPos:"auto",fixedBgPos:"auto",overflowY:"auto",closeMarkup:'<button title="%title%" type="button" class="mfp-close">&#215;</button>',tClose:"Close (Esc)",tLoading:"Loading...",autoFocusLast:!0}},e.fn.magnificPopup=function(n){T();var i=e(this);if("string"==typeof n)if("open"===n){var a,o=b?i.data("magnificPopup"):i[0].magnificPopup,r=parseInt(arguments[1],10)||0;o.items?a=o.items[r]:(a=i,o.delegate&&(a=a.find(o.delegate)),a=a.eq(r)),t._openClick({mfpEl:a},i,o)}else t.isOpen&&t[n].apply(t,Array.prototype.slice.call(arguments,1));else n=e.extend(!0,{},n),b?i.data("magnificPopup",n):i[0].magnificPopup=n,t.addGroup(i,n);return i};var S,O,z,E="inline",M=function(){z&&(O.after(z.addClass(S)).detach(),z=null)};e.magnificPopup.registerModule(E,{options:{hiddenClass:"hide",markup:"",tNotFound:"Content not found"},proto:{initInline:function(){t.types.push(E),x(s+"."+E,(function(){M()}))},getInline:function(n,i){if(M(),n.src){var a=t.st.inline,o=e(n.src);if(o.length){var r=o[0].parentNode;r&&r.tagName&&(O||(S=a.hiddenClass,O=k(S),S="mfp-"+S),z=o.after(O).detach().removeClass(S)),t.updateStatus("ready")}else t.updateStatus("error",a.tNotFound),o=e("<div>");return n.inlineElement=o,o}return t.updateStatus("ready"),t._parseMarkup(i,{},n),i}}});var B,F="ajax",H=function(){B&&e(document.body).removeClass(B)},L=function(){H(),t.req&&t.req.abort()};e.magnificPopup.registerModule(F,{options:{settings:null,cursor:"mfp-ajax-cur",tError:'<a href="%url%">The content</a> could not be loaded.'},proto:{initAjax:function(){t.types.push(F),B=t.st.ajax.cursor,x(s+"."+F,L),x("BeforeChange."+F,L)},getAjax:function(n){B&&e(document.body).addClass(B),t.updateStatus("loading");var i=e.extend({url:n.src,success:function(i,a,o){var r={data:i,xhr:o};I("ParseAjax",r),t.appendContent(e(r.data),F),n.finished=!0,H(),t._setFocus(),setTimeout((function(){t.wrap.addClass(h)}),16),t.updateStatus("ready"),I("AjaxContentAdded")},error:function(){H(),n.finished=n.loadError=!0,t.updateStatus("error",t.st.ajax.tError.replace("%url%",n.src))}},t.st.ajax.settings);return t.req=e.ajax(i),""}}});var A,j=function(n){if(n.data&&void 0!==n.data.title)return n.data.title;var i=t.st.image.titleSrc;if(i){if(e.isFunction(i))return i.call(t,n);if(n.el)return n.el.attr(i)||""}return""};e.magnificPopup.registerModule("image",{options:{markup:'<div class="mfp-figure"><div class="mfp-close"></div><figure><div class="mfp-img"></div><figcaption><div class="mfp-bottom-bar"><div class="mfp-title"></div><div class="mfp-counter"></div></div></figcaption></figure></div>',cursor:"mfp-zoom-out-cur",titleSrc:"title",verticalFit:!0,tError:'<a href="%url%">The image</a> could not be loaded.'},proto:{initImage:function(){var n=t.st.image,i=".image";t.types.push("image"),x(p+i,(function(){"image"===t.currItem.type&&n.cursor&&e(document.body).addClass(n.cursor)})),x(s+i,(function(){n.cursor&&e(document.body).removeClass(n.cursor),w.off("resize"+g)})),x("Resize"+i,t.resizeImage),t.isLowIE&&x("AfterChange",t.resizeImage)},resizeImage:function(){var e=t.currItem;if(e&&e.img&&t.st.image.verticalFit){var n=0;t.isLowIE&&(n=parseInt(e.img.css("padding-top"),10)+parseInt(e.img.css("padding-bottom"),10)),e.img.css("max-height",t.wH-n)}},_onImageHasSize:function(e){e.img&&(e.hasSize=!0,A&&clearInterval(A),e.isCheckingImgSize=!1,I("ImageHasSize",e),e.imgHidden&&(t.content&&t.content.removeClass("mfp-loading"),e.imgHidden=!1))},findImageSize:function(e){var n=0,i=e.img[0];!function a(o){A&&clearInterval(A),A=setInterval((function(){i.naturalWidth>0?t._onImageHasSize(e):(n>200&&clearInterval(A),3==++n?a(10):40===n?a(50):100===n&&a(500))}),o)}(1)},getImage:function(n,i){var a=0,o=function e(){n&&(n.img[0].complete?(n.img.off(".mfploader"),n===t.currItem&&(t._onImageHasSize(n),t.updateStatus("ready")),n.hasSize=!0,n.loaded=!0,I("ImageLoadComplete")):++a<200?setTimeout(e,100):r())},r=function(){n&&(n.img.off(".mfploader"),n===t.currItem&&(t._onImageHasSize(n),t.updateStatus("error",s.tError.replace("%url%",n.src))),n.hasSize=!0,n.loaded=!0,n.loadError=!0)},s=t.st.image,l=i.find(".mfp-img");if(l.length){var c=document.createElement("img");c.className="mfp-img",n.el&&n.el.find("img").length&&(c.alt=n.el.find("img").attr("alt")),n.img=e(c).on("load.mfploader",o).on("error.mfploader",r),c.src=n.src,l.is("img")&&(n.img=n.img.clone()),(c=n.img[0]).naturalWidth>0?n.hasSize=!0:c.width||(n.hasSize=!1)}return t._parseMarkup(i,{title:j(n),img_replaceWith:n.img},n),t.resizeImage(),n.hasSize?(A&&clearInterval(A),n.loadError?(i.addClass("mfp-loading"),t.updateStatus("error",s.tError.replace("%url%",n.src))):(i.removeClass("mfp-loading"),t.updateStatus("ready")),i):(t.updateStatus("loading"),n.loading=!0,n.hasSize||(n.imgHidden=!0,i.addClass("mfp-loading"),t.findImageSize(n)),i)}}});var $,W=function(){return void 0===$&&($=void 0!==document.createElement("p").style.MozTransform),$};e.magnificPopup.registerModule("zoom",{options:{enabled:!1,easing:"ease-in-out",duration:300,opener:function(e){return e.is("img")?e:e.find("img")}},proto:{initZoom:function(){var e,n=t.st.zoom,i=".zoom";if(n.enabled&&t.supportsTransition){var a,o,r=n.duration,c=function(e){var t=e.clone().removeAttr("style").removeAttr("class").addClass("mfp-animated-image"),i="all "+n.duration/1e3+"s "+n.easing,a={position:"fixed",zIndex:9999,left:0,top:0,"-webkit-backface-visibility":"hidden"},o="transition";return a["-webkit-"+o]=a["-moz-"+o]=a["-o-"+o]=a[o]=i,t.css(a),t},d=function(){t.content.css("visibility","visible")};x("BuildControls"+i,(function(){if(t._allowZoom()){if(clearTimeout(a),t.content.css("visibility","hidden"),!(e=t._getItemToZoom()))return void d();(o=c(e)).css(t._getOffset()),t.wrap.append(o),a=setTimeout((function(){o.css(t._getOffset(!0)),a=setTimeout((function(){d(),setTimeout((function(){o.remove(),e=o=null,I("ZoomAnimationEnded")}),16)}),r)}),16)}})),x(l+i,(function(){if(t._allowZoom()){if(clearTimeout(a),t.st.removalDelay=r,!e){if(!(e=t._getItemToZoom()))return;o=c(e)}o.css(t._getOffset(!0)),t.wrap.append(o),t.content.css("visibility","hidden"),setTimeout((function(){o.css(t._getOffset())}),16)}})),x(s+i,(function(){t._allowZoom()&&(d(),o&&o.remove(),e=null)}))}},_allowZoom:function(){return"image"===t.currItem.type},_getItemToZoom:function(){return!!t.currItem.hasSize&&t.currItem.img},_getOffset:function(n){var i,a=(i=n?t.currItem.img:t.st.zoom.opener(t.currItem.el||t.currItem)).offset(),o=parseInt(i.css("padding-top"),10),r=parseInt(i.css("padding-bottom"),10);a.top-=e(window).scrollTop()-o;var s={width:i.width(),height:(b?i.innerHeight():i[0].offsetHeight)-r-o};return W()?s["-moz-transform"]=s.transform="translate("+a.left+"px,"+a.top+"px)":(s.left=a.left,s.top=a.top),s}}});var N="iframe",q="//about:blank",D=function(e){if(t.currTemplate[N]){var n=t.currTemplate[N].find("iframe");n.length&&(e||(n[0].src=q),t.isIE8&&n.css("display",e?"block":"none"))}};e.magnificPopup.registerModule(N,{options:{markup:'<div class="mfp-iframe-scaler"><div class="mfp-close"></div><iframe class="mfp-iframe" src="//about:blank" frameborder="0" allowfullscreen></iframe></div>',srcAction:"iframe_src",patterns:{youtube:{index:"youtube.com",id:"v=",src:"//www.youtube.com/embed/%id%?autoplay=1"},vimeo:{index:"vimeo.com/",id:"/",src:"//player.vimeo.com/video/%id%?autoplay=1"},gmaps:{index:"//maps.google.",src:"%id%&output=embed"}}},proto:{initIframe:function(){t.types.push(N),x("BeforeChange",(function(e,t,n){t!==n&&(t===N?D():n===N&&D(!0))})),x(s+"."+N,(function(){D()}))},getIframe:function(n,i){var a=n.src,o=t.st.iframe;e.each(o.patterns,(function(){if(a.indexOf(this.index)>-1)return this.id&&(a="string"==typeof this.id?a.substr(a.lastIndexOf(this.id)+this.id.length,a.length):this.id.call(this,a)),a=this.src.replace("%id%",a),!1}));var r={};return o.srcAction&&(r[o.srcAction]=a),t._parseMarkup(i,r,n),t.updateStatus("ready"),i}}});var K=function(e){var n=t.items.length;return e>n-1?e-n:e<0?n+e:e},R=function(e,t,n){return e.replace(/%curr%/gi,t+1).replace(/%total%/gi,n)};e.magnificPopup.registerModule("gallery",{options:{enabled:!1,arrowMarkup:'<button title="%title%" type="button" class="mfp-arrow mfp-arrow-%dir%"></button>',preload:[0,2],navigateByImgClick:!0,arrows:!0,tPrev:"Previous (Left arrow key)",tNext:"Next (Right arrow key)",tCounter:"%curr% of %total%"},proto:{initGallery:function(){var n=t.st.gallery,a=".mfp-gallery";if(t.direction=!0,!n||!n.enabled)return!1;o+=" mfp-gallery",x(p+a,(function(){n.navigateByImgClick&&t.wrap.on("click"+a,".mfp-img",(function(){if(t.items.length>1)return t.next(),!1})),i.on("keydown"+a,(function(e){37===e.keyCode?t.prev():39===e.keyCode&&t.next()}))})),x("UpdateStatus"+a,(function(e,n){n.text&&(n.text=R(n.text,t.currItem.index,t.items.length))})),x(u+a,(function(e,i,a,o){var r=t.items.length;a.counter=r>1?R(n.tCounter,o.index,r):""})),x("BuildControls"+a,(function(){if(t.items.length>1&&n.arrows&&!t.arrowLeft){var i=n.arrowMarkup,a=t.arrowLeft=e(i.replace(/%title%/gi,n.tPrev).replace(/%dir%/gi,"left")).addClass(C),o=t.arrowRight=e(i.replace(/%title%/gi,n.tNext).replace(/%dir%/gi,"right")).addClass(C);a.click((function(){t.prev()})),o.click((function(){t.next()})),t.container.append(a.add(o))}})),x(f+a,(function(){t._preloadTimeout&&clearTimeout(t._preloadTimeout),t._preloadTimeout=setTimeout((function(){t.preloadNearbyImages(),t._preloadTimeout=null}),16)})),x(s+a,(function(){i.off(a),t.wrap.off("click"+a),t.arrowRight=t.arrowLeft=null}))},next:function(){t.direction=!0,t.index=K(t.index+1),t.updateItemHTML()},prev:function(){t.direction=!1,t.index=K(t.index-1),t.updateItemHTML()},goTo:function(e){t.direction=e>=t.index,t.index=e,t.updateItemHTML()},preloadNearbyImages:function(){var e,n=t.st.gallery.preload,i=Math.min(n[0],t.items.length),a=Math.min(n[1],t.items.length);for(e=1;e<=(t.direction?a:i);e++)t._preloadItem(t.index+e);for(e=1;e<=(t.direction?i:a);e++)t._preloadItem(t.index-e)},_preloadItem:function(n){if(n=K(n),!t.items[n].preloaded){var i=t.items[n];i.parsed||(i=t.parseEl(n)),I("LazyLoad",i),"image"===i.type&&(i.img=e('<img class="mfp-img" />').on("load.mfploader",(function(){i.hasSize=!0})).on("error.mfploader",(function(){i.hasSize=!0,i.loadError=!0,I("LazyLoadError",i)})).attr("src",i.src)),i.preloaded=!0}}}});var Z="retina";e.magnificPopup.registerModule(Z,{options:{replaceSrc:function(e){return e.src.replace(/\.\w+$/,(function(e){return"@2x"+e}))},ratio:1},proto:{initRetina:function(){if(window.devicePixelRatio>1){var e=t.st.retina,n=e.ratio;(n=isNaN(n)?n():n)>1&&(x("ImageHasSize."+Z,(function(e,t){t.img.css({"max-width":t.img[0].naturalWidth/n,width:"100%"})})),x("ElementParse."+Z,(function(t,i){i.src=e.replaceSrc(i,n)})))}}}}),T()},void 0===(o="function"==typeof i?i.apply(t,a):i)||(e.exports=o)},1669:e=>{"use strict";e.exports=jQuery}},t={};function n(i){var a=t[i];if(void 0!==a)return a.exports;var o=t[i]={exports:{}};return e[i](o,o.exports,n),o.exports}n.n=e=>{var t=e&&e.__esModule?()=>e.default:()=>e;return n.d(t,{a:t}),t},n.d=(e,t)=>{for(var i in t)n.o(t,i)&&!n.o(e,i)&&Object.defineProperty(e,i,{enumerable:!0,get:t[i]})},n.o=(e,t)=>Object.prototype.hasOwnProperty.call(e,t),(()=>{"use strict";n(8119),n(9703),n(5547),n(4062);window.$=window.jQuery=jQuery})()})();