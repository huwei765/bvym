<?php

namespace Home\Model;
use Think\Model;

class HtopsModel extends Model{


    protected $_validate = array(
        //array('name','','名称已经存在！',0,'unique',1),
    );

    protected $_auto = array (
        array('addtime','getUnixTime',1,'function')
    );
}