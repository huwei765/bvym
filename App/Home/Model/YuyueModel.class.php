<?php

namespace Home\Model;
use Think\Model;

class YuyueModel extends Model{


    protected $_validate = array(
		array('cuname','require','请输入客户姓名！'),
        array('title','','名称已经存在！',0,'unique',1),
		array('phone','/^1[3|4|5|8][0-9]\d{4,8}$/','手机号码错误！',0,'regex',1),
		array('jcname','require','请输入店家名称！'),
		array('uname','require','请输入老师名称！'),
		array('juname','require','请输入接待人名称！'),
		array('address','require','请输入地址！'),
		array('oktime','require','请输入预约时间！')
    );
    
		// 自动完成规则
	protected $_auto = array (
	    array('stype',0,1),
	    array('addtime','getUnixTime',1,'function'),
	);

}