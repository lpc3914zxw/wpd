
var countdown = 
{
	status:'1',
	start_time:'2016-06-10 16:00',
	msg:'直播倒计时'
}

function get_unix_time(dateStr){
		    var newstr = dateStr.replace(/-/g,'/'); 
		    var date =  new Date(newstr); 
		    var time_str = date.getTime().toString();
		    return time_str.substr(0, 10);
}
function get_now_unix_time()
{
	var date = new Date();
	var time_str = date.getTime().toString();
	return time_str.substr(0,10);
}
if(countdown && countdown.status=='1' && countdown.start_time){
            var ts = get_unix_time(countdown.start_time);
			var nowTimestamp = get_now_unix_time();
            if(ts>nowTimestamp){
            	$('#countdown-box').countdown({
	                timestamp   : ts*1000,
	                title       : countdown.msg?countdown.msg:act_name
	            });		
                 $("#countdown-box").height($(".surface-container").height());

            }
        }