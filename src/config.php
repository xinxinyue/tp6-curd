<?php
/**
 * File config.php

 * @Time: 2021/3/4   14:01
 */

return [
    'stub_path'     => false,       //模板路径 复制src/stubs/ 下的模板并修改为自己的模板

    'model' => [
        'delete_filed' => 'delete_time',
        'create_field' => 'create_time',
        'update_field' => 'update_time',
        'auto_timestamp' => 'int',
    ],
    'service' => [
        'admin_event' => true,
    ],
    'response_code' => [
        'success' => 1000,
        'validate_error' => 1001,
        'not_login' => 1002,
        'method_error' => 1003,
        //自由拓展
    ]

];