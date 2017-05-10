<?php

/**
 *      联系人模型
 *      [X-Mis] (C)2007-2099  
 *      This is NOT a freeware, use is subject to license terms
 *      http://www.xinyou88.com
 *      tel:400-000-9981
 *      qq:16129825
 */

namespace Home\Model;
use Think\Model;

class CustconModel extends Model{


    protected $_validate = array(
		array('xingming','require','请输入客户姓名！'),
        array('xingming','','姓名已经存在！',0,'unique',1),
		array('phone','','手机号已经存在！',2,'unique',3),
		array('phone','/^1[3|4|5|8][0-9]\d{4,8}$/','手机号码错误！',2,'regex',3),//新增、编辑都得验证手机号是否错误
		array('email','email','email格式错误！',2),
    );
    
		// 自动完成规则
	protected $_auto = array (
	    array('status',1,1), // 对status字段在新增的时候赋值0
		array('uid','getuserid',1,'function'),
        array('uname','gettruename',1,'function'), 		
	    array('addtime','getUnixTime',1,'function'),
		array('uuid','getuserid',2,'function'),
        array('uuname','gettruename',2,'function'), 		
	    array('updatetime','getUnixTime',2,'function'),
	);

}