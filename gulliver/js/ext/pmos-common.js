PMExtJSCommon = function() {
  this.version = '1.8';
   
  this.confirm = function(title, msg, fnYes, fnNo)
  {
    Ext.MessageBox.confirm(title, msg, function(btn, text){
      if ( btn == 'yes' )
        setTimeout(fnYes, 0)
      else if( fnNo != undefined )
        setTimeout(fnNo, 0)
    });      
  }
  
  this.info = function(title, msg, fn) {
    Ext.MessageBox.show({
      title: title,
      msg: msg,
      buttons: Ext.MessageBox.OK,
      animEl: 'mb9',
      fn: fn != undefined ? fn: function(){},
      icon: Ext.MessageBox.INFO
    });
  }

  this.question = function(title, msg, fn) {
    Ext.MessageBox.show({
      title: title,
      msg: msg,
      buttons: Ext.MessageBox.OK,
      animEl: 'mb9',
      fn: fn != undefined ? fn: function(){},
      icon: Ext.MessageBox.QUESTION
    });
  }

  this.warning = function(title, msg, fn) {
    Ext.MessageBox.show({
      title: title,
      msg: msg,
      buttons: Ext.MessageBox.OK,
      animEl: 'mb9',
      fn: fn != undefined ? fn: function(){},
      icon: Ext.MessageBox.WARNING
    });
  }

  this.error = function(title, msg, fn) {
    Ext.MessageBox.show({
      title: title,
      msg: msg,
      buttons: Ext.MessageBox.OK,
      animEl: 'mb9',
      fn: fn != undefined ? fn: function(){},
      icon: Ext.MessageBox.ERROR
    });
  }
  
  this.notify = function(title, msg)
  {
    Ext.msgBoxSlider.msg(title, msg);
  }
} 
var PMExt = new PMExtJSCommon();


/**
 * Common Ext Functions
 */

Ext.msgBoxSlider = function(){
  var msgCt;

  function createBox(t, s){
    return [
      '<div class="msg">',
      '<div class="x-box-tl"><div class="x-box-tr"><div class="x-box-tc"></div></div></div>',
      '<div class="x-box-ml"><div class="x-box-mr"><div id="x-box-mc-inner" class="x-box-mc"><h3>', t, '</h3>', s, '</div></div></div>',
      '<div class="x-box-bl"><div class="x-box-br"><div class="x-box-bc"></div></div></div>',
      '</div>'
    ].join('');
  }
  return {
    msg : function(title, format) {
      if( ! msgCt ) {
          msgCt = Ext.DomHelper.insertFirst(document.body, {id:'msg-div', style:'position:absolute'}, true);
      }
      msgCt.alignTo(document, 't-t');
      var s = String.format.apply(String, Array.prototype.slice.call(arguments, 1));
      var m = Ext.DomHelper.append(msgCt, {html:createBox(title, s)}, true);
      m.setWidth(400 );
      m.position(null, 5000 );
      m.alignTo(document, 't-t');
      Ext.get('x-box-mc-inner' ).setStyle('background-image', 'url("<?php echo _EXT_URL ?>/images/_accept.png")');
      Ext.get('x-box-mc-inner' ).setStyle('background-position', '5px 10px');
      Ext.get('x-box-mc-inner' ).setStyle('background-repeat', 'no-repeat');
      Ext.get('x-box-mc-inner' ).setStyle('padding-left', '35px');
      m.slideIn('t').pause(3).ghost("t", {remove:true});
    }
  };
}();

/** 
 * Translator function for internationalization
 */
function _(ID_LABEL)
{
  if( typeof(TRANSLATIONS) != 'undefined' ) {
    if( typeof(TRANSLATIONS[ID_LABEL]) != 'undefined' ) {
      trn = TRANSLATIONS[ID_LABEL];
    } else {
      trn = '**' + ID_LABEL + '**';
    }
  } else {
    PMExt.error('Processmaker JS Core Error', 'The TRANSLATIONS global object is not loaded!');
    trn = '';
  }
  return trn;
}

var getBrowserInf = function(){
  var aBrowFull = new Array("opera", "msie", "firefox", "opera", "safari");
  var sInfo = navigator.userAgent.toLowerCase();
  var sBrowser = "";
  var screen;
  
  var wSize = [0, 0];
  if (typeof window.innerWidth != 'undefined'){
    wSize = [
      window.innerWidth,
      window.innerHeight
    ];
  } else if (typeof document.documentElement != 'undefined' && typeof document.documentElement.clientWidth != 'undefined' && document.documentElement.clientWidth != 0){
    wSize = [
      document.documentElement.clientWidth,
      document.documentElement.clientHeight
    ];
  } else {
    wSize = [
      document.getElementsByTagName('body')[0].clientWidth,
      document.getElementsByTagName('body')[0].clientHeight
    ];
  }
  
  screen = {width:wSize[0], height:wSize[1]};
  
  for (var i = 0; i < aBrowFull.length; i++){
    if ((sBrowser == "") && (sInfo.indexOf(aBrowFull[i]) != -1)){
      sBrowser = aBrowFull[i];
      sVersion = String(parseFloat(sInfo.substr(sInfo.indexOf(aBrowFull[i]) + aBrowFull[i].length + 1)));
      return {name:sBrowser, version:sVersion, screen: screen}
    }
  }
  
  return {name:'unknow', version:'', screen: screen}
};
var _BROWSER = getBrowserInf();

