# tp6-curd
基于thinkphp6框架的命令行工具

##部署

###composer安装
>composer require xinxinyue/xinxinyue/tp-api-curd

安装完成后会新增配置文件curd.php

~~~
<?php
/**
 * File config.php
 */

return [
    'stub_path'     => false,		//自定义模板路径

    'model' => [
        'delete_filed' => 'delete_time',	//软删除字段
        'create_field' => 'create_time',	//创建时间字段
        'update_field' => 'update_time',	//修改时间字段
        'auto_timestamp' => 'int',	//自动完成字段类型
    ],
    'service' => [
        'admin_event' => true,	//是否添加管理员日志事件--自用
    ]

];
~~~

config目录下console.php配置文件新增 

~~~
// 指令定义
    'commands' => [
        'xinxinyue\curd\command\model',
        'xinxinyue\curd\command\validate',
        'xinxinyue\curd\command\service',
        'xinxinyue\curd\command\controller',
        'xinxinyue\curd\command\curd',
    ],
~~~

##使用

###查看命令是否存在

>php think

~~~
make
  make:command          Create a new command class
  make:controller       Create a new resource controller class
  make:curd             Creat a new curd class
  make:curd-controller  Creat a new controller class
  make:curd-model       Creat a new model class
  make:curd-service     Creat a new service class
  make:curd-validate    Creat a new validate class

~~~

会多出curd的命令

###实际使用

>php think make:curd admin@News prefix_news

会生成admin应用下的 NewsController、NewsService、NewsModel、NewsValidate,注意这里的service层只是单纯的逻辑层。

也可以单独生成每个文件，先写这些吧，有人用再写
