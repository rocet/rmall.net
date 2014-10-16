<?php

/**
 * 店铺地址简写插件
 *
 * @return  array
 */
class Sys_pay_notifyPlugin extends BasePlugin
{
    function execute()
    {
        if (defined('IN_BACKEND') && IN_BACKEND === true)
        {
            return; // 后台无需执行
        }
        else
        {
		  if(APP=='paynotify')
		  {
				 if(isset($_REQUEST['order_id']))
				{
					$arr=explode("_",$_REQUEST['order_id']);
					if(count($arr)>1)
					{
					  $id=$arr[1];
					  if($result)
						{							
								   header('location:index.php?app=syspaynotify&order_id='.$id);							  
						}else{  
									 $this->show_warning('pay_empty');
									return;
								}
					}
				}
		  }elseif(APP=='syspaynotify')
		  {
		     if(isset($_REQUEST['order_id']))
				{
					$arr=explode("_",$_REQUEST['order_id']);
					if(count($arr)>1)
					{
						$id=$arr[1];
						$_REQUEST['order_id']=$id;
					}
				}
		  
		  }
			
		}
    }
}

?>