<?php

/**
 * 这里可以放一些安装模块时需要执行的代码，比如新建表，新建目录、文件之类的
 */

/* 下面的代码不是必需的，只是作为示例 */
$filename = ROOT_PATH . '/data/rocetads.inc.php';

/**
 * 秒杀配置文件
 * kill_allow 秒杀活动开关
 * goods_qty 秒杀活动总商品个数
 * period 秒杀活动周期，以秒为单位
 * start_date 开始时间
 */


file_put_contents($filename, "<?php return array(
	'kill_allow' => '1',
	'goods_qty' => '0',
	'period' => '30',
	'start_date' => '2:00:00',
); ?>");

$db=&db();

//秒杀商品记录表
$db->query("CREATE TABLE `".DB_PREFIX."rocetads` (                           
               `sec_id` int(10) NOT NULL auto_increment,            
               `goods_name` varchar(225) NOT NULL,                  
               `subject_id` int(10) NOT NULL,                       
               `goods_id` int(10) unsigned NOT NULL,                
               `store_id` int(10) unsigned NOT NULL,                
               `sec_quantity` smallint(5) unsigned NOT NULL,        
               `sec_price` varchar(255) NOT NULL,                   
               `add_time` int(10) NOT NULL,                         
               `start_time` int(10) NOT NULL,                       
               `sec_state` tinyint(3) unsigned NOT NULL,            
               `recommended` tinyint(3) unsigned NOT NULL,          
               `views` int(10) unsigned NOT NULL,                   
               PRIMARY KEY  (`sec_id`)                              
             ) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8");

//秒杀主题表
$db->query("CREATE TABLE `".DB_PREFIX."rocetads_subject` (                      
                       `subject_id` int(10) unsigned NOT NULL auto_increment,  
                       `subject_name` varchar(100) NOT NULL default '',        
                       `subject_desc` varchar(255) NOT NULL,                   
                       `subject_state` tinyint(3) unsigned NOT NULL,           
                       PRIMARY KEY  (`subject_id`)                             
                     ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 ");

?>