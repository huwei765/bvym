<?php

/**
 *		客户签到逻辑
 */

namespace Home\Logic;
use Think\Model;

class SigninLogic extends Model{

	/**
	 * 通过微信扫码签到
	 * @param $openId
	 * @return array
	 */
	public function signInByWxOpenId($openId){
		//注册获取用户信息
		$wxUser_Logic = D("wxuser","Logic");
		$wxUserData = $wxUser_Logic->registerByOpenId($openId);
		if(empty($wxUserData)){
			return array('status' => 0,'data' => '该微信用户未注册！');
		}
		//查询该微信用户是否已经注册成为客户
		$custcon_data = D("custcon","Logic")->getCustconInfoByWeixinId($wxUserData["openid"]);
		if(empty($custcon_data)){
			return array('status' => 1,'data' => '该微信用户还未成为正式客户！');
		}
		//签到
		$ret = $this->signInLogicByUid($custcon_data["id"],1,"微信签到");
		$SignInData = array(
			"nickname" => $wxUserData["nickname"],
			"xingming"=> $custcon_data["xingming"],
			"wxid" => $wxUserData["id"],
			"openid" => $wxUserData["openid"],
			"custid" => $custcon_data["jcid"]
		);
		if($ret){
			$this->sendSignInMsg($SignInData);
		}
	}

	/**
	 * 签到流程
	 * @param $cuid
	 * @param int $stype
	 * @param string $memo
	 * @return bool
	 */
	public function signInLogicByUid($cuid,$stype = 0,$memo = ""){
		$isTrue = $this->signInByUid($cuid,$stype,$memo);//新增/更新签到数据是否成功
		return $isTrue;
	}

	/**
	 * 客户签到
	 * @param $cuid
	 * @param int $stype
	 * @param string $memo
	 * @return bool
	 */
	public function signInByUid($cuid,$stype = 0,$memo = ""){
		if(intval($cuid) <= 0){
			return false;
		}
		//声明提交的数据
		$post_data["cuid"] = $cuid;
		$post_data["stype"] = intval($stype);
		$post_data["beizhu"] = $memo;

		//查询客户信息
		$con_data = D("custcon","Logic")->getCustconInfoById($cuid);
		if(empty($con_data)){
			return false;
		}
		$post_data["cuname"] = $con_data["xingming"];//客户姓名
		//查询微信信息
		if(intval($con_data["wxid"]) > 0){
			$wx_data = D("wxuser","Logic")->getWxUserInfoById($con_data["wxid"],"id,nickname");
			if(!empty($wx_data)){
				$post_data["wxuid"] = $wx_data["id"];
				$post_data["wxuname"] = $wx_data["nickname"];
			}
		}
		//查询该客户今天是否已经签到
		$sign_data = $this->getTodaySignInfoByCuid($cuid);
		if(!empty($sign_data)){
			$ret = $this->saveSignInfo($sign_data["id"],$post_data);
		}
		else{
			$ret = $this->addSignInfo($post_data);
		}
		return intval($ret) > 0 ? true : false;
	}

	/**
	 * 查询指定客户当天的签到信息
	 * @param $cuid
	 * @param $field
	 * @return mixed
	 */
	public function getTodaySignInfoByCuid($cuid,$field){
		$condition["cuid"] = $cuid;
		$condition["addtime"] = array(array("gt",strtotime("Y-m-d",time())),array('lt',strtotime(date('Y-m-d',strtotime('+1 day')))));
		return $this->getSignInfo($condition);
	}

	/**
	 * 查询签到信息
	 * @param $condition
	 * @param string $field
	 * @return mixed
	 */
	public function getSignInfo($condition,$field="*"){
		return M("signin")->field($field)->where($condition)->find();
	}

	/**
	 * 新增签到信息
	 * @param $postData
	 * @return mixed
	 */
	public function addSignInfo($postData){
		$signIn_model = D("signin");
		if($signIn_model->create($postData)){
			return $signIn_model->add();
		}
		else{
			return 0;
		}
	}

	/**
	 * 签到更新
	 * @param $id
	 * @param $postData
	 * @return bool
	 */
	public function saveSignInfo($id,$postData){
		return M("signin")->where("id=".$id)->save($postData);
	}

	/**
	 * 发送签到成功消息
	 * @param $signData
	 */
	public function sendSignInMsg($signData){
		if(!empty($signData)){
			if(isset($signData["custid"])){
				$this->sendSignMsgToCust($signData);
			}
			if(isset($signData["wxid"])){
				$this->sendSignMsgToWxUser($signData);
			}
			if(isset($signData["openid"])){
				$this->sendSignMsgToSelf($signData);
			}
		}
	}

	/**
	 * 给合作机构绑定的微信号发送模板签到消息
	 * @param $signData
	 * @return bool
	 */
	public function sendSignMsgToCust($signData){
		$custId = $signData["custid"];
		//查询推广商绑定的微信号
		$custData = D("cust","Logic")->getCustInfoById($custId,"id,wxid");
		if(!empty($custData)){
			if(isset($custData["wxid"]) && intval($custData["wxid"]) > 0){
				//查询openid
				$wxUserData = D("wxuser","Logic")->getWxUserInfoById($custData["wxid"],"id,openid");
				if(empty($wxUserData)){
					return false;
				}
				//消息模板
				$sigInTPL = array(
					"touser" => $wxUserData["openid"],
					"template_id" => C("WX_MSG_TPL.signin_id"),
					"data"=> array(
						"first"=>array(
							"value"=>"客户签到提醒",
							"color"=>"#173177"
						),
						"keyword1"=>array(
							"value"=>$signData["xingming"],
							"color"=>"#173177"
						),
						"keyword2"=>array(
							"value"=>"碧薇医美",
							"color"=>"#173177"
						),
						"keyword3"=>array(
							"value"=>Date("Y-m-d H:i:s",time()),
							"color"=>"#173177"
						),
						"remark"=>array(
							"value"=>"爱美丽，碧薇医美成就你的梦想",
							"color"=>"#173177"
						)
					)
				);
				//发送模板消息
				$this->sendTPLMsg($sigInTPL);
			}
		}
	}

	/**
	 * 给推广微信用户发送签到消息
	 * @param $signData
	 */
	public function sendSignMsgToWxUser($signData){

	}

	/**
	 * 客户签到收到的信息
	 * @param $signData
	 */
	private function sendSignMsgToSelf($signData){
		//消息模板
		$sigInTPL = array(
			"touser" => $signData["openid"],
			"template_id" => C("WX_MSG_TPL.signin_id"),
			"data"=> array(
				"first"=>array(
					"value"=>"签到提醒",
					"color"=>"#173177"
				),
				"keyword1"=>array(
					"value"=>$signData["xingming"],
					"color"=>"#173177"
				),
				"keyword2"=>array(
					"value"=>"碧薇医美",
					"color"=>"#173177"
				),
				"keyword3"=>array(
					"value"=>Date("Y-m-d H:i:s",time()),
					"color"=>"#173177"
				),
				"remark"=>array(
					"value"=>"爱美丽，碧薇医美成就你的梦想",
					"color"=>"#173177"
				)
			)
		);
		//发送模板消息
		D("wechat","Logic")->sendTemplateMessage($sigInTPL);
	}

}