<?php

/**
 *      合同管理模型
 *      [X-Mis] (C)2007-2099  
 *      This is NOT a freeware, use is subject to license terms
 *      http://www.xinyou88.com
 *      tel:400-000-9981
 *      qq:16129825
 */

namespace Home\Model;
use Think\Model;

class HetongModel extends Model{


    protected $_validate = array(
		array('bianhao','require','请输入订单编号！'),
        array('bianhao','','编号已经存在！',0,'unique',1),
		array('bianhao','/[a-z0-9]{6,12}/','编号长度必须是6到12位，且必须为数字或字母！',0,'length'),
		array('cuid','require','请选择客户！'),
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