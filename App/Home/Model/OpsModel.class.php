<?php

namespace Home\Model;
use Think\Model;

class OpsModel extends Model{


    protected $_validate = array(
        array('title','','名称已经存在！',0,'unique',1),
    );
    
		// 自动完成规则
	protected $_auto = array (
	    array('status',1,1),
	    array('addtime','getUnixTime',1,'function'),
	);

}