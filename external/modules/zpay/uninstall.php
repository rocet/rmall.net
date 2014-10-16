<?php

/**
 * 这里可以放一些卸载模块时需要执行的代码，比如删除表，删除目录、文件之类的
 */
$db=&db();
$db->query("DROP TABLE ".DB_PREFIX."zpay");
$db->query("DROP TABLE ".DB_PREFIX."zpaylog");
$del=array();
$del[]=ROOT_PATH."/includes/models/zpay.model.php";
$del[]=ROOT_PATH."/includes/models/zpaylog.model.php";
$del[]=ROOT_PATH."/languages/".LANG."/zpay.lang.php";
$del[]=ROOT_PATH."/app/zpay.app.php";
$del[]=ROOT_PATH."/themes/mall/".Conf::get('template_name')."/epay.czlist.html";
$del[]=ROOT_PATH."/themes/mall/".Conf::get('template_name')."/epay.czlog.html";
$del[]=ROOT_PATH."/themes/mall/".Conf::get('template_name')."/epay.editpassword.html";
$del[]=ROOT_PATH."/themes/mall/".Conf::get('template_name')."/epay.logall.html";
$del[]=ROOT_PATH."/themes/mall/".Conf::get('template_name')."/epay.out.html";
$del[]=ROOT_PATH."/themes/mall/".Conf::get('template_name')."/epay.outlog.html";
$del[]=ROOT_PATH."/themes/mall/".Conf::get('template_name')."/epay.set.html";
$del[]=ROOT_PATH."/themes/mall/".Conf::get('template_name')."/epay.txlist.html";
$del[]=ROOT_PATH."/themes/mall/".Conf::get('template_name')."/epay.txlog.html";
$del[]=ROOT_PATH."/themes/mall/default/styles/default/css/epay.css";
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