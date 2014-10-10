<?php

return array(
    'id'    => 'rocetads',
    'name'  => Lang::get('second_kill'),
    'desc'  => Lang::get('second_kill_desc'),
    'version'   => '1.0',
	// 作者
	'author' => 'Rocet',

	// 模块管理菜单，可以是多个，该菜单将被显示在后台模块管理列表对应的模块项中
    'menu'  => array(
        array(
        	// 菜单显示文字
            'text'  => Lang::get('datacall_manage'),
        	//后台控制器 (admin.module.php) 的 act 参数值
            'act'   => 'set_start_time',
        ),
    ),
);

?>
