<?php if (!class_exists('CTemplate')) die('Access Denied');?>
<div id="fg"></div>
<div id="footer"><?php if(empty($serviceInfo['copyRight'])) { ?>2006 - 2015 aodianyun.com, All Rights Reserved. 奥点科技 版权所有  增值电信业务经营许可证：浙B2-20110306  浙ICP备07500424号-7<?php } else { ?><?php echo $serviceInfo['copyRight'];?><?php } ?></div>
<script type="text/javascript">
function changePos(id){
	var obj = document.getElementById(id);
	var sHeight = document.documentElement.clientHeight;
	if(sHeight > 800){
		obj.style.position = 'fixed';
	}else{
    	obj.style.position = 'relative';
	}
}	
function changeDis(id){
	var obj = document.getElementById(id);
	var sHeight = window.screen.height;
	if(sHeight > 800){
		obj.style.display="block";
	}else{
    	obj.style.display="none";
	}
}
window.onload = function(){
	if($.browser.msie && ($.browser.version == "7.0")){
		return;
	}
	changePos('footer');
	changeDis('footer');
	window.onresize=function(){
		changePos('footer');
	}
}
</script>
<!----------百度统计代码------------>
<script type="text/javascript" src="http://cdn.aodianyun.com/static/analytics/bdcount.js"></script>
<!----------cnzz统计代码------------>
<div style="display:none"><script type="text/javascript" src="http://cdn.aodianyun.com/static/analytics/cnzztj.js"></script></div>