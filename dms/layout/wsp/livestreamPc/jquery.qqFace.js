// QQ表情插件
(function($){  
	$.fn.qqFace = function(options){
		var defaults = {
			id : 'facebox',
			path : 'face/',
			assign : 'content',
			tip : 'em_'
		};
		var option = $.extend(defaults, options);
		var assign = $('#'+option.assign);
		var id = option.id;
		var path = option.path;
		var tip = option.tip;
		
		if(assign.length<=0){
			alert('缺少表情赋值对象。');
			return false;
		}
		
		$(this).click(
			function(e){
				if(document.getElementById(id)){
					$('#'+id).hide();
					$('#'+id).remove();
					return;
				}
				var strFace, labFace;
				if($('#'+id).length<=0){
					strFace = '<div id="'+id+'" style="position:absolute;display:none;z-index:1000;background:#fff;border:1px solid #B2B3B3" class="qqFace">' +
								  '<table border="0" style="border-collapse: separate;border-spacing: 5px 5px;cursor: pointer;"><tr>';
					for(var i=1; i<=75; i++){
						labFace = '['+tip+i+']';
						strFace += '<td><img src="'+path+i+'.gif" onclick="$(\'#'+option.assign+'\').setCaret();$(\'#'+option.assign+'\').insertAtCaret(\'' + labFace + '\');" /></td>';
						if( i % 15 == 0 ) strFace += '</tr><tr>';
					}
					strFace += '</tr></table></div>';
				}
				$(this).parent().parent().append(strFace);
				var offset = $(this).position();
				var top = offset.top + $(this).outerHeight();
				$('#'+id).css('bottom',top);
				$('#'+id).css('left',offset.left);
				$('#'+id).show();
				e.stopPropagation();
			}
		);

		$(document).click(function(){
			$('#'+id).hide();
			$('#'+id).remove();
		});
	};

})(jQuery);

jQuery.extend({ 
unselectContents: function(){ 
	if(window.getSelection) 
		window.getSelection().removeAllRanges(); 
	else if(document.selection) 
		document.selection.empty(); 
	} 
}); 
jQuery.fn.extend({ 
	selectContents: function(){ 
		$(this).each(function(i){ 
			var node = this; 
			var selection, range, doc, win; 
			if ((doc = node.ownerDocument) && (win = doc.defaultView) && typeof win.getSelection != 'undefined' && typeof doc.createRange != 'undefined' && (selection = window.getSelection()) && typeof selection.removeAllRanges != 'undefined'){ 
				range = doc.createRange(); 
				range.selectNode(node); 
				if(i == 0){ 
					selection.removeAllRanges(); 
				} 
				selection.addRange(range); 
			} else if (document.body && typeof document.body.createTextRange != 'undefined' && (range = document.body.createTextRange())){ 
				range.moveToElementText(node); 
				range.select(); 
			} 
		}); 
	}, 

	setCaret: function(){ 
		if(!$.browser.msie) return; 
		var initSetCaret = function(){ 
			var textObj = $(this).get(0); 
			textObj.caretPos = document.selection.createRange().duplicate(); 
		}; 
		$(this).click(initSetCaret).select(initSetCaret).keyup(initSetCaret); 
	}, 

	insertAtCaret: function(textFeildValue){ 
		var textObj = $(this).get(0);
		var textFeildValue=dmsFaceArr[textFeildValue]; 
		if(document.all && textObj.createTextRange && textObj.caretPos){ 
			var caretPos=textObj.caretPos; 
			caretPos.text = caretPos.text.charAt(caretPos.text.length-1) == '' ? 
			textFeildValue+'' : textFeildValue; 
		} else if(textObj.setSelectionRange){ 
			var rangeStart=textObj.selectionStart; 
			var rangeEnd=textObj.selectionEnd; 
			var tempStr1=textObj.value.substring(0,rangeStart); 
			var tempStr2=textObj.value.substring(rangeEnd); 
			textObj.value=tempStr1+textFeildValue+tempStr2; 
			textObj.focus(); 
			var len=textFeildValue.length; 
			textObj.setSelectionRange(rangeStart+len,rangeStart+len); 
			textObj.blur(); 
		}else{ 
			textObj.value+=textFeildValue; 
		} 
	} 
});


