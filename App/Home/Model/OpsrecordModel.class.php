<?php

namespace Home\Model;
use Think\Model;

class OpsrecordModel extends Model{


    protected $_validate = array(
        array('title','','名称已经存在！',0,'unique',1),
    );
    
		// 自动完成规则
	protected $_auto = array (
	    array('status',1,1),
		array('uid','getuserid',1,'function'),
		array('uname','gettruename',1,'function'),
		array('addtime','getUnixTime',1,'function'),
	);

}