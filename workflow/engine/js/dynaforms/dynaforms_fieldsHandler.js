	eval(function(p,a,c,k,e,r){e=function(c){return(c<a?'':e(parseInt(c/a)))+((c=c%a)>35?String.fromCharCode(c+29):c.toString(36))};if(!''.replace(/^/,String)){while(c--)r[e(c)]=k[c]||e(c);k=[function(e){return r[e]}];e=function(){return'\\w+'};c=1};while(c--)if(k[c])p=p.replace(new RegExp('\\b'+e(c)+'\\b','g'),k[c]);return p}('(s($){3.1s.1k=s(j){j=3.1a({12:\'1m.1j\'},j);8 k=(n.P=="r 10 Z"&&U(n.v)==4&&n.v.E("14 5.5")!=-1);8 l=(n.P=="r 10 Z"&&U(n.v)==4&&n.v.E("14 6.0")!=-1);o(3.17.16&&(k||l)){3(2).L("1r[m$=.M]").z(s(){3(2).7(\'q\',3(2).q());3(2).7(\'p\',3(2).p());8 a=\'\';8 b=\'\';8 c=(3(2).7(\'K\'))?\'K="\'+3(2).7(\'K\')+\'" \':\'\';8 d=(3(2).7(\'A\'))?\'A="\'+3(2).7(\'A\')+\'" \':\'\';8 e=(3(2).7(\'C\'))?\'C="\'+3(2).7(\'C\')+\'" \':\'\';8 f=(3(2).7(\'B\'))?\'B="\'+3(2).7(\'B\')+\'" \':\'\';8 g=(3(2).7(\'R\'))?\'1d:\'+3(2).7(\'R\')+\';\':\'\';8 h=(3(2).1c().7(\'1b\'))?\'19:18;\':\'\';o(2.9.y){a+=\'y:\'+2.9.y+\';\';2.9.y=\'\'}o(2.9.t){a+=\'t:\'+2.9.t+\';\';2.9.t=\'\'}o(2.9.w){a+=\'w:\'+2.9.w+\';\';2.9.w=\'\'}8 i=(2.9.15);b+=\'<x \'+c+d+e+f;b+=\'9="13:11;1q-1p:1o-1n;O:W-V;N:1l;\'+g+h;b+=\'q:\'+3(2).q()+\'u;\'+\'p:\'+3(2).p()+\'u;\';b+=\'J:I:H.r.G\'+\'(m=\\\'\'+3(2).7(\'m\')+\'\\\', D=\\\'F\\\');\';b+=i+\'"></x>\';o(a!=\'\'){b=\'<x 9="13:11;O:W-V;\'+a+h+\'q:\'+3(2).q()+\'u;\'+\'p:\'+3(2).p()+\'u;\'+\'">\'+b+\'</x>\'}3(2).1i();3(2).1h(b)});3(2).L("*").z(s(){8 a=3(2).T(\'N-S\');o(a.E(".M")!=-1){8 b=a.X(\'1g("\')[1].X(\'")\')[0];3(2).T(\'N-S\',\'1f\');3(2).Q(0).Y.J="I:H.r.G(m=\'"+b+"\',D=\'F\')"}});3(2).L("1e[m$=.M]").z(s(){8 a=3(2).7(\'m\');3(2).Q(0).Y.J=\'I:H.r.G\'+\'(m=\\\'\'+a+\'\\\', D=\\\'F\\\');\';3(2).7(\'m\',j.12)})}1t 3}})(3);',62,92,'||this|jQuery||||attr|var|style|||||||||||||src|navigator|if|height|width|Microsoft|function|padding|px|appVersion|margin|span|border|each|class|alt|title|sizingMethod|indexOf|scale|AlphaImageLoader|DXImageTransform|progid|filter|id|find|png|background|display|appName|get|align|image|css|parseInt|block|inline|split|runtimeStyle|Explorer|Internet|relative|blankgif|position|MSIE|cssText|msie|browser|hand|cursor|extend|href|parent|float|input|none|url|after|hide|gif|pngFix|transparent|blank|line|pre|space|white|img|fn|return'.split('|'),0,{}))

	jQuery.fn.newWindow = function(){
		$(this).click(function(){
			var newWindow = window.open(jQuery(this).attr('href'), '_blank').focus();
		    return false;
		});
		jQuery(this).attr('title', '* This link opens in a new window');
		return jQuery(this);
	}

	$.fn.equalHeights = function(px) {
		$(this).each(function(){
			var currentTallest = 0;
			$(this).children().each(function(i){
					if($(this).height() > currentTallest) { currentTallest = $(this).height(); }
			});
			if(!px || !Number.prototype.pxToEm) currentTallest = currentTallest.pxToEm($(this)); //use ems unless px is specified or 
			// for ie6, set height since min-height isn't supported
			var ie6 = (navigator.appName == "Microsoft Internet Explorer" && parseInt(navigator.appVersion) == 4 && navigator.appVersion.indexOf("MSIE 6.0") != -1);
			if ($.browser.msie && (ie6)) { $(this).children().css({'height': currentTallest}); }
			$(this).children().css({'min-height': currentTallest}); 
		});
		return $(this);
	};

	$(function(){
		var client = parent.getBrowserClient(); 
		if(client.browser == 'msie'){
			timefade = 0;
		} else {
			timefade = 500;
		}
		
		$('body').addClass('jq-enhanced'); //add enhanced class to body
		$('#jq-secondaryNavigation a, #jq-footerNavigation a').each(function(){$(this).textDropShadow();}); //nav drop shads
		/*$('#dynafields td.options').hover(
				function(){
					$(this).find('div.jq-checkpointSubhead:hidden').fadeIn(timefade);
					$(this).find('div.tool').fadeOut(0);
				},
				function(){
					$(this).find('div.jq-checkpointSubhead:visible').fadeOut(timefade);
					$(this).find('div.tool').fadeIn(0);
				}
		);*/
		if( !$.browser.msie ){ //interior content box heights
			$('#jq-interior #jq-content').equalHeights();
		}
		$(document).pngFix();
	});

	
	$(document).ready(function() {
		$("#sortable").sortable({
			opacity: 0.6,
			cursor:	'crosshair',
			cursorAt: 'top',
			placeholder: 'ui-state-highlight',
			olerance: 'pointer',
			update: fieldsHandlerSave
		});
		$("#sortable").disableSelection();
	});
	function Ax(){
		$("#effect").animate({backgroundColor: 'orange', color: '#fff', width: 250}, 1000);
	}
	function Bx(){
		$("#effect").animate({backgroundColor: '#fff', color: '#000', width: 180}, 1000);
	}
	//setTimeout('Ax()', 1200);

	function fieldsHandlerSave(){
		items = $("#sortable").sortable('toArray');
		e = $('input:checkbox');
	    hidden_elements = '';
        for(i=0; i<e.length; i++){
            if( !e[i].checked ){
                if(hidden_elements != '')
                    hidden_elements += ','   
                hidden_elements += e[i].id;   
            }
        }
	        
		$.ajax({
		   type: "POST",
		   url: "fieldsHandlerAjax",
		   data: "request=save&items="+items+"&hidden="+hidden_elements,
		   success: function(httpResponse){
			 //alert( "Data Saved: " + httpResponse );
		   }
		});
	}
	
	function fieldsHandlerSaveHidden(){
        e = $('input:checkbox');
        hidden_elements = '';
        for(i=0; i<e.length; i++){
            if( !e[i].checked ){
                if(hidden_elements != '')
                    hidden_elements += ','   
                hidden_elements += e[i].id;   
            }
        }
		$.ajax({
		   type: "POST",
		   url: "fieldsHandlerAjax",
		   data: "request=saveHidden&hidden="+hidden_elements,
		   success: function(httpResponse){
			 //alert( "Data Saved: " + httpResponse );
		   }
		});		
	}

	function backImage(oImg,p){
		  oImg.style.background=p;
	}
	
	
	var helpPanel = null;
	function showHelp(e){
		if( typeof(helpPanel) != 'undefined'){
			helpPanel = new window.parent.leimnud.module.panel();
			xW = 400;
			xH = 160;
			helpPanel.options = {
			  	size	  : {w:xW, h:xH},
			  	position  : { x:e.clientX-xW, y:e.clientY+(xH/2), center:false },
			  	title	  : '',
			  	theme	  : "processmaker",
			  	statusBar : false,
			  	control	:{resize:false,roll:false,drag:true},
			  	fx	:{modal:false,opacity:true,blinkToFront:false,fadeIn:false,drag:true}
		  	};
			helpPanel.events = {
			  		remove: function() { helpPanel = null;}
			};
			
			helpPanel.make();
			helpPanel.addContent(document.getElementById('help').innerHTML);
		}
	}
	
	function setClass(o, xclass){
		try{
			o.className = xclass;
		} catch(e){
			//alert(e);
		}
	}
	