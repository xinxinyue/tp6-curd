# tp6-curd
基于thinkphp6框架多应用模式的命令行工具,根据数据表生成对应得增删改查API

## 部署

### composer安装
>composer require xinxinyue/tp-api-curd

安装完成后会新增配置文件curd.php

~~~
<?php
/**
 * File config.php
 */

return [
    'stub_path'     => false,		//自定义模板路径  复制src/stubs/ 下的模板并修改为自己的模板

    'model' => [
        'delete_filed' => 'delete_time',	//软删除字段
        'create_field' => 'create_time',	//创建时间字段
        'update_field' => 'update_time',	//修改时间字段
        'auto_timestamp' => 'int',	//自动完成字段类型
    ],
    'service' => [
        'admin_event' => false,	//是否添加管理员日志事件--自用
    ],
    'response_code' => [
        'success' => 1000,
        'validate_error' => 1001,
        'not_login' => 1002,
        'method_error' => 1003,
        //自由拓展
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

## 使用

### 查看命令是否存在

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

### 实际使用

>php think make:curd admin@News prefix_news

以上命令会生成admin应用下的 NewsController、NewsService、NewsModel、NewsValidate,注意这里的service层只是单纯的逻辑层。

‘prefix_news’ 修改为带前缀的表名, admin修改为应用名 News修改为controller、serviced等名称

也可以单独生成每个文件,例只希望生成test_news表的model，生成到index应用下，可以使用以下命令:

>php think make:curd-model index@News test_news

在单独生成controller和service的时候需要分别制定对应的service和model

单独生成controller:
>php think make:curd-controller index@News test_news --require app\index\service\NewsService

'app\index\service\NewsService' 为需要依赖的service命名空间

单独生成service:
>php think make:curd-service index@News test_news --require app\index\model\NewsModel

'app\index\service\NewsModel' 为需要依赖的model命名空间

model和validate则不需要依赖