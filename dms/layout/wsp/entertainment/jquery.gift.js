// 礼物生成插件
(function($){  
	$.fn.aodianGIFT = function(options){
		var defaults = {
			id : 'aodianyun-dms-giftbox',
			path : 'face/',
			assign : 'content',
			tip : 'em_'
		};
		var option = $.extend(defaults, options);
		var assign = $('#'+option.assign);
		var id = option.id;
		var path = option.path;
		var tip = option.tip;
		var isfirstload=true;
		var strFace;
		if(assign.length<=0){
			alert('缺少表情赋值对象。');
			return false;
		}
		var getGifthtml=function(self,gifts){
			
			console.log(gifts,id);
			var strFacegiftid;
			if($('#'+id).length<=0){

				strFace = '<div id="'+id+'" style="background: #fff;position:absolute;display:none;z-index:99999;" class="aodianyun-dms-giftbox">'; 
	   			strFace +='<div class="swiper-container">';
	      		strFace +='<div class="swiper-wrapper">';
		       
		         var numPage = Math.ceil(gifts.length/15);
		         for(var k=0;k<numPage;k++){ 
		         	strFace +='<div class="swiper-slide">'
		         			+'<table border="0" cellspacing="0" >';
		         	for(var i=0,j=1; i<15; i++,j++){
		         		var curIndex = k*15+i;
		         		if(curIndex==gifts.length)
		         		break;	         		
		         		var curGift= gifts[curIndex];	         		
						strFace += '<td><div class="giftbox"><img id="'+curGift.giftid+'" src="'+curGift.gfiturl+'"/><span class="giftname" >'+curGift.giftname+'</span><span class="price">'+curGift.price+'</span></div></td>';
						if( j % 5== 0 ) strFace += '</tr><tr>';
					}
					strFace += '</tr></table></div>';
		         }
					
	       		strFace += '</div></div><div class="gift-pagination" ></div>';
	       		strFace += '<div class="gift_balance"><em>余额</em>：<span id="count-money">256843</span>';
	       		strFace += '<span class="btn btn-white recharge"> 充值></span></div>';
	       		strFace += '<div class="cf"></div>';
	       		strFace += '<div class="gift-num"><em>数量</em>：<span class="gift-input-box"><input type="number" value="1"  id="gift-input-text" class="gift-input-text" onkeyup="this.value=this.value.replace(/\D/g,"")" onafterpaste="this.value=this.value.replace(/\D/g,"")"><a href="#" id="giftShapeBtns" onclick="$(\'#'+option.assign+'\').setGiftNum();"class="to-select"></a></span>';
	       		strFace += '<span class="btn btn-red send" onclick="$(\'#'+option.assign+'\').sendGift();">赠  送</span></div></div>';
			}
			
			showGifts(self);
		}
		var showGifts=function(self){
			self.parent().parent().parent().parent().append(strFace);
			var fbwidth=$(window).width();
			var fbheight=$(window).height()/2;
			fbheight='100%';
			var tablecp=parseInt((fbwidth-42*5)/14);
			$(".swiper-container").css({"height":fbheight,"width":fbwidth});
			$(".swiper-slide table").attr("cellpadding",tablecp);
			var mySwiper = new Swiper('.swiper-container',{
			    pagination: '.gift-pagination',
			    loop:true,
			    grabCursor: true,
			    paginationClickable: true
				});			

			var offset = self.position();
			var top = 0;
			$('#'+id).css('bottom',top);
			$('#'+id).show();
		}
		$(this).click(function(e){
			if(document.getElementById('facebox')){
				$('#facebox').hide();
				$('#facebox').remove();						
			}
			var self=$(this);
			if(isfirstload){
				dmsConfig.getGiftList(function(data){
						getGifthtml(self,data);
						isfirstload=false;
					});
			
			}
			else{showGifts(self);}
			e.stopPropagation();	
		});
		<!--显示选择礼物时框住的div-->
	    $(".giftbox").live("click", 
	            function(n) {

	               var i = jQuery(this);
	                                                                   
	                if (n.type == "click") {                                     
	                    try {
	                      $('.selected').removeClass('selected');
	                        
	                    } catch(n) {}
	                    i.addClass('selected');
	                    return false;                       
	                   
	                } 
	                
	    });		
	    <!--点击空白隐藏礼物-->
		$(function(){
			    $(document).bind("click",function(e){
			        var target  = $(e.target);
			        if(target.closest('#'+id).length == 0){
			            	$('#'+id).hide();
						$('#'+id).remove();
			        }
			    })   
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
	sendGift:function(){
		
		var giftId=$('.selected').children('img').attr("id");
		if ( $("#"+giftId).length <= 0 ) {
			alert('请选择一个礼物再赠送');
			return;
		} 
		var giftnum=$('#gift-input-text').val();
		if(giftnum<=0){
			alert('请选择礼物数量');
			return;
		}
		var count=$('#count-money').text();
		var giftprice=$('.selected').children('.price').text();
		var presentcount=count-giftprice*giftnum;
		if(presentcount<0){
			alert('余额不足，请充值后再送礼物');
			return;
		}
		dmsConfig.sendGift(giftId,giftnum,function(data){
				$('#count-money').text(presentcount);
				console.log(data)
			});
		
		//console.log(giftId,giftnum);
	},
	setGiftNum:function(){
		<!--礼物数量-->           
	
      var a=$("#gift-input-text");
      var sum=a.val();
      ++sum;
      a.attr("value",sum);
          
	}
});

