<?php

namespace Home\Logic;
use Think\Model;
class BaseLogic{

	function callback($state = true, $msg = '', $data = array()) {
		return array('state' => $state, 'msg' => $msg, 'data' => $data);
	}
}