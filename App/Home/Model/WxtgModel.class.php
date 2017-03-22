<?php

/**
 *      微信用户模型
 */

namespace Home\Model;
use Think\Model;

class WxtgModel extends Model{


    protected $_validate = array(
        //array('name','','名称已经存在！',0,'unique',1),
    );
    
		// 自动完成规则
	protected $_auto = array (
	    array('addtime','getUnixTime',1,'function'),
	);

}