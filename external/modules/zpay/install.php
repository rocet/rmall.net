<?php
$db=&db();
$db->query("DROP TABLE IF EXISTS ".DB_PREFIX."zpay");
$db->query("CREATE TABLE ".DB_PREFIX."zpay (

  `id` int(10) unsigned NOT NULL auto_increment,
  `user_id` int(10) unsigned NOT NULL default '0',
  `user_name` varchar(100) NULL,
  `bank_sn` varchar(100) NULL,
  `bank_name` varchar(20) NULL,
  `bank_username` varchar(20) NULL,
  `bank_add` varchar(60) NULL,
  `zf_pass` varchar(32) NULL,
  `money_dj` decimal(10,2) NOT NULL default '0',
  `money` decimal(10,2) NOT NULL default '0',
  `add_time` int(10) unsigned NULL,

  PRIMARY KEY  (id)
) 
 ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;");

$db->query("DROP TABLE IF EXISTS ".DB_PREFIX."zpaylog"); 
$db->query("CREATE TABLE ".DB_PREFIX."zpaylog (

  `id` int(10) unsigned NOT NULL auto_increment,
  `user_id` int(10) NOT NULL default '0',
  `user_name` varchar(50) NULL default '0',
  `order_id` int(10) unsigned NULL,
  `order_sn` varchar(50) NULL default '0',
  `to_id` int(10) unsigned NULL,
  `to_name` varchar(100) NULL,
  `type` int(3) unsigned NOT NULL default '0',
  `states` int(3) unsigned NOT NULL default '0',  
  `money` decimal(10,2) NOT NULL default '0.00',
  `money_zj` decimal(10,2) NOT NULL default '0.00',
  `complete` int(3) unsigned NOT NULL default '0',
  `log_text` varchar(255) NULL,
  `add_time` int(10) unsigned NULL,
  `admin_name` varchar(100) NULL,
  `admin_time` int(10) unsigned NULL,
  
  PRIMARY KEY  (id)
) 
 ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;");
 
$db->query("INSERT INTO ".DB_PREFIX."payment(
`payment_id` ,
`store_id` ,
`payment_code` ,
`payment_name` ,
`payment_desc` ,
`config` ,
`is_online` ,
`enabled` ,
`sort_order`
)
VALUES (
'0', '0', 'epay', '站内余额支付', '余额支付', NULL , '0', '1', '1'
);");
$src=ROOT_PATH."/external/modules/".$_GET['id']."/source/zpay.model.php";
$des=ROOT_PATH."/includes/models/zpay.model.php";
copy($src,$des);
$src=ROOT_PATH."/external/modules/".$_GET['id']."/source/zpaylog.model.php";
$des=ROOT_PATH."/includes/models/zpaylog.model.php";
copy($src,$des);
$src=ROOT_PATH."/external/modules/".$_GET['id']."/source/zpay.app.php";
$des=ROOT_PATH."/app/zpay.app.php";
copy($src,$des); 
$src=ROOT_PATH."/external/modules/".$_GET['id']."/source/zpay.lang.php";
$des=ROOT_PATH."/languages/".LANG."/zpay.lang.php";
copy($src,$des);
$src=ROOT_PATH."/external/modules/".$_GET['id']."/source/template/epay.czlist.html";
$des=ROOT_PATH."/themes/mall/".Conf::get('template_name')."/epay.czlist.html";
copy($src,$des);
$src=ROOT_PATH."/external/modules/".$_GET['id']."/source/template/epay.czlog.html";
$des=ROOT_PATH."/themes/mall/".Conf::get('template_name')."/epay.czlog.html";
copy($src,$des);
$src=ROOT_PATH."/external/modules/".$_GET['id']."/source/template/epay.editpassword.html";
$des=ROOT_PATH."/themes/mall/".Conf::get('template_name')."/epay.editpassword.html";
copy($src,$des);
$src=ROOT_PATH."/external/modules/".$_GET['id']."/source/template/epay.logall.html";
$des=ROOT_PATH."/themes/mall/".Conf::get('template_name')."/epay.logall.html";
copy($src,$des);
$src=ROOT_PATH."/external/modules/".$_GET['id']."/source/template/epay.out.html";
$des=ROOT_PATH."/themes/mall/".Conf::get('template_name')."/epay.out.html";
copy($src,$des);
$src=ROOT_PATH."/external/modules/".$_GET['id']."/source/template/epay.outlog.html";
$des=ROOT_PATH."/themes/mall/".Conf::get('template_name')."/epay.outlog.html";
copy($src,$des);
$src=ROOT_PATH."/external/modules/".$_GET['id']."/source/template/epay.set.html";
$des=ROOT_PATH."/themes/mall/".Conf::get('template_name')."/epay.set.html";
copy($src,$des);
$src=ROOT_PATH."/external/modules/".$_GET['id']."/source/template/epay.txlist.html";
$des=ROOT_PATH."/themes/mall/".Conf::get('template_name')."/epay.txlist.html";
copy($src,$des);
$src=ROOT_PATH."/external/modules/".$_GET['id']."/source/template/epay.txlog.html";
$des=ROOT_PATH."/themes/mall/".Conf::get('template_name')."/epay.txlog.html";
copy($src,$des);
$src=ROOT_PATH."/external/modules/".$_GET['id']."/source/template/styles/default/css/epay.css";
$des=ROOT_PATH."/themes/mall/default/styles/default/css/epay.css";
copy($src,$des);



  
 
?>