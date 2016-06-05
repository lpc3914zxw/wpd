<?php
function checkPath($path){
	if(is_readable($path)){
		if(is_writable($path)){
		  return '<font color="green">可读、可写</font><input type="hidden" name="checkPath[]" value="1">';
		}
		else{
		  return '<font color="red">可读、不可写</font><input type="hidden" name="checkPath[]" value="0">';
		}
	}
	else{
		return '<font color="red">不可读，请设置0777权限</font><input type="hidden" name="checkPath[]" value="0">';
	}
}
function scan_file($path){
    $item=scandir($path);
    foreach($item as $k=>$v){
        if($v=='.' || $v=='..' || is_numeric(strpos($v,"."))){
          continue;
        }
        $v=$path.'/'.$v;
        echo '<p>模板路径'.$v.' - - - - - '.checkPath($v).'</p>';
        if(is_dir($v)){
            scan_file($v);
        }
    }
}
function checkRewrite($url){
	if(CModel::curl($url) == 'success'){
		return '<font color="green">已生效</font><input type="hidden" name="checkRewrite[]" value="1">';
	}
	else{
		 return '<font color="red">未生效，请阅读安装包中说明</font><input type="hidden" name="checkRewrite[]" value="0">';
	}
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
 <head>
  <title> 奥点云——微视评安装 </title>
  <meta charset="utf-8">
  <meta name="Keywords" content="">
  <meta name="Description" content="">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
<?php include __BASE__.'/install/tmp/'.'common_css.html'; ?>
 </head>
 <body>
    <!-- content start -->
    <div class="container">
      <div class="block">
        <div class="navbar-inner block-header"><span>微视评安装</span></div>
        <!-- step start -->
        <div class="step-content">
          <div class="arrow-blue"></div>
          <div class="step-words-current">
            <p>Step1</p>
            <p>安装环境检测</p>
          </div>
          
          <div class="arrow-2"></div>
          
          <div class="arrow-gary"></div>
          <div class="step-words">
            <p>Step2</p>
            <p>微信号验证</p>
          </div>
          
          <div class="arrow-2"></div>
          
          <div class="arrow-gary"></div>
          <div class="step-words">
            <p>Step3</p>
            <p>安装成功</p>
          </div>
          <div class="cb"></div>
        </div>
        <!-- step end -->
        <!-- from start -->
        <div class="block-content">
          <form class="form-horizontal" action="index.php?r=install/step2" onSubmit="return checkForm(this);" method="post">
            <div class="control-group">
              <label class="control-label">当前安装路径：</label>
              <div class="controls mt5">
              <p><?php echo SYSTEM_HOST; ?>（<span style="color:#ff6600;">注意：</span><span style="color:#666666;">该路径为程序检测路径，若不正确，请将system/Base.php文件中SYSTEM_HOST值改成您的域名+目录</span>）</p>
              </div>
            </div>
            <div class="control-group">
              <label class="control-label">curl组件：</label>
              <div class="controls mt5">
              <p>
              <?php
                if(function_exists('curl_init')){
              ?>
                <font color="green">已启用</font><input type="hidden" name="checkCurl" value="1">
              <?php
                }
                else{
              ?>
                <font color="red">未启用，请开启curl</font><input type="hidden" name="checkCurl" value="0">
              <?php
                }
              ?>
              </p>
              </div>
            </div>
            <div class="control-group">
              <label class="control-label">fsockopen组件：</label>
              <div class="controls mt5">
              <p>
              <?php
                if(function_exists('fsockopen')){
              ?>
                <font color="green">已启用</font><input type="hidden" name="checkFsockopen" value="1">
              <?php
                }
                else{
              ?>
                <font color="red">未启用，请开启fsockopen</font><input type="hidden" name="checkFsockopen" value="0">
              <?php
                }
              ?>
              </p>
              </div>
            </div>
          	<div class="control-group">
              <label class="control-label">配置文件权限：</label>
              <div class="controls mt5">
              <p>文件路径
              <?php
                $dir = __BASE__.'/system/config/system/base.php';echo $dir.' - - - - - '.checkPath($dir);
              ?>
              </p>
              </div>
            </div>
            <div class="control-group">
              <label class="control-label">模板目录权限：</label>
              <div class="controls mt5">
              <?php
                $dir = __BASE__.'/themes';scan_file($dir);
              ?>
              </div>
            </div>
            <div class="control-group">
              <label class="control-label">图片目录权限：</label>
              <div class="controls mt5">
              <p>文件路径
              <?php
                $dir = __BASE__.'/upload/pic';echo $dir.' - - - - - '.checkPath($dir);
              ?>
              </p>
              </div>
            </div>
            <div class="control-group">
              <label class="control-label">是否开启rewrite伪静态：</label>
              <div class="controls mt5">
              	<input type="radio" name="rewrite" value="1" onClick="$('#rewriteBox').show();">开启&nbsp;&nbsp;&nbsp;&nbsp;
              	<input type="radio" name="rewrite" value="0" onClick="$('#rewriteBox').hide();" checked>关闭
              </div>
            </div>
            <div class="control-group" id="rewriteBox" style="display:none;">
              <label class="control-label">重写规则：</label>
              <div class="controls mt5">
              	<p>频道列表：
				  <?php
                    $dir = SYSTEM_HOST.'/layout/livestream/1?install=1';
					echo $dir;
                  ?> - - - - - 
                  <?php echo checkRewrite($dir); ?>
                </p>
              	<p>活动详情：
				  <?php
                    $dir = SYSTEM_HOST.'/layout/party/1?install=1';
					echo $dir;
                  ?> - - - - - 
                  <?php echo checkRewrite($dir); ?>
                </p>
              </div>
            </div>
            <div class="control-group">
              <div class="controls">
                <button class="btn btn-warning btn-large" type="submit">下一步</button>
              </div>
            </div>
          </form>
        </div>
        <!-- from end -->
      </div>
    </div>
    <?php include __BASE__.'/install/tmp/'.'footer.html'; ?>
    <!-- content end -->
    <?php include __BASE__.'/install/tmp/'.'common_js.html'; ?>
	<script type="text/javascript">
	$(function(){
		var rewrite = $('input[name="rewrite"]:checked').val();
		if(rewrite == 0){
			$('#rewriteBox').hide();
		}
		else{
			$('#rewriteBox').show();
		}
	});
	function checkForm(form){
		if(form.checkCurl.value == '0'){
			alert('您的服务器不支持curl，请开启curl');
			return false;
		}
		if(form.checkFsockopen.value == '0'){
			alert('您的服务器不支持fsockopen，请开启fsockopen');
			return false;
		}
		var checkPath = document.getElementsByName('checkPath[]');
		for(var i=0;i<checkPath.length;i++){
			if(checkPath[i].value == '0'){
				alert('文件或目录权限验证失败，请设置权限！');
				return false;
			}
		}
		var rewrite = $('input[name="rewrite"]:checked').val();
		if(rewrite == 1){
			var checkRewrite = document.getElementsByName('checkRewrite[]');
			for(var i=0;i<checkRewrite.length;i++){
				if(checkRewrite[i].value == '0'){
					alert('重写规则验证失败，请阅读安装文档！');
					return false;
				}
			}
		}
		return true;
	}
    </script>
 </body>
</html>