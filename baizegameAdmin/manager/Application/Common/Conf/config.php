<?php
return array(
	//'配置项'=>'配置值'
    
    //默认错误跳转对应的模板文件
    'TMPL_ACTION_ERROR' => 'Public/dispatch_jump',
    //默认成功跳转对应的模板文件
    'TMPL_ACTION_SUCCESS' => 'Public/dispatch_jump',
    
    //Redis Session配置
    'SESSION_AUTO_START'    =>  true,    // 是否自动开启Session
    'SESSION_TYPE'            =>  'Redis',    //session类型
    'SESSION_PERSISTENT'    =>  1,        //是否长连接(对于php来说0和1都一样)
    'SESSION_CACHE_TIME'    =>  300,        //连接超时时间(秒)
    'SESSION_EXPIRE'        =>  10800,        //session有效期(单位:秒) 0表示永久缓存
    'SESSION_PREFIX'        =>  'sess_',        //session前缀
    //'SESSION_REDIS_HOST'    =>  '172.16.0.10', //分布式Redis,默认第一个为主服务器
    'SESSION_REDIS_HOST'    =>  '127.0.0.1',
    'SESSION_REDIS_PORT'    =>  '6379',           //端口,如果相同只填一个,用英文逗号分隔
    'SESSION_REDIS_AUTH'    =>  'MH3E1ZPY',    //Redis auth认证(密钥中不能有逗号),如果相同只填一个,用英文逗号分隔
    
    //数据redis 缓存配置
    'DATA_CACHE_PREFIX' => 'Redis_',//缓存前缀
    'DATA_CACHE_TYPE'=>'Redis',//默认动态缓存为Redis
    'REDIS_RW_SEPARATE' => false, //Redis读写分离 true 开启
    //'REDIS_HOST'=>'172.16.0.10', //redis服务器ip，多台用逗号隔开；读写分离开启时，第一台负责写，其它[随机]负责读；
    'REDIS_HOST'=>'127.0.0.1',
    'REDIS_PORT'=>'6379',//端口号
    'REDIS_TIMEOUT'=>'300',//超时时间
    'REDIS_PERSISTENT'=>false,//是否长连接 false=短连接
    'REDIS_AUTH_PASSWORD'=>'MH3E1ZPY',//AUTH认证密码
    'DATA_CACHE_TIME'       => 10800,   
    
    //分布式数据库配置定义
   /* 'DB_CONFIG1'=>array(
        'DB_DEPLOY_TYPE'=> 1, // 设置分布式数据库支持
        'DB_TYPE'       => 'mysql', //分布式数据库类型必须相同
        'DB_HOST'       => '172.16.0.8',
        'DB_NAME'       => '2460', //如果相同可以不用定义多个
        'DB_USER'       => 'root',
        'DB_PWD'        => 'a7SOyQWJ',
        'DB_PORT'       => '3306',
         //'DB_PREFIX'     => 'think_',
    ),*/
    'DB_CONFIG1'=>array(
        'DB_DEPLOY_TYPE'=> 0, // 设置分布式数据库支持
        'DB_TYPE'       => 'mysql', //分布式数据库类型必须相同
        'DB_HOST'       => '127.0.0.1',
        'DB_NAME'       => '2460', //如果相同可以不用定义多个
        'DB_USER'       => 'root',
        'DB_PWD'        => 'root',
        'DB_PORT'       => '3306',
        //'DB_PREFIX'     => 'think_',
    ),
);