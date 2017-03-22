<?php
namespace Com;
// 微信public
class Wechat {

    const MSGTYPE_TEXT = 'text';//文字消息
    const MSGTYPE_IMAGE = 'image';
    const MSGTYPE_LOCATION = 'location';
    const MSGTYPE_LINK = 'link';
    const MSGTYPE_EVENT = 'event';
    const MSGTYPE_MUSIC = 'music';
    const MSGTYPE_NEWS = 'news';
    const MSGTYPE_VOICE = 'voice';
    const MSGTYPE_VIDEO = 'video';

    const Get_Token_Url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential";
    const API_URL_PREFIX = 'https://api.weixin.qq.com/cgi-bin';
    const AUTH_URL = '/token?grant_type=client_credential';
    const QRCODE_CREATE_URL='/qrcode/create?';
    const QRCODE_IMG_URL='https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=';
    const USER_INFO_URL='/user/info?';
    const TEMPLATE_SEND_URL = '/message/template/send?';

    private $appId;
    private $appSecret;
    private $token;//开发者定义的token
    private $access_token;//公众平台的token
    private $encrypt_type;//加密类型
    private $encodingAesKey;

    private $postxml;
    private $_msg;
    private $_receive;
    private $_text_filter = true;

    public $debug =  false;
    public $errCode = 40001;
    public $errMsg = "no access";
    public $logcallback;

    public function __construct($options)
    {
        $this->token = isset($options['token'])?$options['token']:'';
        $this->encodingAesKey = isset($options['encodingaeskey'])?$options['encodingaeskey']:'';
        $this->appId = isset($options['appid'])?$options['appid']:'';
        $this->appSecret = isset($options['appsecret'])?$options['appsecret']:'';
        $this->debug = isset($options['debug'])?$options['debug']:false;
        $this->logcallback = isset($options['logcallback'])?$options['logcallback']:false;
    }

