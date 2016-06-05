<?php
/**
 * 奥点云框架扩展类微信api
 *
 * @author phenixsoul(329580886@qq.com)
 * @link http://www.aodianyun.com
 * @copyright 2008-2015 aodiansoft
 */

class LWxjssdk {
  private $storge;
  private $appId;
  private $appSecret;
  private $jsApiTicketKey;
  private $accessTokenKey;


  public function __construct($appId,$appSecret) {
    $this->storge = CModel::modelHandle('JSON_FILE');
    $this->appId = $appId;
    $this->appSecret = $appSecret;
    $this->jsApiTicketKey = md5('jsApiTicket'.$this->appId.$this->appSecret);
    $this->accessTokenKey = md5('accessToken'.$this->appId.$this->appSecret);
  }

  public function getSignPackage() {
    $jsapiTicket = $this->getJsApiTicket();
    if($jsapiTicket['Flag'] != 100){
      return $jsapiTicket;
    }
    $jsapiTicket = $jsapiTicket['Info'];

    // 注意 URL 一定要动态获取，不能 hardcode.
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

    $timestamp = time();
    $nonceStr = $this->createNonceStr();

    // 这里参数的顺序要按照 key 值 ASCII 码升序排序
    $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

    $signature = sha1($string);

    $signPackage = array(
      "appId"     => $this->appId,
      "nonceStr"  => $nonceStr,
      "timestamp" => $timestamp,
      "url"       => $url,
      "signature" => $signature,
      "rawString" => $string
    );
    return $signPackage; 
  }

  private function createNonceStr($length = 16) {
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $str = "";
    for ($i = 0; $i < $length; $i++) {
      $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
    }
    return $str;
  }

  private function getJsApiTicket() {
    // jsapi_ticket 应该全局存储与更新，以下代码以写入到文件中做示例
    $ticket = $this->storge->get($this->jsApiTicketKey);
    if (empty($ticket)) {
      $accessToken = $this->getAccessToken();
      if($accessToken['Flag'] != 100){
        return $accessToken;
      }
      $accessToken = $accessToken['Info'];
      // 如果是企业号用以下 URL 获取 ticket
      // $url = "https://qyapi.weixin.qq.com/cgi-bin/get_jsapi_ticket?access_token=$accessToken";
      $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";
      $res = json_decode($this->httpGet($url));
      $ticket = $res->ticket;
      if (!empty($ticket)) {
        if(!$this->storge->set($this->jsApiTicketKey,$ticket,7000)){
            return array('Flag'=>102,'FlagString'=>'Storage Error');
        }
      }
      else{
        return array('Flag'=>101,'FlagString'=>'Weixin Error');
      }
    }
    return array('Flag'=>100,'FlagString'=>'success','Info'=>$ticket);
  }

  public function getUserInfo($code){
    $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$this->appId&secret=$this->appSecret&code=$code&grant_type=authorization_code";
    $res = $this->httpGet($url);
    $res = json_decode($res,true);
    if(!empty($res['errcode'])){
      return array('Flag'=>101,'FlagString'=>$res);
    }
    $openid = $res['openid'];
    $access_token = $res['access_token'];
    $url = "https://api.weixin.qq.com/sns/userinfo?access_token=$access_token&openid=$openid&lang=zh_CN";
    $res = $this->httpGet($url);
    $res = json_decode($res,true);
    if(!empty($res['errcode'])){
      $url = "https://api.weixin.qq.com/sns/userinfo?access_token=$access_token&openid=$openid&lang=zh_CN";
      $res = $this->httpGet($url);
      $res = json_decode($res,true);
      if(!empty($res['errcode'])){
        return array('Flag'=>101,'FlagString'=>$res);
      }
    }
    return array('Flag'=>100,'FlagString'=>'success','Info'=>$res);
  }

  private function getAccessToken() {
    // access_token 应该全局存储与更新，以下代码以写入到文件中做示例
    $access_token = $this->storge->get($this->accessTokenKey);
    if (empty($access_token)) {
      // 如果是企业号用以下URL获取access_token
      // $url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=$this->appId&corpsecret=$this->appSecret";
      $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$this->appId&secret=$this->appSecret";
      $res = json_decode($this->httpGet($url));
      $access_token = $res->access_token;
      if (!empty($access_token)) {
        if(!$this->storge->set($this->accessTokenKey,$access_token,7000)){
            return array('Flag'=>102,'FlagString'=>'Storage Error');
        }
      }
      else{
        return array('Flag'=>101,'FlagString'=>'Weixin Error');
      }
    }
    return array('Flag'=>100,'FlagString'=>'success','Info'=>$access_token);
  }

  private function httpGet($url) {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_TIMEOUT, 500);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_URL, $url);

    $res = curl_exec($curl);
    curl_close($curl);

    return $res;
  }
}

