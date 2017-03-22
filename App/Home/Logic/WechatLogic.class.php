<?php

/**
 *		微信逻辑层
 */

namespace Home\Logic;

class WechatLogic{

	const SIGN_QR_SCENE_STR = "fixed_100";//永久签到码
	const SIGN_QR_TICKET_CACHE_NAME = "cache_sign_100";//永久签到码的缓存标记
	private $wechat;//wechat实例

	public function __construct(){
		if(!$this->wechat){
			$options = array(
				'token'=>C("WX_PUBLIC.token"), //填写应用接口的Token
				//'encodingaeskey'=>'encodingaeskey', //填写加密用的EncodingAESKey
				'appId'=>C("WX_PUBLIC.appId"), //填写高级调用功能的app id
				'appSecret'=>C("WX_PUBLIC.appSecret"), //填写高级调用功能的密钥
				'debug'=>false, //调试开关
				//'_logcallback'=>'logg', //调试输出方法，需要有一个string类型的参数
			);
			$this->wechat = new \Com\Wechat($options);
		}
	}

	/**
	 * 被动回复文字消息
	 * @param $textMsgData
	 */
	public function replyTextMsg($textMsgData){
		$this->wechat->reply($textMsgData);
	}

	/**
	 * 发送模板消息
	 * @param $data
	 * @return bool|mixed
	 */
	public function sendTemplateMessage($data){
		return $this->wechat->sendTemplateMessage($data);
	}

	/**
	 * 设置实例
	 * @param $options
	 */
	public function setInstance($options){
		if(!$this->wechat){
			$this->wechat = new \Com\Wechat($options);
		}
	}

	/**
	 * 获取永久签到码的二维码图片地址
	 * @return bool|string
	 */
	public function getSignInQRUrl(){
		$ticket = $this->getSignInQRTicket();
		if(!is_null($ticket)){
			return $this->wechat->getQRUrl($ticket);
		}
		else{
			return "";
		}
	}

	/**
	 * 获取永久签到码的ticket
	 * @return mixed
	 */
	public function getSignInQRTicket(){
		//从缓存中读取ticket
		$ticket = S(SIGN_QR_TICKET_CACHE_NAME);
		if(!$ticket){
			$grCode = $this->wechat->getQRCode(SIGN_QR_SCENE_STR,2);
			if($grCode){
				$ticket = $grCode["ticket"];
				S(SIGN_QR_TICKET_CACHE_NAME,$grCode["ticket"],$grCode["expire_seconds"]);
			}
		}
		return $ticket;
	}

	/**
	 * 查询用户信息
	 * @param $openId
	 * @return bool|mixed
	 */
	public function getUserInfo($openId){
		return $this->wechat->getUserInfo($openId);
	}

}