<?php

/**
 *      员工档案模型
 *      [X-Mis] (C)2007-2099  
 *      This is NOT a freeware, use is subject to license terms
 *      http://www.xinyou88.com
 *      tel:400-000-9981
 *      qq:16129825
 */

namespace Home\Model;
use Think\Model;

class HrModel extends Model{


    protected $_validate = array(
        //array('name','','名称已经存在！',0,'unique',1),
    );
    
		// 自动完成规则
	protected $_auto = array (
	    array('status',1,0), // 对status字段在新增的时候赋值0
		array('addtime','getUnixTime',1,'function'),
		array('shengri','gettime',1,'function'),
		array('ruzhi','gettime',1,'function'),
	);

}