/** 
 * Environment Formats function for full name
 */
function _FNF(USER_NAME, FIRST_NAME, LAST_NAME, FN_FORMAT)
{
  if (FN_FORMAT===undefined)
	if (typeof FORMATS == 'undefined') 
	  FN_FORMAT = '(@lastName, @firstName) @userName';
	else
	  FN_FORMAT = FORMATS.FullNameFormat;
  var aux = FN_FORMAT;
  aux = aux.replace('@userName',USER_NAME);
  aux = aux.replace('@firstName',FIRST_NAME);
  aux = aux.replace('@lastName',LAST_NAME);
  return aux;
}

/** 
 * Environment Formats function for date
 */
function _DF(DATE_TIME, D_FORMAT){
	  function LZ(x){return(x<0||x>9?"":"0")+x;}
	  if (D_FORMAT===undefined) 
		if (typeof FORMATS == 'undefined')
		  D_FORMAT = 'm/d/Y';
		else
		  D_FORMAT = FORMATS.DateFormat;
	  if (DATE_TIME!=''){
		  var arrD = DATE_TIME.split(' ');
		  var arrF = arrD[0].split('-');
		  if (arrD.length ==2){
			  var arrH = arrD[1].split(':');
		  }else{
			  var arrH = new Array(0,0,0);
		  }
		  
		  
	    var MONTH_NAMES=new Array(_('ID_MONTH_1'),_('ID_MONTH_2'),_('ID_MONTH_3'),_('ID_MONTH_4'),_('ID_MONTH_5'),_('ID_MONTH_6'),_('ID_MONTH_7'),
	      _('ID_MONTH_8'),_('ID_MONTH_9'),_('ID_MONTH_10'),_('ID_MONTH_11'),_('ID_MONTH_12'),_('ID_MONTH_ABB_1'),_('ID_MONTH_ABB_2'),
	      _('ID_MONTH_ABB_3'),_('ID_MONTH_ABB_4'),_('ID_MONTH_ABB_5'),_('ID_MONTH_ABB_6'),_('ID_MONTH_ABB_7'),_('ID_MONTH_ABB_8'),
	      _('ID_MONTH_ABB_9'),_('ID_MONTH_ABB_10'),_('ID_MONTH_ABB_11'),_('ID_MONTH_ABB_12'));
		var DAY_NAMES=new Array(_('ID_WEEKDAY_0'),_('ID_WEEKDAY_1'),_('ID_WEEKDAY_2'),_('ID_WEEKDAY_3'),_('ID_WEEKDAY_4'),_('ID_WEEKDAY_5'),
		  _('ID_WEEKDAY_6'),_('ID_WEEKDAY_ABB_0'),_('ID_WEEKDAY_ABB_1'),_('ID_WEEKDAY_ABB_2'),_('ID_WEEKDAY_ABB_3'),_('ID_WEEKDAY_ABB_4'),
		  _('ID_WEEKDAY_ABB_5'),_('ID_WEEKDAY_ABB_6'));

		var date = new Date(arrF[0],parseInt(arrF[1])-1,arrF[2],arrH[0],arrH[1],arrH[2],0);
	    var y=date.getFullYear()+'';
		var M=date.getMonth()+1;
		var d=date.getDate();
		var E=date.getDay();
		var H=date.getHours();
		var m=date.getMinutes();
		var s=date.getSeconds();
		
		var values = new Object();
		values['Y'] = y;
		values['y'] = y.substring(2, 4);
		values['F'] = MONTH_NAMES[M-1];
		values['M'] = MONTH_NAMES[M+11];
		values['m'] = LZ(M);
		values['n'] = M;
		values['d'] = LZ(d);
		values['j'] = d;
		values['D'] = DAY_NAMES[E+7];
		values['l'] = DAY_NAMES[E];
		values['G'] = H;
		values['H'] = LZ(H);
		if (H==0){ values['g'] = 12;}
		else if (H>12){	values['g'] = H-12; }
		else { values['g'] = H; }
		values['h'] = LZ(values['g']);
		values['i'] = LZ(m);
		values['s'] = LZ(s);
		if (H>11) values['a'] = 'pm'; else values['a'] = 'am';
		if (H>11) values['A'] = 'PM'; else values['A'] = 'AM';
		if (typeof FORMATS == 'undefined') values['T'] = '**';
		else values['T'] = FORMATS.TimeZone;
		
		var aDate = D_FORMAT.split('');
		var aux = '';
		
		var xParts = new Array('Y','y','F','M','m','n','d','j','D','l','G','H','g','h','i','s','a','A','T');
		for (var i=0; i < aDate.length; i++){
		   if (xParts.indexOf(aDate[i])==-1){
			   aux = aux + aDate[i]; 
		   }else{
			   aux = aux + values[aDate[i]];
		   }
		}
		return aux;
	  }else{
		if (DATE_TIME=='')
		  return '';
		else
		  return '**' + DATE_TIME + '**';  
	  }
	}