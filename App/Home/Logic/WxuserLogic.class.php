<?php

/**
 *		微信用户信息
 */

namespace Home\Logic;
use Think\Model;

class WxuserLogic extends Model{

	/**
	 * 将微信用户注册到本地用户
	 * @param $userData
	 * @param int $parentId
	 * @return array|mixed
	 */
	public function registerFromWxUser($userData,$parentId = 0){
		if(empty($userData)){
			return array();
		}
		if(isset($userData["openid"])){
			return array();
		}
		//查询用户是否已经注册
		$wxUserData = $this->getWxUserInfoByOpenId($userData["openid"]);
		if(empty($wxUserData)){
			$record_id = $this->addWxUser($userData,$parentId);
			if(intval($record_id) > 0){
				$wxUserData = $this->getWxUserInfoById(intval($record_id));
			}
		}
		return $wxUserData;
	}

	/**
	 * 添加微信用户
	 * @param $userData
	 * @param int $parentId
	 * @return int|mixed
	 */
	public function addWxUser($userData,$parentId = 0){
		if(empty($userData)){
			return 0;
		}
		if(isset($userData["openid"])){
			return 0;
		}
		//data
		$wxUserData["nickname"] = $userData["nickname"];
		$wxUserData["sex"] = $userData["sex"];
		$wxUserData["city"] = $userData["city"];
		$wxUserData["country"] = $userData["country"];
		$wxUserData["province"] = $userData["province"];
		$wxUserData["headimgurl"] = $userData["headimgurl"];
		$wxUserData["openid"] = $userData["openid"];
		if(isset($userData["unionid"])){
			$wxUserData["unionid"] = $userData["unionid"];
		}
		$wxUserData["addtime"] = time();
		$wxUserData["parent"] = $parentId;
		//add
		$wxUserModel = M("wxuser");
		if($wxUserModel->create($wxUserData)){
			return $wxUserModel->add();
		}
		else{
			return 0;
		}
	}

	public function registerByOpenId($openId){
		//判断用户是否已经注册
		$wxUserData = $this->getWxUserInfoByOpenId($openId);
		if(empty($wxUserData)){
			//获取远程微信用户信息
			//微信用户注册
			$wxResult = D("wechat","Logic")->getUserInfo($openId);
			if(!$wxResult){
				return array();
			}
			//注册微信用户
			return $this->registerFromWxUser($wxResult);
		}
		else{
			return $wxUserData;
		}
	}

	/**
	 * 根据openid来查询微信用户信息
	 * @param $openId
	 * @param string $field
	 * @return mixed
	 */
	public function getWxUserInfoByOpenId($openId,$field="*"){
		return $this->getWxUserInfo(array("openid"=>$openId),$field);
	}

	/**
	 * 根据微信ID查询微信用户信息
	 * @param $id
	 * @param $field
	 * @return mixed
	 */
	public function getWxUserInfoById($id,$field="*"){
		return $this->getWxUserInfo(array("id"=>$id),$field);
	}

	/**
	 * 查询微信用户的基本逻辑
	 * @param $condition
	 * @param string $field
	 * @return mixed
	 */
	public function getWxUserInfo($condition,$field="*"){
		return M("wxuser")->field($field)->where($condition)->find();
	}

}