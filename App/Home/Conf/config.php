<?php
return array(
	//'配置项'=>'配置值'
    'SHOW_PAGE_TRACE'=>true,
    'debug' =>  true,
    'wx_msg_data_tpl' => array(
        'sign_in_customer' => array(
            'touser'=>'%s',
            'template_id'=>"UJqNWRZOHquaiPaCN_r9ysXlhMpFQJX61SXgMO1VBDQ",
            'data'=> array(
                'first'=> array('value'=>'你好，你已成功签到！','color'=>'#173177'),
                "keyword1"=>array("value"=>'%s',"color"=>"#173177"),//用户名
                "keyword2"=>array("value"=>Date("Y-m-d H:i:s",time()),"color"=>"#173177"),
                "remark"=>array("value"=>"爱美丽，碧薇医美成就你的梦想","color"=>"#173177")
            )
        ),
        'sign_in_agent' => array(
            'touser'=>'%s',
            'template_id'=>"UJqNWRZOHquaiPaCN_r9ysXlhMpFQJX61SXgMO1VBDQ",
            'data'=> array(
                'first'=> array('value'=>'您好，【%s】客户已到院！','color'=>'#173177'),
                "keyword1"=>array("value"=>'%s',"color"=>"#173177"),//客户姓名
                "keyword2"=>array("value"=>Date("Y-m-d H:i:s",time()),"color"=>"#173177"),
                "remark"=>array("value"=>"爱美丽，碧薇医美成就你的梦想","color"=>"#173177")
            )
        ),
        'ht_new_agent' => array(
            'touser'=>'%s',
            'template_id'=>"8ptWAb0cUdu7gVXaCRHPjE5VIdQ1iQgsbNUZoNYljiw",
            'data'=> array(
                'first'=> array('value'=>'您好，【%s】客户%s已成功出单！','color'=>'#173177'),
                "keyword1"=>array("value"=>'%s',"color"=>"#173177"),//客户姓名
                "keyword2"=>array("value"=>Date("Y-m-d H:i:s",time()),"color"=>"#173177"),
                "remark"=>array("value"=>"单号：%s,总金额为%s，待支付！","color"=>"#173177")
            )
        ),
        'pay_agent' => array(
            'touser'=>'%s',
            'template_id'=>"RNtGkChOfxKKRGeEOyp9xzzfmkk7OKfy8S4wrGcuOvo",
            'data'=> array(
                'first'=> array('value'=>'您好，【%s】客户%s已成功支付！','color'=>'#173177'),
                "keyword1"=>array("value"=>'%s',"color"=>"#173177"),//付款金额
                "keyword2"=>array("value"=>'%s',"color"=>"#173177"),//交易单号
                "remark"=>array("value"=>"出单总金额%s元，累计付款%s元，剩余%s元待支付！","color"=>"#173177")
            )
        ),
        'opr_new_agent' => array(
            'touser'=>'%s',
            'template_id'=>"LvQeT7QDwijeCabpyVyqD1aAICkMDw1oy27n7D38MAc",
            'data'=> array(
                'first'=> array('value'=>'医美项目操作完成通知','color'=>'#173177'),
                "keyword1"=>array("value"=>'【%s】客户%s已完成本次医美项目操作',"color"=>"#173177"),//提醒内容
                "keyword2"=>array("value"=>Date("Y-m-d",time()),"color"=>"#173177"),//具体时间
                "remark"=>array("value"=>"爱美丽，碧薇医美成就你的梦想！","color"=>"#173177")
            )
        ),
        'customer_new_agent' => array(
            'touser'=>'%s',
            'template_id'=>"6QXWf-xJ4fhfMneMOCwzeXDQr51GPhglqDlulzI7W_k",
            'data'=> array(
                'first'=> array('value'=>'您好，有新会员成为【%s】的客户','color'=>'#173177'),
                "keyword1"=>array("value"=>'%s',"color"=>"#173177"),//姓名
                "keyword2"=>array("value"=>'%s'),//手机
                "keyword3"=>array("value"=>"%s","color"=>"#173177"),//登记时间
                "remark"=>array("value"=>"爱美丽，碧薇医美成就你的梦想！","color"=>"#173177")
            )
        )
    )
);