    /**
     * 获取微信用户信息
     * @param $openid
     * @param string $lang
     * @return bool|mixed
     */
    public function getUserInfo($openid, $lang = 'zh_CN'){
        if (!$this->access_token && !$this->checkAuth()) return false;
        $result = $this->http_get(self::API_URL_PREFIX.self::USER_INFO_URL.'access_token='.$this->access_token.'&openid='.$openid.'&lang='.$lang);
        if ($result)
        {
            $json = json_decode($result,true);
            if (isset($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return $json;
        }
        return false;
    }

    /**
     * 回复微信服务器, 此函数支持链式操作
     * Example: $this->text('msg tips')->reply();
     * @param array $msg 要发送的信息, 默认取$this->_msg
     * @param bool|false $return 是否返回信息而不抛出到浏览器 默认:否
     * @return bool|string
     */
    public function reply($msg=array(),$return = false)
    {
        if (empty($msg)) {
            if (empty($this->_msg))   //防止不先设置回复内容，直接调用reply方法导致异常
                return false;
            $msg = $this->_msg;
        }
        $xmldata=  $this->xml_encode($msg);
        $this->log($xmldata);
        if ($this->encrypt_type == 'aes') { //如果来源消息为加密方式
            //Todo:加密方式暂不考虑
        }
        if ($return)
            return $xmldata;
        else
            echo $xmldata;
    }

    /**
     * 获取微信服务器发来的消息
     * @return $this
     */
    public function getRev()
    {
        if ($this->_receive) return $this;
        $postStr = !empty($this->postxml)?$this->postxml:file_get_contents("php://input");
        //兼顾使用明文又不想调用valid()方法的情况
        $this->log($postStr);
        if (!empty($postStr)) {
            $this->_receive = (array)simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
        }
        return $this;
    }

    /**
     * 获取微信发来的消息
     * @return mixed
     */
    public function getRevData()
    {
        return $this->_receive;
    }

    /**
     * 获取消息发送者
     * @return bool
     */
    public function getRevFrom() {
        if (isset($this->_receive['FromUserName']))
            return $this->_receive['FromUserName'];
        else
            return false;
    }

    /**
     * 获取消息接收者
     * @return bool
     */
    public function getRevTo() {
        if (isset($this->_receive['ToUserName']))
            return $this->_receive['ToUserName'];
        else
            return false;
    }

    /**
     * 获取接收消息的类型
     * @return bool
     */
    public function getRevType() {
        if (isset($this->_receive['MsgType']))
            return $this->_receive['MsgType'];
        else
            return false;
    }

    /**
     * 获取消息ID
     * @return bool
     */
    public function getRevID() {
        if (isset($this->_receive['MsgId']))
            return $this->_receive['MsgId'];
        else
            return false;
    }

    /**
     * 获取消息发送时间
     */
    public function getRevCtime() {
        if (isset($this->_receive['CreateTime']))
            return $this->_receive['CreateTime'];
        else
            return false;
    }

    /**
     * 获取接收消息内容正文
     */
    public function getRevContent(){
        if (isset($this->_receive['Content']))
            return $this->_receive['Content'];
        else if (isset($this->_receive['Recognition'])) //获取语音识别文字内容，需申请开通
            return $this->_receive['Recognition'];
        else
            return false;
    }

    /**
     * 获取接收消息事件推送
     * @return bool
     */
    public function getRevEvent(){
        if (isset($this->_receive['Event'])){
            $array['event'] = $this->_receive['Event'];
        }
        if (isset($this->_receive['EventKey'])){
            $array['key'] = $this->_receive['EventKey'];
        }
        if (isset($array) && count($array) > 0) {
            return $array;
        } else {
            return false;
        }
    }

    /**
     * 获取自定义菜单的扫码推事件信息
     * 事件类型为以下两种时则调用此方法有效
     * Event	 事件类型，scancode_push
     * Event	 事件类型，scancode_waitmsg
     * @return array|bool
     */
    public function getRevScanInfo(){
        if (isset($this->_receive['ScanCodeInfo'])){
            if (!is_array($this->_receive['ScanCodeInfo'])) {
                $array=(array)$this->_receive['ScanCodeInfo'];
                $this->_receive['ScanCodeInfo']=$array;
            }else {
                $array=$this->_receive['ScanCodeInfo'];
            }
        }
        if (isset($array) && count($array) > 0) {
            return $array;
        } else {
            return false;
        }
    }

    /**
     * 获取主动推送的消息ID
     * 经过验证，这个和普通的消息MsgId不一样
     * 当Event为 MASSSENDJOBFINISH 或 TEMPLATESENDJOBFINISH
     */
    public function getRevTplMsgID(){
        if (isset($this->_receive['MsgID'])){
            return $this->_receive['MsgID'];
        } else
            return false;
    }

    /**
     * 获取模板消息发送状态
     */
    public function getRevStatus(){
        if (isset($this->_receive['Status'])){
            return $this->_receive['Status'];
        } else
            return false;
    }

    /**
     * 设置回复消息
     * @param string $text
     * @return $this
     */
    public function text($text='')
    {
        $msg = array(
            'ToUserName' => $this->getRevFrom(),
            'FromUserName'=>$this->getRevTo(),
            'MsgType'=>self::MSGTYPE_TEXT,
            'Content'=>$this->_auto_text_filter($text),
            'CreateTime'=>time()
        );
        $this->Message($msg);
        return $this;
    }

    /**
     * 过滤文字回复\r\n换行符
     * @param $text
     * @return mixed
     */
    private function _auto_text_filter($text) {
        if (!$this->_text_filter) return $text;
        return str_replace("\r\n", "\n", $text);
    }

    /**
     * 发送消息
     * @param string $msg
     * @param bool|false $append
     * @return array
     */
    public function Message($msg = '',$append = false){
        if (is_null($msg)) {
            $this->_msg =array();
        }elseif (is_array($msg)) {
            if ($append)
                $this->_msg = array_merge($this->_msg,$msg);
            else
                $this->_msg = $msg;
            return $this->_msg;
        } else {
            return $this->_msg;
        }
    }

    /**
     * 发送模板消息
     * @param $data
     * @return bool|mixed
     */
    public function sendTemplateMessage($data){
        if (!$this->access_token && !$this->checkAuth()) return false;
        $result = $this->http_post(self::API_URL_PREFIX.self::TEMPLATE_SEND_URL.'access_token='.$this->access_token,self::json_encode($data));
        if($result){
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return $json;
        }
        return false;
    }


    /**
     * 创建二维码ticket
     * @param $scene_id 自定义追踪id,临时二维码只能用数值型
     * @param int $type 0:临时二维码；1:数值型永久二维码(此时expire参数无效)；2:字符串型永久二维码(此时expire参数无效)
     * @param int $expire 临时二维码有效期，最大为604800秒
     * @return bool|mixed array('ticket'=>'qrcode字串','expire_seconds'=>604800,'url'=>'二维码图片解析后的地址')
     */
    public function getQRCode($scene_id,$type=0,$expire=604800){
        if (!$this->access_token && !$this->checkAuth()) return false;
        if (!isset($scene_id)) return false;
        switch ($type) {
            case '0':
                if (!is_numeric($scene_id))
                    return false;
                $action_name = 'QR_SCENE';
                $action_info = array('scene'=>(array('scene_id'=>$scene_id)));
                break;
            case '1':
                if (!is_numeric($scene_id))
                    return false;
                $action_name = 'QR_LIMIT_SCENE';
                $action_info = array('scene'=>(array('scene_id'=>$scene_id)));
                break;
            case '2':
                if (!is_string($scene_id))
                    return false;
                $action_name = 'QR_LIMIT_STR_SCENE';
                $action_info = array('scene'=>(array('scene_str'=>$scene_id)));
                break;
            default:
                return false;
        }
        $data = array(
            'action_name'    => $action_name,
            'expire_seconds' => $expire,
            'action_info'    => $action_info
        );
        if ($type) {
            unset($data['expire_seconds']);
        }
        $result = $this->http_post(self::API_URL_PREFIX.self::QRCODE_CREATE_URL.'access_token='.$this->access_token,self::json_encode($data));
        if ($result) {
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return $json;
        }
        return false;
    }

    /**
     * 获取二维码图片
     * @param string $ticket 传入由getQRCode方法生成的ticket参数
     * @return string url 返回http地址
     */
    public function getQRUrl($ticket) {
        return self::QRCODE_IMG_URL.urlencode($ticket);
    }

    /**
     * 微信服务器验证
     * @param string $str
     * @return bool
     */
    private function checkSignature($str='')
    {
        $signature = isset($_GET["signature"])?$_GET["signature"]:'';
        $signature = isset($_GET["msg_signature"])?$_GET["msg_signature"]:$signature; //如果存在加密验证则用加密验证段
        $timestamp = isset($_GET["timestamp"])?$_GET["timestamp"]:'';
        $nonce = isset($_GET["nonce"])?$_GET["nonce"]:'';

        $token = $this->token;
        $tmpArr = array($token, $timestamp, $nonce,$str);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }

    /**
     * XML编码
     * @param mixed $data 数据
     * @param string $root 根节点名
     * @param string $item 数字索引的子节点名
     * @param string $attr 根节点属性
     * @param string $id   数字索引子节点key转换的属性名
     * @param string $encoding 数据编码
     * @return string
     */
    public function xml_encode($data, $root='xml', $item='item', $attr='', $id='id', $encoding='utf-8') {
        if(is_array($attr)){
            $_attr = array();
            foreach ($attr as $key => $value) {
                $_attr[] = "{$key}=\"{$value}\"";
            }
            $attr = implode(' ', $_attr);
        }
        $attr   = trim($attr);
        $attr   = empty($attr) ? '' : " {$attr}";
        $xml   = "<{$root}{$attr}>";
        $xml   .= self::data_to_xml($data, $item, $id);
        $xml   .= "</{$root}>";
        return $xml;
    }

    /**
     * 数据XML编码
     * @param mixed $data 数据
     * @return string
     */
    public static function data_to_xml($data) {
        $xml = '';
        foreach ($data as $key => $val) {
            is_numeric($key) && $key = "item id=\"$key\"";
            $xml    .=  "<$key>";
            $xml    .=  ( is_array($val) || is_object($val)) ? self::data_to_xml($val)  : self::xmlSafeStr($val);
            list($key, ) = explode(' ', $key);
            $xml    .=  "</$key>";
        }
        return $xml;
    }

    /**
     * 微信安全数据
     * @param $str
     * @return string
     */
    public static function xmlSafeStr($str)
    {
        return '<![CDATA['.preg_replace("/[\\x00-\\x08\\x0b-\\x0c\\x0e-\\x1f]/",'',$str).']]>';
    }

    /**
     * 日志记录，可被重载
     * @param $log
     * @return mixed
     */
    protected function log($log){
        if ($this->debug && function_exists($this->logcallback)) {
            if (is_array($log)) $log = print_r($log,true);
            return call_user_func($this->logcallback,$log);
        }
    }

    /**
     * get请求
     * @param $url
     * @return bool|mixed
     */
    private function http_get($url){
        $oCurl = curl_init();
        if(stripos($url,"https://")!==FALSE){
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);
        if(intval($aStatus["http_code"])==200){
            return $sContent;
        }else{
            return false;
        }
    }

    /**
     * post请求
     * @param $url
     * @param $param
     * @param bool|false $post_file
     * @return bool|mixed
     */
    private function http_post($url,$param,$post_file=false){
        $oCurl = curl_init();
        if(stripos($url,"https://")!==FALSE){
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
        }
        if (PHP_VERSION_ID >= 50500 && class_exists('\CURLFile')) {
            $is_curlFile = true;
        } else {
            $is_curlFile = false;
            if (defined('CURLOPT_SAFE_UPLOAD')) {
                curl_setopt($oCurl, CURLOPT_SAFE_UPLOAD, false);
            }
        }
        if (is_string($param)) {
            $strPOST = $param;
        }elseif($post_file) {
            if($is_curlFile) {
                foreach ($param as $key => $val) {
                    if (substr($val, 0, 1) == '@') {
                        $param[$key] = new \CURLFile(realpath(substr($val,1)));
                    }
                }
            }
            $strPOST = $param;
        } else {
            $aPOST = array();
            foreach($param as $key=>$val){
                $aPOST[] = $key."=".urlencode($val);
            }
            $strPOST =  join("&", $aPOST);
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt($oCurl, CURLOPT_POST,true);
        curl_setopt($oCurl, CURLOPT_POSTFIELDS,$strPOST);
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);
        if(intval($aStatus["http_code"])==200){
            return $sContent;
        }else{
            return false;
        }
    }

    /**
     * 设置缓存
     * @param $cachename
     * @param $value
     * @param $expired
     * @return bool
     */
    protected function setCache($cachename,$value,$expired){
        return S($cachename,$value,$expired);
    }

    /**
     * 获取缓存
     * @param $cachename
     * @return bool
     */
    protected function getCache($cachename){
        return S($cachename);
    }

    /**
     * 清除缓存
     * @param $cachename
     * @return bool
     */
    protected function removeCache($cachename){
        return S($cachename,null);
    }

    /**
     * 获取access_token
     * @param string $appid
     * @param string $appsecret
     * @param string $token
     * @return bool|string
     */
    public function checkAuth($appid='',$appsecret='',$token=''){
        if (!$appid || !$appsecret) {
            $appid = $this->appId;
            $appsecret = $this->appSecret;
        }
        if ($token) { //手动指定token，优先使用
            $this->access_token=$token;
            return $this->access_token;
        }
        $authname = 'wechat_access_token'.$appid;
        if ($rs = $this->getCache($authname))  {
            $this->access_token = $rs;
            return $rs;
        }
        $result = $this->http_get(self::API_URL_PREFIX.self::AUTH_URL.'&appid='.$appid.'&secret='.$appsecret);
        if ($result)
        {
            $json = json_decode($result,true);
            if (!$json || isset($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            $this->access_token = $json['access_token'];
            $expire = $json['expires_in'] ? intval($json['expires_in'])-100 : 3600;
            $this->setCache($authname,$this->access_token,$expire);
            return $this->access_token;
        }
        return false;
    }

    /**
     * 删除验证数据
     * @param string $appid
     * @return bool
     */
    public function resetAuth($appid=''){
        if (!$appid) $appid = $this->appId;
        $this->access_token = '';
        $authname = 'wechat_access_token'.$appid;
        $this->removeCache($authname);
        return true;
    }

    /**
     * 微信api不支持中文转义的json结构
     * @param $arr
     * @return string
     */
    static function json_encode($arr) {
        if (count($arr) == 0) return "[]";
        $parts = array ();
        $is_list = false;
        //Find out if the given array is a numerical array
        $keys = array_keys ( $arr );
        $max_length = count ( $arr ) - 1;
        if (($keys [0] === 0) && ($keys [$max_length] === $max_length )) { //See if the first key is 0 and last key is length - 1
            $is_list = true;
            for($i = 0; $i < count ( $keys ); $i ++) { //See if each key correspondes to its position
                if ($i != $keys [$i]) { //A key fails at position check.
                    $is_list = false; //It is an associative array.
                    break;
                }
            }
        }
        foreach ( $arr as $key => $value ) {
            if (is_array ( $value )) { //Custom handling for arrays
                if ($is_list)
                    $parts [] = self::json_encode ( $value ); /* :RECURSION: */
                else
                    $parts [] = '"' . $key . '":' . self::json_encode ( $value ); /* :RECURSION: */
            } else {
                $str = '';
                if (! $is_list)
                    $str = '"' . $key . '":';
                //Custom handling for multiple data types
                if (!is_string ( $value ) && is_numeric ( $value ) && $value<2000000000)
                    $str .= $value; //Numbers
                elseif ($value === false)
                    $str .= 'false'; //The booleans
                elseif ($value === true)
                    $str .= 'true';
                else
                    $str .= '"' . addslashes ( $value ) . '"'; //All other things
                // :TODO: Is there any more datatype we should be in the lookout for? (Object?)
                $parts [] = $str;
            }
        }
        $json = implode ( ',', $parts );
        if ($is_list)
            return '[' . $json . ']'; //Return numerical JSON
        return '{' . $json . '}'; //Return associative JSON
    }
}
