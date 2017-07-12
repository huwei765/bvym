<?php

/**
 *      客户管理模型
 *      [X-Mis] (C)2007-2099  
 *      This is NOT a freeware, use is subject to license terms
 *      http://www.xinyou88.com
 *      tel:400-000-9981
 *      qq:16129825
 */

namespace Home\Model;
use Think\Model;

class CustModel extends Model{


    protected $_validate = array(
		array('title','require','请输入客户姓名！'),
        array('title','','名称已经存在！',0,'unique',1),
		array('phone','/^1[3|4|5|8][0-9]\d{4,8}$/','手机号码错误！',0,'regex',1),
		array('email','email','email格式错误！',2),
		array('rate','require','请输入提成比率！',1),
		array('rate',array(0,100),'提成比率必须是0~100之间！',1,"between"),
    );
    
		// 自动完成规则
	protected $_auto = array (
	    array('status',1,1), // 对status字段在新增的时候赋值0
		array('fenlei',"暂未联系",1),
		array('uid','getuserid',1,'function'),
        array('uname','gettruename',1,'function'), 		
	    array('addtime','getUnixTime',1,'function'),
		array('uuid','getuserid',2,'function'),
        array('uuname','gettruename',2,'function'), 		
	    array('updatetime','getUnixTime',2,'function'),
	);

}