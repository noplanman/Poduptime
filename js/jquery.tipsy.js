(function(a){a.fn.tipsy=function(b){b=a.extend({},a.fn.tipsy.defaults,b);return this.each(function(){var c=a.fn.tipsy.elementOptions(this,b);a(this).hover(function(){a.data(this,"cancel.tipsy",true);var b=a.data(this,"active.tipsy");if(!b){b=a('<div class="tipsy"><div class="tipsy-inner"/></div>');b.css({position:"absolute",zIndex:1e5});a.data(this,"active.tipsy",b)}if(a(this).attr("title")||typeof a(this).attr("original-title")!="string"){a(this).attr("original-title",a(this).attr("title")||"").removeAttr("title")}var d;if(typeof c.title=="string"){d=a(this).attr(c.title=="title"?"original-title":c.title)}else if(typeof c.title=="function"){d=c.title.call(this)}b.find(".tipsy-inner")[c.html?"html":"text"](d||c.fallback);var e=a.extend({},a(this).offset(),{width:this.offsetWidth,height:this.offsetHeight});b.get(0).className="tipsy";b.remove().css({top:0,left:0,visibility:"hidden",display:"block"}).appendTo(document.body);var f=b[0].offsetWidth,g=b[0].offsetHeight;var h=typeof c.gravity=="function"?c.gravity.call(this):c.gravity;switch(h.charAt(0)){case"n":b.css({top:e.top+e.height,left:e.left+e.width/2-f/2}).addClass("tipsy-north");break;case"s":b.css({top:e.top-g,left:e.left+e.width/2-f/2}).addClass("tipsy-south");break;case"e":b.css({top:e.top+e.height/2-g/2,left:e.left-f}).addClass("tipsy-east");break;case"w":b.css({top:e.top+e.height/2-g/2,left:e.left+e.width}).addClass("tipsy-west");break}if(c.fade){b.css({opacity:0,display:"block",visibility:"visible"}).animate({opacity:.8})}else{b.css({visibility:"visible"})}},function(){a.data(this,"cancel.tipsy",false);var b=this;setTimeout(function(){if(a.data(this,"cancel.tipsy"))return;var d=a.data(b,"active.tipsy");if(c.fade){d.stop().fadeOut(function(){a(this).remove()})}else{d.remove()}},100)})})};a.fn.tipsy.elementOptions=function(b,c){return a.metadata?a.extend({},c,a(b).metadata()):c};a.fn.tipsy.defaults={fade:false,fallback:"",gravity:"n",html:false,title:"title"};a.fn.tipsy.autoNS=function(){return a(this).offset().top>a(document).scrollTop()+a(window).height()/2?"s":"n"};a.fn.tipsy.autoWE=function(){return a(this).offset().left>a(document).scrollLeft()+a(window).width()/2?"e":"w"}})(jQuery)

