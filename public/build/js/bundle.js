<<<<<<< HEAD
/* axios v0.18.0 | (c) 2018 by Matt Zabriskie */
!function(e,t){"object"==typeof exports&&"object"==typeof module?module.exports=t():"function"==typeof define&&define.amd?define([],t):"object"==typeof exports?exports.axios=t():e.axios=t()}(this,function(){return function(e){function t(r){if(n[r])return n[r].exports;var o=n[r]={exports:{},id:r,loaded:!1};return e[r].call(o.exports,o,o.exports,t),o.loaded=!0,o.exports}var n={};return t.m=e,t.c=n,t.p="",t(0)}([function(e,t,n){e.exports=n(1)},function(e,t,n){"use strict";function r(e){var t=new s(e),n=i(s.prototype.request,t);return o.extend(n,s.prototype,t),o.extend(n,t),n}var o=n(2),i=n(3),s=n(5),u=n(6),a=r(u);a.Axios=s,a.create=function(e){return r(o.merge(u,e))},a.Cancel=n(23),a.CancelToken=n(24),a.isCancel=n(20),a.all=function(e){return Promise.all(e)},a.spread=n(25),e.exports=a,e.exports.default=a},function(e,t,n){"use strict";function r(e){return"[object Array]"===R.call(e)}function o(e){return"[object ArrayBuffer]"===R.call(e)}function i(e){return"undefined"!=typeof FormData&&e instanceof FormData}function s(e){var t;return t="undefined"!=typeof ArrayBuffer&&ArrayBuffer.isView?ArrayBuffer.isView(e):e&&e.buffer&&e.buffer instanceof ArrayBuffer}function u(e){return"string"==typeof e}function a(e){return"number"==typeof e}function c(e){return"undefined"==typeof e}function f(e){return null!==e&&"object"==typeof e}function p(e){return"[object Date]"===R.call(e)}function d(e){return"[object File]"===R.call(e)}function l(e){return"[object Blob]"===R.call(e)}function h(e){return"[object Function]"===R.call(e)}function m(e){return f(e)&&h(e.pipe)}function y(e){return"undefined"!=typeof URLSearchParams&&e instanceof URLSearchParams}function w(e){return e.replace(/^\s*/,"").replace(/\s*$/,"")}function g(){return("undefined"==typeof navigator||"ReactNative"!==navigator.product)&&("undefined"!=typeof window&&"undefined"!=typeof document)}function v(e,t){if(null!==e&&"undefined"!=typeof e)if("object"!=typeof e&&(e=[e]),r(e))for(var n=0,o=e.length;n<o;n++)t.call(null,e[n],n,e);else for(var i in e)Object.prototype.hasOwnProperty.call(e,i)&&t.call(null,e[i],i,e)}function x(){function e(e,n){"object"==typeof t[n]&&"object"==typeof e?t[n]=x(t[n],e):t[n]=e}for(var t={},n=0,r=arguments.length;n<r;n++)v(arguments[n],e);return t}function b(e,t,n){return v(t,function(t,r){n&&"function"==typeof t?e[r]=E(t,n):e[r]=t}),e}var E=n(3),C=n(4),R=Object.prototype.toString;e.exports={isArray:r,isArrayBuffer:o,isBuffer:C,isFormData:i,isArrayBufferView:s,isString:u,isNumber:a,isObject:f,isUndefined:c,isDate:p,isFile:d,isBlob:l,isFunction:h,isStream:m,isURLSearchParams:y,isStandardBrowserEnv:g,forEach:v,merge:x,extend:b,trim:w}},function(e,t){"use strict";e.exports=function(e,t){return function(){for(var n=new Array(arguments.length),r=0;r<n.length;r++)n[r]=arguments[r];return e.apply(t,n)}}},function(e,t){function n(e){return!!e.constructor&&"function"==typeof e.constructor.isBuffer&&e.constructor.isBuffer(e)}function r(e){return"function"==typeof e.readFloatLE&&"function"==typeof e.slice&&n(e.slice(0,0))}/*!
	 * Determine if an object is a Buffer
	 *
	 * @author   Feross Aboukhadijeh <https://feross.org>
	 * @license  MIT
	 */
e.exports=function(e){return null!=e&&(n(e)||r(e)||!!e._isBuffer)}},function(e,t,n){"use strict";function r(e){this.defaults=e,this.interceptors={request:new s,response:new s}}var o=n(6),i=n(2),s=n(17),u=n(18);r.prototype.request=function(e){"string"==typeof e&&(e=i.merge({url:arguments[0]},arguments[1])),e=i.merge(o,{method:"get"},this.defaults,e),e.method=e.method.toLowerCase();var t=[u,void 0],n=Promise.resolve(e);for(this.interceptors.request.forEach(function(e){t.unshift(e.fulfilled,e.rejected)}),this.interceptors.response.forEach(function(e){t.push(e.fulfilled,e.rejected)});t.length;)n=n.then(t.shift(),t.shift());return n},i.forEach(["delete","get","head","options"],function(e){r.prototype[e]=function(t,n){return this.request(i.merge(n||{},{method:e,url:t}))}}),i.forEach(["post","put","patch"],function(e){r.prototype[e]=function(t,n,r){return this.request(i.merge(r||{},{method:e,url:t,data:n}))}}),e.exports=r},function(e,t,n){"use strict";function r(e,t){!i.isUndefined(e)&&i.isUndefined(e["Content-Type"])&&(e["Content-Type"]=t)}function o(){var e;return"undefined"!=typeof XMLHttpRequest?e=n(8):"undefined"!=typeof process&&(e=n(8)),e}var i=n(2),s=n(7),u={"Content-Type":"application/x-www-form-urlencoded"},a={adapter:o(),transformRequest:[function(e,t){return s(t,"Content-Type"),i.isFormData(e)||i.isArrayBuffer(e)||i.isBuffer(e)||i.isStream(e)||i.isFile(e)||i.isBlob(e)?e:i.isArrayBufferView(e)?e.buffer:i.isURLSearchParams(e)?(r(t,"application/x-www-form-urlencoded;charset=utf-8"),e.toString()):i.isObject(e)?(r(t,"application/json;charset=utf-8"),JSON.stringify(e)):e}],transformResponse:[function(e){if("string"==typeof e)try{e=JSON.parse(e)}catch(e){}return e}],timeout:0,xsrfCookieName:"XSRF-TOKEN",xsrfHeaderName:"X-XSRF-TOKEN",maxContentLength:-1,validateStatus:function(e){return e>=200&&e<300}};a.headers={common:{Accept:"application/json, text/plain, */*"}},i.forEach(["delete","get","head"],function(e){a.headers[e]={}}),i.forEach(["post","put","patch"],function(e){a.headers[e]=i.merge(u)}),e.exports=a},function(e,t,n){"use strict";var r=n(2);e.exports=function(e,t){r.forEach(e,function(n,r){r!==t&&r.toUpperCase()===t.toUpperCase()&&(e[t]=n,delete e[r])})}},function(e,t,n){"use strict";var r=n(2),o=n(9),i=n(12),s=n(13),u=n(14),a=n(10),c="undefined"!=typeof window&&window.btoa&&window.btoa.bind(window)||n(15);e.exports=function(e){return new Promise(function(t,f){var p=e.data,d=e.headers;r.isFormData(p)&&delete d["Content-Type"];var l=new XMLHttpRequest,h="onreadystatechange",m=!1;if("undefined"==typeof window||!window.XDomainRequest||"withCredentials"in l||u(e.url)||(l=new window.XDomainRequest,h="onload",m=!0,l.onprogress=function(){},l.ontimeout=function(){}),e.auth){var y=e.auth.username||"",w=e.auth.password||"";d.Authorization="Basic "+c(y+":"+w)}if(l.open(e.method.toUpperCase(),i(e.url,e.params,e.paramsSerializer),!0),l.timeout=e.timeout,l[h]=function(){if(l&&(4===l.readyState||m)&&(0!==l.status||l.responseURL&&0===l.responseURL.indexOf("file:"))){var n="getAllResponseHeaders"in l?s(l.getAllResponseHeaders()):null,r=e.responseType&&"text"!==e.responseType?l.response:l.responseText,i={data:r,status:1223===l.status?204:l.status,statusText:1223===l.status?"No Content":l.statusText,headers:n,config:e,request:l};o(t,f,i),l=null}},l.onerror=function(){f(a("Network Error",e,null,l)),l=null},l.ontimeout=function(){f(a("timeout of "+e.timeout+"ms exceeded",e,"ECONNABORTED",l)),l=null},r.isStandardBrowserEnv()){var g=n(16),v=(e.withCredentials||u(e.url))&&e.xsrfCookieName?g.read(e.xsrfCookieName):void 0;v&&(d[e.xsrfHeaderName]=v)}if("setRequestHeader"in l&&r.forEach(d,function(e,t){"undefined"==typeof p&&"content-type"===t.toLowerCase()?delete d[t]:l.setRequestHeader(t,e)}),e.withCredentials&&(l.withCredentials=!0),e.responseType)try{l.responseType=e.responseType}catch(t){if("json"!==e.responseType)throw t}"function"==typeof e.onDownloadProgress&&l.addEventListener("progress",e.onDownloadProgress),"function"==typeof e.onUploadProgress&&l.upload&&l.upload.addEventListener("progress",e.onUploadProgress),e.cancelToken&&e.cancelToken.promise.then(function(e){l&&(l.abort(),f(e),l=null)}),void 0===p&&(p=null),l.send(p)})}},function(e,t,n){"use strict";var r=n(10);e.exports=function(e,t,n){var o=n.config.validateStatus;n.status&&o&&!o(n.status)?t(r("Request failed with status code "+n.status,n.config,null,n.request,n)):e(n)}},function(e,t,n){"use strict";var r=n(11);e.exports=function(e,t,n,o,i){var s=new Error(e);return r(s,t,n,o,i)}},function(e,t){"use strict";e.exports=function(e,t,n,r,o){return e.config=t,n&&(e.code=n),e.request=r,e.response=o,e}},function(e,t,n){"use strict";function r(e){return encodeURIComponent(e).replace(/%40/gi,"@").replace(/%3A/gi,":").replace(/%24/g,"$").replace(/%2C/gi,",").replace(/%20/g,"+").replace(/%5B/gi,"[").replace(/%5D/gi,"]")}var o=n(2);e.exports=function(e,t,n){if(!t)return e;var i;if(n)i=n(t);else if(o.isURLSearchParams(t))i=t.toString();else{var s=[];o.forEach(t,function(e,t){null!==e&&"undefined"!=typeof e&&(o.isArray(e)?t+="[]":e=[e],o.forEach(e,function(e){o.isDate(e)?e=e.toISOString():o.isObject(e)&&(e=JSON.stringify(e)),s.push(r(t)+"="+r(e))}))}),i=s.join("&")}return i&&(e+=(e.indexOf("?")===-1?"?":"&")+i),e}},function(e,t,n){"use strict";var r=n(2),o=["age","authorization","content-length","content-type","etag","expires","from","host","if-modified-since","if-unmodified-since","last-modified","location","max-forwards","proxy-authorization","referer","retry-after","user-agent"];e.exports=function(e){var t,n,i,s={};return e?(r.forEach(e.split("\n"),function(e){if(i=e.indexOf(":"),t=r.trim(e.substr(0,i)).toLowerCase(),n=r.trim(e.substr(i+1)),t){if(s[t]&&o.indexOf(t)>=0)return;"set-cookie"===t?s[t]=(s[t]?s[t]:[]).concat([n]):s[t]=s[t]?s[t]+", "+n:n}}),s):s}},function(e,t,n){"use strict";var r=n(2);e.exports=r.isStandardBrowserEnv()?function(){function e(e){var t=e;return n&&(o.setAttribute("href",t),t=o.href),o.setAttribute("href",t),{href:o.href,protocol:o.protocol?o.protocol.replace(/:$/,""):"",host:o.host,search:o.search?o.search.replace(/^\?/,""):"",hash:o.hash?o.hash.replace(/^#/,""):"",hostname:o.hostname,port:o.port,pathname:"/"===o.pathname.charAt(0)?o.pathname:"/"+o.pathname}}var t,n=/(msie|trident)/i.test(navigator.userAgent),o=document.createElement("a");return t=e(window.location.href),function(n){var o=r.isString(n)?e(n):n;return o.protocol===t.protocol&&o.host===t.host}}():function(){return function(){return!0}}()},function(e,t){"use strict";function n(){this.message="String contains an invalid character"}function r(e){for(var t,r,i=String(e),s="",u=0,a=o;i.charAt(0|u)||(a="=",u%1);s+=a.charAt(63&t>>8-u%1*8)){if(r=i.charCodeAt(u+=.75),r>255)throw new n;t=t<<8|r}return s}var o="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";n.prototype=new Error,n.prototype.code=5,n.prototype.name="InvalidCharacterError",e.exports=r},function(e,t,n){"use strict";var r=n(2);e.exports=r.isStandardBrowserEnv()?function(){return{write:function(e,t,n,o,i,s){var u=[];u.push(e+"="+encodeURIComponent(t)),r.isNumber(n)&&u.push("expires="+new Date(n).toGMTString()),r.isString(o)&&u.push("path="+o),r.isString(i)&&u.push("domain="+i),s===!0&&u.push("secure"),document.cookie=u.join("; ")},read:function(e){var t=document.cookie.match(new RegExp("(^|;\\s*)("+e+")=([^;]*)"));return t?decodeURIComponent(t[3]):null},remove:function(e){this.write(e,"",Date.now()-864e5)}}}():function(){return{write:function(){},read:function(){return null},remove:function(){}}}()},function(e,t,n){"use strict";function r(){this.handlers=[]}var o=n(2);r.prototype.use=function(e,t){return this.handlers.push({fulfilled:e,rejected:t}),this.handlers.length-1},r.prototype.eject=function(e){this.handlers[e]&&(this.handlers[e]=null)},r.prototype.forEach=function(e){o.forEach(this.handlers,function(t){null!==t&&e(t)})},e.exports=r},function(e,t,n){"use strict";function r(e){e.cancelToken&&e.cancelToken.throwIfRequested()}var o=n(2),i=n(19),s=n(20),u=n(6),a=n(21),c=n(22);e.exports=function(e){r(e),e.baseURL&&!a(e.url)&&(e.url=c(e.baseURL,e.url)),e.headers=e.headers||{},e.data=i(e.data,e.headers,e.transformRequest),e.headers=o.merge(e.headers.common||{},e.headers[e.method]||{},e.headers||{}),o.forEach(["delete","get","head","post","put","patch","common"],function(t){delete e.headers[t]});var t=e.adapter||u.adapter;return t(e).then(function(t){return r(e),t.data=i(t.data,t.headers,e.transformResponse),t},function(t){return s(t)||(r(e),t&&t.response&&(t.response.data=i(t.response.data,t.response.headers,e.transformResponse))),Promise.reject(t)})}},function(e,t,n){"use strict";var r=n(2);e.exports=function(e,t,n){return r.forEach(n,function(n){e=n(e,t)}),e}},function(e,t){"use strict";e.exports=function(e){return!(!e||!e.__CANCEL__)}},function(e,t){"use strict";e.exports=function(e){return/^([a-z][a-z\d\+\-\.]*:)?\/\//i.test(e)}},function(e,t){"use strict";e.exports=function(e,t){return t?e.replace(/\/+$/,"")+"/"+t.replace(/^\/+/,""):e}},function(e,t){"use strict";function n(e){this.message=e}n.prototype.toString=function(){return"Cancel"+(this.message?": "+this.message:"")},n.prototype.__CANCEL__=!0,e.exports=n},function(e,t,n){"use strict";function r(e){if("function"!=typeof e)throw new TypeError("executor must be a function.");var t;this.promise=new Promise(function(e){t=e});var n=this;e(function(e){n.reason||(n.reason=new o(e),t(n.reason))})}var o=n(23);r.prototype.throwIfRequested=function(){if(this.reason)throw this.reason},r.source=function(){var e,t=new r(function(t){e=t});return{token:t,cancel:e}},e.exports=r},function(e,t){"use strict";e.exports=function(e){return function(t){return e.apply(null,t)}}}])});
/*
     _ _      _       _
 ___| (_) ___| | __  (_)___
/ __| | |/ __| |/ /  | / __|
\__ \ | | (__|   < _ | \__ \
|___/_|_|\___|_|\_(_)/ |___/
                   |__/

 Version: 1.6.0
  Author: Ken Wheeler
 Website: http://kenwheeler.github.io
    Docs: http://kenwheeler.github.io/slick
    Repo: http://github.com/kenwheeler/slick
  Issues: http://github.com/kenwheeler/slick/issues

 */
!function(a){"use strict";"function"==typeof define&&define.amd?define(["jquery"],a):"undefined"!=typeof exports?module.exports=a(require("jquery")):a(jQuery)}(function(a){"use strict";var b=window.Slick||{};b=function(){function c(c,d){var f,e=this;e.defaults={accessibility:!0,adaptiveHeight:!1,appendArrows:a(c),appendDots:a(c),arrows:!0,asNavFor:null,prevArrow:'<button type="button" data-role="none" class="slick-prev" aria-label="Previous" tabindex="0" role="button">Previous</button>',nextArrow:'<button type="button" data-role="none" class="slick-next" aria-label="Next" tabindex="0" role="button">Next</button>',autoplay:!1,autoplaySpeed:3e3,centerMode:!1,centerPadding:"50px",cssEase:"ease",customPaging:function(b,c){return a('<button type="button" data-role="none" role="button" tabindex="0" />').text(c+1)},dots:!1,dotsClass:"slick-dots",draggable:!0,easing:"linear",edgeFriction:.35,fade:!1,focusOnSelect:!1,infinite:!0,initialSlide:0,lazyLoad:"ondemand",mobileFirst:!1,pauseOnHover:!0,pauseOnFocus:!0,pauseOnDotsHover:!1,respondTo:"window",responsive:null,rows:1,rtl:!1,slide:"",slidesPerRow:1,slidesToShow:1,slidesToScroll:1,speed:500,swipe:!0,swipeToSlide:!1,touchMove:!0,touchThreshold:5,useCSS:!0,useTransform:!0,variableWidth:!1,vertical:!1,verticalSwiping:!1,waitForAnimate:!0,zIndex:1e3},e.initials={animating:!1,dragging:!1,autoPlayTimer:null,currentDirection:0,currentLeft:null,currentSlide:0,direction:1,$dots:null,listWidth:null,listHeight:null,loadIndex:0,$nextArrow:null,$prevArrow:null,slideCount:null,slideWidth:null,$slideTrack:null,$slides:null,sliding:!1,slideOffset:0,swipeLeft:null,$list:null,touchObject:{},transformsEnabled:!1,unslicked:!1},a.extend(e,e.initials),e.activeBreakpoint=null,e.animType=null,e.animProp=null,e.breakpoints=[],e.breakpointSettings=[],e.cssTransitions=!1,e.focussed=!1,e.interrupted=!1,e.hidden="hidden",e.paused=!0,e.positionProp=null,e.respondTo=null,e.rowCount=1,e.shouldClick=!0,e.$slider=a(c),e.$slidesCache=null,e.transformType=null,e.transitionType=null,e.visibilityChange="visibilitychange",e.windowWidth=0,e.windowTimer=null,f=a(c).data("slick")||{},e.options=a.extend({},e.defaults,d,f),e.currentSlide=e.options.initialSlide,e.originalSettings=e.options,"undefined"!=typeof document.mozHidden?(e.hidden="mozHidden",e.visibilityChange="mozvisibilitychange"):"undefined"!=typeof document.webkitHidden&&(e.hidden="webkitHidden",e.visibilityChange="webkitvisibilitychange"),e.autoPlay=a.proxy(e.autoPlay,e),e.autoPlayClear=a.proxy(e.autoPlayClear,e),e.autoPlayIterator=a.proxy(e.autoPlayIterator,e),e.changeSlide=a.proxy(e.changeSlide,e),e.clickHandler=a.proxy(e.clickHandler,e),e.selectHandler=a.proxy(e.selectHandler,e),e.setPosition=a.proxy(e.setPosition,e),e.swipeHandler=a.proxy(e.swipeHandler,e),e.dragHandler=a.proxy(e.dragHandler,e),e.keyHandler=a.proxy(e.keyHandler,e),e.instanceUid=b++,e.htmlExpr=/^(?:\s*(<[\w\W]+>)[^>]*)$/,e.registerBreakpoints(),e.init(!0)}var b=0;return c}(),b.prototype.activateADA=function(){var a=this;a.$slideTrack.find(".slick-active").attr({"aria-hidden":"false"}).find("a, input, button, select").attr({tabindex:"0"})},b.prototype.addSlide=b.prototype.slickAdd=function(b,c,d){var e=this;if("boolean"==typeof c)d=c,c=null;else if(0>c||c>=e.slideCount)return!1;e.unload(),"number"==typeof c?0===c&&0===e.$slides.length?a(b).appendTo(e.$slideTrack):d?a(b).insertBefore(e.$slides.eq(c)):a(b).insertAfter(e.$slides.eq(c)):d===!0?a(b).prependTo(e.$slideTrack):a(b).appendTo(e.$slideTrack),e.$slides=e.$slideTrack.children(this.options.slide),e.$slideTrack.children(this.options.slide).detach(),e.$slideTrack.append(e.$slides),e.$slides.each(function(b,c){a(c).attr("data-slick-index",b)}),e.$slidesCache=e.$slides,e.reinit()},b.prototype.animateHeight=function(){var a=this;if(1===a.options.slidesToShow&&a.options.adaptiveHeight===!0&&a.options.vertical===!1){var b=a.$slides.eq(a.currentSlide).outerHeight(!0);a.$list.animate({height:b},a.options.speed)}},b.prototype.animateSlide=function(b,c){var d={},e=this;e.animateHeight(),e.options.rtl===!0&&e.options.vertical===!1&&(b=-b),e.transformsEnabled===!1?e.options.vertical===!1?e.$slideTrack.animate({left:b},e.options.speed,e.options.easing,c):e.$slideTrack.animate({top:b},e.options.speed,e.options.easing,c):e.cssTransitions===!1?(e.options.rtl===!0&&(e.currentLeft=-e.currentLeft),a({animStart:e.currentLeft}).animate({animStart:b},{duration:e.options.speed,easing:e.options.easing,step:function(a){a=Math.ceil(a),e.options.vertical===!1?(d[e.animType]="translate("+a+"px, 0px)",e.$slideTrack.css(d)):(d[e.animType]="translate(0px,"+a+"px)",e.$slideTrack.css(d))},complete:function(){c&&c.call()}})):(e.applyTransition(),b=Math.ceil(b),e.options.vertical===!1?d[e.animType]="translate3d("+b+"px, 0px, 0px)":d[e.animType]="translate3d(0px,"+b+"px, 0px)",e.$slideTrack.css(d),c&&setTimeout(function(){e.disableTransition(),c.call()},e.options.speed))},b.prototype.getNavTarget=function(){var b=this,c=b.options.asNavFor;return c&&null!==c&&(c=a(c).not(b.$slider)),c},b.prototype.asNavFor=function(b){var c=this,d=c.getNavTarget();null!==d&&"object"==typeof d&&d.each(function(){var c=a(this).slick("getSlick");c.unslicked||c.slideHandler(b,!0)})},b.prototype.applyTransition=function(a){var b=this,c={};b.options.fade===!1?c[b.transitionType]=b.transformType+" "+b.options.speed+"ms "+b.options.cssEase:c[b.transitionType]="opacity "+b.options.speed+"ms "+b.options.cssEase,b.options.fade===!1?b.$slideTrack.css(c):b.$slides.eq(a).css(c)},b.prototype.autoPlay=function(){var a=this;a.autoPlayClear(),a.slideCount>a.options.slidesToShow&&(a.autoPlayTimer=setInterval(a.autoPlayIterator,a.options.autoplaySpeed))},b.prototype.autoPlayClear=function(){var a=this;a.autoPlayTimer&&clearInterval(a.autoPlayTimer)},b.prototype.autoPlayIterator=function(){var a=this,b=a.currentSlide+a.options.slidesToScroll;a.paused||a.interrupted||a.focussed||(a.options.infinite===!1&&(1===a.direction&&a.currentSlide+1===a.slideCount-1?a.direction=0:0===a.direction&&(b=a.currentSlide-a.options.slidesToScroll,a.currentSlide-1===0&&(a.direction=1))),a.slideHandler(b))},b.prototype.buildArrows=function(){var b=this;b.options.arrows===!0&&(b.$prevArrow=a(b.options.prevArrow).addClass("slick-arrow"),b.$nextArrow=a(b.options.nextArrow).addClass("slick-arrow"),b.slideCount>b.options.slidesToShow?(b.$prevArrow.removeClass("slick-hidden").removeAttr("aria-hidden tabindex"),b.$nextArrow.removeClass("slick-hidden").removeAttr("aria-hidden tabindex"),b.htmlExpr.test(b.options.prevArrow)&&b.$prevArrow.prependTo(b.options.appendArrows),b.htmlExpr.test(b.options.nextArrow)&&b.$nextArrow.appendTo(b.options.appendArrows),b.options.infinite!==!0&&b.$prevArrow.addClass("slick-disabled").attr("aria-disabled","true")):b.$prevArrow.add(b.$nextArrow).addClass("slick-hidden").attr({"aria-disabled":"true",tabindex:"-1"}))},b.prototype.buildDots=function(){var c,d,b=this;if(b.options.dots===!0&&b.slideCount>b.options.slidesToShow){for(b.$slider.addClass("slick-dotted"),d=a("<ul />").addClass(b.options.dotsClass),c=0;c<=b.getDotCount();c+=1)d.append(a("<li />").append(b.options.customPaging.call(this,b,c)));b.$dots=d.appendTo(b.options.appendDots),b.$dots.find("li").first().addClass("slick-active").attr("aria-hidden","false")}},b.prototype.buildOut=function(){var b=this;b.$slides=b.$slider.children(b.options.slide+":not(.slick-cloned)").addClass("slick-slide"),b.slideCount=b.$slides.length,b.$slides.each(function(b,c){a(c).attr("data-slick-index",b).data("originalStyling",a(c).attr("style")||"")}),b.$slider.addClass("slick-slider"),b.$slideTrack=0===b.slideCount?a('<div class="slick-track"/>').appendTo(b.$slider):b.$slides.wrapAll('<div class="slick-track"/>').parent(),b.$list=b.$slideTrack.wrap('<div aria-live="polite" class="slick-list"/>').parent(),b.$slideTrack.css("opacity",0),(b.options.centerMode===!0||b.options.swipeToSlide===!0)&&(b.options.slidesToScroll=1),a("img[data-lazy]",b.$slider).not("[src]").addClass("slick-loading"),b.setupInfinite(),b.buildArrows(),b.buildDots(),b.updateDots(),b.setSlideClasses("number"==typeof b.currentSlide?b.currentSlide:0),b.options.draggable===!0&&b.$list.addClass("draggable")},b.prototype.buildRows=function(){var b,c,d,e,f,g,h,a=this;if(e=document.createDocumentFragment(),g=a.$slider.children(),a.options.rows>1){for(h=a.options.slidesPerRow*a.options.rows,f=Math.ceil(g.length/h),b=0;f>b;b++){var i=document.createElement("div");for(c=0;c<a.options.rows;c++){var j=document.createElement("div");for(d=0;d<a.options.slidesPerRow;d++){var k=b*h+(c*a.options.slidesPerRow+d);g.get(k)&&j.appendChild(g.get(k))}i.appendChild(j)}e.appendChild(i)}a.$slider.empty().append(e),a.$slider.children().children().children().css({width:100/a.options.slidesPerRow+"%",display:"inline-block"})}},b.prototype.checkResponsive=function(b,c){var e,f,g,d=this,h=!1,i=d.$slider.width(),j=window.innerWidth||a(window).width();if("window"===d.respondTo?g=j:"slider"===d.respondTo?g=i:"min"===d.respondTo&&(g=Math.min(j,i)),d.options.responsive&&d.options.responsive.length&&null!==d.options.responsive){f=null;for(e in d.breakpoints)d.breakpoints.hasOwnProperty(e)&&(d.originalSettings.mobileFirst===!1?g<d.breakpoints[e]&&(f=d.breakpoints[e]):g>d.breakpoints[e]&&(f=d.breakpoints[e]));null!==f?null!==d.activeBreakpoint?(f!==d.activeBreakpoint||c)&&(d.activeBreakpoint=f,"unslick"===d.breakpointSettings[f]?d.unslick(f):(d.options=a.extend({},d.originalSettings,d.breakpointSettings[f]),b===!0&&(d.currentSlide=d.options.initialSlide),d.refresh(b)),h=f):(d.activeBreakpoint=f,"unslick"===d.breakpointSettings[f]?d.unslick(f):(d.options=a.extend({},d.originalSettings,d.breakpointSettings[f]),b===!0&&(d.currentSlide=d.options.initialSlide),d.refresh(b)),h=f):null!==d.activeBreakpoint&&(d.activeBreakpoint=null,d.options=d.originalSettings,b===!0&&(d.currentSlide=d.options.initialSlide),d.refresh(b),h=f),b||h===!1||d.$slider.trigger("breakpoint",[d,h])}},b.prototype.changeSlide=function(b,c){var f,g,h,d=this,e=a(b.currentTarget);switch(e.is("a")&&b.preventDefault(),e.is("li")||(e=e.closest("li")),h=d.slideCount%d.options.slidesToScroll!==0,f=h?0:(d.slideCount-d.currentSlide)%d.options.slidesToScroll,b.data.message){case"previous":g=0===f?d.options.slidesToScroll:d.options.slidesToShow-f,d.slideCount>d.options.slidesToShow&&d.slideHandler(d.currentSlide-g,!1,c);break;case"next":g=0===f?d.options.slidesToScroll:f,d.slideCount>d.options.slidesToShow&&d.slideHandler(d.currentSlide+g,!1,c);break;case"index":var i=0===b.data.index?0:b.data.index||e.index()*d.options.slidesToScroll;d.slideHandler(d.checkNavigable(i),!1,c),e.children().trigger("focus");break;default:return}},b.prototype.checkNavigable=function(a){var c,d,b=this;if(c=b.getNavigableIndexes(),d=0,a>c[c.length-1])a=c[c.length-1];else for(var e in c){if(a<c[e]){a=d;break}d=c[e]}return a},b.prototype.cleanUpEvents=function(){var b=this;b.options.dots&&null!==b.$dots&&a("li",b.$dots).off("click.slick",b.changeSlide).off("mouseenter.slick",a.proxy(b.interrupt,b,!0)).off("mouseleave.slick",a.proxy(b.interrupt,b,!1)),b.$slider.off("focus.slick blur.slick"),b.options.arrows===!0&&b.slideCount>b.options.slidesToShow&&(b.$prevArrow&&b.$prevArrow.off("click.slick",b.changeSlide),b.$nextArrow&&b.$nextArrow.off("click.slick",b.changeSlide)),b.$list.off("touchstart.slick mousedown.slick",b.swipeHandler),b.$list.off("touchmove.slick mousemove.slick",b.swipeHandler),b.$list.off("touchend.slick mouseup.slick",b.swipeHandler),b.$list.off("touchcancel.slick mouseleave.slick",b.swipeHandler),b.$list.off("click.slick",b.clickHandler),a(document).off(b.visibilityChange,b.visibility),b.cleanUpSlideEvents(),b.options.accessibility===!0&&b.$list.off("keydown.slick",b.keyHandler),b.options.focusOnSelect===!0&&a(b.$slideTrack).children().off("click.slick",b.selectHandler),a(window).off("orientationchange.slick.slick-"+b.instanceUid,b.orientationChange),a(window).off("resize.slick.slick-"+b.instanceUid,b.resize),a("[draggable!=true]",b.$slideTrack).off("dragstart",b.preventDefault),a(window).off("load.slick.slick-"+b.instanceUid,b.setPosition),a(document).off("ready.slick.slick-"+b.instanceUid,b.setPosition)},b.prototype.cleanUpSlideEvents=function(){var b=this;b.$list.off("mouseenter.slick",a.proxy(b.interrupt,b,!0)),b.$list.off("mouseleave.slick",a.proxy(b.interrupt,b,!1))},b.prototype.cleanUpRows=function(){var b,a=this;a.options.rows>1&&(b=a.$slides.children().children(),b.removeAttr("style"),a.$slider.empty().append(b))},b.prototype.clickHandler=function(a){var b=this;b.shouldClick===!1&&(a.stopImmediatePropagation(),a.stopPropagation(),a.preventDefault())},b.prototype.destroy=function(b){var c=this;c.autoPlayClear(),c.touchObject={},c.cleanUpEvents(),a(".slick-cloned",c.$slider).detach(),c.$dots&&c.$dots.remove(),c.$prevArrow&&c.$prevArrow.length&&(c.$prevArrow.removeClass("slick-disabled slick-arrow slick-hidden").removeAttr("aria-hidden aria-disabled tabindex").css("display",""),c.htmlExpr.test(c.options.prevArrow)&&c.$prevArrow.remove()),c.$nextArrow&&c.$nextArrow.length&&(c.$nextArrow.removeClass("slick-disabled slick-arrow slick-hidden").removeAttr("aria-hidden aria-disabled tabindex").css("display",""),c.htmlExpr.test(c.options.nextArrow)&&c.$nextArrow.remove()),c.$slides&&(c.$slides.removeClass("slick-slide slick-active slick-center slick-visible slick-current").removeAttr("aria-hidden").removeAttr("data-slick-index").each(function(){a(this).attr("style",a(this).data("originalStyling"))}),c.$slideTrack.children(this.options.slide).detach(),c.$slideTrack.detach(),c.$list.detach(),c.$slider.append(c.$slides)),c.cleanUpRows(),c.$slider.removeClass("slick-slider"),c.$slider.removeClass("slick-initialized"),c.$slider.removeClass("slick-dotted"),c.unslicked=!0,b||c.$slider.trigger("destroy",[c])},b.prototype.disableTransition=function(a){var b=this,c={};c[b.transitionType]="",b.options.fade===!1?b.$slideTrack.css(c):b.$slides.eq(a).css(c)},b.prototype.fadeSlide=function(a,b){var c=this;c.cssTransitions===!1?(c.$slides.eq(a).css({zIndex:c.options.zIndex}),c.$slides.eq(a).animate({opacity:1},c.options.speed,c.options.easing,b)):(c.applyTransition(a),c.$slides.eq(a).css({opacity:1,zIndex:c.options.zIndex}),b&&setTimeout(function(){c.disableTransition(a),b.call()},c.options.speed))},b.prototype.fadeSlideOut=function(a){var b=this;b.cssTransitions===!1?b.$slides.eq(a).animate({opacity:0,zIndex:b.options.zIndex-2},b.options.speed,b.options.easing):(b.applyTransition(a),b.$slides.eq(a).css({opacity:0,zIndex:b.options.zIndex-2}))},b.prototype.filterSlides=b.prototype.slickFilter=function(a){var b=this;null!==a&&(b.$slidesCache=b.$slides,b.unload(),b.$slideTrack.children(this.options.slide).detach(),b.$slidesCache.filter(a).appendTo(b.$slideTrack),b.reinit())},b.prototype.focusHandler=function(){var b=this;b.$slider.off("focus.slick blur.slick").on("focus.slick blur.slick","*:not(.slick-arrow)",function(c){c.stopImmediatePropagation();var d=a(this);setTimeout(function(){b.options.pauseOnFocus&&(b.focussed=d.is(":focus"),b.autoPlay())},0)})},b.prototype.getCurrent=b.prototype.slickCurrentSlide=function(){var a=this;return a.currentSlide},b.prototype.getDotCount=function(){var a=this,b=0,c=0,d=0;if(a.options.infinite===!0)for(;b<a.slideCount;)++d,b=c+a.options.slidesToScroll,c+=a.options.slidesToScroll<=a.options.slidesToShow?a.options.slidesToScroll:a.options.slidesToShow;else if(a.options.centerMode===!0)d=a.slideCount;else if(a.options.asNavFor)for(;b<a.slideCount;)++d,b=c+a.options.slidesToScroll,c+=a.options.slidesToScroll<=a.options.slidesToShow?a.options.slidesToScroll:a.options.slidesToShow;else d=1+Math.ceil((a.slideCount-a.options.slidesToShow)/a.options.slidesToScroll);return d-1},b.prototype.getLeft=function(a){var c,d,f,b=this,e=0;return b.slideOffset=0,d=b.$slides.first().outerHeight(!0),b.options.infinite===!0?(b.slideCount>b.options.slidesToShow&&(b.slideOffset=b.slideWidth*b.options.slidesToShow*-1,e=d*b.options.slidesToShow*-1),b.slideCount%b.options.slidesToScroll!==0&&a+b.options.slidesToScroll>b.slideCount&&b.slideCount>b.options.slidesToShow&&(a>b.slideCount?(b.slideOffset=(b.options.slidesToShow-(a-b.slideCount))*b.slideWidth*-1,e=(b.options.slidesToShow-(a-b.slideCount))*d*-1):(b.slideOffset=b.slideCount%b.options.slidesToScroll*b.slideWidth*-1,e=b.slideCount%b.options.slidesToScroll*d*-1))):a+b.options.slidesToShow>b.slideCount&&(b.slideOffset=(a+b.options.slidesToShow-b.slideCount)*b.slideWidth,e=(a+b.options.slidesToShow-b.slideCount)*d),b.slideCount<=b.options.slidesToShow&&(b.slideOffset=0,e=0),b.options.centerMode===!0&&b.options.infinite===!0?b.slideOffset+=b.slideWidth*Math.floor(b.options.slidesToShow/2)-b.slideWidth:b.options.centerMode===!0&&(b.slideOffset=0,b.slideOffset+=b.slideWidth*Math.floor(b.options.slidesToShow/2)),c=b.options.vertical===!1?a*b.slideWidth*-1+b.slideOffset:a*d*-1+e,b.options.variableWidth===!0&&(f=b.slideCount<=b.options.slidesToShow||b.options.infinite===!1?b.$slideTrack.children(".slick-slide").eq(a):b.$slideTrack.children(".slick-slide").eq(a+b.options.slidesToShow),c=b.options.rtl===!0?f[0]?-1*(b.$slideTrack.width()-f[0].offsetLeft-f.width()):0:f[0]?-1*f[0].offsetLeft:0,b.options.centerMode===!0&&(f=b.slideCount<=b.options.slidesToShow||b.options.infinite===!1?b.$slideTrack.children(".slick-slide").eq(a):b.$slideTrack.children(".slick-slide").eq(a+b.options.slidesToShow+1),c=b.options.rtl===!0?f[0]?-1*(b.$slideTrack.width()-f[0].offsetLeft-f.width()):0:f[0]?-1*f[0].offsetLeft:0,c+=(b.$list.width()-f.outerWidth())/2)),c},b.prototype.getOption=b.prototype.slickGetOption=function(a){var b=this;return b.options[a]},b.prototype.getNavigableIndexes=function(){var e,a=this,b=0,c=0,d=[];for(a.options.infinite===!1?e=a.slideCount:(b=-1*a.options.slidesToScroll,c=-1*a.options.slidesToScroll,e=2*a.slideCount);e>b;)d.push(b),b=c+a.options.slidesToScroll,c+=a.options.slidesToScroll<=a.options.slidesToShow?a.options.slidesToScroll:a.options.slidesToShow;return d},b.prototype.getSlick=function(){return this},b.prototype.getSlideCount=function(){var c,d,e,b=this;return e=b.options.centerMode===!0?b.slideWidth*Math.floor(b.options.slidesToShow/2):0,b.options.swipeToSlide===!0?(b.$slideTrack.find(".slick-slide").each(function(c,f){return f.offsetLeft-e+a(f).outerWidth()/2>-1*b.swipeLeft?(d=f,!1):void 0}),c=Math.abs(a(d).attr("data-slick-index")-b.currentSlide)||1):b.options.slidesToScroll},b.prototype.goTo=b.prototype.slickGoTo=function(a,b){var c=this;c.changeSlide({data:{message:"index",index:parseInt(a)}},b)},b.prototype.init=function(b){var c=this;a(c.$slider).hasClass("slick-initialized")||(a(c.$slider).addClass("slick-initialized"),c.buildRows(),c.buildOut(),c.setProps(),c.startLoad(),c.loadSlider(),c.initializeEvents(),c.updateArrows(),c.updateDots(),c.checkResponsive(!0),c.focusHandler()),b&&c.$slider.trigger("init",[c]),c.options.accessibility===!0&&c.initADA(),c.options.autoplay&&(c.paused=!1,c.autoPlay())},b.prototype.initADA=function(){var b=this;b.$slides.add(b.$slideTrack.find(".slick-cloned")).attr({"aria-hidden":"true",tabindex:"-1"}).find("a, input, button, select").attr({tabindex:"-1"}),b.$slideTrack.attr("role","listbox"),b.$slides.not(b.$slideTrack.find(".slick-cloned")).each(function(c){a(this).attr({role:"option","aria-describedby":"slick-slide"+b.instanceUid+c})}),null!==b.$dots&&b.$dots.attr("role","tablist").find("li").each(function(c){a(this).attr({role:"presentation","aria-selected":"false","aria-controls":"navigation"+b.instanceUid+c,id:"slick-slide"+b.instanceUid+c})}).first().attr("aria-selected","true").end().find("button").attr("role","button").end().closest("div").attr("role","toolbar"),b.activateADA()},b.prototype.initArrowEvents=function(){var a=this;a.options.arrows===!0&&a.slideCount>a.options.slidesToShow&&(a.$prevArrow.off("click.slick").on("click.slick",{message:"previous"},a.changeSlide),a.$nextArrow.off("click.slick").on("click.slick",{message:"next"},a.changeSlide))},b.prototype.initDotEvents=function(){var b=this;b.options.dots===!0&&b.slideCount>b.options.slidesToShow&&a("li",b.$dots).on("click.slick",{message:"index"},b.changeSlide),b.options.dots===!0&&b.options.pauseOnDotsHover===!0&&a("li",b.$dots).on("mouseenter.slick",a.proxy(b.interrupt,b,!0)).on("mouseleave.slick",a.proxy(b.interrupt,b,!1))},b.prototype.initSlideEvents=function(){var b=this;b.options.pauseOnHover&&(b.$list.on("mouseenter.slick",a.proxy(b.interrupt,b,!0)),b.$list.on("mouseleave.slick",a.proxy(b.interrupt,b,!1)))},b.prototype.initializeEvents=function(){var b=this;b.initArrowEvents(),b.initDotEvents(),b.initSlideEvents(),b.$list.on("touchstart.slick mousedown.slick",{action:"start"},b.swipeHandler),b.$list.on("touchmove.slick mousemove.slick",{action:"move"},b.swipeHandler),b.$list.on("touchend.slick mouseup.slick",{action:"end"},b.swipeHandler),b.$list.on("touchcancel.slick mouseleave.slick",{action:"end"},b.swipeHandler),b.$list.on("click.slick",b.clickHandler),a(document).on(b.visibilityChange,a.proxy(b.visibility,b)),b.options.accessibility===!0&&b.$list.on("keydown.slick",b.keyHandler),b.options.focusOnSelect===!0&&a(b.$slideTrack).children().on("click.slick",b.selectHandler),a(window).on("orientationchange.slick.slick-"+b.instanceUid,a.proxy(b.orientationChange,b)),a(window).on("resize.slick.slick-"+b.instanceUid,a.proxy(b.resize,b)),a("[draggable!=true]",b.$slideTrack).on("dragstart",b.preventDefault),a(window).on("load.slick.slick-"+b.instanceUid,b.setPosition),a(document).on("ready.slick.slick-"+b.instanceUid,b.setPosition)},b.prototype.initUI=function(){var a=this;a.options.arrows===!0&&a.slideCount>a.options.slidesToShow&&(a.$prevArrow.show(),a.$nextArrow.show()),a.options.dots===!0&&a.slideCount>a.options.slidesToShow&&a.$dots.show()},b.prototype.keyHandler=function(a){var b=this;a.target.tagName.match("TEXTAREA|INPUT|SELECT")||(37===a.keyCode&&b.options.accessibility===!0?b.changeSlide({data:{message:b.options.rtl===!0?"next":"previous"}}):39===a.keyCode&&b.options.accessibility===!0&&b.changeSlide({data:{message:b.options.rtl===!0?"previous":"next"}}))},b.prototype.lazyLoad=function(){function g(c){a("img[data-lazy]",c).each(function(){var c=a(this),d=a(this).attr("data-lazy"),e=document.createElement("img");e.onload=function(){c.animate({opacity:0},100,function(){c.attr("src",d).animate({opacity:1},200,function(){c.removeAttr("data-lazy").removeClass("slick-loading")}),b.$slider.trigger("lazyLoaded",[b,c,d])})},e.onerror=function(){c.removeAttr("data-lazy").removeClass("slick-loading").addClass("slick-lazyload-error"),b.$slider.trigger("lazyLoadError",[b,c,d])},e.src=d})}var c,d,e,f,b=this;b.options.centerMode===!0?b.options.infinite===!0?(e=b.currentSlide+(b.options.slidesToShow/2+1),f=e+b.options.slidesToShow+2):(e=Math.max(0,b.currentSlide-(b.options.slidesToShow/2+1)),f=2+(b.options.slidesToShow/2+1)+b.currentSlide):(e=b.options.infinite?b.options.slidesToShow+b.currentSlide:b.currentSlide,f=Math.ceil(e+b.options.slidesToShow),b.options.fade===!0&&(e>0&&e--,f<=b.slideCount&&f++)),c=b.$slider.find(".slick-slide").slice(e,f),g(c),b.slideCount<=b.options.slidesToShow?(d=b.$slider.find(".slick-slide"),g(d)):b.currentSlide>=b.slideCount-b.options.slidesToShow?(d=b.$slider.find(".slick-cloned").slice(0,b.options.slidesToShow),g(d)):0===b.currentSlide&&(d=b.$slider.find(".slick-cloned").slice(-1*b.options.slidesToShow),g(d))},b.prototype.loadSlider=function(){var a=this;a.setPosition(),a.$slideTrack.css({opacity:1}),a.$slider.removeClass("slick-loading"),a.initUI(),"progressive"===a.options.lazyLoad&&a.progressiveLazyLoad()},b.prototype.next=b.prototype.slickNext=function(){var a=this;a.changeSlide({data:{message:"next"}})},b.prototype.orientationChange=function(){var a=this;a.checkResponsive(),a.setPosition()},b.prototype.pause=b.prototype.slickPause=function(){var a=this;a.autoPlayClear(),a.paused=!0},b.prototype.play=b.prototype.slickPlay=function(){var a=this;a.autoPlay(),a.options.autoplay=!0,a.paused=!1,a.focussed=!1,a.interrupted=!1},b.prototype.postSlide=function(a){var b=this;b.unslicked||(b.$slider.trigger("afterChange",[b,a]),b.animating=!1,b.setPosition(),b.swipeLeft=null,b.options.autoplay&&b.autoPlay(),b.options.accessibility===!0&&b.initADA())},b.prototype.prev=b.prototype.slickPrev=function(){var a=this;a.changeSlide({data:{message:"previous"}})},b.prototype.preventDefault=function(a){a.preventDefault()},b.prototype.progressiveLazyLoad=function(b){b=b||1;var e,f,g,c=this,d=a("img[data-lazy]",c.$slider);d.length?(e=d.first(),f=e.attr("data-lazy"),g=document.createElement("img"),g.onload=function(){e.attr("src",f).removeAttr("data-lazy").removeClass("slick-loading"),c.options.adaptiveHeight===!0&&c.setPosition(),c.$slider.trigger("lazyLoaded",[c,e,f]),c.progressiveLazyLoad()},g.onerror=function(){3>b?setTimeout(function(){c.progressiveLazyLoad(b+1)},500):(e.removeAttr("data-lazy").removeClass("slick-loading").addClass("slick-lazyload-error"),c.$slider.trigger("lazyLoadError",[c,e,f]),c.progressiveLazyLoad())},g.src=f):c.$slider.trigger("allImagesLoaded",[c])},b.prototype.refresh=function(b){var d,e,c=this;e=c.slideCount-c.options.slidesToShow,!c.options.infinite&&c.currentSlide>e&&(c.currentSlide=e),c.slideCount<=c.options.slidesToShow&&(c.currentSlide=0),d=c.currentSlide,c.destroy(!0),a.extend(c,c.initials,{currentSlide:d}),c.init(),b||c.changeSlide({data:{message:"index",index:d}},!1)},b.prototype.registerBreakpoints=function(){var c,d,e,b=this,f=b.options.responsive||null;if("array"===a.type(f)&&f.length){b.respondTo=b.options.respondTo||"window";for(c in f)if(e=b.breakpoints.length-1,d=f[c].breakpoint,f.hasOwnProperty(c)){for(;e>=0;)b.breakpoints[e]&&b.breakpoints[e]===d&&b.breakpoints.splice(e,1),e--;b.breakpoints.push(d),b.breakpointSettings[d]=f[c].settings}b.breakpoints.sort(function(a,c){return b.options.mobileFirst?a-c:c-a})}},b.prototype.reinit=function(){var b=this;b.$slides=b.$slideTrack.children(b.options.slide).addClass("slick-slide"),b.slideCount=b.$slides.length,b.currentSlide>=b.slideCount&&0!==b.currentSlide&&(b.currentSlide=b.currentSlide-b.options.slidesToScroll),b.slideCount<=b.options.slidesToShow&&(b.currentSlide=0),b.registerBreakpoints(),b.setProps(),b.setupInfinite(),b.buildArrows(),b.updateArrows(),b.initArrowEvents(),b.buildDots(),b.updateDots(),b.initDotEvents(),b.cleanUpSlideEvents(),b.initSlideEvents(),b.checkResponsive(!1,!0),b.options.focusOnSelect===!0&&a(b.$slideTrack).children().on("click.slick",b.selectHandler),b.setSlideClasses("number"==typeof b.currentSlide?b.currentSlide:0),b.setPosition(),b.focusHandler(),b.paused=!b.options.autoplay,b.autoPlay(),b.$slider.trigger("reInit",[b])},b.prototype.resize=function(){var b=this;a(window).width()!==b.windowWidth&&(clearTimeout(b.windowDelay),b.windowDelay=window.setTimeout(function(){b.windowWidth=a(window).width(),b.checkResponsive(),b.unslicked||b.setPosition()},50))},b.prototype.removeSlide=b.prototype.slickRemove=function(a,b,c){var d=this;return"boolean"==typeof a?(b=a,a=b===!0?0:d.slideCount-1):a=b===!0?--a:a,d.slideCount<1||0>a||a>d.slideCount-1?!1:(d.unload(),c===!0?d.$slideTrack.children().remove():d.$slideTrack.children(this.options.slide).eq(a).remove(),d.$slides=d.$slideTrack.children(this.options.slide),d.$slideTrack.children(this.options.slide).detach(),d.$slideTrack.append(d.$slides),d.$slidesCache=d.$slides,void d.reinit())},b.prototype.setCSS=function(a){var d,e,b=this,c={};b.options.rtl===!0&&(a=-a),d="left"==b.positionProp?Math.ceil(a)+"px":"0px",e="top"==b.positionProp?Math.ceil(a)+"px":"0px",c[b.positionProp]=a,b.transformsEnabled===!1?b.$slideTrack.css(c):(c={},b.cssTransitions===!1?(c[b.animType]="translate("+d+", "+e+")",b.$slideTrack.css(c)):(c[b.animType]="translate3d("+d+", "+e+", 0px)",b.$slideTrack.css(c)))},b.prototype.setDimensions=function(){var a=this;a.options.vertical===!1?a.options.centerMode===!0&&a.$list.css({padding:"0px "+a.options.centerPadding}):(a.$list.height(a.$slides.first().outerHeight(!0)*a.options.slidesToShow),a.options.centerMode===!0&&a.$list.css({padding:a.options.centerPadding+" 0px"})),a.listWidth=a.$list.width(),a.listHeight=a.$list.height(),a.options.vertical===!1&&a.options.variableWidth===!1?(a.slideWidth=Math.ceil(a.listWidth/a.options.slidesToShow),a.$slideTrack.width(Math.ceil(a.slideWidth*a.$slideTrack.children(".slick-slide").length))):a.options.variableWidth===!0?a.$slideTrack.width(5e3*a.slideCount):(a.slideWidth=Math.ceil(a.listWidth),a.$slideTrack.height(Math.ceil(a.$slides.first().outerHeight(!0)*a.$slideTrack.children(".slick-slide").length)));var b=a.$slides.first().outerWidth(!0)-a.$slides.first().width();a.options.variableWidth===!1&&a.$slideTrack.children(".slick-slide").width(a.slideWidth-b)},b.prototype.setFade=function(){var c,b=this;b.$slides.each(function(d,e){c=b.slideWidth*d*-1,b.options.rtl===!0?a(e).css({position:"relative",right:c,top:0,zIndex:b.options.zIndex-2,opacity:0}):a(e).css({position:"relative",left:c,top:0,zIndex:b.options.zIndex-2,opacity:0})}),b.$slides.eq(b.currentSlide).css({zIndex:b.options.zIndex-1,opacity:1})},b.prototype.setHeight=function(){var a=this;if(1===a.options.slidesToShow&&a.options.adaptiveHeight===!0&&a.options.vertical===!1){var b=a.$slides.eq(a.currentSlide).outerHeight(!0);a.$list.css("height",b)}},b.prototype.setOption=b.prototype.slickSetOption=function(){var c,d,e,f,h,b=this,g=!1;if("object"===a.type(arguments[0])?(e=arguments[0],g=arguments[1],h="multiple"):"string"===a.type(arguments[0])&&(e=arguments[0],f=arguments[1],g=arguments[2],"responsive"===arguments[0]&&"array"===a.type(arguments[1])?h="responsive":"undefined"!=typeof arguments[1]&&(h="single")),"single"===h)b.options[e]=f;else if("multiple"===h)a.each(e,function(a,c){b.options[a]=c});else if("responsive"===h)for(d in f)if("array"!==a.type(b.options.responsive))b.options.responsive=[f[d]];else{for(c=b.options.responsive.length-1;c>=0;)b.options.responsive[c].breakpoint===f[d].breakpoint&&b.options.responsive.splice(c,1),c--;b.options.responsive.push(f[d])}g&&(b.unload(),b.reinit())},b.prototype.setPosition=function(){var a=this;a.setDimensions(),a.setHeight(),a.options.fade===!1?a.setCSS(a.getLeft(a.currentSlide)):a.setFade(),a.$slider.trigger("setPosition",[a])},b.prototype.setProps=function(){var a=this,b=document.body.style;a.positionProp=a.options.vertical===!0?"top":"left","top"===a.positionProp?a.$slider.addClass("slick-vertical"):a.$slider.removeClass("slick-vertical"),(void 0!==b.WebkitTransition||void 0!==b.MozTransition||void 0!==b.msTransition)&&a.options.useCSS===!0&&(a.cssTransitions=!0),a.options.fade&&("number"==typeof a.options.zIndex?a.options.zIndex<3&&(a.options.zIndex=3):a.options.zIndex=a.defaults.zIndex),void 0!==b.OTransform&&(a.animType="OTransform",a.transformType="-o-transform",a.transitionType="OTransition",void 0===b.perspectiveProperty&&void 0===b.webkitPerspective&&(a.animType=!1)),void 0!==b.MozTransform&&(a.animType="MozTransform",a.transformType="-moz-transform",a.transitionType="MozTransition",void 0===b.perspectiveProperty&&void 0===b.MozPerspective&&(a.animType=!1)),void 0!==b.webkitTransform&&(a.animType="webkitTransform",a.transformType="-webkit-transform",a.transitionType="webkitTransition",void 0===b.perspectiveProperty&&void 0===b.webkitPerspective&&(a.animType=!1)),void 0!==b.msTransform&&(a.animType="msTransform",a.transformType="-ms-transform",a.transitionType="msTransition",void 0===b.msTransform&&(a.animType=!1)),void 0!==b.transform&&a.animType!==!1&&(a.animType="transform",a.transformType="transform",a.transitionType="transition"),a.transformsEnabled=a.options.useTransform&&null!==a.animType&&a.animType!==!1},b.prototype.setSlideClasses=function(a){var c,d,e,f,b=this;d=b.$slider.find(".slick-slide").removeClass("slick-active slick-center slick-current").attr("aria-hidden","true"),b.$slides.eq(a).addClass("slick-current"),b.options.centerMode===!0?(c=Math.floor(b.options.slidesToShow/2),b.options.infinite===!0&&(a>=c&&a<=b.slideCount-1-c?b.$slides.slice(a-c,a+c+1).addClass("slick-active").attr("aria-hidden","false"):(e=b.options.slidesToShow+a,
d.slice(e-c+1,e+c+2).addClass("slick-active").attr("aria-hidden","false")),0===a?d.eq(d.length-1-b.options.slidesToShow).addClass("slick-center"):a===b.slideCount-1&&d.eq(b.options.slidesToShow).addClass("slick-center")),b.$slides.eq(a).addClass("slick-center")):a>=0&&a<=b.slideCount-b.options.slidesToShow?b.$slides.slice(a,a+b.options.slidesToShow).addClass("slick-active").attr("aria-hidden","false"):d.length<=b.options.slidesToShow?d.addClass("slick-active").attr("aria-hidden","false"):(f=b.slideCount%b.options.slidesToShow,e=b.options.infinite===!0?b.options.slidesToShow+a:a,b.options.slidesToShow==b.options.slidesToScroll&&b.slideCount-a<b.options.slidesToShow?d.slice(e-(b.options.slidesToShow-f),e+f).addClass("slick-active").attr("aria-hidden","false"):d.slice(e,e+b.options.slidesToShow).addClass("slick-active").attr("aria-hidden","false")),"ondemand"===b.options.lazyLoad&&b.lazyLoad()},b.prototype.setupInfinite=function(){var c,d,e,b=this;if(b.options.fade===!0&&(b.options.centerMode=!1),b.options.infinite===!0&&b.options.fade===!1&&(d=null,b.slideCount>b.options.slidesToShow)){for(e=b.options.centerMode===!0?b.options.slidesToShow+1:b.options.slidesToShow,c=b.slideCount;c>b.slideCount-e;c-=1)d=c-1,a(b.$slides[d]).clone(!0).attr("id","").attr("data-slick-index",d-b.slideCount).prependTo(b.$slideTrack).addClass("slick-cloned");for(c=0;e>c;c+=1)d=c,a(b.$slides[d]).clone(!0).attr("id","").attr("data-slick-index",d+b.slideCount).appendTo(b.$slideTrack).addClass("slick-cloned");b.$slideTrack.find(".slick-cloned").find("[id]").each(function(){a(this).attr("id","")})}},b.prototype.interrupt=function(a){var b=this;a||b.autoPlay(),b.interrupted=a},b.prototype.selectHandler=function(b){var c=this,d=a(b.target).is(".slick-slide")?a(b.target):a(b.target).parents(".slick-slide"),e=parseInt(d.attr("data-slick-index"));return e||(e=0),c.slideCount<=c.options.slidesToShow?(c.setSlideClasses(e),void c.asNavFor(e)):void c.slideHandler(e)},b.prototype.slideHandler=function(a,b,c){var d,e,f,g,j,h=null,i=this;return b=b||!1,i.animating===!0&&i.options.waitForAnimate===!0||i.options.fade===!0&&i.currentSlide===a||i.slideCount<=i.options.slidesToShow?void 0:(b===!1&&i.asNavFor(a),d=a,h=i.getLeft(d),g=i.getLeft(i.currentSlide),i.currentLeft=null===i.swipeLeft?g:i.swipeLeft,i.options.infinite===!1&&i.options.centerMode===!1&&(0>a||a>i.getDotCount()*i.options.slidesToScroll)?void(i.options.fade===!1&&(d=i.currentSlide,c!==!0?i.animateSlide(g,function(){i.postSlide(d)}):i.postSlide(d))):i.options.infinite===!1&&i.options.centerMode===!0&&(0>a||a>i.slideCount-i.options.slidesToScroll)?void(i.options.fade===!1&&(d=i.currentSlide,c!==!0?i.animateSlide(g,function(){i.postSlide(d)}):i.postSlide(d))):(i.options.autoplay&&clearInterval(i.autoPlayTimer),e=0>d?i.slideCount%i.options.slidesToScroll!==0?i.slideCount-i.slideCount%i.options.slidesToScroll:i.slideCount+d:d>=i.slideCount?i.slideCount%i.options.slidesToScroll!==0?0:d-i.slideCount:d,i.animating=!0,i.$slider.trigger("beforeChange",[i,i.currentSlide,e]),f=i.currentSlide,i.currentSlide=e,i.setSlideClasses(i.currentSlide),i.options.asNavFor&&(j=i.getNavTarget(),j=j.slick("getSlick"),j.slideCount<=j.options.slidesToShow&&j.setSlideClasses(i.currentSlide)),i.updateDots(),i.updateArrows(),i.options.fade===!0?(c!==!0?(i.fadeSlideOut(f),i.fadeSlide(e,function(){i.postSlide(e)})):i.postSlide(e),void i.animateHeight()):void(c!==!0?i.animateSlide(h,function(){i.postSlide(e)}):i.postSlide(e))))},b.prototype.startLoad=function(){var a=this;a.options.arrows===!0&&a.slideCount>a.options.slidesToShow&&(a.$prevArrow.hide(),a.$nextArrow.hide()),a.options.dots===!0&&a.slideCount>a.options.slidesToShow&&a.$dots.hide(),a.$slider.addClass("slick-loading")},b.prototype.swipeDirection=function(){var a,b,c,d,e=this;return a=e.touchObject.startX-e.touchObject.curX,b=e.touchObject.startY-e.touchObject.curY,c=Math.atan2(b,a),d=Math.round(180*c/Math.PI),0>d&&(d=360-Math.abs(d)),45>=d&&d>=0?e.options.rtl===!1?"left":"right":360>=d&&d>=315?e.options.rtl===!1?"left":"right":d>=135&&225>=d?e.options.rtl===!1?"right":"left":e.options.verticalSwiping===!0?d>=35&&135>=d?"down":"up":"vertical"},b.prototype.swipeEnd=function(a){var c,d,b=this;if(b.dragging=!1,b.interrupted=!1,b.shouldClick=b.touchObject.swipeLength>10?!1:!0,void 0===b.touchObject.curX)return!1;if(b.touchObject.edgeHit===!0&&b.$slider.trigger("edge",[b,b.swipeDirection()]),b.touchObject.swipeLength>=b.touchObject.minSwipe){switch(d=b.swipeDirection()){case"left":case"down":c=b.options.swipeToSlide?b.checkNavigable(b.currentSlide+b.getSlideCount()):b.currentSlide+b.getSlideCount(),b.currentDirection=0;break;case"right":case"up":c=b.options.swipeToSlide?b.checkNavigable(b.currentSlide-b.getSlideCount()):b.currentSlide-b.getSlideCount(),b.currentDirection=1}"vertical"!=d&&(b.slideHandler(c),b.touchObject={},b.$slider.trigger("swipe",[b,d]))}else b.touchObject.startX!==b.touchObject.curX&&(b.slideHandler(b.currentSlide),b.touchObject={})},b.prototype.swipeHandler=function(a){var b=this;if(!(b.options.swipe===!1||"ontouchend"in document&&b.options.swipe===!1||b.options.draggable===!1&&-1!==a.type.indexOf("mouse")))switch(b.touchObject.fingerCount=a.originalEvent&&void 0!==a.originalEvent.touches?a.originalEvent.touches.length:1,b.touchObject.minSwipe=b.listWidth/b.options.touchThreshold,b.options.verticalSwiping===!0&&(b.touchObject.minSwipe=b.listHeight/b.options.touchThreshold),a.data.action){case"start":b.swipeStart(a);break;case"move":b.swipeMove(a);break;case"end":b.swipeEnd(a)}},b.prototype.swipeMove=function(a){var d,e,f,g,h,b=this;return h=void 0!==a.originalEvent?a.originalEvent.touches:null,!b.dragging||h&&1!==h.length?!1:(d=b.getLeft(b.currentSlide),b.touchObject.curX=void 0!==h?h[0].pageX:a.clientX,b.touchObject.curY=void 0!==h?h[0].pageY:a.clientY,b.touchObject.swipeLength=Math.round(Math.sqrt(Math.pow(b.touchObject.curX-b.touchObject.startX,2))),b.options.verticalSwiping===!0&&(b.touchObject.swipeLength=Math.round(Math.sqrt(Math.pow(b.touchObject.curY-b.touchObject.startY,2)))),e=b.swipeDirection(),"vertical"!==e?(void 0!==a.originalEvent&&b.touchObject.swipeLength>4&&a.preventDefault(),g=(b.options.rtl===!1?1:-1)*(b.touchObject.curX>b.touchObject.startX?1:-1),b.options.verticalSwiping===!0&&(g=b.touchObject.curY>b.touchObject.startY?1:-1),f=b.touchObject.swipeLength,b.touchObject.edgeHit=!1,b.options.infinite===!1&&(0===b.currentSlide&&"right"===e||b.currentSlide>=b.getDotCount()&&"left"===e)&&(f=b.touchObject.swipeLength*b.options.edgeFriction,b.touchObject.edgeHit=!0),b.options.vertical===!1?b.swipeLeft=d+f*g:b.swipeLeft=d+f*(b.$list.height()/b.listWidth)*g,b.options.verticalSwiping===!0&&(b.swipeLeft=d+f*g),b.options.fade===!0||b.options.touchMove===!1?!1:b.animating===!0?(b.swipeLeft=null,!1):void b.setCSS(b.swipeLeft)):void 0)},b.prototype.swipeStart=function(a){var c,b=this;return b.interrupted=!0,1!==b.touchObject.fingerCount||b.slideCount<=b.options.slidesToShow?(b.touchObject={},!1):(void 0!==a.originalEvent&&void 0!==a.originalEvent.touches&&(c=a.originalEvent.touches[0]),b.touchObject.startX=b.touchObject.curX=void 0!==c?c.pageX:a.clientX,b.touchObject.startY=b.touchObject.curY=void 0!==c?c.pageY:a.clientY,void(b.dragging=!0))},b.prototype.unfilterSlides=b.prototype.slickUnfilter=function(){var a=this;null!==a.$slidesCache&&(a.unload(),a.$slideTrack.children(this.options.slide).detach(),a.$slidesCache.appendTo(a.$slideTrack),a.reinit())},b.prototype.unload=function(){var b=this;a(".slick-cloned",b.$slider).remove(),b.$dots&&b.$dots.remove(),b.$prevArrow&&b.htmlExpr.test(b.options.prevArrow)&&b.$prevArrow.remove(),b.$nextArrow&&b.htmlExpr.test(b.options.nextArrow)&&b.$nextArrow.remove(),b.$slides.removeClass("slick-slide slick-active slick-visible slick-current").attr("aria-hidden","true").css("width","")},b.prototype.unslick=function(a){var b=this;b.$slider.trigger("unslick",[b,a]),b.destroy()},b.prototype.updateArrows=function(){var b,a=this;b=Math.floor(a.options.slidesToShow/2),a.options.arrows===!0&&a.slideCount>a.options.slidesToShow&&!a.options.infinite&&(a.$prevArrow.removeClass("slick-disabled").attr("aria-disabled","false"),a.$nextArrow.removeClass("slick-disabled").attr("aria-disabled","false"),0===a.currentSlide?(a.$prevArrow.addClass("slick-disabled").attr("aria-disabled","true"),a.$nextArrow.removeClass("slick-disabled").attr("aria-disabled","false")):a.currentSlide>=a.slideCount-a.options.slidesToShow&&a.options.centerMode===!1?(a.$nextArrow.addClass("slick-disabled").attr("aria-disabled","true"),a.$prevArrow.removeClass("slick-disabled").attr("aria-disabled","false")):a.currentSlide>=a.slideCount-1&&a.options.centerMode===!0&&(a.$nextArrow.addClass("slick-disabled").attr("aria-disabled","true"),a.$prevArrow.removeClass("slick-disabled").attr("aria-disabled","false")))},b.prototype.updateDots=function(){var a=this;null!==a.$dots&&(a.$dots.find("li").removeClass("slick-active").attr("aria-hidden","true"),a.$dots.find("li").eq(Math.floor(a.currentSlide/a.options.slidesToScroll)).addClass("slick-active").attr("aria-hidden","false"))},b.prototype.visibility=function(){var a=this;a.options.autoplay&&(document[a.hidden]?a.interrupted=!0:a.interrupted=!1)},a.fn.slick=function(){var f,g,a=this,c=arguments[0],d=Array.prototype.slice.call(arguments,1),e=a.length;for(f=0;e>f;f++)if("object"==typeof c||"undefined"==typeof c?a[f].slick=new b(a[f],c):g=a[f].slick[c].apply(a[f].slick,d),"undefined"!=typeof g)return g;return a}});
window.fetchPackages = function (page, search, orderBy, order, itemPerPage, group) {

    page = page || 1;
    search = search || $("body").find("input#melis_market_place_search_input").val();
    orderBy = orderBy || 'mp_total_downloads';
    order = order || 'desc';
    itemPerPage = itemPerPage || 9;

    if (!group) {
        group = ["1", "2", "3", "4", '5'];
    }

    $(".market-place-btn-filter-group button").attr("disabled", "disabled");
    $("#btnMarketPlaceSearch").attr("disabled", "disabled");
    $.ajax({
        type: 'POST',
        url: "/melis/MelisMarketPlace/MelisMarketPlace/package-list?page=" + page + "&search=" + search + "&orderBy=" + orderBy + "&group=" + group,
        data: {page: page, search: search, orderBy: orderBy, order: order, itemPerPage: itemPerPage, group: group},
        dataType: "html",
        success: function (data) {
            $("body").find("div#melis-market-place-package-list").html(data);
            $(".market-place-btn-filter-group button").removeAttr("disabled", "disabled");
            $("#btnMarketPlaceSearch").removeAttr("disabled", "disabled");
        }
    });
};

function getActiveGroupIdFilter() {
    var groupId = $(".market-place-btn-filter-group").find('.active');
    var btnId = [];
    var tmpData = {};

    //get the active buttons Id
    for (var ctr = 0; ctr < groupId.length; ctr++) {
        var dataId = $(groupId[ctr]).val();

        btnId.push(dataId);
    }
    tmpData = [btnId];

    return tmpData;
}

function initSlick(tab) {
    // Big Slider
    $('#' + tab + ' .slider-single').not('.slick-initialized').slick({
        slidesToShow: 1,
        slidesToScroll: 1,
        arrows: true,
        fade: true,
        adaptiveHeight: true
    });

    // Navigation Slider
    $('#' + tab + ' .slider-nav')
        .not('.slick-initialized')
        .on('init', function (event, slick) {
            $('#' + tab + ' .slider-nav .slick-slide.slick-current').addClass('is-active');
        })
        .slick({
            slidesToShow: 6,
            slidesToScroll: 6,
            dots: false,
            focusOnSelect: false,
            infinite: false,
            responsive: [{
                breakpoint: 1400,
                settings: {
                    slidesToShow: 6,
                    slidesToScroll: 6
                }
            }, {
                breakpoint: 992,
                settings: {
                    slidesToShow: 4,
                    slidesToScroll: 4
                }
            }, {
                breakpoint: 767,
                settings: {
                    slidesToShow: 3,
                    slidesToScroll: 3
                }
            }]
        });

    // Detect active nav slider
    $('#' + tab + ' .slider-single').on('afterChange', function (event, slick, currentSlide) {
        $('#' + tab + ' .slider-nav').slick('slickGoTo', currentSlide);
        var currrentNavSlideElem = '#' + tab + ' .slider-nav .slick-slide[data-slick-index="' + currentSlide + '"]';
        $('#' + tab + ' .slider-nav .slick-slide.is-active').removeClass('is-active');
        $(currrentNavSlideElem).addClass('is-active');
    });

    $('#' + tab + ' .slider-nav').on('click', '.slick-slide', function (event) {
        event.preventDefault();
        var goToSingleSlide = $(this).data('slick-index');

        $('#' + tab + ' .slider-single').slick('slickGoTo', goToSingleSlide);
    });
}

$(function () {

    var preventModalClose = true;

    // Tab Click
    $("body").on('shown.bs.tab', "#melis-id-nav-bar-tabs [data-tool-meliskey='melis_market_place_tool_package_display'] a[data-toggle='tab']", function (e) {
        initSlick(activeTabId);
    });

    $('body').on('click', '#melis-marketplace-setup-modal-submit', function () { // @status: currently working
        var form = $('#melis-marketplace-setup-modal-submit').parents().find('form');

        if (form.length) {
            var modal = $('#id_melis_market_place_module_setup_form_content');
            var data = form.serializeArray();
            var action = modal.data().action;
            var module = modal.data().module;

            data.push({name: 'module', value: module});
            data.push({name: 'action', value: action});
            melisCoreTool.pending("#melis-marketplace-setup-modal-submit");

            doAjax('POST', '/melis/MelisMarketPlace/MelisMarketPlace/validateSetupForm', $.param(data), function (response) {
                // display errors if it has
                if (response.result.errors != null || typeof response.result.errors !== 'undefined') {
                    if (response.result.success) {
                        // if everything went well, call the submitAction to process the data
                        doAjax('POST', '/melis/MelisMarketPlace/MelisMarketPlace/submitSetupForm', $.param(data), function (response) {
                            if (response.success) {
                                melisHelper.melisOkNotification(response.module, response.message);
                                // unplug module
                                doAjax('POST', '/melis/MelisMarketPlace/MelisMarketPlace/unplugModule', {module: module}, function (response) {
                                    if (response.success === true) {
                                        preventModalClose = false;
                                        $('#id_melis_market_place_module_setup_form_content_ajax_container').modal('hide');

                                        // inform the user that everything is good
                                        addSuccessCmdText(translations.tr_melis_marketplace_setup_config_ok);

                                        // ask the user if they want to activate the module, this will only happen if the action is "download"
                                        if (action === 'download' || action === 'require' && response.moduleSite === false) {
                                            $("button.melis-marketplace-modal-activate-module").removeClass("hidden");
                                        }

                                        // ask the user if they want to reload the page
                                        $("button.melis-marketplace-modal-reload").removeClass("hidden");
                                    } else {
                                        throw new Error(translations.tr_melis_market_place_plug_module_ko.replace('%s', module));
                                    }
                                });

                            } else {
                                melisHelper.melisKoNotification(translations.tr_melis_market_place_setup_title.replace('%s', response.module), response.result.message, response.result.errors);
                                melisCoreTool.highlightErrors(response.result.success, response.result.errors, form.prop('id'));
                            }
                        });

                        // end of execution
                    } else {
                        melisHelper.melisKoNotification(translations.tr_melis_market_place_setup_title.replace('%s', response.module), response.result.message, response.result.errors);
                        melisCoreTool.highlightErrors(response.result.success, response.result.errors, form.prop('id'));
                    }

                }
                melisCoreTool.done("#melis-marketplace-setup-modal-submit");
            });
        }
    });

    $("body").on("click", "button.melis-marketplace-product-action", function () {
        var action = $(this).data().action;
        var pkg = $(this).data().package;
        var module = $(this).data().module;

        var zoneId = "id_melis_market_place_tool_package_modal_content";
        var melisKey = "melis_market_place_tool_package_modal_content";
        var modalUrl = "/melis/MelisMarketPlace/MelisMarketPlace/toolProductModalContainer";

        var objData = {action: action, package: pkg, module: module};

        melisCoreTool.pending("button");
        if (action === "remove") {

            var tables = [];
            var files = [];
            doAjax("POST", "/melis/MelisMarketPlace/MelisMarketPlace/getModuleTables", {module: module}, function (data) {
                tables = data.tables;
                files = data.files;
            });

            doAjax("POST", "/melis/MelisCore/Modules/getDependents", {
                module: module,
                tables: tables,
                files: files
            }, function (data) {
                var modules = "<br/><br/><div class='container'><div class='row'><div class='col-lg-12'><ul>%s</ul></div></div></div>";
                var moduleList = '';

                $.each(data.modules, function (i, v) {
                    moduleList += "<li>" + v + "</li>";
                });

                modules = modules.replace("%s", moduleList);

                if (data.success) {
                    melisCoreTool.confirm(
                        translations.tr_meliscore_common_yes,
                        translations.tr_meliscore_tool_emails_mngt_generic_from_header_cancel,
                        translations.tr_meliscore_delete_module_header,
                        translations.melis_market_place_tool_package_remove_confirm_on_dependencies.replace("%s", module) + modules + "<br/>" + translations.melis_market_place_tool_package_remove_confirm.replace("%s", module),
                        function () {
                            melisHelper.createModal(zoneId, melisKey, false, objData, modalUrl, function () {

                                melisCoreTool.done("button");
                                checkPermission(module, function () {
                                    doEvent(objData, function () {
                                        postDeleteEvent(module, tables, files);
                                    });
                                });

                            });
                        }
                    );
                }

                if (moduleList === "") {
                    melisCoreTool.confirm(
                        translations.tr_meliscore_common_yes,
                        translations.tr_meliscore_tool_emails_mngt_generic_from_header_cancel,
                        translations.tr_meliscore_delete_module_header,
                        translations.melis_market_place_tool_package_remove_confirm.replace("%s", module),
                        function () {
                            melisHelper.createModal(zoneId, melisKey, true, objData, modalUrl, function () {
                                melisCoreTool.done("button");
                                checkPermission(module, function () {
                                    doEvent(objData, function () {
                                        postDeleteEvent(module, tables, files);
                                    });
                                });
                            });
                        }
                    );
                }

                melisCoreTool.done("button");
                $('div[data-module-name]').bootstrapSwitch('setActive', true);
                $("h4#meliscore-tool-module-content-title").html(translations.tr_meliscore_module_management_modules);
            });
        }
        else {
            // Download and update action
            var modalTitle = translations.tr_market_place_modal_download_title;
            var modalContent = translations.tr_market_place_modal_download_content.replace('%s', module);

            if (action === 'update') {
                modalTitle = translations.tr_market_place_modal_update_title;
                modalContent = translations.tr_market_place_modal_update_content.replace('%s', module);
            }

            melisCoreTool.confirm(
                translations.tr_meliscore_common_yes,
                translations.tr_meliscore_tool_emails_mngt_generic_from_header_cancel,
                modalTitle,
                modalContent,
                function () {
                    function processWorkFlow() {
                        return new Promise(function (resolve, reject) {
                            melisHelper.createModal(zoneId, melisKey, false, objData, modalUrl, function () {
                                melisCoreTool.done("button");
                                doEvent(objData, function () {
                                    // check if the module exists
                                    axiosPost('/melis/MelisMarketPlace/MelisMarketPlace/isModuleExists', {module: module})
                                        .then(function (response) {
                                            if (response.data.isExist || response.data.isExist === true) {
                                                // show reload and activate module buttons
                                                execDbDeploy(module, response, action, resolve, reject);
                                            }
                                        });
                                });
                            });
                        });
                    }

                    processWorkFlow()
                        .then(function (payload) { // @status done | tested
                            // plug module
                            var module = payload.module;
                            axiosPost('/melis/MelisMarketPlace/MelisMarketPlace/plugModule', {module: module})
                                .then(function (response) {
                                    if (response.data.success === true) {
                                        return payload;
                                    } else {
                                        throw new Error(translations.tr_melis_market_place_plug_module_ko.replace('%s', module));
                                    }
                                })
                                .then(function (payload) {
                                    if (typeof payload === 'undefined' || typeof payload == null) {
                                        melisHelper.melisKoNotification('Melis Marketplace', translations.tr_melis_marketplace_setup_error);
                                        return Promise.reject('Melis Marketplace', translations.tr_melis_marketplace_setup_error);
                                    }

                                    // Check for composer scripts to be executed
                                    addLazyCmdText('span_c_scripts_setup', translations.tr_melis_core_composer_scrpts_executing);

                                    setTimeout(function(){
                                        $.get("/melis/MelisMarketPlace/MelisMarketPlace/executeComposerScripts").done(function(res){
                                            updateCmdText(res);
                                            clearLazyCmdText('span_c_scripts_setup', translations.tr_melis_core_composer_scrpts_executed);

                                            // check if the module has a form setup
                                            var hasSetupForm = false;
                                            var form = null;

                                            addLazyCmdText('span_get_setup', translations.tr_melis_marketplace_check_addtl_setup);

                                            setTimeout(function(){
                                                axiosPost('/melis/MelisMarketPlace/MelisMarketPlace/getSetupModuleForm', {
                                                    action: payload.action,
                                                    module: payload.module
                                                })
                                                .then(function (response) {

                                                    clearLazyCmdText('span_get_setup', translations.tr_melis_marketplace_check_addtl_setup_ok);

                                                    if (response.data.form !== '' && response.data.form !== null) {
                                                        hasSetupForm = true;
                                                    }

                                                    return Object.assign(payload, {hasSetupForm: hasSetupForm});
                                                })
                                                .then(function (payload) {

                                                    if (typeof payload === 'undefined' || typeof payload == null) {
                                                        melisHelper.melisKoNotification('Melis Marketplace', translations.tr_melis_marketplace_setup_error);
                                                        return Promise.reject('Melis Marketplace', translations.tr_melis_marketplace_setup_error);
                                                    }

                                                    if (payload.hasSetupForm) {
                                                        // ask the user to proceed or skip setup
                                                        var skip = true;
                                                        melisCoreTool.confirm(
                                                            translations.tr_meliscore_common_yes,
                                                            translations.tr_melis_marketplace_common_no_skip,
                                                            translations.tr_melis_market_place_setup_title.replace('%s', payload.module),
                                                            translations.tr_melis_market_place_has_setup_form.replace('%s', payload.module),
                                                            function () {
                                                                // show the setup form, but verify if the form has a content
                                                                skip = false;
                                                                // open a new modal with the setup form
                                                                melisHelper.createModal('id_melis_market_place_module_setup_form_content_ajax',
                                                                    'melis_market_place_module_setup_form_content', false, payload, modalUrl, function () {
                                                                        melisCoreTool.done("button");
                                                                    });
                                                            },
                                                            function () {
                                                                modalActivateReloadBtns(module, payload);
                                                            }
                                                        );
                                                    }else{
                                                        modalActivateReloadBtns(module, payload);
                                                    }

                                                    return Object.assign(payload, {skip: skip});
                                                })
                                                .catch(function (error) {
                                                    updateCmdText('<span style="color: #ff190d;">' + translations.tr_melis_marketplace_check_addtl_setup_ko + "</span>");
                                                });
                                            }, 5000);
                                        });
                                    }, 5000);
                                });

                            return payload;

                        })
                        .catch(function (err) {
                            console.log(err);
                        });
                }
            );

            melisCoreTool.done("button");
        }

    });

    function execDbDeploy(module, response, action, resolve, reject){
        axiosPost('/melis/MelisMarketPlace/MelisMarketPlace/execDbDeploy', {module: response.data.module})
            .then(function (res) {

                if (res.data.success === -1) {
                    execDbDeploy(module, response, action, resolve, reject);
                }else{
                    if (res.data.success === true) {
                        // replace this text with "Checking additional setup..."
                        updateCmdText(translations.tr_melis_market_place_task_done);
                        // stored to an object, since native Promise object doesn't pass multiple args
                        var payload = Object.assign({action: action}, {data: response.data}, {module: module});
                        resolve(payload);
                    } else {
                        reject(response);
                    }
                }
            });
    }

    function modalActivateReloadBtns(module, payload) {
        // ask the user if they want to activate the module, this will only happen if the action is "download"
        updateCmdText(translations.tr_melis_marketplace_check_addtl_setup_skipped);

        // make sure to unplug module
        axiosPost('/melis/MelisMarketPlace/MelisMarketPlace/unplugModule', {module : module});
        if (payload.action === 'require' || payload.form === '' || payload.form === null && payload.moduleSite === false) {
            $("button.melis-marketplace-modal-activate-module").removeClass("hidden");
        }

        $("button.melis-marketplace-modal-reload").removeClass("hidden");
    }

    $("body").on("click", "button.melis-marketplace-modal-activate-module", function () {
        var module = $(this).data().module;
        doAjax("POST", "/melis/MelisMarketPlace/MelisMarketPlace/activateModule", {module: module}, function () {
            $.get("/melis", function(){
                setTimeout(function(){
                    $("button.melis-marketplace-modal-reload").trigger("click");
                }, 1000);
            });
        });
    });


    $("body").on("click", "button.melis-marketplace-modal-reload", function () {
        melisCoreTool.processing();
        location.reload(true);
    });

    function checkPermission(module, callback) {
        var vConsole = $("body").find("#melis-marketplace-event-do-response");
        var vConsoleText = vConsole.html();

        doAjax("POST", "/melis/MelisMarketPlace/MelisMarketPlace/isPackageDirectoryRemovable", {module: module}, function (resp) {
            if (resp.success == "1" || resp.success === 1) {
                callback();
            }
            else {
                doAjax("POST", "/melis/MelisMarketPlace/MelisMarketPlace/changePackageDirectoryPermission", {module: module}, function (response) {
                    vConsole.html(vConsoleText + '<br/><span style="color:#02de02">' + translations.tr_melis_marketplace_package_directory_change_permission.replace("%s", module) + '</span><br/>');
                    if (resp.success == "1") {
                        callback();
                    }
                    else {
                        vConsole.html(vConsoleText + '<br/><span style="color:#ff190d">' + response.message + '</span>');
                        vConsole.animate({
                            scrollTop: vConsole.prop("scrollHeight")
                        }, 1115);
                    }
                });
            }

        });
    }

    function postDeleteEvent(module, tables, files) {

        var vConsole = $("body").find("#melis-marketplace-event-do-response");
        var vConsoleText = vConsole.html() + '<br/>';

        // check if the module still exists
        doAjax("POST", "/melis/MelisMarketPlace/MelisMarketPlace/isModuleExists", {module: module}, function (module) {

            if (!module.isExist || module.isExist === false) {

                vConsole.html(vConsoleText + '<br/><span style="color:#02de02">' + translations.melis_market_place_tool_package_remove_ok.replace("%s", module.module) + '</span>');

                // export tables
                if (tables.length) {
                    vConsole.html(vConsoleText + '<br/><span style="color:#fbff0f">' + translations.melis_market_place_tool_package_remove_table_dump.replace("%s", module.module) + '</span>');
                    vConsole.animate({
                        scrollTop: vConsole.prop("scrollHeight")
                    }, 1115);

                    $.ajax({
                        type: 'POST',
                        url: '/melis/MelisMarketPlace/MelisMarketPlace/exportTables',
                        data: {module: module.module, tables: tables, files: files},
                        success: function (data, textStatus, request) {
                            var vConsoleText = vConsole.html();
                            // if data is not empty
                            if (data) {
                                var isError = request.getResponseHeader("error");
                                if (isError === "0") {
                                    var fileName = request.getResponseHeader("fileName");
                                    var blob = new Blob([data], {type: "application/sql;charset=utf-8"});
                                    saveAs(blob, fileName);
                                    vConsole.animate({
                                        scrollTop: vConsole.prop("scrollHeight")
                                    }, 1115);
                                    $("button.melis-marketplace-modal-reload").removeClass("hidden");
                                    vConsole.html(vConsoleText + '<br/>' + translations.tr_melis_market_place_export_table_ok + '<br/><span style="color:#02de02">Done</span>');
                                }
                                else {
                                    vConsoleText = vConsole.html();
                                    vConsole.html(vConsoleText + '<br/><span style="color:#fbff0f">' + data.message + '</span>');
                                    vConsoleText = vConsole.html();
                                    vConsole.html(vConsoleText + '<br/><span style="color:#02de02">' + translations.tr_melis_market_place_task_done + '</span>');
                                    vConsole.animate({
                                        scrollTop: vConsole.prop("scrollHeight")
                                    }, 1115);
                                }
                            }
                        }
                    });
                }
                $("button.melis-marketplace-modal-reload").removeClass("hidden");

            }
            else {
                vConsole.html(vConsoleText + '<br/><span style="color:#ff190d">' + translations.melis_market_place_tool_package_remove_ko.replace("%s", module.module) + '</span>');
                vConsole.animate({
                    scrollTop: vConsole.prop("scrollHeight")
                }, 1115);
            }
        });

    }

    function doAjax(type, url, data, callbackOnSuccess, callbackOnFail) {
        $.ajax({
            type: type,
            url: url,
            data: data,
            dataType: 'json',
            encode: true
            // processData: false
        }).success(function (data) {
            try {
                if (callbackOnSuccess !== undefined || callbackOnSuccess !== null) {
                    if (callbackOnSuccess) {
                        callbackOnSuccess(data);
                    }
                }
            } catch (err) {
                addErrorCmdText('<i class="fa fa-close"></i> ' + err.toString());
                melisHelper.melisKoNotification(err.toString());
                console.error(err);
            }
        }).error(function (e) {
            if (callbackOnFail !== undefined || callbackOnFail !== null) {
                if (callbackOnFail) {
                    callbackOnFail(data);
                }
            }
        });
    }

    function axiosPost(url, data) {
        return axiosXhr('POST', url, data);
    }

    function axiosGet(url, data) {
        return axiosXhr('GET', url, data);
    }

    function axiosXhr(method, url, data) {
        if (typeof data === 'object') {
            var formData = new FormData();
            for (var obj in data) {
                formData.append(obj, data[obj]);
            }
            data = formData;
        }
        return axios({
            method: method,
            url: url,
            data: data,
            config: {headers: {'Content-Type': 'multipart/form-data'}}
        });
    }

    function doEvent(data, callback) {
        setTimeout(function () {
            var vConsole = $("body").find("#melis-marketplace-event-do-response");

            var vConsoleText = vConsole.html();
            var lastResponseLen = false;

            $.ajax(
                {
                    type: 'POST',
                    url: '/melis/MelisMarketPlace/MelisMarketPlace/melisMarketPlaceProductDo',
                    data: data,
                    dataType: "html",
                    xhrFields: {
                        onprogress: function (e) {

                            var vConsole = $("body").find("#melis-marketplace-event-do-response");
                            vConsole.html("");
                            var vConsoleText = vConsole.html();

                            var curResponse, response = e.currentTarget.response;
                            if (lastResponseLen === false) {
                                curResponse = response;
                                lastResponseLen = response.length;
                            }
                            else {
                                curResponse = response.substring(lastResponseLen);
                                lastResponseLen = response.length;
                            }
                            vConsoleText += curResponse + "\n<br/>";
                            if (typeof vConsoleText !== "undefined") {

                                vConsole.html(vConsoleText);

                                // always scroll to bottom
                                vConsole.animate({
                                    scrollTop: vConsole.prop("scrollHeight")
                                }, 1115);
                            }

                        }
                    },
                    beforeSend: function () {
                        // do additional task here
                    },
                    success: function (data) {

                        setTimeout(function () {
                            // Composer re-Dumpautoload
                            $.get("/melis/MelisMarketPlace/MelisMarketPlace/reDumpAutoload", function(){

                                vConsoleText = "" + vConsole.html();
                                vConsole.html(vConsoleText + '<span style="color:#02de02"><i class="fa fa-info-circle"></i> ' + translations.tr_melis_market_place_exec_do_done + '<br/>');
                                vConsole.animate({
                                    scrollTop: vConsole.prop("scrollHeight")
                                }, 1115);
                                $("#melis-marketplace-product-modal-hide").removeAttr("disabled");
                                $("#melis-marketplace-product-modal-hide").removeClass("disabled");
                                $("body").find("p#melis-marketplace-console-loading").remove();
                                if (callback !== undefined || callback !== null) {
                                    if (callback) {
                                        callback();
                                    }
                                }
                            });
                        }, 3000);
                    }
                }).error(function (e) {
                var vConsole = $("body").find("#melis-marketplace-event-do-response");
                vConsole.html("An error has occured, please try again");
                $("#melis-marketplace-product-modal-hide").removeAttr("disabled");
                $("#melis-marketplace-product-modal-hide").removeClass("disabled");
            });

        }, 800);
    }


    $("body").on("click", ".melis-market-place-pagination", function () {
        var divOverlay = '<div class="melis-overlay"></div>';
        $("#melis-market-place-package-list").append(divOverlay);
        var page = $(this).data("goto-page");
        var groupId = getActiveGroupIdFilter();
        fetchPackages(page, null, null, null, null, groupId);
    });

    $("body").on("keypress", "input#melis_market_place_search_input", function (e) {
        if (e.which === 13) {
            $("body").find("button#btnMarketPlaceSearch").trigger("click");
        }
    });

    $("body").on("click", "button#btnMarketPlaceSearch", function () {
        var divOverlay = '<div class="melis-overlay"></div>';
        $(".product-list-view").append(divOverlay);
        var search = $("body").find("input#melis_market_place_search_input").val();
        var groupId = getActiveGroupIdFilter();
        fetchPackages(null, search, null, null, null, groupId);

    });

    $("body").on("submit", "form#melis_market_place_search_form", function (e) {
        e.preventDefault();
    });

    $("body").on("click", ".melis-market-place-view-details", function () {
        var packageId = $(this).data().packageid;
        var packageTitle = $(this).data().packagetitle;
        melisHelper.disableAllTabs();
        melisHelper.tabOpen(packageTitle, 'fa-shopping-cart', packageId + '_id_melis_market_place_tool_package_display', 'melis_market_place_tool_package_display', {packageId: packageId}, "id_melis_market_place_tool_display", function () {

        });
        melisHelper.enableAllTabs();

    });


    function plus() {
        var qtyBox = $(this).closest(".product-quantity__box").find("#productQuantity");
        var qtycount = parseInt(qtyBox.val());
        if (qtycount !== qtycount) {
            qtyBox.val(1);
        } else {
            qtycount++;
            qtyBox.val(qtycount);
        }
    }

    function minus() {

        var qtyBox = $(this).closest(".product-quantity__box").find("#productQuantity");
        var qtycount = parseInt(qtyBox.val());

        if (qtycount > 1) {
            qtycount--;
            qtyBox.val(qtycount);
        }
    }

    $("body").on("click", "#btnMinus", minus);
    $("body").on("click", "#btnPlus", plus);

    $("body").on("hide.bs.modal", "#id_melis_market_place_tool_package_modal_content_container, #id_melis_market_place_module_setup_form_content_ajax_container", function (e) {
        if (preventModalClose === true) {
            e.preventDefault();
        }
    });


    $("body").on("click", "#melis-marketplace-product-modal-hide", function () {
        preventModalClose = false;
        $("#id_melis_market_place_tool_package_modal_content_container").modal("hide");
        preventModalClose = true;
    });

    function updateCmdText(message) {
        var vConsole = $("body").find("#melis-marketplace-event-do-response");
        var vConsoleText = "" + vConsole.html();

        vConsole.html(vConsoleText + '<br/>' + message);
        vConsole.animate({
            scrollTop: vConsole.prop("scrollHeight")
        }, 1115);
    }

    function addSuccessCmdText(message)
    {
        updateCmdText('<span style="color: #02de02;">' + message + '</span>');
    }

    function addWarningCmdText(message)
    {
        updateCmdText('<span style="color: #fbff0f;">' + message + '</span>');
    }

    function addErrorCmdText(message)
    {
        updateCmdText('<span style="color: #ff190d;">' + message + '</span>');
    }


    function addLazyCmdText(id, message) {
        updateCmdText('<br/><span id="' + id + '"><i class="fa fa-spinner fa-spin"></i> ' + message + '</span> <br/>');
    }

    function clearLazyCmdText(id, message) {
        $("#" + id).html('<i class="fa fa-info-circle"></i> ' + message + '<br/>');
    }


});


function initSlick() {
    $("#" + activeTabId + ' .slider-single').slick({
        slidesToShow: 1,
        slidesToScroll: 1,
        arrows: true,
        fade: true,
        asNavFor: '.slider-nav',
        adaptiveHeight: true
    });
    $("#" + activeTabId + ' .slider-nav').slick({
        slidesToShow: 6,
        slidesToScroll: 1,
        asNavFor: '.slider-single',
        dots: false,
        centerMode: false,
        focusOnSelect: true,
        arrows: true,
        infinite: false,

        responsive: [{
            breakpoint: 1400,
            settings: {
                slidesToShow: 6,
                slidesToScroll: 6,
            }
        }, {
            breakpoint: 992,
            settings: {
                slidesToShow: 4,
                slidesToScroll: 4,
            }
        }, {
            breakpoint: 767,
            settings: {
                slidesToShow: 2,
                slidesToScroll: 2,
            }
        }]
    });
}

/*Dashboard slider*/
$(document).ready(function () {
    function initDashboardSlider() {
        $(".slider-dashboard-downloaded-packages").slick({

            slidesToShow: 1,
            slidesToScroll: 1,
            autoplay: true,
            autoplaySpeed: 2000,
            arrows: true,
            adaptiveHeight: true,
            dots: true
        });

    }

    //Initialize dashboard slider
    initDashboardSlider();

    //Refresh button in dashboard slider
    $("body").on("click", ".dashboard-downloaded-packages", function () {

        var melisKey = "market_place_most_downloaded_modules";
        var zoneId = "id_market_place_most_downloaded_modules";

        //Zone Reload
        melisHelper.zoneReload(zoneId, melisKey, {}, function () {
            initDashboardSlider();
        });

    });

    //link to market-place
    $("body").on("click", "#link-to-marketplace", function () {
        // tabOpen(title, icon, zoneId, melisKey, parameters, navTabsGroup, callback){

        melisHelper.tabOpen(translations.tr_market_place, "fa-shopping-cart", "id_melis_market_place_tool_display", "melis_market_place_tool_display", {}, null, null);
    });


    /*
   * This is for filtering button
   */
    $("body").on("click", ".market-place-btn-filter-group .btn", function () {

        var flag = 0;
        //put overlay for loading
        var divOverlay = '<div class="melis-overlay"></div>';
        $(".product-list-view").append(divOverlay);

        var isActive = $(this).hasClass("active");


        //get ActiveButtons
        $(this).toggleClass("active");

        var data = getActiveGroupIdFilter();
        var search = $("body").find("input#melis_market_place_search_input").val();

        fetchPackages(null, search, null, null, null, data);


    });

    /*
     * For outdated melis modules
     * that needs to be updated
     */
    $("body").on("click", "#outdated-module-link", function () {
        var packageId = $(this).data().packageid;
        var packageTitle = $(this).data().packagetitle;

        melisHelper.disableAllTabs();
        melisHelper.tabOpen(packageTitle, 'fa-shopping-cart', packageId + '_id_melis_market_place_tool_package_display', 'melis_market_place_tool_package_display', {packageId: packageId}, "id_melis_market_place_tool_display", function () {

        });
        melisHelper.enableAllTabs();

    });

});
=======
function getActiveGroupIdFilter(){for(var e=$(".market-place-btn-filter-group").find(".active"),t=[],i=0;i<e.length;i++){var o=$(e[i]).val();t.push(o)}return[t]}function initSlick(e){$("#"+e+" .slider-single").not(".slick-initialized").slick({slidesToShow:1,slidesToScroll:1,arrows:!0,fade:!0,adaptiveHeight:!0}),$("#"+e+" .slider-nav").not(".slick-initialized").on("init",function(t,i){$("#"+e+" .slider-nav .slick-slide.slick-current").addClass("is-active")}).slick({slidesToShow:6,slidesToScroll:6,dots:!1,focusOnSelect:!1,infinite:!1,responsive:[{breakpoint:1400,settings:{slidesToShow:6,slidesToScroll:6}},{breakpoint:992,settings:{slidesToShow:4,slidesToScroll:4}},{breakpoint:767,settings:{slidesToShow:3,slidesToScroll:3}}]}),$("#"+e+" .slider-single").on("afterChange",function(t,i,o){$("#"+e+" .slider-nav").slick("slickGoTo",o);var s="#"+e+' .slider-nav .slick-slide[data-slick-index="'+o+'"]';$("#"+e+" .slider-nav .slick-slide.is-active").removeClass("is-active"),$(s).addClass("is-active")}),$("#"+e+" .slider-nav").on("click",".slick-slide",function(t){t.preventDefault();var i=$(this).data("slick-index");$("#"+e+" .slider-single").slick("slickGoTo",i)})}function initSlick(){$("#"+activeTabId+" .slider-single").slick({slidesToShow:1,slidesToScroll:1,arrows:!0,fade:!0,asNavFor:".slider-nav",adaptiveHeight:!0}),$("#"+activeTabId+" .slider-nav").slick({slidesToShow:6,slidesToScroll:1,asNavFor:".slider-single",dots:!1,centerMode:!1,focusOnSelect:!0,arrows:!0,infinite:!1,responsive:[{breakpoint:1400,settings:{slidesToShow:6,slidesToScroll:6}},{breakpoint:992,settings:{slidesToShow:4,slidesToScroll:4}},{breakpoint:767,settings:{slidesToShow:2,slidesToScroll:2}}]})}!function(e,t){"object"==typeof exports&&"object"==typeof module?module.exports=t():"function"==typeof define&&define.amd?define([],t):"object"==typeof exports?exports.axios=t():e.axios=t()}(this,function(){return function(e){function t(o){if(i[o])return i[o].exports;var s=i[o]={exports:{},id:o,loaded:!1};return e[o].call(s.exports,s,s.exports,t),s.loaded=!0,s.exports}var i={};return t.m=e,t.c=i,t.p="",t(0)}([function(e,t,i){e.exports=i(1)},function(e,t,i){"use strict";function o(e){var t=new r(e),i=n(r.prototype.request,t);return s.extend(i,r.prototype,t),s.extend(i,t),i}var s=i(2),n=i(3),r=i(5),l=i(6),a=o(l);a.Axios=r,a.create=function(e){return o(s.merge(l,e))},a.Cancel=i(23),a.CancelToken=i(24),a.isCancel=i(20),a.all=function(e){return Promise.all(e)},a.spread=i(25),e.exports=a,e.exports.default=a},function(e,t,i){"use strict";function o(e){return"[object Array]"===S.call(e)}function s(e){return"[object ArrayBuffer]"===S.call(e)}function n(e){return"undefined"!=typeof FormData&&e instanceof FormData}function r(e){return"undefined"!=typeof ArrayBuffer&&ArrayBuffer.isView?ArrayBuffer.isView(e):e&&e.buffer&&e.buffer instanceof ArrayBuffer}function l(e){return"string"==typeof e}function a(e){return"number"==typeof e}function d(e){return void 0===e}function c(e){return null!==e&&"object"==typeof e}function p(e){return"[object Date]"===S.call(e)}function u(e){return"[object File]"===S.call(e)}function f(e){return"[object Blob]"===S.call(e)}function h(e){return"[object Function]"===S.call(e)}function m(e){return c(e)&&h(e.pipe)}function k(e){return"undefined"!=typeof URLSearchParams&&e instanceof URLSearchParams}function v(e){return e.replace(/^\s*/,"").replace(/\s*$/,"")}function g(){return("undefined"==typeof navigator||"ReactNative"!==navigator.product)&&"undefined"!=typeof window&&"undefined"!=typeof document}function y(e,t){if(null!==e&&void 0!==e)if("object"!=typeof e&&(e=[e]),o(e))for(var i=0,s=e.length;i<s;i++)t.call(null,e[i],i,e);else for(var n in e)Object.prototype.hasOwnProperty.call(e,n)&&t.call(null,e[n],n,e)}function w(){function e(e,i){"object"==typeof t[i]&&"object"==typeof e?t[i]=w(t[i],e):t[i]=e}for(var t={},i=0,o=arguments.length;i<o;i++)y(arguments[i],e);return t}function b(e,t,i){return y(t,function(t,o){e[o]=i&&"function"==typeof t?_(t,i):t}),e}var _=i(3),T=i(4),S=Object.prototype.toString;e.exports={isArray:o,isArrayBuffer:s,isBuffer:T,isFormData:n,isArrayBufferView:r,isString:l,isNumber:a,isObject:c,isUndefined:d,isDate:p,isFile:u,isBlob:f,isFunction:h,isStream:m,isURLSearchParams:k,isStandardBrowserEnv:g,forEach:y,merge:w,extend:b,trim:v}},function(e,t){"use strict";e.exports=function(e,t){return function(){for(var i=new Array(arguments.length),o=0;o<i.length;o++)i[o]=arguments[o];return e.apply(t,i)}}},function(e,t){function i(e){return!!e.constructor&&"function"==typeof e.constructor.isBuffer&&e.constructor.isBuffer(e)}function o(e){return"function"==typeof e.readFloatLE&&"function"==typeof e.slice&&i(e.slice(0,0))}e.exports=function(e){return null!=e&&(i(e)||o(e)||!!e._isBuffer)}},function(e,t,i){"use strict";function o(e){this.defaults=e,this.interceptors={request:new r,response:new r}}var s=i(6),n=i(2),r=i(17),l=i(18);o.prototype.request=function(e){"string"==typeof e&&(e=n.merge({url:arguments[0]},arguments[1])),e=n.merge(s,{method:"get"},this.defaults,e),e.method=e.method.toLowerCase();var t=[l,void 0],i=Promise.resolve(e);for(this.interceptors.request.forEach(function(e){t.unshift(e.fulfilled,e.rejected)}),this.interceptors.response.forEach(function(e){t.push(e.fulfilled,e.rejected)});t.length;)i=i.then(t.shift(),t.shift());return i},n.forEach(["delete","get","head","options"],function(e){o.prototype[e]=function(t,i){return this.request(n.merge(i||{},{method:e,url:t}))}}),n.forEach(["post","put","patch"],function(e){o.prototype[e]=function(t,i,o){return this.request(n.merge(o||{},{method:e,url:t,data:i}))}}),e.exports=o},function(e,t,i){"use strict";function o(e,t){!s.isUndefined(e)&&s.isUndefined(e["Content-Type"])&&(e["Content-Type"]=t)}var s=i(2),n=i(7),r={"Content-Type":"application/x-www-form-urlencoded"},l={adapter:function(){var e;return"undefined"!=typeof XMLHttpRequest?e=i(8):"undefined"!=typeof process&&(e=i(8)),e}(),transformRequest:[function(e,t){return n(t,"Content-Type"),s.isFormData(e)||s.isArrayBuffer(e)||s.isBuffer(e)||s.isStream(e)||s.isFile(e)||s.isBlob(e)?e:s.isArrayBufferView(e)?e.buffer:s.isURLSearchParams(e)?(o(t,"application/x-www-form-urlencoded;charset=utf-8"),e.toString()):s.isObject(e)?(o(t,"application/json;charset=utf-8"),JSON.stringify(e)):e}],transformResponse:[function(e){if("string"==typeof e)try{e=JSON.parse(e)}catch(e){}return e}],timeout:0,xsrfCookieName:"XSRF-TOKEN",xsrfHeaderName:"X-XSRF-TOKEN",maxContentLength:-1,validateStatus:function(e){return e>=200&&e<300}};l.headers={common:{Accept:"application/json, text/plain, */*"}},s.forEach(["delete","get","head"],function(e){l.headers[e]={}}),s.forEach(["post","put","patch"],function(e){l.headers[e]=s.merge(r)}),e.exports=l},function(e,t,i){"use strict";var o=i(2);e.exports=function(e,t){o.forEach(e,function(i,o){o!==t&&o.toUpperCase()===t.toUpperCase()&&(e[t]=i,delete e[o])})}},function(e,t,i){"use strict";var o=i(2),s=i(9),n=i(12),r=i(13),l=i(14),a=i(10),d="undefined"!=typeof window&&window.btoa&&window.btoa.bind(window)||i(15);e.exports=function(e){return new Promise(function(t,c){var p=e.data,u=e.headers;o.isFormData(p)&&delete u["Content-Type"];var f=new XMLHttpRequest,h="onreadystatechange",m=!1;if("undefined"==typeof window||!window.XDomainRequest||"withCredentials"in f||l(e.url)||(f=new window.XDomainRequest,h="onload",m=!0,f.onprogress=function(){},f.ontimeout=function(){}),e.auth){var k=e.auth.username||"",v=e.auth.password||"";u.Authorization="Basic "+d(k+":"+v)}if(f.open(e.method.toUpperCase(),n(e.url,e.params,e.paramsSerializer),!0),f.timeout=e.timeout,f[h]=function(){if(f&&(4===f.readyState||m)&&(0!==f.status||f.responseURL&&0===f.responseURL.indexOf("file:"))){var i="getAllResponseHeaders"in f?r(f.getAllResponseHeaders()):null,o=e.responseType&&"text"!==e.responseType?f.response:f.responseText,n={data:o,status:1223===f.status?204:f.status,statusText:1223===f.status?"No Content":f.statusText,headers:i,config:e,request:f};s(t,c,n),f=null}},f.onerror=function(){c(a("Network Error",e,null,f)),f=null},f.ontimeout=function(){c(a("timeout of "+e.timeout+"ms exceeded",e,"ECONNABORTED",f)),f=null},o.isStandardBrowserEnv()){var g=i(16),y=(e.withCredentials||l(e.url))&&e.xsrfCookieName?g.read(e.xsrfCookieName):void 0;y&&(u[e.xsrfHeaderName]=y)}if("setRequestHeader"in f&&o.forEach(u,function(e,t){void 0===p&&"content-type"===t.toLowerCase()?delete u[t]:f.setRequestHeader(t,e)}),e.withCredentials&&(f.withCredentials=!0),e.responseType)try{f.responseType=e.responseType}catch(t){if("json"!==e.responseType)throw t}"function"==typeof e.onDownloadProgress&&f.addEventListener("progress",e.onDownloadProgress),"function"==typeof e.onUploadProgress&&f.upload&&f.upload.addEventListener("progress",e.onUploadProgress),e.cancelToken&&e.cancelToken.promise.then(function(e){f&&(f.abort(),c(e),f=null)}),void 0===p&&(p=null),f.send(p)})}},function(e,t,i){"use strict";var o=i(10);e.exports=function(e,t,i){var s=i.config.validateStatus;i.status&&s&&!s(i.status)?t(o("Request failed with status code "+i.status,i.config,null,i.request,i)):e(i)}},function(e,t,i){"use strict";var o=i(11);e.exports=function(e,t,i,s,n){var r=new Error(e);return o(r,t,i,s,n)}},function(e,t){"use strict";e.exports=function(e,t,i,o,s){return e.config=t,i&&(e.code=i),e.request=o,e.response=s,e}},function(e,t,i){"use strict";function o(e){return encodeURIComponent(e).replace(/%40/gi,"@").replace(/%3A/gi,":").replace(/%24/g,"$").replace(/%2C/gi,",").replace(/%20/g,"+").replace(/%5B/gi,"[").replace(/%5D/gi,"]")}var s=i(2);e.exports=function(e,t,i){if(!t)return e;var n;if(i)n=i(t);else if(s.isURLSearchParams(t))n=t.toString();else{var r=[];s.forEach(t,function(e,t){null!==e&&void 0!==e&&(s.isArray(e)?t+="[]":e=[e],s.forEach(e,function(e){s.isDate(e)?e=e.toISOString():s.isObject(e)&&(e=JSON.stringify(e)),r.push(o(t)+"="+o(e))}))}),n=r.join("&")}return n&&(e+=(-1===e.indexOf("?")?"?":"&")+n),e}},function(e,t,i){"use strict";var o=i(2),s=["age","authorization","content-length","content-type","etag","expires","from","host","if-modified-since","if-unmodified-since","last-modified","location","max-forwards","proxy-authorization","referer","retry-after","user-agent"];e.exports=function(e){var t,i,n,r={};return e?(o.forEach(e.split("\n"),function(e){if(n=e.indexOf(":"),t=o.trim(e.substr(0,n)).toLowerCase(),i=o.trim(e.substr(n+1)),t){if(r[t]&&s.indexOf(t)>=0)return;r[t]="set-cookie"===t?(r[t]?r[t]:[]).concat([i]):r[t]?r[t]+", "+i:i}}),r):r}},function(e,t,i){"use strict";var o=i(2);e.exports=o.isStandardBrowserEnv()?function(){function e(e){var t=e;return i&&(s.setAttribute("href",t),t=s.href),s.setAttribute("href",t),{href:s.href,protocol:s.protocol?s.protocol.replace(/:$/,""):"",host:s.host,search:s.search?s.search.replace(/^\?/,""):"",hash:s.hash?s.hash.replace(/^#/,""):"",hostname:s.hostname,port:s.port,pathname:"/"===s.pathname.charAt(0)?s.pathname:"/"+s.pathname}}var t,i=/(msie|trident)/i.test(navigator.userAgent),s=document.createElement("a");return t=e(window.location.href),function(i){var s=o.isString(i)?e(i):i;return s.protocol===t.protocol&&s.host===t.host}}():function(){return function(){return!0}}()},function(e,t){"use strict";function i(){this.message="String contains an invalid character"}function o(e){for(var t,o,n=String(e),r="",l=0,a=s;n.charAt(0|l)||(a="=",l%1);r+=a.charAt(63&t>>8-l%1*8)){if((o=n.charCodeAt(l+=.75))>255)throw new i;t=t<<8|o}return r}var s="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";i.prototype=new Error,i.prototype.code=5,i.prototype.name="InvalidCharacterError",e.exports=o},function(e,t,i){"use strict";var o=i(2);e.exports=o.isStandardBrowserEnv()?function(){return{write:function(e,t,i,s,n,r){var l=[];l.push(e+"="+encodeURIComponent(t)),o.isNumber(i)&&l.push("expires="+new Date(i).toGMTString()),o.isString(s)&&l.push("path="+s),o.isString(n)&&l.push("domain="+n),!0===r&&l.push("secure"),document.cookie=l.join("; ")},read:function(e){var t=document.cookie.match(new RegExp("(^|;\\s*)("+e+")=([^;]*)"));return t?decodeURIComponent(t[3]):null},remove:function(e){this.write(e,"",Date.now()-864e5)}}}():function(){return{write:function(){},read:function(){return null},remove:function(){}}}()},function(e,t,i){"use strict";function o(){this.handlers=[]}var s=i(2);o.prototype.use=function(e,t){return this.handlers.push({fulfilled:e,rejected:t}),this.handlers.length-1},o.prototype.eject=function(e){this.handlers[e]&&(this.handlers[e]=null)},o.prototype.forEach=function(e){s.forEach(this.handlers,function(t){null!==t&&e(t)})},e.exports=o},function(e,t,i){"use strict";function o(e){e.cancelToken&&e.cancelToken.throwIfRequested()}var s=i(2),n=i(19),r=i(20),l=i(6),a=i(21),d=i(22);e.exports=function(e){return o(e),e.baseURL&&!a(e.url)&&(e.url=d(e.baseURL,e.url)),e.headers=e.headers||{},e.data=n(e.data,e.headers,e.transformRequest),e.headers=s.merge(e.headers.common||{},e.headers[e.method]||{},e.headers||{}),s.forEach(["delete","get","head","post","put","patch","common"],function(t){delete e.headers[t]}),(e.adapter||l.adapter)(e).then(function(t){return o(e),t.data=n(t.data,t.headers,e.transformResponse),t},function(t){return r(t)||(o(e),t&&t.response&&(t.response.data=n(t.response.data,t.response.headers,e.transformResponse))),Promise.reject(t)})}},function(e,t,i){"use strict";var o=i(2);e.exports=function(e,t,i){return o.forEach(i,function(i){e=i(e,t)}),e}},function(e,t){"use strict";e.exports=function(e){return!(!e||!e.__CANCEL__)}},function(e,t){"use strict";e.exports=function(e){return/^([a-z][a-z\d\+\-\.]*:)?\/\//i.test(e)}},function(e,t){"use strict";e.exports=function(e,t){return t?e.replace(/\/+$/,"")+"/"+t.replace(/^\/+/,""):e}},function(e,t){"use strict";function i(e){this.message=e}i.prototype.toString=function(){return"Cancel"+(this.message?": "+this.message:"")},i.prototype.__CANCEL__=!0,e.exports=i},function(e,t,i){"use strict";function o(e){if("function"!=typeof e)throw new TypeError("executor must be a function.");var t;this.promise=new Promise(function(e){t=e});var i=this;e(function(e){i.reason||(i.reason=new s(e),t(i.reason))})}var s=i(23);o.prototype.throwIfRequested=function(){if(this.reason)throw this.reason},o.source=function(){var e;return{token:new o(function(t){e=t}),cancel:e}},e.exports=o},function(e,t){"use strict";e.exports=function(e){return function(t){return e.apply(null,t)}}}])}),function(e){"use strict";"function"==typeof define&&define.amd?define(["jquery"],e):"undefined"!=typeof exports?module.exports=e(require("jquery")):e(jQuery)}(function(e){"use strict";var t=window.Slick||{};t=function(){function t(t,o){var s,n=this;n.defaults={accessibility:!0,adaptiveHeight:!1,appendArrows:e(t),appendDots:e(t),arrows:!0,asNavFor:null,prevArrow:'<button type="button" data-role="none" class="slick-prev" aria-label="Previous" tabindex="0" role="button">Previous</button>',nextArrow:'<button type="button" data-role="none" class="slick-next" aria-label="Next" tabindex="0" role="button">Next</button>',autoplay:!1,autoplaySpeed:3e3,centerMode:!1,centerPadding:"50px",cssEase:"ease",customPaging:function(t,i){return e('<button type="button" data-role="none" role="button" tabindex="0" />').text(i+1)},dots:!1,dotsClass:"slick-dots",draggable:!0,easing:"linear",edgeFriction:.35,fade:!1,focusOnSelect:!1,infinite:!0,initialSlide:0,lazyLoad:"ondemand",mobileFirst:!1,pauseOnHover:!0,pauseOnFocus:!0,pauseOnDotsHover:!1,respondTo:"window",responsive:null,rows:1,rtl:!1,slide:"",slidesPerRow:1,slidesToShow:1,slidesToScroll:1,speed:500,swipe:!0,swipeToSlide:!1,touchMove:!0,touchThreshold:5,useCSS:!0,useTransform:!0,variableWidth:!1,vertical:!1,verticalSwiping:!1,waitForAnimate:!0,zIndex:1e3},n.initials={animating:!1,dragging:!1,autoPlayTimer:null,currentDirection:0,currentLeft:null,currentSlide:0,direction:1,$dots:null,listWidth:null,listHeight:null,loadIndex:0,$nextArrow:null,$prevArrow:null,slideCount:null,slideWidth:null,$slideTrack:null,$slides:null,sliding:!1,slideOffset:0,swipeLeft:null,$list:null,touchObject:{},transformsEnabled:!1,unslicked:!1},e.extend(n,n.initials),n.activeBreakpoint=null,n.animType=null,n.animProp=null,n.breakpoints=[],n.breakpointSettings=[],n.cssTransitions=!1,n.focussed=!1,n.interrupted=!1,n.hidden="hidden",n.paused=!0,n.positionProp=null,n.respondTo=null,n.rowCount=1,n.shouldClick=!0,n.$slider=e(t),n.$slidesCache=null,n.transformType=null,n.transitionType=null,n.visibilityChange="visibilitychange",n.windowWidth=0,n.windowTimer=null,s=e(t).data("slick")||{},n.options=e.extend({},n.defaults,o,s),n.currentSlide=n.options.initialSlide,n.originalSettings=n.options,void 0!==document.mozHidden?(n.hidden="mozHidden",n.visibilityChange="mozvisibilitychange"):void 0!==document.webkitHidden&&(n.hidden="webkitHidden",n.visibilityChange="webkitvisibilitychange"),n.autoPlay=e.proxy(n.autoPlay,n),n.autoPlayClear=e.proxy(n.autoPlayClear,n),n.autoPlayIterator=e.proxy(n.autoPlayIterator,n),n.changeSlide=e.proxy(n.changeSlide,n),n.clickHandler=e.proxy(n.clickHandler,n),n.selectHandler=e.proxy(n.selectHandler,n),n.setPosition=e.proxy(n.setPosition,n),n.swipeHandler=e.proxy(n.swipeHandler,n),n.dragHandler=e.proxy(n.dragHandler,n),n.keyHandler=e.proxy(n.keyHandler,n),n.instanceUid=i++,n.htmlExpr=/^(?:\s*(<[\w\W]+>)[^>]*)$/,n.registerBreakpoints(),n.init(!0)}var i=0;return t}(),t.prototype.activateADA=function(){this.$slideTrack.find(".slick-active").attr({"aria-hidden":"false"}).find("a, input, button, select").attr({tabindex:"0"})},t.prototype.addSlide=t.prototype.slickAdd=function(t,i,o){var s=this;if("boolean"==typeof i)o=i,i=null;else if(0>i||i>=s.slideCount)return!1;s.unload(),"number"==typeof i?0===i&&0===s.$slides.length?e(t).appendTo(s.$slideTrack):o?e(t).insertBefore(s.$slides.eq(i)):e(t).insertAfter(s.$slides.eq(i)):!0===o?e(t).prependTo(s.$slideTrack):e(t).appendTo(s.$slideTrack),s.$slides=s.$slideTrack.children(this.options.slide),s.$slideTrack.children(this.options.slide).detach(),s.$slideTrack.append(s.$slides),s.$slides.each(function(t,i){e(i).attr("data-slick-index",t)}),s.$slidesCache=s.$slides,s.reinit()},t.prototype.animateHeight=function(){var e=this;if(1===e.options.slidesToShow&&!0===e.options.adaptiveHeight&&!1===e.options.vertical){var t=e.$slides.eq(e.currentSlide).outerHeight(!0);e.$list.animate({height:t},e.options.speed)}},t.prototype.animateSlide=function(t,i){var o={},s=this;s.animateHeight(),!0===s.options.rtl&&!1===s.options.vertical&&(t=-t),!1===s.transformsEnabled?!1===s.options.vertical?s.$slideTrack.animate({left:t},s.options.speed,s.options.easing,i):s.$slideTrack.animate({top:t},s.options.speed,s.options.easing,i):!1===s.cssTransitions?(!0===s.options.rtl&&(s.currentLeft=-s.currentLeft),e({animStart:s.currentLeft}).animate({animStart:t},{duration:s.options.speed,easing:s.options.easing,step:function(e){e=Math.ceil(e),!1===s.options.vertical?(o[s.animType]="translate("+e+"px, 0px)",s.$slideTrack.css(o)):(o[s.animType]="translate(0px,"+e+"px)",s.$slideTrack.css(o))},complete:function(){i&&i.call()}})):(s.applyTransition(),t=Math.ceil(t),!1===s.options.vertical?o[s.animType]="translate3d("+t+"px, 0px, 0px)":o[s.animType]="translate3d(0px,"+t+"px, 0px)",s.$slideTrack.css(o),i&&setTimeout(function(){s.disableTransition(),i.call()},s.options.speed))},t.prototype.getNavTarget=function(){var t=this,i=t.options.asNavFor;return i&&null!==i&&(i=e(i).not(t.$slider)),i},t.prototype.asNavFor=function(t){var i=this,o=i.getNavTarget();null!==o&&"object"==typeof o&&o.each(function(){var i=e(this).slick("getSlick");i.unslicked||i.slideHandler(t,!0)})},t.prototype.applyTransition=function(e){var t=this,i={};!1===t.options.fade?i[t.transitionType]=t.transformType+" "+t.options.speed+"ms "+t.options.cssEase:i[t.transitionType]="opacity "+t.options.speed+"ms "+t.options.cssEase,!1===t.options.fade?t.$slideTrack.css(i):t.$slides.eq(e).css(i)},t.prototype.autoPlay=function(){var e=this;e.autoPlayClear(),e.slideCount>e.options.slidesToShow&&(e.autoPlayTimer=setInterval(e.autoPlayIterator,e.options.autoplaySpeed))},t.prototype.autoPlayClear=function(){var e=this;e.autoPlayTimer&&clearInterval(e.autoPlayTimer)},t.prototype.autoPlayIterator=function(){var e=this,t=e.currentSlide+e.options.slidesToScroll;e.paused||e.interrupted||e.focussed||(!1===e.options.infinite&&(1===e.direction&&e.currentSlide+1===e.slideCount-1?e.direction=0:0===e.direction&&(t=e.currentSlide-e.options.slidesToScroll,e.currentSlide-1==0&&(e.direction=1))),e.slideHandler(t))},t.prototype.buildArrows=function(){var t=this;!0===t.options.arrows&&(t.$prevArrow=e(t.options.prevArrow).addClass("slick-arrow"),t.$nextArrow=e(t.options.nextArrow).addClass("slick-arrow"),t.slideCount>t.options.slidesToShow?(t.$prevArrow.removeClass("slick-hidden").removeAttr("aria-hidden tabindex"),t.$nextArrow.removeClass("slick-hidden").removeAttr("aria-hidden tabindex"),t.htmlExpr.test(t.options.prevArrow)&&t.$prevArrow.prependTo(t.options.appendArrows),t.htmlExpr.test(t.options.nextArrow)&&t.$nextArrow.appendTo(t.options.appendArrows),!0!==t.options.infinite&&t.$prevArrow.addClass("slick-disabled").attr("aria-disabled","true")):t.$prevArrow.add(t.$nextArrow).addClass("slick-hidden").attr({"aria-disabled":"true",tabindex:"-1"}))},t.prototype.buildDots=function(){var t,i,o=this;if(!0===o.options.dots&&o.slideCount>o.options.slidesToShow){for(o.$slider.addClass("slick-dotted"),i=e("<ul />").addClass(o.options.dotsClass),t=0;t<=o.getDotCount();t+=1)i.append(e("<li />").append(o.options.customPaging.call(this,o,t)));o.$dots=i.appendTo(o.options.appendDots),o.$dots.find("li").first().addClass("slick-active").attr("aria-hidden","false")}},t.prototype.buildOut=function(){var t=this;t.$slides=t.$slider.children(t.options.slide+":not(.slick-cloned)").addClass("slick-slide"),t.slideCount=t.$slides.length,t.$slides.each(function(t,i){e(i).attr("data-slick-index",t).data("originalStyling",e(i).attr("style")||"")}),t.$slider.addClass("slick-slider"),t.$slideTrack=0===t.slideCount?e('<div class="slick-track"/>').appendTo(t.$slider):t.$slides.wrapAll('<div class="slick-track"/>').parent(),t.$list=t.$slideTrack.wrap('<div aria-live="polite" class="slick-list"/>').parent(),t.$slideTrack.css("opacity",0),(!0===t.options.centerMode||!0===t.options.swipeToSlide)&&(t.options.slidesToScroll=1),e("img[data-lazy]",t.$slider).not("[src]").addClass("slick-loading"),t.setupInfinite(),t.buildArrows(),t.buildDots(),t.updateDots(),t.setSlideClasses("number"==typeof t.currentSlide?t.currentSlide:0),!0===t.options.draggable&&t.$list.addClass("draggable")},t.prototype.buildRows=function(){var e,t,i,o,s,n,r,l=this;if(o=document.createDocumentFragment(),n=l.$slider.children(),l.options.rows>1){for(r=l.options.slidesPerRow*l.options.rows,s=Math.ceil(n.length/r),e=0;s>e;e++){var a=document.createElement("div");for(t=0;t<l.options.rows;t++){var d=document.createElement("div");for(i=0;i<l.options.slidesPerRow;i++){var c=e*r+(t*l.options.slidesPerRow+i);n.get(c)&&d.appendChild(n.get(c))}a.appendChild(d)}o.appendChild(a)}l.$slider.empty().append(o),l.$slider.children().children().children().css({width:100/l.options.slidesPerRow+"%",display:"inline-block"})}},t.prototype.checkResponsive=function(t,i){var o,s,n,r=this,l=!1,a=r.$slider.width(),d=window.innerWidth||e(window).width();if("window"===r.respondTo?n=d:"slider"===r.respondTo?n=a:"min"===r.respondTo&&(n=Math.min(d,a)),r.options.responsive&&r.options.responsive.length&&null!==r.options.responsive){s=null;for(o in r.breakpoints)r.breakpoints.hasOwnProperty(o)&&(!1===r.originalSettings.mobileFirst?n<r.breakpoints[o]&&(s=r.breakpoints[o]):n>r.breakpoints[o]&&(s=r.breakpoints[o]));null!==s?null!==r.activeBreakpoint?(s!==r.activeBreakpoint||i)&&(r.activeBreakpoint=s,"unslick"===r.breakpointSettings[s]?r.unslick(s):(r.options=e.extend({},r.originalSettings,r.breakpointSettings[s]),!0===t&&(r.currentSlide=r.options.initialSlide),r.refresh(t)),l=s):(r.activeBreakpoint=s,"unslick"===r.breakpointSettings[s]?r.unslick(s):(r.options=e.extend({},r.originalSettings,r.breakpointSettings[s]),!0===t&&(r.currentSlide=r.options.initialSlide),r.refresh(t)),l=s):null!==r.activeBreakpoint&&(r.activeBreakpoint=null,r.options=r.originalSettings,!0===t&&(r.currentSlide=r.options.initialSlide),r.refresh(t),l=s),t||!1===l||r.$slider.trigger("breakpoint",[r,l])}},t.prototype.changeSlide=function(t,i){var o,s,n,r=this,l=e(t.currentTarget);switch(l.is("a")&&t.preventDefault(),l.is("li")||(l=l.closest("li")),n=r.slideCount%r.options.slidesToScroll!=0,o=n?0:(r.slideCount-r.currentSlide)%r.options.slidesToScroll,t.data.message){case"previous":s=0===o?r.options.slidesToScroll:r.options.slidesToShow-o,r.slideCount>r.options.slidesToShow&&r.slideHandler(r.currentSlide-s,!1,i);break;case"next":s=0===o?r.options.slidesToScroll:o,r.slideCount>r.options.slidesToShow&&r.slideHandler(r.currentSlide+s,!1,i);break;case"index":var a=0===t.data.index?0:t.data.index||l.index()*r.options.slidesToScroll;r.slideHandler(r.checkNavigable(a),!1,i),l.children().trigger("focus");break;default:return}},t.prototype.checkNavigable=function(e){var t,i;if(t=this.getNavigableIndexes(),i=0,e>t[t.length-1])e=t[t.length-1];else for(var o in t){if(e<t[o]){e=i;break}i=t[o]}return e},t.prototype.cleanUpEvents=function(){var t=this;t.options.dots&&null!==t.$dots&&e("li",t.$dots).off("click.slick",t.changeSlide).off("mouseenter.slick",e.proxy(t.interrupt,t,!0)).off("mouseleave.slick",e.proxy(t.interrupt,t,!1)),t.$slider.off("focus.slick blur.slick"),!0===t.options.arrows&&t.slideCount>t.options.slidesToShow&&(t.$prevArrow&&t.$prevArrow.off("click.slick",t.changeSlide),t.$nextArrow&&t.$nextArrow.off("click.slick",t.changeSlide)),t.$list.off("touchstart.slick mousedown.slick",t.swipeHandler),t.$list.off("touchmove.slick mousemove.slick",t.swipeHandler),t.$list.off("touchend.slick mouseup.slick",t.swipeHandler),t.$list.off("touchcancel.slick mouseleave.slick",t.swipeHandler),t.$list.off("click.slick",t.clickHandler),e(document).off(t.visibilityChange,t.visibility),t.cleanUpSlideEvents(),!0===t.options.accessibility&&t.$list.off("keydown.slick",t.keyHandler),!0===t.options.focusOnSelect&&e(t.$slideTrack).children().off("click.slick",t.selectHandler),e(window).off("orientationchange.slick.slick-"+t.instanceUid,t.orientationChange),e(window).off("resize.slick.slick-"+t.instanceUid,t.resize),e("[draggable!=true]",t.$slideTrack).off("dragstart",t.preventDefault),e(window).off("load.slick.slick-"+t.instanceUid,t.setPosition),e(document).off("ready.slick.slick-"+t.instanceUid,t.setPosition)},t.prototype.cleanUpSlideEvents=function(){var t=this;t.$list.off("mouseenter.slick",e.proxy(t.interrupt,t,!0)),t.$list.off("mouseleave.slick",e.proxy(t.interrupt,t,!1))},t.prototype.cleanUpRows=function(){var e,t=this;t.options.rows>1&&(e=t.$slides.children().children(),e.removeAttr("style"),t.$slider.empty().append(e))},t.prototype.clickHandler=function(e){!1===this.shouldClick&&(e.stopImmediatePropagation(),e.stopPropagation(),e.preventDefault())},t.prototype.destroy=function(t){var i=this;i.autoPlayClear(),i.touchObject={},i.cleanUpEvents(),e(".slick-cloned",i.$slider).detach(),i.$dots&&i.$dots.remove(),i.$prevArrow&&i.$prevArrow.length&&(i.$prevArrow.removeClass("slick-disabled slick-arrow slick-hidden").removeAttr("aria-hidden aria-disabled tabindex").css("display",""),i.htmlExpr.test(i.options.prevArrow)&&i.$prevArrow.remove()),i.$nextArrow&&i.$nextArrow.length&&(i.$nextArrow.removeClass("slick-disabled slick-arrow slick-hidden").removeAttr("aria-hidden aria-disabled tabindex").css("display",""),i.htmlExpr.test(i.options.nextArrow)&&i.$nextArrow.remove()),i.$slides&&(i.$slides.removeClass("slick-slide slick-active slick-center slick-visible slick-current").removeAttr("aria-hidden").removeAttr("data-slick-index").each(function(){e(this).attr("style",e(this).data("originalStyling"))}),i.$slideTrack.children(this.options.slide).detach(),i.$slideTrack.detach(),i.$list.detach(),i.$slider.append(i.$slides)),i.cleanUpRows(),i.$slider.removeClass("slick-slider"),i.$slider.removeClass("slick-initialized"),i.$slider.removeClass("slick-dotted"),i.unslicked=!0,t||i.$slider.trigger("destroy",[i])},t.prototype.disableTransition=function(e){var t=this,i={};i[t.transitionType]="",!1===t.options.fade?t.$slideTrack.css(i):t.$slides.eq(e).css(i)},t.prototype.fadeSlide=function(e,t){var i=this;!1===i.cssTransitions?(i.$slides.eq(e).css({zIndex:i.options.zIndex}),i.$slides.eq(e).animate({opacity:1},i.options.speed,i.options.easing,t)):(i.applyTransition(e),i.$slides.eq(e).css({opacity:1,zIndex:i.options.zIndex}),t&&setTimeout(function(){i.disableTransition(e),t.call()},i.options.speed))},t.prototype.fadeSlideOut=function(e){var t=this;!1===t.cssTransitions?t.$slides.eq(e).animate({opacity:0,zIndex:t.options.zIndex-2},t.options.speed,t.options.easing):(t.applyTransition(e),t.$slides.eq(e).css({opacity:0,zIndex:t.options.zIndex-2}))},t.prototype.filterSlides=t.prototype.slickFilter=function(e){var t=this;null!==e&&(t.$slidesCache=t.$slides,t.unload(),t.$slideTrack.children(this.options.slide).detach(),t.$slidesCache.filter(e).appendTo(t.$slideTrack),t.reinit())},t.prototype.focusHandler=function(){var t=this;t.$slider.off("focus.slick blur.slick").on("focus.slick blur.slick","*:not(.slick-arrow)",function(i){i.stopImmediatePropagation();var o=e(this);setTimeout(function(){t.options.pauseOnFocus&&(t.focussed=o.is(":focus"),t.autoPlay())},0)})},t.prototype.getCurrent=t.prototype.slickCurrentSlide=function(){return this.currentSlide},t.prototype.getDotCount=function(){var e=this,t=0,i=0,o=0;if(!0===e.options.infinite)for(;t<e.slideCount;)++o,t=i+e.options.slidesToScroll,i+=e.options.slidesToScroll<=e.options.slidesToShow?e.options.slidesToScroll:e.options.slidesToShow;else if(!0===e.options.centerMode)o=e.slideCount;else if(e.options.asNavFor)for(;t<e.slideCount;)++o,t=i+e.options.slidesToScroll,i+=e.options.slidesToScroll<=e.options.slidesToShow?e.options.slidesToScroll:e.options.slidesToShow;else o=1+Math.ceil((e.slideCount-e.options.slidesToShow)/e.options.slidesToScroll);return o-1},t.prototype.getLeft=function(e){var t,i,o,s=this,n=0;return s.slideOffset=0,i=s.$slides.first().outerHeight(!0),!0===s.options.infinite?(s.slideCount>s.options.slidesToShow&&(s.slideOffset=s.slideWidth*s.options.slidesToShow*-1,n=i*s.options.slidesToShow*-1),s.slideCount%s.options.slidesToScroll!=0&&e+s.options.slidesToScroll>s.slideCount&&s.slideCount>s.options.slidesToShow&&(e>s.slideCount?(s.slideOffset=(s.options.slidesToShow-(e-s.slideCount))*s.slideWidth*-1,n=(s.options.slidesToShow-(e-s.slideCount))*i*-1):(s.slideOffset=s.slideCount%s.options.slidesToScroll*s.slideWidth*-1,n=s.slideCount%s.options.slidesToScroll*i*-1))):e+s.options.slidesToShow>s.slideCount&&(s.slideOffset=(e+s.options.slidesToShow-s.slideCount)*s.slideWidth,n=(e+s.options.slidesToShow-s.slideCount)*i),s.slideCount<=s.options.slidesToShow&&(s.slideOffset=0,n=0),!0===s.options.centerMode&&!0===s.options.infinite?s.slideOffset+=s.slideWidth*Math.floor(s.options.slidesToShow/2)-s.slideWidth:!0===s.options.centerMode&&(s.slideOffset=0,s.slideOffset+=s.slideWidth*Math.floor(s.options.slidesToShow/2)),t=!1===s.options.vertical?e*s.slideWidth*-1+s.slideOffset:e*i*-1+n,!0===s.options.variableWidth&&(o=s.slideCount<=s.options.slidesToShow||!1===s.options.infinite?s.$slideTrack.children(".slick-slide").eq(e):s.$slideTrack.children(".slick-slide").eq(e+s.options.slidesToShow),t=!0===s.options.rtl?o[0]?-1*(s.$slideTrack.width()-o[0].offsetLeft-o.width()):0:o[0]?-1*o[0].offsetLeft:0,!0===s.options.centerMode&&(o=s.slideCount<=s.options.slidesToShow||!1===s.options.infinite?s.$slideTrack.children(".slick-slide").eq(e):s.$slideTrack.children(".slick-slide").eq(e+s.options.slidesToShow+1),t=!0===s.options.rtl?o[0]?-1*(s.$slideTrack.width()-o[0].offsetLeft-o.width()):0:o[0]?-1*o[0].offsetLeft:0,t+=(s.$list.width()-o.outerWidth())/2)),t},t.prototype.getOption=t.prototype.slickGetOption=function(e){return this.options[e]},t.prototype.getNavigableIndexes=function(){var e,t=this,i=0,o=0,s=[]
;for(!1===t.options.infinite?e=t.slideCount:(i=-1*t.options.slidesToScroll,o=-1*t.options.slidesToScroll,e=2*t.slideCount);e>i;)s.push(i),i=o+t.options.slidesToScroll,o+=t.options.slidesToScroll<=t.options.slidesToShow?t.options.slidesToScroll:t.options.slidesToShow;return s},t.prototype.getSlick=function(){return this},t.prototype.getSlideCount=function(){var t,i,o=this;return i=!0===o.options.centerMode?o.slideWidth*Math.floor(o.options.slidesToShow/2):0,!0===o.options.swipeToSlide?(o.$slideTrack.find(".slick-slide").each(function(s,n){return n.offsetLeft-i+e(n).outerWidth()/2>-1*o.swipeLeft?(t=n,!1):void 0}),Math.abs(e(t).attr("data-slick-index")-o.currentSlide)||1):o.options.slidesToScroll},t.prototype.goTo=t.prototype.slickGoTo=function(e,t){this.changeSlide({data:{message:"index",index:parseInt(e)}},t)},t.prototype.init=function(t){var i=this;e(i.$slider).hasClass("slick-initialized")||(e(i.$slider).addClass("slick-initialized"),i.buildRows(),i.buildOut(),i.setProps(),i.startLoad(),i.loadSlider(),i.initializeEvents(),i.updateArrows(),i.updateDots(),i.checkResponsive(!0),i.focusHandler()),t&&i.$slider.trigger("init",[i]),!0===i.options.accessibility&&i.initADA(),i.options.autoplay&&(i.paused=!1,i.autoPlay())},t.prototype.initADA=function(){var t=this;t.$slides.add(t.$slideTrack.find(".slick-cloned")).attr({"aria-hidden":"true",tabindex:"-1"}).find("a, input, button, select").attr({tabindex:"-1"}),t.$slideTrack.attr("role","listbox"),t.$slides.not(t.$slideTrack.find(".slick-cloned")).each(function(i){e(this).attr({role:"option","aria-describedby":"slick-slide"+t.instanceUid+i})}),null!==t.$dots&&t.$dots.attr("role","tablist").find("li").each(function(i){e(this).attr({role:"presentation","aria-selected":"false","aria-controls":"navigation"+t.instanceUid+i,id:"slick-slide"+t.instanceUid+i})}).first().attr("aria-selected","true").end().find("button").attr("role","button").end().closest("div").attr("role","toolbar"),t.activateADA()},t.prototype.initArrowEvents=function(){var e=this;!0===e.options.arrows&&e.slideCount>e.options.slidesToShow&&(e.$prevArrow.off("click.slick").on("click.slick",{message:"previous"},e.changeSlide),e.$nextArrow.off("click.slick").on("click.slick",{message:"next"},e.changeSlide))},t.prototype.initDotEvents=function(){var t=this;!0===t.options.dots&&t.slideCount>t.options.slidesToShow&&e("li",t.$dots).on("click.slick",{message:"index"},t.changeSlide),!0===t.options.dots&&!0===t.options.pauseOnDotsHover&&e("li",t.$dots).on("mouseenter.slick",e.proxy(t.interrupt,t,!0)).on("mouseleave.slick",e.proxy(t.interrupt,t,!1))},t.prototype.initSlideEvents=function(){var t=this;t.options.pauseOnHover&&(t.$list.on("mouseenter.slick",e.proxy(t.interrupt,t,!0)),t.$list.on("mouseleave.slick",e.proxy(t.interrupt,t,!1)))},t.prototype.initializeEvents=function(){var t=this;t.initArrowEvents(),t.initDotEvents(),t.initSlideEvents(),t.$list.on("touchstart.slick mousedown.slick",{action:"start"},t.swipeHandler),t.$list.on("touchmove.slick mousemove.slick",{action:"move"},t.swipeHandler),t.$list.on("touchend.slick mouseup.slick",{action:"end"},t.swipeHandler),t.$list.on("touchcancel.slick mouseleave.slick",{action:"end"},t.swipeHandler),t.$list.on("click.slick",t.clickHandler),e(document).on(t.visibilityChange,e.proxy(t.visibility,t)),!0===t.options.accessibility&&t.$list.on("keydown.slick",t.keyHandler),!0===t.options.focusOnSelect&&e(t.$slideTrack).children().on("click.slick",t.selectHandler),e(window).on("orientationchange.slick.slick-"+t.instanceUid,e.proxy(t.orientationChange,t)),e(window).on("resize.slick.slick-"+t.instanceUid,e.proxy(t.resize,t)),e("[draggable!=true]",t.$slideTrack).on("dragstart",t.preventDefault),e(window).on("load.slick.slick-"+t.instanceUid,t.setPosition),e(document).on("ready.slick.slick-"+t.instanceUid,t.setPosition)},t.prototype.initUI=function(){var e=this;!0===e.options.arrows&&e.slideCount>e.options.slidesToShow&&(e.$prevArrow.show(),e.$nextArrow.show()),!0===e.options.dots&&e.slideCount>e.options.slidesToShow&&e.$dots.show()},t.prototype.keyHandler=function(e){var t=this;e.target.tagName.match("TEXTAREA|INPUT|SELECT")||(37===e.keyCode&&!0===t.options.accessibility?t.changeSlide({data:{message:!0===t.options.rtl?"next":"previous"}}):39===e.keyCode&&!0===t.options.accessibility&&t.changeSlide({data:{message:!0===t.options.rtl?"previous":"next"}}))},t.prototype.lazyLoad=function(){function t(t){e("img[data-lazy]",t).each(function(){var t=e(this),i=e(this).attr("data-lazy"),o=document.createElement("img");o.onload=function(){t.animate({opacity:0},100,function(){t.attr("src",i).animate({opacity:1},200,function(){t.removeAttr("data-lazy").removeClass("slick-loading")}),r.$slider.trigger("lazyLoaded",[r,t,i])})},o.onerror=function(){t.removeAttr("data-lazy").removeClass("slick-loading").addClass("slick-lazyload-error"),r.$slider.trigger("lazyLoadError",[r,t,i])},o.src=i})}var i,o,s,n,r=this;!0===r.options.centerMode?!0===r.options.infinite?(s=r.currentSlide+(r.options.slidesToShow/2+1),n=s+r.options.slidesToShow+2):(s=Math.max(0,r.currentSlide-(r.options.slidesToShow/2+1)),n=r.options.slidesToShow/2+1+2+r.currentSlide):(s=r.options.infinite?r.options.slidesToShow+r.currentSlide:r.currentSlide,n=Math.ceil(s+r.options.slidesToShow),!0===r.options.fade&&(s>0&&s--,n<=r.slideCount&&n++)),i=r.$slider.find(".slick-slide").slice(s,n),t(i),r.slideCount<=r.options.slidesToShow?(o=r.$slider.find(".slick-slide"),t(o)):r.currentSlide>=r.slideCount-r.options.slidesToShow?(o=r.$slider.find(".slick-cloned").slice(0,r.options.slidesToShow),t(o)):0===r.currentSlide&&(o=r.$slider.find(".slick-cloned").slice(-1*r.options.slidesToShow),t(o))},t.prototype.loadSlider=function(){var e=this;e.setPosition(),e.$slideTrack.css({opacity:1}),e.$slider.removeClass("slick-loading"),e.initUI(),"progressive"===e.options.lazyLoad&&e.progressiveLazyLoad()},t.prototype.next=t.prototype.slickNext=function(){this.changeSlide({data:{message:"next"}})},t.prototype.orientationChange=function(){var e=this;e.checkResponsive(),e.setPosition()},t.prototype.pause=t.prototype.slickPause=function(){var e=this;e.autoPlayClear(),e.paused=!0},t.prototype.play=t.prototype.slickPlay=function(){var e=this;e.autoPlay(),e.options.autoplay=!0,e.paused=!1,e.focussed=!1,e.interrupted=!1},t.prototype.postSlide=function(e){var t=this;t.unslicked||(t.$slider.trigger("afterChange",[t,e]),t.animating=!1,t.setPosition(),t.swipeLeft=null,t.options.autoplay&&t.autoPlay(),!0===t.options.accessibility&&t.initADA())},t.prototype.prev=t.prototype.slickPrev=function(){this.changeSlide({data:{message:"previous"}})},t.prototype.preventDefault=function(e){e.preventDefault()},t.prototype.progressiveLazyLoad=function(t){t=t||1;var i,o,s,n=this,r=e("img[data-lazy]",n.$slider);r.length?(i=r.first(),o=i.attr("data-lazy"),s=document.createElement("img"),s.onload=function(){i.attr("src",o).removeAttr("data-lazy").removeClass("slick-loading"),!0===n.options.adaptiveHeight&&n.setPosition(),n.$slider.trigger("lazyLoaded",[n,i,o]),n.progressiveLazyLoad()},s.onerror=function(){3>t?setTimeout(function(){n.progressiveLazyLoad(t+1)},500):(i.removeAttr("data-lazy").removeClass("slick-loading").addClass("slick-lazyload-error"),n.$slider.trigger("lazyLoadError",[n,i,o]),n.progressiveLazyLoad())},s.src=o):n.$slider.trigger("allImagesLoaded",[n])},t.prototype.refresh=function(t){var i,o,s=this;o=s.slideCount-s.options.slidesToShow,!s.options.infinite&&s.currentSlide>o&&(s.currentSlide=o),s.slideCount<=s.options.slidesToShow&&(s.currentSlide=0),i=s.currentSlide,s.destroy(!0),e.extend(s,s.initials,{currentSlide:i}),s.init(),t||s.changeSlide({data:{message:"index",index:i}},!1)},t.prototype.registerBreakpoints=function(){var t,i,o,s=this,n=s.options.responsive||null;if("array"===e.type(n)&&n.length){s.respondTo=s.options.respondTo||"window";for(t in n)if(o=s.breakpoints.length-1,i=n[t].breakpoint,n.hasOwnProperty(t)){for(;o>=0;)s.breakpoints[o]&&s.breakpoints[o]===i&&s.breakpoints.splice(o,1),o--;s.breakpoints.push(i),s.breakpointSettings[i]=n[t].settings}s.breakpoints.sort(function(e,t){return s.options.mobileFirst?e-t:t-e})}},t.prototype.reinit=function(){var t=this;t.$slides=t.$slideTrack.children(t.options.slide).addClass("slick-slide"),t.slideCount=t.$slides.length,t.currentSlide>=t.slideCount&&0!==t.currentSlide&&(t.currentSlide=t.currentSlide-t.options.slidesToScroll),t.slideCount<=t.options.slidesToShow&&(t.currentSlide=0),t.registerBreakpoints(),t.setProps(),t.setupInfinite(),t.buildArrows(),t.updateArrows(),t.initArrowEvents(),t.buildDots(),t.updateDots(),t.initDotEvents(),t.cleanUpSlideEvents(),t.initSlideEvents(),t.checkResponsive(!1,!0),!0===t.options.focusOnSelect&&e(t.$slideTrack).children().on("click.slick",t.selectHandler),t.setSlideClasses("number"==typeof t.currentSlide?t.currentSlide:0),t.setPosition(),t.focusHandler(),t.paused=!t.options.autoplay,t.autoPlay(),t.$slider.trigger("reInit",[t])},t.prototype.resize=function(){var t=this;e(window).width()!==t.windowWidth&&(clearTimeout(t.windowDelay),t.windowDelay=window.setTimeout(function(){t.windowWidth=e(window).width(),t.checkResponsive(),t.unslicked||t.setPosition()},50))},t.prototype.removeSlide=t.prototype.slickRemove=function(e,t,i){var o=this;return"boolean"==typeof e?(t=e,e=!0===t?0:o.slideCount-1):e=!0===t?--e:e,!(o.slideCount<1||0>e||e>o.slideCount-1)&&(o.unload(),!0===i?o.$slideTrack.children().remove():o.$slideTrack.children(this.options.slide).eq(e).remove(),o.$slides=o.$slideTrack.children(this.options.slide),o.$slideTrack.children(this.options.slide).detach(),o.$slideTrack.append(o.$slides),o.$slidesCache=o.$slides,void o.reinit())},t.prototype.setCSS=function(e){var t,i,o=this,s={};!0===o.options.rtl&&(e=-e),t="left"==o.positionProp?Math.ceil(e)+"px":"0px",i="top"==o.positionProp?Math.ceil(e)+"px":"0px",s[o.positionProp]=e,!1===o.transformsEnabled?o.$slideTrack.css(s):(s={},!1===o.cssTransitions?(s[o.animType]="translate("+t+", "+i+")",o.$slideTrack.css(s)):(s[o.animType]="translate3d("+t+", "+i+", 0px)",o.$slideTrack.css(s)))},t.prototype.setDimensions=function(){var e=this;!1===e.options.vertical?!0===e.options.centerMode&&e.$list.css({padding:"0px "+e.options.centerPadding}):(e.$list.height(e.$slides.first().outerHeight(!0)*e.options.slidesToShow),!0===e.options.centerMode&&e.$list.css({padding:e.options.centerPadding+" 0px"})),e.listWidth=e.$list.width(),e.listHeight=e.$list.height(),!1===e.options.vertical&&!1===e.options.variableWidth?(e.slideWidth=Math.ceil(e.listWidth/e.options.slidesToShow),e.$slideTrack.width(Math.ceil(e.slideWidth*e.$slideTrack.children(".slick-slide").length))):!0===e.options.variableWidth?e.$slideTrack.width(5e3*e.slideCount):(e.slideWidth=Math.ceil(e.listWidth),e.$slideTrack.height(Math.ceil(e.$slides.first().outerHeight(!0)*e.$slideTrack.children(".slick-slide").length)));var t=e.$slides.first().outerWidth(!0)-e.$slides.first().width();!1===e.options.variableWidth&&e.$slideTrack.children(".slick-slide").width(e.slideWidth-t)},t.prototype.setFade=function(){var t,i=this;i.$slides.each(function(o,s){t=i.slideWidth*o*-1,!0===i.options.rtl?e(s).css({position:"relative",right:t,top:0,zIndex:i.options.zIndex-2,opacity:0}):e(s).css({position:"relative",left:t,top:0,zIndex:i.options.zIndex-2,opacity:0})}),i.$slides.eq(i.currentSlide).css({zIndex:i.options.zIndex-1,opacity:1})},t.prototype.setHeight=function(){var e=this;if(1===e.options.slidesToShow&&!0===e.options.adaptiveHeight&&!1===e.options.vertical){var t=e.$slides.eq(e.currentSlide).outerHeight(!0);e.$list.css("height",t)}},t.prototype.setOption=t.prototype.slickSetOption=function(){var t,i,o,s,n,r=this,l=!1;if("object"===e.type(arguments[0])?(o=arguments[0],l=arguments[1],n="multiple"):"string"===e.type(arguments[0])&&(o=arguments[0],s=arguments[1],l=arguments[2],"responsive"===arguments[0]&&"array"===e.type(arguments[1])?n="responsive":void 0!==arguments[1]&&(n="single")),"single"===n)r.options[o]=s;else if("multiple"===n)e.each(o,function(e,t){r.options[e]=t});else if("responsive"===n)for(i in s)if("array"!==e.type(r.options.responsive))r.options.responsive=[s[i]];else{for(t=r.options.responsive.length-1;t>=0;)r.options.responsive[t].breakpoint===s[i].breakpoint&&r.options.responsive.splice(t,1),t--;r.options.responsive.push(s[i])}l&&(r.unload(),r.reinit())},t.prototype.setPosition=function(){var e=this;e.setDimensions(),e.setHeight(),!1===e.options.fade?e.setCSS(e.getLeft(e.currentSlide)):e.setFade(),e.$slider.trigger("setPosition",[e])},t.prototype.setProps=function(){var e=this,t=document.body.style;e.positionProp=!0===e.options.vertical?"top":"left","top"===e.positionProp?e.$slider.addClass("slick-vertical"):e.$slider.removeClass("slick-vertical"),(void 0!==t.WebkitTransition||void 0!==t.MozTransition||void 0!==t.msTransition)&&!0===e.options.useCSS&&(e.cssTransitions=!0),e.options.fade&&("number"==typeof e.options.zIndex?e.options.zIndex<3&&(e.options.zIndex=3):e.options.zIndex=e.defaults.zIndex),void 0!==t.OTransform&&(e.animType="OTransform",e.transformType="-o-transform",e.transitionType="OTransition",void 0===t.perspectiveProperty&&void 0===t.webkitPerspective&&(e.animType=!1)),void 0!==t.MozTransform&&(e.animType="MozTransform",e.transformType="-moz-transform",e.transitionType="MozTransition",void 0===t.perspectiveProperty&&void 0===t.MozPerspective&&(e.animType=!1)),void 0!==t.webkitTransform&&(e.animType="webkitTransform",e.transformType="-webkit-transform",e.transitionType="webkitTransition",void 0===t.perspectiveProperty&&void 0===t.webkitPerspective&&(e.animType=!1)),void 0!==t.msTransform&&(e.animType="msTransform",e.transformType="-ms-transform",e.transitionType="msTransition",void 0===t.msTransform&&(e.animType=!1)),void 0!==t.transform&&!1!==e.animType&&(e.animType="transform",e.transformType="transform",e.transitionType="transition"),e.transformsEnabled=e.options.useTransform&&null!==e.animType&&!1!==e.animType},t.prototype.setSlideClasses=function(e){var t,i,o,s,n=this;i=n.$slider.find(".slick-slide").removeClass("slick-active slick-center slick-current").attr("aria-hidden","true"),n.$slides.eq(e).addClass("slick-current"),!0===n.options.centerMode?(t=Math.floor(n.options.slidesToShow/2),!0===n.options.infinite&&(e>=t&&e<=n.slideCount-1-t?n.$slides.slice(e-t,e+t+1).addClass("slick-active").attr("aria-hidden","false"):(o=n.options.slidesToShow+e,i.slice(o-t+1,o+t+2).addClass("slick-active").attr("aria-hidden","false")),0===e?i.eq(i.length-1-n.options.slidesToShow).addClass("slick-center"):e===n.slideCount-1&&i.eq(n.options.slidesToShow).addClass("slick-center")),n.$slides.eq(e).addClass("slick-center")):e>=0&&e<=n.slideCount-n.options.slidesToShow?n.$slides.slice(e,e+n.options.slidesToShow).addClass("slick-active").attr("aria-hidden","false"):i.length<=n.options.slidesToShow?i.addClass("slick-active").attr("aria-hidden","false"):(s=n.slideCount%n.options.slidesToShow,o=!0===n.options.infinite?n.options.slidesToShow+e:e,n.options.slidesToShow==n.options.slidesToScroll&&n.slideCount-e<n.options.slidesToShow?i.slice(o-(n.options.slidesToShow-s),o+s).addClass("slick-active").attr("aria-hidden","false"):i.slice(o,o+n.options.slidesToShow).addClass("slick-active").attr("aria-hidden","false")),"ondemand"===n.options.lazyLoad&&n.lazyLoad()},t.prototype.setupInfinite=function(){var t,i,o,s=this;if(!0===s.options.fade&&(s.options.centerMode=!1),!0===s.options.infinite&&!1===s.options.fade&&(i=null,s.slideCount>s.options.slidesToShow)){for(o=!0===s.options.centerMode?s.options.slidesToShow+1:s.options.slidesToShow,t=s.slideCount;t>s.slideCount-o;t-=1)i=t-1,e(s.$slides[i]).clone(!0).attr("id","").attr("data-slick-index",i-s.slideCount).prependTo(s.$slideTrack).addClass("slick-cloned");for(t=0;o>t;t+=1)i=t,e(s.$slides[i]).clone(!0).attr("id","").attr("data-slick-index",i+s.slideCount).appendTo(s.$slideTrack).addClass("slick-cloned");s.$slideTrack.find(".slick-cloned").find("[id]").each(function(){e(this).attr("id","")})}},t.prototype.interrupt=function(e){var t=this;e||t.autoPlay(),t.interrupted=e},t.prototype.selectHandler=function(t){var i=this,o=e(t.target).is(".slick-slide")?e(t.target):e(t.target).parents(".slick-slide"),s=parseInt(o.attr("data-slick-index"));return s||(s=0),i.slideCount<=i.options.slidesToShow?(i.setSlideClasses(s),void i.asNavFor(s)):void i.slideHandler(s)},t.prototype.slideHandler=function(e,t,i){var o,s,n,r,l,a=null,d=this;return t=t||!1,!0===d.animating&&!0===d.options.waitForAnimate||!0===d.options.fade&&d.currentSlide===e||d.slideCount<=d.options.slidesToShow?void 0:(!1===t&&d.asNavFor(e),o=e,a=d.getLeft(o),r=d.getLeft(d.currentSlide),d.currentLeft=null===d.swipeLeft?r:d.swipeLeft,!1===d.options.infinite&&!1===d.options.centerMode&&(0>e||e>d.getDotCount()*d.options.slidesToScroll)?void(!1===d.options.fade&&(o=d.currentSlide,!0!==i?d.animateSlide(r,function(){d.postSlide(o)}):d.postSlide(o))):!1===d.options.infinite&&!0===d.options.centerMode&&(0>e||e>d.slideCount-d.options.slidesToScroll)?void(!1===d.options.fade&&(o=d.currentSlide,!0!==i?d.animateSlide(r,function(){d.postSlide(o)}):d.postSlide(o))):(d.options.autoplay&&clearInterval(d.autoPlayTimer),s=0>o?d.slideCount%d.options.slidesToScroll!=0?d.slideCount-d.slideCount%d.options.slidesToScroll:d.slideCount+o:o>=d.slideCount?d.slideCount%d.options.slidesToScroll!=0?0:o-d.slideCount:o,d.animating=!0,d.$slider.trigger("beforeChange",[d,d.currentSlide,s]),n=d.currentSlide,d.currentSlide=s,d.setSlideClasses(d.currentSlide),d.options.asNavFor&&(l=d.getNavTarget(),l=l.slick("getSlick"),l.slideCount<=l.options.slidesToShow&&l.setSlideClasses(d.currentSlide)),d.updateDots(),d.updateArrows(),!0===d.options.fade?(!0!==i?(d.fadeSlideOut(n),d.fadeSlide(s,function(){d.postSlide(s)})):d.postSlide(s),void d.animateHeight()):void(!0!==i?d.animateSlide(a,function(){d.postSlide(s)}):d.postSlide(s))))},t.prototype.startLoad=function(){var e=this;!0===e.options.arrows&&e.slideCount>e.options.slidesToShow&&(e.$prevArrow.hide(),e.$nextArrow.hide()),!0===e.options.dots&&e.slideCount>e.options.slidesToShow&&e.$dots.hide(),e.$slider.addClass("slick-loading")},t.prototype.swipeDirection=function(){var e,t,i,o,s=this;return e=s.touchObject.startX-s.touchObject.curX,t=s.touchObject.startY-s.touchObject.curY,i=Math.atan2(t,e),o=Math.round(180*i/Math.PI),0>o&&(o=360-Math.abs(o)),45>=o&&o>=0?!1===s.options.rtl?"left":"right":360>=o&&o>=315?!1===s.options.rtl?"left":"right":o>=135&&225>=o?!1===s.options.rtl?"right":"left":!0===s.options.verticalSwiping?o>=35&&135>=o?"down":"up":"vertical"},t.prototype.swipeEnd=function(e){var t,i,o=this;if(o.dragging=!1,o.interrupted=!1,o.shouldClick=!(o.touchObject.swipeLength>10),void 0===o.touchObject.curX)return!1;if(!0===o.touchObject.edgeHit&&o.$slider.trigger("edge",[o,o.swipeDirection()]),o.touchObject.swipeLength>=o.touchObject.minSwipe){switch(i=o.swipeDirection()){case"left":case"down":t=o.options.swipeToSlide?o.checkNavigable(o.currentSlide+o.getSlideCount()):o.currentSlide+o.getSlideCount(),o.currentDirection=0;break;case"right":case"up":t=o.options.swipeToSlide?o.checkNavigable(o.currentSlide-o.getSlideCount()):o.currentSlide-o.getSlideCount(),o.currentDirection=1}"vertical"!=i&&(o.slideHandler(t),o.touchObject={},o.$slider.trigger("swipe",[o,i]))}else o.touchObject.startX!==o.touchObject.curX&&(o.slideHandler(o.currentSlide),o.touchObject={})},t.prototype.swipeHandler=function(e){var t=this;if(!(!1===t.options.swipe||"ontouchend"in document&&!1===t.options.swipe||!1===t.options.draggable&&-1!==e.type.indexOf("mouse")))switch(t.touchObject.fingerCount=e.originalEvent&&void 0!==e.originalEvent.touches?e.originalEvent.touches.length:1,t.touchObject.minSwipe=t.listWidth/t.options.touchThreshold,!0===t.options.verticalSwiping&&(t.touchObject.minSwipe=t.listHeight/t.options.touchThreshold),e.data.action){case"start":t.swipeStart(e);break;case"move":t.swipeMove(e);break;case"end":t.swipeEnd(e)}},t.prototype.swipeMove=function(e){var t,i,o,s,n,r=this;return n=void 0!==e.originalEvent?e.originalEvent.touches:null,!(!r.dragging||n&&1!==n.length)&&(t=r.getLeft(r.currentSlide),r.touchObject.curX=void 0!==n?n[0].pageX:e.clientX,r.touchObject.curY=void 0!==n?n[0].pageY:e.clientY,r.touchObject.swipeLength=Math.round(Math.sqrt(Math.pow(r.touchObject.curX-r.touchObject.startX,2))),!0===r.options.verticalSwiping&&(r.touchObject.swipeLength=Math.round(Math.sqrt(Math.pow(r.touchObject.curY-r.touchObject.startY,2)))),i=r.swipeDirection(),"vertical"!==i?(void 0!==e.originalEvent&&r.touchObject.swipeLength>4&&e.preventDefault(),s=(!1===r.options.rtl?1:-1)*(r.touchObject.curX>r.touchObject.startX?1:-1),!0===r.options.verticalSwiping&&(s=r.touchObject.curY>r.touchObject.startY?1:-1),o=r.touchObject.swipeLength,r.touchObject.edgeHit=!1,!1===r.options.infinite&&(0===r.currentSlide&&"right"===i||r.currentSlide>=r.getDotCount()&&"left"===i)&&(o=r.touchObject.swipeLength*r.options.edgeFriction,r.touchObject.edgeHit=!0),!1===r.options.vertical?r.swipeLeft=t+o*s:r.swipeLeft=t+o*(r.$list.height()/r.listWidth)*s,!0===r.options.verticalSwiping&&(r.swipeLeft=t+o*s),!0!==r.options.fade&&!1!==r.options.touchMove&&(!0===r.animating?(r.swipeLeft=null,!1):void r.setCSS(r.swipeLeft))):void 0)},t.prototype.swipeStart=function(e){var t,i=this;return i.interrupted=!0,1!==i.touchObject.fingerCount||i.slideCount<=i.options.slidesToShow?(i.touchObject={},!1):(void 0!==e.originalEvent&&void 0!==e.originalEvent.touches&&(t=e.originalEvent.touches[0]),i.touchObject.startX=i.touchObject.curX=void 0!==t?t.pageX:e.clientX,i.touchObject.startY=i.touchObject.curY=void 0!==t?t.pageY:e.clientY,void(i.dragging=!0))},t.prototype.unfilterSlides=t.prototype.slickUnfilter=function(){var e=this;null!==e.$slidesCache&&(e.unload(),e.$slideTrack.children(this.options.slide).detach(),e.$slidesCache.appendTo(e.$slideTrack),e.reinit())},t.prototype.unload=function(){var t=this;e(".slick-cloned",t.$slider).remove(),t.$dots&&t.$dots.remove(),t.$prevArrow&&t.htmlExpr.test(t.options.prevArrow)&&t.$prevArrow.remove(),t.$nextArrow&&t.htmlExpr.test(t.options.nextArrow)&&t.$nextArrow.remove(),t.$slides.removeClass("slick-slide slick-active slick-visible slick-current").attr("aria-hidden","true").css("width","")},t.prototype.unslick=function(e){var t=this;t.$slider.trigger("unslick",[t,e]),t.destroy()},t.prototype.updateArrows=function(){var e=this;Math.floor(e.options.slidesToShow/2),!0===e.options.arrows&&e.slideCount>e.options.slidesToShow&&!e.options.infinite&&(e.$prevArrow.removeClass("slick-disabled").attr("aria-disabled","false"),e.$nextArrow.removeClass("slick-disabled").attr("aria-disabled","false"),0===e.currentSlide?(e.$prevArrow.addClass("slick-disabled").attr("aria-disabled","true"),e.$nextArrow.removeClass("slick-disabled").attr("aria-disabled","false")):e.currentSlide>=e.slideCount-e.options.slidesToShow&&!1===e.options.centerMode?(e.$nextArrow.addClass("slick-disabled").attr("aria-disabled","true"),e.$prevArrow.removeClass("slick-disabled").attr("aria-disabled","false")):e.currentSlide>=e.slideCount-1&&!0===e.options.centerMode&&(e.$nextArrow.addClass("slick-disabled").attr("aria-disabled","true"),e.$prevArrow.removeClass("slick-disabled").attr("aria-disabled","false")))},t.prototype.updateDots=function(){var e=this;null!==e.$dots&&(e.$dots.find("li").removeClass("slick-active").attr("aria-hidden","true"),e.$dots.find("li").eq(Math.floor(e.currentSlide/e.options.slidesToScroll)).addClass("slick-active").attr("aria-hidden","false"))},t.prototype.visibility=function(){var e=this;e.options.autoplay&&(document[e.hidden]?e.interrupted=!0:e.interrupted=!1)},e.fn.slick=function(){var e,i,o=this,s=arguments[0],n=Array.prototype.slice.call(arguments,1),r=o.length;for(e=0;r>e;e++)if("object"==typeof s||void 0===s?o[e].slick=new t(o[e],s):i=o[e].slick[s].apply(o[e].slick,n),void 0!==i)return i;return o}}),window.fetchPackages=function(e,t,i,o,s,n){e=e||1,t=t||$("body").find("input#melis_market_place_search_input").val(),i=i||"mp_total_downloads",o=o||"desc",s=s||9,n||(n=["1","2","3","4","5"]),$(".market-place-btn-filter-group button").attr("disabled","disabled"),$("#btnMarketPlaceSearch").attr("disabled","disabled"),$.ajax({type:"POST",url:"/melis/MelisMarketPlace/MelisMarketPlace/package-list?page="+e+"&search="+t+"&orderBy="+i+"&group="+n,data:{page:e,search:t,orderBy:i,order:o,itemPerPage:s,group:n},dataType:"html",success:function(e){$("body").find("div#melis-market-place-package-list").html(e),$(".market-place-btn-filter-group button").removeAttr("disabled","disabled"),$("#btnMarketPlaceSearch").removeAttr("disabled","disabled")}})},$(function(){function e(t,i,o,s,r){n("/melis/MelisMarketPlace/MelisMarketPlace/execDbDeploy",{module:i.data.module}).then(function(n){if(-1===n.data.success)e(t,i,o,s,r);else if(!0===n.data.success){c(translations.tr_melis_market_place_task_done);var l=Object.assign({action:o},{data:i.data},{module:t});s(l)}else r(i)})}function t(e,t){c(translations.tr_melis_marketplace_check_addtl_setup_skipped),n("/melis/MelisMarketPlace/MelisMarketPlace/unplugModule",{module:e}),("require"===t.action||""===t.form||null===t.form&&!1===t.moduleSite)&&$("button.melis-marketplace-modal-activate-module").removeClass("hidden"),$("button.melis-marketplace-modal-reload").removeClass("hidden")}function i(e,t){var i=$("body").find("#melis-marketplace-event-do-response"),o=i.html();s("POST","/melis/MelisMarketPlace/MelisMarketPlace/isPackageDirectoryRemovable",{module:e},function(n){"1"==n.success||1===n.success?t():s("POST","/melis/MelisMarketPlace/MelisMarketPlace/changePackageDirectoryPermission",{module:e},function(s){i.html(o+'<br/><span style="color:#02de02">'+translations.tr_melis_marketplace_package_directory_change_permission.replace("%s",e)+"</span><br/>"),"1"==n.success?t():(i.html(o+'<br/><span style="color:#ff190d">'+s.message+"</span>"),i.animate({scrollTop:i.prop("scrollHeight")},1115))})})}function o(e,t,i){var o=$("body").find("#melis-marketplace-event-do-response"),n=o.html()+"<br/>";s("POST","/melis/MelisMarketPlace/MelisMarketPlace/isModuleExists",{module:e},function(e){e.isExist&&!1!==e.isExist?(o.html(n+'<br/><span style="color:#ff190d">'+translations.melis_market_place_tool_package_remove_ko.replace("%s",e.module)+"</span>"),o.animate({scrollTop:o.prop("scrollHeight")},1115)):(o.html(n+'<br/><span style="color:#02de02">'+translations.melis_market_place_tool_package_remove_ok.replace("%s",e.module)+"</span>"),t.length&&(o.html(n+'<br/><span style="color:#fbff0f">'+translations.melis_market_place_tool_package_remove_table_dump.replace("%s",e.module)+"</span>"),o.animate({scrollTop:o.prop("scrollHeight")},1115),$.ajax({type:"POST",url:"/melis/MelisMarketPlace/MelisMarketPlace/exportTables",data:{module:e.module,tables:t,files:i},success:function(e,t,i){var s=o.html();if(e){if("0"===i.getResponseHeader("error")){var n=i.getResponseHeader("fileName"),r=new Blob([e],{type:"application/sql;charset=utf-8"});saveAs(r,n),o.animate({scrollTop:o.prop("scrollHeight")},1115),$("button.melis-marketplace-modal-reload").removeClass("hidden"),o.html(s+"<br/>"+translations.tr_melis_market_place_export_table_ok+'<br/><span style="color:#02de02">Done</span>')}else s=o.html(),o.html(s+'<br/><span style="color:#fbff0f">'+e.message+"</span>"),s=o.html(),o.html(s+'<br/><span style="color:#02de02">'+translations.tr_melis_market_place_task_done+"</span>"),o.animate({scrollTop:o.prop("scrollHeight")},1115)}}})),$("button.melis-marketplace-modal-reload").removeClass("hidden"))})}function s(e,t,i,o,s){$.ajax({type:e,url:t,data:i,dataType:"json",encode:!0}).success(function(e){try{void 0===o&&null===o||o&&o(e)}catch(e){u('<i class="fa fa-close"></i> '+e.toString()),melisHelper.melisKoNotification(e.toString()),console.error(e)}}).error(function(e){void 0===s&&null===s||s&&s(i)})}function n(e,t){return r("POST",e,t)}function r(e,t,i){if("object"==typeof i){var o=new FormData;for(var s in i)o.append(s,i[s]);i=o}return axios({method:e,url:t,data:i,config:{headers:{"Content-Type":"multipart/form-data"}}})}function l(e,t){setTimeout(function(){var i=$("body").find("#melis-marketplace-event-do-response"),o=i.html(),s=!1;$.ajax({type:"POST",url:"/melis/MelisMarketPlace/MelisMarketPlace/melisMarketPlaceProductDo",data:e,dataType:"html",xhrFields:{onprogress:function(e){var t=$("body").find("#melis-marketplace-event-do-response");t.html().includes("pre-task-action")&&t.html("");var i,o=t.html(),n=e.currentTarget.response;!1===s?(i=n,s=n.length):(i=n.substring(s),s=n.length),void 0!==(o+=i+"\n<br/>")&&(t.html(o),t.animate({scrollTop:t.prop("scrollHeight")},1115))}},beforeSend:function(){},success:function(e){setTimeout(function(){$.get("/melis/MelisMarketPlace/MelisMarketPlace/reDumpAutoload",function(){o=""+i.html(),i.html(o+'<span style="color:#02de02"><i class="fa fa-info-circle"></i> '+translations.tr_melis_market_place_exec_do_done+"<br/>"),i.animate({scrollTop:i.prop("scrollHeight")},1115),$("#melis-marketplace-product-modal-hide").removeAttr("disabled"),$("#melis-marketplace-product-modal-hide").removeClass("disabled"),$("body").find("p#melis-marketplace-console-loading").remove(),void 0===t&&null===t||t&&t()})},3e3)}}).error(function(e){$("body").find("#melis-marketplace-event-do-response").html("An error has occured, please try again"),$("#melis-marketplace-product-modal-hide").removeAttr("disabled"),$("#melis-marketplace-product-modal-hide").removeClass("disabled")})},800)}function a(){var e=$(this).closest(".product-quantity__box").find("#productQuantity"),t=parseInt(e.val());t!==t?e.val(1):(t++,e.val(t))}function d(){var e=$(this).closest(".product-quantity__box").find("#productQuantity"),t=parseInt(e.val());t>1&&(t--,e.val(t))}function c(e){var t=$("body").find("#melis-marketplace-event-do-response"),i=""+t.html();t.html(i+"<br/>"+e),t.animate({scrollTop:t.prop("scrollHeight")},1115)}function p(e){c('<span style="color: #02de02;">'+e+"</span>")}function u(e){c('<span style="color: #ff190d;">'+e+"</span>")}function f(e,t){c('<br/><span id="'+e+'"><i class="fa fa-spinner fa-spin"></i> '+t+"</span> <br/>")}function h(e,t){$("#"+e).html('<i class="fa fa-info-circle"></i> '+t+"<br/>")}var m=!0;$("body").on("shown.bs.tab","#melis-id-nav-bar-tabs [data-tool-meliskey='melis_market_place_tool_package_display'] a[data-toggle='tab']",function(e){initSlick(activeTabId)}),$("body").on("click","#melis-marketplace-setup-modal-submit",function(){var e=$("#melis-marketplace-setup-modal-submit").parents().find("form");if(e.length){var t=$("#id_melis_market_place_module_setup_form_content"),i=e.serializeArray(),o=t.data().action,n=t.data().module;i.push({name:"module",value:n}),i.push({name:"action",value:o}),melisCoreTool.pending("#melis-marketplace-setup-modal-submit"),s("POST","/melis/MelisMarketPlace/MelisMarketPlace/validateSetupForm",$.param(i),function(t){null==t.result.errors&&void 0===t.result.errors||(t.result.success?s("POST","/melis/MelisMarketPlace/MelisMarketPlace/submitSetupForm",$.param(i),function(t){t.success?(melisHelper.melisOkNotification(t.module,t.message),s("POST","/melis/MelisMarketPlace/MelisMarketPlace/unplugModule",{module:n},function(e){if(!0!==e.success)throw new Error(translations.tr_melis_market_place_plug_module_ko.replace("%s",n));m=!1,$("#id_melis_market_place_module_setup_form_content_ajax_container").modal("hide"),p(translations.tr_melis_marketplace_setup_config_ok),("download"===o||"require"===o&&!1===e.moduleSite)&&$("button.melis-marketplace-modal-activate-module").removeClass("hidden"),$("button.melis-marketplace-modal-reload").removeClass("hidden")})):(melisHelper.melisKoNotification(translations.tr_melis_market_place_setup_title.replace("%s",t.module),t.result.message,t.result.errors),melisCoreTool.highlightErrors(t.result.success,t.result.errors,e.prop("id")))}):(melisHelper.melisKoNotification(translations.tr_melis_market_place_setup_title.replace("%s",t.module),t.result.message,t.result.errors),melisCoreTool.highlightErrors(t.result.success,t.result.errors,e.prop("id")))),
melisCoreTool.done("#melis-marketplace-setup-modal-submit")})}}),$("body").on("click","button.melis-marketplace-product-action",function(){var r=$(this).data().action,a=$(this).data().package,d=$(this).data().module,p="id_melis_market_place_tool_package_modal_content",u="melis_market_place_tool_package_modal_content",m="/melis/MelisMarketPlace/MelisMarketPlace/toolProductModalContainer",k={action:r,package:a,module:d};if(melisCoreTool.pending("button"),"remove"===r){var v=[],g=[];s("POST","/melis/MelisMarketPlace/MelisMarketPlace/getModuleTables",{module:d},function(e){v=e.tables,g=e.files}),s("POST","/melis/MelisCore/Modules/getDependents",{module:d,tables:v,files:g},function(e){var t="<br/><br/><div class='container'><div class='row'><div class='col-lg-12'><ul>%s</ul></div></div></div>",s="";$.each(e.modules,function(e,t){s+="<li>"+t+"</li>"}),t=t.replace("%s",s),e.success&&melisCoreTool.closeDialog(translations.tr_meliscore_delete_module_header,translations.melis_market_place_tool_package_remove_no_no_msg_1.replace("%s",d)+t+"<br/>"+translations.melis_market_place_tool_package_remove_no_no_msg_2),""===s&&melisCoreTool.confirm(translations.tr_meliscore_common_yes,translations.tr_meliscore_tool_emails_mngt_generic_from_header_cancel,translations.tr_meliscore_delete_module_header,translations.melis_market_place_tool_package_remove_confirm.replace("%s",d),function(){melisHelper.createModal(p,u,!0,k,m,function(){melisCoreTool.done("button"),i(d,function(){l(k,function(){o(d,v,g)})})})}),melisCoreTool.done("button"),$("div[data-module-name]").bootstrapSwitch("setActive",!0),$("h4#meliscore-tool-module-content-title").html(translations.tr_meliscore_module_management_modules)})}else{var y=translations.tr_market_place_modal_download_title,w=translations.tr_market_place_modal_download_content.replace("%s",d);"update"===r&&(y=translations.tr_market_place_modal_update_title,w=translations.tr_market_place_modal_update_content.replace("%s",d)),melisCoreTool.confirm(translations.tr_meliscore_common_yes,translations.tr_meliscore_tool_emails_mngt_generic_from_header_cancel,y,w,function(){(function(){return new Promise(function(t,i){melisHelper.createModal(p,u,!1,k,m,function(){melisCoreTool.done("button"),l(k,function(){n("/melis/MelisMarketPlace/MelisMarketPlace/isModuleExists",{module:d}).then(function(o){o.data.isExist||!0===o.data.isExist?e(d,o,r,t,i):c('<span style="color: #ff190d;">'+translations.tr_meliscore_error_message+"</span>")})})})})})().then(function(e){var i=e.module;return n("/melis/MelisMarketPlace/MelisMarketPlace/plugModule",{module:i}).then(function(t){if(!0===t.data.success)return e;throw new Error(translations.tr_melis_market_place_plug_module_ko.replace("%s",i))}).then(function(e){if(void 0===e||null==typeof e)return melisHelper.melisKoNotification("Melis Marketplace",translations.tr_melis_marketplace_setup_error),Promise.reject("Melis Marketplace",translations.tr_melis_marketplace_setup_error);f("span_c_scripts_setup",translations.tr_melis_core_composer_scrpts_executing),setTimeout(function(){$.get("/melis/MelisMarketPlace/MelisMarketPlace/executeComposerScripts").done(function(o){c(o),h("span_c_scripts_setup",translations.tr_melis_core_composer_scrpts_executed);var s=!1;f("span_get_setup",translations.tr_melis_marketplace_check_addtl_setup),setTimeout(function(){n("/melis/MelisMarketPlace/MelisMarketPlace/getSetupModuleForm",{action:e.action,module:e.module}).then(function(t){return h("span_get_setup",translations.tr_melis_marketplace_check_addtl_setup_ok),""!==t.data.form&&null!==t.data.form&&(s=!0),Object.assign(e,{hasSetupForm:s})}).then(function(e){if(void 0===e||null==typeof e)return melisHelper.melisKoNotification("Melis Marketplace",translations.tr_melis_marketplace_setup_error),Promise.reject("Melis Marketplace",translations.tr_melis_marketplace_setup_error);if(e.hasSetupForm){var o=!0;melisCoreTool.confirm(translations.tr_meliscore_common_yes,translations.tr_melis_marketplace_common_no_skip,translations.tr_melis_market_place_setup_title.replace("%s",e.module),translations.tr_melis_market_place_has_setup_form.replace("%s",e.module),function(){o=!1,melisHelper.createModal("id_melis_market_place_module_setup_form_content_ajax","melis_market_place_module_setup_form_content",!1,e,m,function(){melisCoreTool.done("button")})},function(){t(i,e)})}else t(i,e);return Object.assign(e,{skip:o})}).catch(function(e){c('<span style="color: #ff190d;">'+translations.tr_melis_marketplace_check_addtl_setup_ko+"</span>")})},5e3)})},5e3)}),e}).catch(function(e){console.log(e)})}),melisCoreTool.done("button")}}),$("body").on("click","button.melis-marketplace-modal-activate-module",function(){s("POST","/melis/MelisMarketPlace/MelisMarketPlace/activateModule",{module:$(this).data().module},function(){$.get("/melis",function(){setTimeout(function(){$("button.melis-marketplace-modal-reload").trigger("click")},1e3)})})}),$("body").on("click","button.melis-marketplace-modal-reload",function(){melisCoreTool.processing(),location.reload(!0)}),$("body").on("click",".melis-market-place-pagination",function(){$("#melis-market-place-package-list").append('<div class="melis-overlay"></div>');var e=$(this).data("goto-page"),t=getActiveGroupIdFilter();fetchPackages(e,null,null,null,null,t)}),$("body").on("keypress","input#melis_market_place_search_input",function(e){13===e.which&&$("body").find("button#btnMarketPlaceSearch").trigger("click")}),$("body").on("click","button#btnMarketPlaceSearch",function(){$(".product-list-view").append('<div class="melis-overlay"></div>');var e=$("body").find("input#melis_market_place_search_input").val(),t=getActiveGroupIdFilter();fetchPackages(null,e,null,null,null,t)}),$("body").on("submit","form#melis_market_place_search_form",function(e){e.preventDefault()}),$("body").on("click",".melis-market-place-view-details",function(){var e=$(this).data().packageid,t=$(this).data().packagetitle;melisHelper.disableAllTabs(),melisHelper.tabOpen(t,"fa-shopping-cart",e+"_id_melis_market_place_tool_package_display","melis_market_place_tool_package_display",{packageId:e},"id_melis_market_place_tool_display",function(){}),melisHelper.enableAllTabs()}),$("body").on("click","#btnMinus",d),$("body").on("click","#btnPlus",a),$("body").on("hide.bs.modal","#id_melis_market_place_tool_package_modal_content_container, #id_melis_market_place_module_setup_form_content_ajax_container",function(e){!0===m&&e.preventDefault()}),$("body").on("click","#melis-marketplace-product-modal-hide",function(){m=!1,$("#id_melis_market_place_tool_package_modal_content_container").modal("hide"),m=!0})}),$(document).ready(function(){function e(){$(".slider-dashboard-downloaded-packages").slick({slidesToShow:1,slidesToScroll:1,autoplay:!0,autoplaySpeed:2e3,arrows:!0,adaptiveHeight:!0,dots:!0})}e(),$("body").on("click",".dashboard-downloaded-packages",function(){melisHelper.zoneReload("id_market_place_most_downloaded_modules","market_place_most_downloaded_modules",{},function(){e()})}),$("body").on("click","#link-to-marketplace",function(){melisHelper.tabOpen(translations.tr_market_place,"fa-shopping-cart","id_melis_market_place_tool_display","melis_market_place_tool_display",{},null,null)}),$("body").on("click",".market-place-btn-filter-group .btn",function(){$(".product-list-view").append('<div class="melis-overlay"></div>');$(this).hasClass("active");$(this).toggleClass("active");var e=getActiveGroupIdFilter(),t=$("body").find("input#melis_market_place_search_input").val();fetchPackages(null,t,null,null,null,e)}),$("body").on("click","#outdated-module-link",function(){var e=$(this).data().packageid,t=$(this).data().packagetitle;melisHelper.disableAllTabs(),melisHelper.tabOpen(t,"fa-shopping-cart",e+"_id_melis_market_place_tool_package_display","melis_market_place_tool_package_display",{packageId:e},"id_melis_market_place_tool_display",function(){}),melisHelper.enableAllTabs()})});
>>>>>>> develop
