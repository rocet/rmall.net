<?php 
/* 函数 listDir( $dirName = null ) 
** 功能 列出目录下所有文件及子目录 
** 参数 $dirName 目录名称 
** 返回 目录结构数组 false为失败 
*/ 
function listDir( $dirName = null ) 
{ 
if( empty( $dirName ) ) 
exit( "IBFileSystem: directory is empty." ); 
if( is_dir( $dirName ) ) 
{ 
if( $dh = opendir( $dirName ) ) 
{ 
$tree = array(); 
while( ( $file = readdir( $dh ) ) !== false ) 
{ 
if( $file != "." && $file != ".." ) 
{ 
$filePath = $dirName . "/" . $file; 
if( is_dir( $filePath ) ) //为目录,递归 
{ 
$tree[$file] = listDir( $filePath ); 
} 
else //为文件,添加到当前数组 
{ 
$tree[] = $file; 
} 
} 
} 
closedir( $dh ); 
} 
else 
{ 
exit( "IBFileSystem: can not open directory $dirName."); 
} 
//返回当前的$tree 
return $tree; 
} 
else 
{ 
exit( "IBFileSystem: $dirName is not a directory."); 
} 
} 

?>
