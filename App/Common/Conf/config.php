<?php
return array( 


    'URL_MODEL'   =>  0,       // URL访问模式,可选参数0、1、2、3,代表以下四种模式：
	
    'URL_CASE_INSENSITIVE' =>true, //表示URL访问不区分大小写

	//权限验证设置
	'AUTH_CONFIG'=>array(
        'AUTH_ON' => true, 
        'AUTH_TYPE' => 1, // 认证方式，1为时时认证；2为登录认证。
        'AUTH_GROUP' => 'xy_auth_group', 
        'AUTH_GROUP_ACCESS' => 'xy_auth_group_access', 
        'AUTH_RULE' => 'xy_auth_rule', 
        'AUTH_USER' => 'xy_user'
    ),
	'NOT_AUTH_MODULE' => 'Public,Index', // 默认无需认证模块
    //超级管理员id,拥有全部权限,只要用户uid在这个角色组里的,就跳出认证.可以设置多个值,如array('1','2','3')
    'ADMINISTRATOR'=>array('1'),
	'SESSION_OPTIONS' =>  array('expire'=>36000),
	'SESSION_PREFIX'        =>  'xykj', 
	
	 // 加载扩展配置文件 多个用,隔开
	'LOAD_EXT_CONFIG' => 'web,db', 
	
	//上传设置
	'UPLOAD_MAXSIZE'=>31457280,
	'UPLOAD_EXTS'=>array('jpg','gif','png','jpeg','txt','doc','docx','xls','xlsx','ppt','pptx','pdf','rar','zip','wps','wpt','dot','rtf','dps','dpt','pot','pps','et','ett','xlt'),// 设置附件上传类型 
	'UPLOAD_SAVEPATH'=>'./Public/',
    'debug'=>true,
    //系统常量配置
    'SYS_SEX'=>array(
        array("name"=>"男","value"=>"0"),
        array("name"=>"女","value"=>"1"),
    ),
    //参与搜索的字段名
    'SYS_SEARCH_KEY'=>array("name","title","username","value","truename","tel","email","phone","xingming","xueli","xuexiao","depname", "posname","dianhua","danwei","zhiwu","uname","uuname","zhuangtai","bumen","zhiwei","zhuanye","zaizhi","jcname","juname","gonghao"),
    //机构类别
    'CUS_CATE'=>array(
        array("name"=>"美容院","value"=>"0"),
        array("name"=>"代理商公司","value"=>"1"),
    ),
    //渠道跟单方式
    'CUS_TYPE'=>array(
        array("name"=>"上门拜访","value"=>"0"),
        array("name"=>"电话","value"=>"1"),
    ),
    //渠道跟单进展
    'CUS_FENLEI'=>array(
        array("name"=>"有意向","value"=>"0"),
        array("name"=>"洽谈","value"=>"1"),
        array("name"=>"已签约","value"=>"2"),
        array("name"=>"取消合作","value"=>"3")
    ),
    //渠道合同类别
    'CUS_HT_TYPE'=>array(
        array("name"=>"劳务合同","value"=>"0"),
        array("name"=>"其它","value"=>"1")
    ),
    //支付方式
    'PAY_WAY'=>array(
        array("name"=>"现金","value"=>"0"),
        array("name"=>"银行转账","value"=>"1")
    ),
    //推广提成
    'SPREAD_LEVEL'=>array("JG"=>3,"WX"=>3),
    'CUS_SPREAD_RATE'=>array("1"=>50,"2"=>40,"3"=>30,"other"=>20),
    'WX_SPREAD_RATE'=>array("1"=>10,"2"=>8,"3"=>5,"other"=>2),
    //微信公众平台
    'WX_PUBLIC'=>array(
        'token'=>"bvym",
        'appId'=>"wxfbeaf390146a2c6b",
        'appSecret'=>"40e0dcc503d1fcdded36986ca29697d2"
    ),
    'WX_MSG_TPL'=>array(
        "signin_id"=> "ngqIpbwh8bUfcSsECmogfXcV14J0tQlEpBO27izEYtY"
    )
);