var dmsFaceArr = {
	'[em_1]':'[微笑]',
	'[em_2]':'[撇嘴]',
	'[em_3]':'[色]',
	'[em_4]':'[发呆]',
	'[em_5]':'[流泪]',
	'[em_6]':'[害羞]',
	'[em_7]':'[闭嘴]',
	'[em_8]':'[睡]',
	'[em_9]':'[大哭]',
	'[em_10]':'[尴尬]',
	'[em_11]':'[发怒]',
	'[em_12]':'[调皮]',
	'[em_13]':'[呲牙]',
	'[em_14]':'[惊讶]',
	'[em_15]':'[难过]',
	'[em_16]':'[冷汗]',
	'[em_17]':'[抓狂]',
	'[em_18]':'[吐]',
	'[em_19]':'[偷笑]',
	'[em_20]':'[愉快]',
	'[em_21]':'[白眼]',
	'[em_22]':'[傲慢]',
	'[em_23]':'[饥饿]',
	'[em_24]':'[困]',
	'[em_25]':'[惊恐]',
	'[em_26]':'[流汗]',
	'[em_27]':'[憨笑]',
	'[em_28]':'[悠闲]',
	'[em_29]':'[奋斗]',
	'[em_30]':'[咒骂]',
	'[em_31]':'[疑问]',
	'[em_32]':'[嘘]',
	'[em_33]':'[晕]',
	'[em_34]':'[抓狂]',
	'[em_35]':'[衰]',
	'[em_36]':'[敲打]',
	'[em_37]':'[再见]',
	'[em_38]':'[流汗]',
	'[em_39]':'[抠鼻]',
	'[em_40]':'[出糗]',
	'[em_41]':'[坏笑]',
	'[em_42]':'[左哼哼]',
	'[em_43]':'[右哼哼]',
	'[em_44]':'[哈欠]',	
	'[em_45]':'[鄙视]',
	'[em_46]':'[委屈]',
	'[em_47]':'[快哭了]',
	'[em_48]':'[阴险]',
	'[em_49]':'[亲亲]',
	'[em_50]':'[吓]',
	'[em_51]':'[可怜]',
	'[em_52]':'[抱抱]',
	'[em_53]':'[月亮]',
	'[em_54]':'[太阳]',
	'[em_55]':'[炸弹]',
	'[em_56]':'[骷髅]',
	'[em_57]':'[刀]',
	'[em_58]':'[猪头]',
	'[em_59]':'[西瓜]',
	'[em_60]':'[咖啡]',
	'[em_61]':'[米饭]',
	'[em_62]':'[爱心]',
	'[em_63]':'[棒]',
	'[em_64]':'[弱]',
	'[em_65]':'[握手]',
	'[em_66]':'[胜利]',
	'[em_67]':'[抱拳]',
	'[em_68]':'[勾引]',
	'[em_69]':'[好的]',
	'[em_70]':'[不]',
	'[em_71]':'[玫瑰]',
	'[em_72]':'[凋谢]',
	'[em_73]':'[嘴唇]',
	'[em_74]':'[相爱]',
	'[em_75]':'[飞吻]'
};
var dmsFaceArr2 = {
	'微笑':'em_1',
	'撇嘴':'em_2',
	'色':'em_3',
	'发呆':'em_4',
	'流泪':'em_5',
	'害羞':'em_6',
	'闭嘴':'em_7',
	'睡':'em_8',
	'大哭':'em_9',
	'尴尬':'em_10',
	'发怒':'em_11',
	'调皮':'em_12',
	'呲牙':'em_13',
	'惊讶':'em_14',
	'难过':'em_15',
	'冷汗':'em_16',
	'抓狂':'em_17',
	'吐':'em_18',
	'偷笑':'em_19',
	'愉快':'em_20',
	'白眼':'em_21',
	'傲慢':'em_22',
	'饥饿':'em_23',
	'困':'em_24',
	'惊恐':'em_25',
	'流汗':'em_26',
	'憨笑':'em_27',
	'悠闲':'em_28',
	'奋斗':'em_29',
	'咒骂':'em_30',
	'疑问':'em_31',
	'嘘':'em_32',
	'晕':'em_33',
	'抓狂':'em_34',
	'衰':'em_35',
	'敲打':'em_36',
	'再见':'em_37',
	'流汗':'em_38',
	'抠鼻':'em_39',
	'出糗':'em_40',
	'坏笑':'em_41',
	'左哼哼':'em_42',
	'右哼哼':'em_43',
	'哈欠':'em_44',
	'鄙视':'em_45',
	'委屈':'em_46',
	'快哭了':'em_47',
	'阴险':'em_48',
	'亲亲':'em_49',
	'吓':'em_50',
	'可怜':'em_51',
	'抱抱':'em_52',
	'月亮':'em_53',
	'太阳':'em_54',
	'炸弹':'em_55',
	'骷髅':'em_56',
	'刀':'em_57',
	'猪头':'em_58',
	'西瓜':'em_59',
	'咖啡':'em_60',
	'米饭':'em_61',
	'爱心':'em_62',
	'棒':'em_63',
	'弱':'em_64',    	
	'握手':'em_65',
	'胜利':'em_66',
	'抱拳':'em_67',
	'勾引':'em_68',
	'好的':'em_69',
	'不':'em_70',
	'玫瑰':'em_71',
	'凋谢':'em_72',
	'嘴唇':'em_73',
	'相爱':'em_74',
	'飞吻':'em_75'
};	

