<?php


namespace Home\Model;
use Think\Model;

class SigninModel extends Model{

    protected $_validate = array(
        //array('stype','','名称已经存在！',0,'unique',1),
    );
    
		// 自动完成规则
	protected $_auto = array (
		array('addtime','getUnixTime',1,'function'),
	);

}