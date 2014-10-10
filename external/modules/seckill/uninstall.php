<?php

/**
 * 这里可以放一些卸载模块时需要执行的代码，比如删除表，删除目录、文件之类的
 */

$filename = ROOT_PATH . '/data/seckill.inc.php';
if (file_exists($filename))
{
    @unlink($filename);
}
$db=&db();
$db->query("DROP TABLE ".DB_PREFIX."seckill");
$db->query("DROP TABLE ".DB_PREFIX."seckill_subject");
?>