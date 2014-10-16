<?php

/**
 * 这里可以放一些安装模块时需要执行的代码，比如新建表，新建目录、文件之类的
 */

/* 下面的代码不是必需的，只是作为示例 */
$db=&db();
$db->query("DROP TABLE IF EXISTS `".DB_PREFIX."sys_order`;");
$db->query("CREATE TABLE `".DB_PREFIX."sys_order` (
  `order_id` int(10) unsigned NOT NULL auto_increment,
  `order_sn` varchar(20) NOT NULL default '',
  `type` varchar(10) NOT NULL default 'material',
  `extension` varchar(10) NOT NULL default '',
  `seller_id` int(10) unsigned NOT NULL default '0',
  `seller_name` varchar(100) default NULL,
  `buyer_id` int(10) unsigned NOT NULL default '0',
  `buyer_name` varchar(100) default NULL,
  `buyer_email` varchar(60) NOT NULL default '',
  `status` tinyint(3) unsigned NOT NULL default '0',
  `add_time` int(10) unsigned NOT NULL default '0',
  `payment_id` int(10) unsigned default NULL,
  `payment_name` varchar(100) default NULL,
  `payment_code` varchar(20) NOT NULL default '',
  `out_trade_sn` varchar(20) NOT NULL default '',
  `pay_time` int(10) unsigned default NULL,
  `pay_message` varchar(255) NOT NULL default '',
  `ship_time` int(10) unsigned default NULL,
  `invoice_no` varchar(255) default NULL,
  `finished_time` int(10) unsigned NOT NULL default '0',
  `goods_amount` decimal(10,2) unsigned NOT NULL default '0.00',
  `discount` decimal(10,2) unsigned NOT NULL default '0.00',
  `order_amount` decimal(10,2) unsigned NOT NULL default '0.00',
  `evaluation_status` tinyint(1) unsigned NOT NULL default '0',
  `evaluation_time` int(10) unsigned NOT NULL default '0',
  `anonymous` tinyint(3) unsigned NOT NULL default '0',
  `postscript` varchar(255) NOT NULL default '',
  `pay_alter` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`order_id`),
  KEY `order_sn` (`order_sn`,`seller_id`),
  KEY `seller_name` (`seller_name`),
  KEY `buyer_name` (`buyer_name`),
  KEY `add_time` (`add_time`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;");
//文件拷贝


$src=ROOT_PATH."/external/modules/onlypay/source/sys_order.model.php";
$des=ROOT_PATH."/includes/models/sys_order.model.php";
copy($src,$des);
$src=ROOT_PATH."/external/modules/onlypay/source/cashier_sys.payform.html";
$des=ROOT_PATH."/themes/mall/".Conf::get('template_name')."/cashier_sys.payform.html";
copy($src,$des);
$src=ROOT_PATH."/external/modules/onlypay/source/cashier_sys.payment.html";
$des=ROOT_PATH."/themes/mall/".Conf::get('template_name')."/cashier_sys.payment.html";
copy($src,$des);

$src=ROOT_PATH."/external/modules/onlypay/source/cashier_sys.app.php";
$des=ROOT_PATH."/app/cashier_sys.app.php";
copy($src,$des); 
$src=ROOT_PATH."/external/modules/onlypay/source/cashier_sys.lang.php";
$des=ROOT_PATH."/languages/".LANG."/cashier_sys.lang.php";
copy($src,$des);
$src=ROOT_PATH."/external/modules/onlypay/source/syspaynotify.app.php";
$des=ROOT_PATH."/app/syspaynotify.app.php";
copy($src,$des); 
$src=ROOT_PATH."/external/modules/onlypay/source/syspaynotify.lang.php";
$des=ROOT_PATH."/languages/".LANG."/syspaynotify.lang.php";
copy($src,$des);
$src=ROOT_PATH."/external/modules/onlypay/source/syspaynotify.index.html";
$des=ROOT_PATH."/themes/mall/".Conf::get('template_name')."/syspaynotify.index.html";
copy($src,$des);

?>