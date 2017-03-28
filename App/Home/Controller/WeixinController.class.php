<?php
namespace Home\Controller;
use Think\Controller;

Class WeixinController extends BaseWxController{

	/**
	 * 微信入口
	 * @throws Exception
	 */
	public function index(){
		if (!empty($_GET['echostr'])) {
			echo $_GET['echostr'];
			exit;
		}
		//微信只会在第一次在URL中带echostr参数，以后就不会带这个参数了
		if ($this->checkSignature()) {
			$postObj = $this->getRevData();
			if($postObj){
				$this->route($postObj);
			}
		} else {
			echo "error";
		}
	}

	/**
	 * 路由
	 * @param $postObj
	 */
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
						break;
					case "SCAN"://已关注用户扫描带参数二维码
						$this->scanWithParams($postObj);
						break;
					//自定义菜单的事件功能
				}
		}
	}

	/**
	 * 关注事件:微信用户注册
	 * @param $postObj
	 */
	public function subscribe($postObj) {
		//根据openId注册用户信息
		$open_id = $postObj->FromUserName;
		$wxUserData = D("wxuser","Logic")->registerByOpenId($open_id);
		if(empty($wxUserData)){
			exit();
		}
		//自动回复消息
		$this->replySubscribeMsg($postObj);
	}

	/**
	 * 扫描带参数二维码
	 * @param $postObj
	 * @return bool
	 */
	public function scanWithParams($postObj){
		if(!isset($postObj->EventKey)){
			return false;
		}
		if(strpos($postObj->EventKey, 'fixed_') !== false ){
			//永久二维码
			$sCen_id=intval(str_replace('fixed_','',$postObj->EventKey));
			if($sCen_id == 100){
				//签到
				$retData = D("signin","Logic")->signInByWxOpenId($postObj->FromUserName);
			}
		}
		else if(is_numeric($postObj->EventKey)){
			//临时二维码:用于推广
		}
	}

	/**
	 * 自动回复关注事件消息
	 * @param $postObj
	 */
	private function replySubscribeMsg($postObj){
		$data = array(
			"ToUserName"=>$postObj->FromUserName,
			"FromUserName"=>$postObj->ToUserName,
			"CreateTime"=>time(),
			"MsgType"=>"text",
			"Content"=>"欢迎关注，碧薇医美最懂你的美丽!"
		);
		D("wechat","Logic")->replyTextMsg($data);
	}
}