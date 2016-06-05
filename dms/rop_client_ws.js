
var ROP = function(){
	var ICS_ADDR = "mqtt.dms.aodianyun.com";
	var ROP_FLASH_SITE = 'http://cdn.aodianyun.com/dms/';
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
			try{
				if(arr[i])arr[i](arg1,arg2);
			}catch(err){
				if (window.console){
					window.console.log("catch err at "+evt+" callback",err)
				}
			}
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
	    function InternalSubscribe ( topic,qos ){
	    	if (state_ == STATE_ENTERED ){
	    		if(isNaN(qos)) qos = 0;
				mqttClient_.subscribe(topic,{qos:qos});
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
				console.log(clientid_)
				clientid_ = "ws-"+Paho.MQTT.NewGuid()
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
						InternalSubscribe(topic_list_[k].topic,topic_list_[k].qos)
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
		//window.addEventListener("unload", Leave,false);
	    function Leave(){
			state_ = STATE_INIT;
			clearTimeout(re_enter_timeout_);
			try{
				if(mqttClient_)
					mqttClient_.disconnect()
			}catch(err){

			}
	    }
    	function LoadWs ( callback ){
			var s = document.createElement('script');
			s.type = 'text/javascript';
			s.async = false;
			s.src = 'http://cdn.aodianyun.com/dms/ws.js';
			s.charset = 'UTF-8';
			(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(s);
			var wait = 100
			var timerrr = setInterval(function(){							
				if (typeof Paho !="undefined") {
					clearInterval(timerrr);
					callback();
				}
				wait--
				if(wait <=0){
					clearInterval(timerrr);
					callback("load ws fail")
				}
			},10);
		}
		return {
			Enter:function( pubKey,subKey,clientid ){
				if(state_ == STATE_INIT){
					state_ = STATE_ENTERING;
					pubKey_ = pubKey;
					subKey_ = subKey;
					if (subKey_ == null) {
						subKey_ = pubKey
					}
					if(clientid != null){
						clientid_ = clientid;
					}
					if(typeof Paho == "undefined"){
						LoadWs(function(err){
							if(err != null){
								eventEmit.Emit("enter_fail",err)
								return
							}
							InternalEnter();
						});
					}else{
						InternalEnter();
					}
				}
			},
			Leave:Leave,
			On:function(evt,func){
				eventEmit.On(evt,func)
			},
			Publish:function( body ,topic ,qos,retain){
				if (state_ == STATE_ENTERED ){
					var message = new Paho.MQTT.Message( body);
					message.destinationName = topic;
					if(isNaN(Number(qos))){
						message.qos = 0;
					}else{
						message.qos = Number(qos);
					}
					message.retained = Boolean(retain);
					mqttClient_.send(message)
				}
			},
			Subscribe:function( topic,qos ){
				topic = topic.toString();
				qos = Number(qos);
				if(qos == null) qos = 0;
				if(isNaN(qos)) qos = 0;
				if(qos > 2) qos = 2;
				if(qos < 0) qos = 0;
				if(!topic || topic.length == 0)return;
				for(k in topic_list_){
					if(topic_list_[k].topic == topic){
						return;
					}
				}
				topic_list_.push({topic:topic,qos:qos});
				InternalSubscribe(topic,qos);
			},
			UnSubscribe:function( topic ){
				topic = topic.toString();
				if(!topic || topic.length == 0)return;
				for(k in topic_list_){
					if(topic_list_[k].topic == topic){
						topic_list_.splice(k,1);
						InternalUnSubscribe(topic);
						return
					}
				}
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




