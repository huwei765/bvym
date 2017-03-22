<?php
namespace Home\Service;

class WeixinService extends CommonService {

    /**
     * 微信公众平台入口
     */
    public function index(){
        if ($_GET['echostr'] != NULL ) {
            echo $_GET['echostr'];
            exit;
        }
        //微信只会在第一次在URL中带echostr参数，以后就不会带这个参数了
        if ($this->checkSignature()) { //success!
            $postStr = isset($GLOBALS["HTTP_RAW_POST_DATA"]) && !empty($GLOBALS["HTTP_RAW_POST_DATA"]) ? $GLOBALS["HTTP_RAW_POST_DATA"] : "";
            //extract post data
            if (!empty($postStr)) {
                libxml_disable_entity_loader(true);
                $postObj = simplexml_load_string($postStr,"SimpleXMLElement",LIBXML_NOCDATA);
                //根据消息类型将信息分发
                $this->route($postObj);
            }
        } else {
            echo "error";
        }
    }

    public function route($postObj) {
        $msgType = trim($postObj->MsgType);
        switch ($msgType) {
            //(1)接受的为消息推送
            case "text":
                $this->reponse_text($postObj);
                break;
            case "image":
                $this->reponse_image($postObj);
                break;
            case "voice":
                $this->reponse_voice($postObj);
                break;
            //(2)接受的为事件推送
            case "event":
                $event = $postObj->Event;
                switch ($event) {
                    case "subscribe"://关注
                        $this->subscribe($postObj);
                        break;
                    case "unsubscribe"://取消关注
                        $this->unsubscribe($postObj);
                        break;
                    //自定义菜单的事件功能
                }
        }
    }

    /**
     * 微信开发者身份验证
     * @return bool
     * @throws Exception
     */
    public function checkSignature() {
        $token = C("WX_PUBLIC.user_token");
        if (empty($token)) {
            throw new Exception("TOKEN is not defined!");
        }
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce     = $_GET["nonce"];
        $token = TOKEN;
        $tmpArr = array($token,$timestamp,$nonce);
        sort($tmpArr,SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);
        if ($tmpStr == $signature) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 关注推事件
     * @param $postObj
     */
    public function subscribe($postObj) {
        //根据openId获取用户信息
        $open_id = $postObj->FromUserName;
        $wxUserData = $this->getRemoteWxUserInfoByOpenId($open_id);
        if(empty($wxUserData)){
            exit();
        }
        //注册用户信息
        D("wxuser","Logic")->registerFromWxUser($wxUserData);
    }

    public function sign_in($data){
        if(empty($data)){
            return array('status' => 0,'data' => '未能获取微信用户信息！');
        }
    }

    /**
     * 获取微信公众平台的用户信息
     * @param $openId
     * @return mixed
     */
    public function getRemoteWxUserInfoByOpenId($openId){
        //获取access_token
        $token=$this->getAccessToken();
        $getUserUrl = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$token.'&openid='.$openId."&lang=zh_CN";
        return curl_request($getUserUrl);
    }

    /**
     * 获取访问令牌
     * @return string
     */
    public function getAccessToken()
    {
        $data = json_decode(file_get_contents(__ROOT__."/m/access_token.json"));
        if (empty($data) || $data->expire_time < time()) {
            $AppId = C("WX_PUBLIC.appId");
            $AppSecret = C("WX_PUBLIC.appSecret");
            $gw_url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$AppId.'&secret='.$AppSecret;
            $result = $this->curl_request($gw_url);

            if (isset($result->access_token)) {
                $access_token = $result->access_token;
                $data->expire_time = time() + 7000;
                $data->access_token = $access_token;
                $fp = fopen(__ROOT__."/m/access_token.json", "w");
                fwrite($fp, json_encode($data));
                fclose($fp);
            }
        }
        $access_token = $data->access_token ? $data->access_token : "";
        return $access_token;
    }

    /**
     * curl请求
     * @param $url
     * @return mixed
     */
    public function curl_request($url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //要求保存的结果到字符串还是输出在屏幕上，非0表示保存到字符串中
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); //对认证来源的检查，0表示阻止对证书的合法性检查
        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result,true);
    }
	
	
    public function encrypt($data) {
        //return md5(C('AUTH_MASK') . md5($data));
		return md5(md5($data));
    }
}
