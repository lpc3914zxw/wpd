/**
 * 
 * @authors Your Name (you@example.org)
 * @date    2016-01-29 11:40:33
 * @version $Id$
 */
(function($){
	
	// Number of seconds in every time division
	var days	= 24*60*60,
		hours	= 60*60,
		minutes	= 60;
	
	// Creating the plugin
	$.fn.countdown = function(prop){
		
		var options = $.extend({
			callback	: function(){},
			timestamp	: 0,
			title		:'倒计时'
		},prop);
		
		var left, d, h, m, s, positions, positionsday;

		// Initialize the plugin
		init(this, options);
		
		positions = this.find('.position');
		positionsday = this.find('.position-day');
		
		(function tick(){
			
			// Time left
			left = Math.floor((options.timestamp - (new Date())) / 1000);
			
			if(left < 0){
				left = 0;
				$('.countdownHolder').fadeOut();
			}else{
				// Number of days left
				d = Math.floor(left / days);
				updateDay(0,1,d);
				left -= d*days;
				
				// Number of hours left
				h = Math.floor(left / hours);
				updateDuo(0, 1, h);
				left -= h*hours;
				
				// Number of minutes left
				m = Math.floor(left / minutes);
				updateDuo(2, 3, m);
				left -= m*minutes;
				
				s = left;
				updateDuo(4, 5, s);
				
				options.callback(d, h, m, s);

				setTimeout(tick, 1000);
			}
		})();
		
		function  updateDay(minor,major,value){
			var day0=Math.floor(value/10)%10,day1=value%10;
			if(day0==0 && day1==0){
				$('.countTitle').html(options.title);
			}else{
				positionsday.eq(minor).html(day0);
				positionsday.eq(major).html(day1);
			}
		}
		function updateDuo(minor,major,value){

			switchDigit(positions.eq(minor),Math.floor(value/10)%10);
			switchDigit(positions.eq(major),value%10);
		}
		
		return this;
	};


	function init(elem, options){
		elem.addClass('countdownHolder');
		var h = elem.width()*9/16;
		elem.height(h);
		var countDays = '<span class="countDays">\
							<span class="position-day">0</span>\
							<span class="position-day">0</span>\
						</span>';

		elem.append('<div class="countTitle">'+options.title+countDays+'<span class="countDay">天</span></div>');

		var elemWrap = $('<div class="countWrap"></div>');
		elemWrap.appendTo(elem);
		var elemBox = $('<div class="countBox"></div>');
		elemBox.appendTo(elemWrap);
		
		$.each(['Hours','Minutes','Seconds'],function(i){
			var span = $('<span class="count'+this+'"><span class="position"><span class="digit static">0</span></span><span class="position"><span class="digit static">0</span></span></span>');
			span.appendTo(elemBox);
			
			if(this!="Seconds"){
				elemBox.append('<span class="countDiv countDiv'+i+'"></span>');
			}
		});

	}

	function switchDigit(position,number){
		
		var digit = position.find('.digit')
		
		if(digit.is(':animated')){
			return false;
		}
		
		if(position.data('digit') == number){
			return false;
		}
		
		position.data('digit', number);
		
		var replacement = $('<span>',{
			'class':'digit',
			css:{
				top:'-2.1em',
				opacity:0
			},
			html:number
		});


		digit
			.before(replacement)
			.removeClass('static')
			.animate({top:'2.5em',opacity:0},'fast',function(){
				digit.remove();
			})

		replacement
			.delay(100)
			.animate({top:0,opacity:1},'fast',function(){
				replacement.addClass('static');
			});
	}
})(jQuery);


/*****************
 ** WEBPACK FOOTER
 ** ./js/lib/countdown.js
 ** module id = 208
 ** module chunks = 0
 **/