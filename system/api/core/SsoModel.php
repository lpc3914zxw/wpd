<?php
class SsoModel extends CModel{

    private $token = 'WPD_USER_LOGIN_TOKEN';
    private $wspToken = 'WSP_USER_LOGIN_TOKEN';

    public function apiGetLogin(){
        $userInfo = empty($_COOKIE[$this->token]) ? '' : json_decode(base64_decode($this->decrypt($_COOKIE[$this->token])),true);
        if(empty($userInfo) || empty($userInfo['adminUin']) || empty($userInfo['adminWxUid']) || empty($userInfo['adminWxOpenId']) || empty($userInfo['adminWxNick']) || empty($userInfo['key']) || empty($userInfo['openid']) || empty($userInfo['unionid']) || empty($userInfo['nick'])){
            return array('Flag'=>101,'FlagString'=>'您还未登录');
        }

        $channelId = empty($this->param['channelId']) ? '' : $this->param['channelId'];
        $wspKey = $userInfo['key'];
        $openid = $userInfo['openid'];
        $unionid = $userInfo['unionid'];
        $wxnick = $userInfo['nick'];

        $param = array('wxUid'=>$unionid,'wxOpenId'=>$openid,'wxNick'=>$wxnick,'wspKey'=>$wspKey);
        $channelList = $this->base->api('wsp/wspService/loginChannel',$param);
        if($channelList['Flag'] != 100 || empty($channelList['Info']['list'])){
            return array('Flag'=>102,'FlagString'=>'频道查询错误');
        }
        $channelList = $channelList['Info']['list'];

        $channelInfo = array();
        if(empty($channelId)){
            $channelInfo =$channelList[0];
        }
        else{
            foreach($channelList as $val){
                if($val['id'] == $channelId){
                    $channelInfo = $val;
                    break;
                }
            }
        }
        if(empty($channelInfo)){
            return array('Flag'=>102,'FlagString'=>'服务查询错误');
        }
        $channelInfo['title'] = urldecode($channelInfo['title']);

        $param = array('secKey'=>$wspKey);
        $serviceInfo = $this->base->api('wsp/wspService/getServiceBySecKey',$param);
        if(empty($serviceInfo['Info'])){
            return array('Flag'=>103,'FlagString'=>'服务查询错误');
        }
        $serviceInfo = $serviceInfo['Info'];

        $userInfo['channelInfo'] = $channelInfo;
        $userInfo['serviceInfo'] = $serviceInfo;
        $userInfo['isAdmin'] = $userInfo['adminWxUid'] == $userInfo['unionid'] ? true : false;
        $userInfo['Flag'] = 100;
        $userInfo['FlagString'] = '登陆成功';
        return $userInfo;
    }

    public function apiSetLogin(){
        if(empty($this->param['adminUin']) || empty($this->param['adminWxUid']) || empty($this->param['adminWxOpenId']) || empty($this->param['adminWxNick']) || empty($this->param['key']) || empty($this->param['openid']) || empty($this->param['unionid']) || empty($this->param['nick']) || !isset($this->param['ava'])){
            return array('Flag'=>101,'FlagString'=>'参数错误');
        }

        $wspKey = $this->param['key'];
        $openid = $this->param['openid'];
        $unionid = $this->param['unionid'];
        $nick = $this->param['nick'];
        $ava = empty($this->param['ava']) ? '' : $this->param['ava'];

        $param = array('wxUid'=>$unionid,'wxOpenId'=>$openid,'wxNick'=>$nick,'wspKey'=>$wspKey);
        $channelList = $this->base->api('wsp/wspService/loginChannel',$param);
        if($channelList['Flag'] != 100 || empty($channelList['Info']['list'])){
            return array('Flag'=>102,'FlagString'=>'创建频道错误');
        }
        $channelList = $channelList['Info']['list'];

        $userInfo = $this->param;
        $userInfo = $this->encrypt(base64_encode(json_encode($userInfo)));

        setcookie($this->token,$userInfo,0,'/',CDOMAIN);
        return array('Flag'=>100,'FlagString'=>'登陆成功');
    }

    public function apiLoginOut(){
        setcookie($this->token,'',time()-1000,'/',CDOMAIN);
        return array('Flag'=>100,'FlagString'=>'退出成功');
    }

    public function apiGetWspLogin(){
        $userInfo = empty($_COOKIE[$this->wspToken]) ? '' : json_decode(base64_decode($this->decrypt($_COOKIE[$this->wspToken])),true);
        if(empty($userInfo['openid']) || empty($userInfo['nick'])){
            return array('Flag'=>101,'FlagString'=>'您还未登录');
        }
        $userInfo['Flag'] = 100;
        $userInfo['FlagString'] = '登陆成功';
        return $userInfo;
    }

    public function apiSetWspLogin(){
        if(empty($this->param['openid']) || empty($this->param['nick'])){
            return array('Flag'=>101,'FlagString'=>'参数错误');
        }

        $userInfo = $this->param;
        $userInfo = $this->encrypt(base64_encode(json_encode($userInfo)));
        setcookie($this->wspToken,$userInfo,0,'/',CDOMAIN);
        return array('Flag'=>100,'FlagString'=>'登陆成功');
    }
}