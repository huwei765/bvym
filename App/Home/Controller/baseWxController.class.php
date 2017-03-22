<?php
namespace Home\Controller;
use Think\Controller;

Class BaseWxController extends Controller{

	/**
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

	/**
	 * 微信服务器验证
	 * @return bool
	 * @throws Exception
	 */
	public function checkSignature() {
		$_token = C("WX_PUBLIC.user_token");
		if(empty($_token)){
			throw new Exception("TOKEN is not defined!");
		}
		$signature = $_GET["signature"];
		$timestamp = $_GET["timestamp"];
		$nonce     = $_GET["nonce"];

		$token = $_token;
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
	 * 获取微信服务器发来的消息
	 * @return bool|\SimpleXMLElement
	 */
	public function getRevData()
	{
		$postStr = isset($GLOBALS["HTTP_RAW_POST_DATA"]) && !empty($GLOBALS["HTTP_RAW_POST_DATA"]) ? $GLOBALS["HTTP_RAW_POST_DATA"] : "";
		if (!empty($postStr)) {
			libxml_disable_entity_loader(true);
			return simplexml_load_string($postStr,"SimpleXMLElement",LIBXML_NOCDATA);
		}
		else{
			return false;
		}
	}
}