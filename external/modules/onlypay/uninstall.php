<?php

/**
 * 这里可以放一些卸载模块时需要执行的代码，比如删除表，删除目录、文件之类的
 */

$db=&db();
$db->query("DROP TABLE IF EXISTS `".DB_PREFIX."sys_order`;");

$del=array();
$del[]= ROOT_PATH . "/app/cashier_sys.app.php";
$del[]= ROOT_PATH."/includes/models/sys_order.model.php";
$del[]=ROOT_PATH."/themes/mall/".Conf::get('template_name')."/cashier_sys.payform.html";
$del[]=ROOT_PATH."/themes/mall/".Conf::get('template_name')."/cashier_sys.payment.html";
$del[]=ROOT_PATH."/languages/".LANG."/cashier_sys.lang.php";
$del[]=ROOT_PATH."/app/syspaynotify.app.php";
$del[]=ROOT_PATH."/languages/".LANG."/syspaynotify.lang.php";
$del[]=ROOT_PATH."/themes/mall/".Conf::get('template_name')."/syspaynotify.index.html";
rm($del);





function rm($des)
{
	  if(count($des)>0)
	  {
		foreach($des as $dfile)
		{
			if (file_exists($dfile))
			{
				@unlink($dfile);
			}
		}
	  }
}

?>