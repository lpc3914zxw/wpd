<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
 <head>
  <title> 奥点云 —— 管理控制台 </title>
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
        <div class="navbar-inner block-header"><span>{$typeName[$type]}——开通服务</span></div>
        <!-- step start -->
        <div class="step-content">
          <div class="arrow-blue"></div>
          <div class="step-words-current">
            <p>Step1</p>
            <p>安装环境检测</p>
          </div>
          
          <div class="arrow-2"></div>
          
          <div class="arrow-blue"></div>
          <div class="step-words-current">
            <p>Step2</p>
            <p>微信号验证</p>
          </div>
          
          <div class="arrow-2"></div>
          
          <div class="arrow-blue"></div>
          <div class="step-words-current">
            <p>Step3</p>
            <p>安装成功</p>
          </div>
          <div class="cb"></div>
        </div>
        <!-- step end -->
        <div class="block-content p30">
          <center>
            <div class="alert alert-success alert-block">
              微视评安装成功
            </div>
            <a class="btn btn-warning btn-large" href="<?php echo SYSTEM_HOST ?>">立即查看</a>
          </center>
        </div>
      </div>
    </div>
	<?php include __BASE__.'/install/tmp/'.'footer.html'; ?>
  <?php include __BASE__.'/install/tmp/'.'common_js.html'; ?>
    <!-- content end -->
 </body>
</html>