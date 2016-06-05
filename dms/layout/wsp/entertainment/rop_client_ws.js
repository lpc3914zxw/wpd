
var ROP = function(){
	var ICS_ADDR = "mqtt.dms.aodianyun.com";//"service.ics.aodianyun.com:8365";

	function EventEmit(){
		this.callback_map_ = {}
	}
	EventEmit.prototype.On = function(evt,func){
		if(typeof func != "function" || typeof evt != "string"){
			throw new Error("error arguments ");
		}
		arr = this.callback_map_[evt]
		if(arr == null){
			arr = this.callback_map_[evt] = new Array()
		}
		arr.push(func)
	}
	EventEmit.prototype.Emit = function (evt,arg1,arg2){
		var arr = this.callback_map_[evt];
		if (arr == null) return;
		for(var i in arr){
			if(arr[i])arr[i](arg1,arg2);
		}
	}

	var eventEmit = new EventEmit()
	var topic_list_ = [];

	var WebSocketRop = function() {
		var pubKey_ = null;
		var subKey_ = null
		var mqttClient_ = null
		
		var timers = 0;
		
		var STATE_INIT = 0;
		var STATE_ENTERING = 4;
		var STATE_ENTERED = 5;
		var STATE_ENTER_FAILED = 6;
		var STATE_REENTERING = 7;
		var state_ = STATE_INIT;
		
		var reenter_max_ = 5000
		var reenter_df_ = 100
		var re_enter_timeout_ = reenter_df_

		var clientid_ = null
	    function ReEnter(){
			if (state_ == STATE_ENTERED || state_ == STATE_REENTERING) {
				state_ = STATE_REENTERING;
				setTimeout(InternalEnter,re_enter_timeout_);
				re_enter_timeout_+=reenter_df_
				if (re_enter_timeout_ > reenter_max_) {
					re_enter_timeout_ = reenter_max_
				}
			}
	    }
	    function InternalSubscribe ( topic ){
	    	if (state_ == STATE_ENTERED ){
				mqttClient_.subscribe(topic,{qos:1});
			}
	    }
	    function InternalUnSubscribe ( topic ){
			if (state_ == STATE_ENTERED) {
				mqttClient_.unsubscribe(topic);
			}	
	    }
	    function InternalEnter(){
	    	if(state_ == STATE_REENTERING){
	    		eventEmit.Emit("reconnect")
	    	}
			if(clientid_ == null){
				clientid_ = Paho.MQTT.NewGuid()
			}
	    	mqttClient_ = new Paho.MQTT.Client(ICS_ADDR, Number(8000),clientid_ );
            mqttClient_.onConnectionLost = function(responseObject){
            	 if (responseObject.errorCode !== 0){
            	 	eventEmit.Emit("offline",responseObject.errorMessage)
					ReEnter()
				}
			};
            mqttClient_.onMessageArrived = function(message){
				eventEmit.Emit("publish_data",message.payloadString,message.destinationName)
			}
            mqttClient_.connect({
				timeout:10, // connect timeout
				userName:pubKey_,
				password:subKey_,
				keepAliveInterval:60 , // keepalive 
				cleanSession:false , // 
				onSuccess:function(){
					state_ = STATE_ENTERED
					re_enter_timeout_ = reenter_df_
					for (var k in topic_list_){
						InternalSubscribe(topic_list_[k])
					}
					eventEmit.Emit("enter_suc")
				},
				onFailure:function(err){
					if( state_ == STATE_ENTERING ){
						state_ = STATE_ENTER_FAILED
						console.log(err)
						eventEmit.Emit("enter_fail",err.errorMessage)
						Leave()
					}else if (state_ == STATE_REENTERING){
						console.log(err)
						eventEmit.Emit("offline",err.errorMessage)
						ReEnter()
					}
				}
			});
	    }
		window.addEventListener("unload", Leave,false);
	    function Leave(){
			state_ = STATE_INIT;
			clearTimeout(re_enter_timeout_);
			try{
				mqttClient_.disconnect()
			}catch(err){

			}
	    }
		return {
			Enter:function( pubKey,subKey ){
				if(state_ == STATE_INIT){
					state_ = STATE_ENTERING;
					pubKey_ = pubKey;
					subKey_ = subKey;
					if (subKey_ == null) {
						subKey_ = pubKey
					}
					function LoadWs (){
						document.write("<script src=" + "http://cdn.aodianyun.com/dms/ws.js"  + "><\/script>");
					}
					if(typeof Paho == "undefined"){
						var wait = 100;
						var timerrr = setInterval(function(){							
							if (typeof Paho !="undefined") {
								clearInterval(timerrr);
								InternalEnter();
							}else{
								wait --;
								if (wait <=0) {
									LoadWs();
									wait = 100;
								};
							}
						},10);	

					}else
						InternalEnter();
				}
			},
			Leave:Leave,
			On:function(evt,func){
				eventEmit.On(evt,func)
			},
			Publish:function( body ,topic ,qos,retain){
				var message = new Paho.MQTT.Message( body);
				message.destinationName = topic;
				if(isNaN(Number(qos))){
					message.qos = 0;
				}else{
					message.qos = Number(qos);
				}
				message.retained = Boolean(retain);
				mqttClient_.send(message)
			},
			Subscribe:function( topic ){
				topic = topic.toString();
				if(!topic || topic.length == 0)return;
				if(topic_list_.indexOf(topic) != -1){
					return;
				}
				topic_list_.push(topic);
				InternalSubscribe(topic);
			},
			UnSubscribe:function( topic ){
				topic = topic.toString();
				if(!topic || topic.length == 0)return;
				var index = topic_list_.indexOf(topic);
				if(index == -1) return;
				topic_list_.splice(index,1);
				InternalUnSubscribe(topic);
			}
		}
	}
	if(window.WebSocket){
		var s = document.createElement('script');
		s.type = 'text/javascript';
		s.async = false;
		s.src = 'http://cdn.aodianyun.com/dms/ws.js';
		s.charset = 'UTF-8';
		(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(s);
		return WebSocketRop();
	}else{
		alert('not supprot websocket')
	}
}();




