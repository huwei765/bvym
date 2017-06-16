<?php
return array(
	//'配置项'=>'配置值'
    'debug' =>  false,
    'wx_msg_data_tpl' => array(
        'ht_new_customer'=> array(
            'touser'=>'%s',
            'template_id'=>"111111",
            'data'=> array(
                'first'=> array(
                    'value'=>'新增订单',
                    'color'=>'#173177'
                ),
                "keyword1"=>array(
                    "value"=>'%s',//客户姓名
                    "color"=>"#173177"
                ),
                "keyword2"=>array(
                    "value"=>"碧薇医美",
                    "color"=>"#173177"
                ),
                "keyword3"=>array(
                    "value"=>Date("Y-m-d H:i:s",time()),
                    "color"=>"#173177"
                ),
                "remark"=>array(
                    "value"=>"爱美丽，碧薇医美成就你的梦想",
                    "color"=>"#173177"
                )
            )
        ),
        'sign_in_customer' => array(
            'touser'=>'%s',
            'template_id'=>"111111",
            'data'=> array(
                'first'=> array(
                    'value'=>'签到提醒',
                    'color'=>'#173177'
                ),
                "keyword1"=>array(
                    "value"=>'%s',//客户姓名
                    "color"=>"#173177"
                ),
                "keyword2"=>array(
                    "value"=>"碧薇医美",
                    "color"=>"#173177"
                ),
                "keyword3"=>array(
                    "value"=>Date("Y-m-d H:i:s",time()),
                    "color"=>"#173177"
                ),
                "remark"=>array(
                    "value"=>"爱美丽，碧薇医美成就你的梦想",
                    "color"=>"#173177"
                )
            )
        )
    )
);