<?php

/**
 * 店铺地址简写插件
 *
 * @return  array
 */
class Open_member_menuPlugin extends BasePlugin
{
    function execute()
    {
	        $curr_menu= $this->getAssign('_member_menu');
		    $menu_inc=ROOT_PATH."/data/member_menu.inc.php";
			$menu=null;
			if(!file_exists($menu_inc))
			{
		       	$src=ROOT_PATH."/external/plugins/open_member_menu/member_menu.inc.php";
				$des=ROOT_PATH."/data/member_menu.inc.php";
				copy($src,$des);
			}
			$menu=include($menu_inc);
			$parent_menu=array('my_ecmall','im_buyer','im_seller');
			foreach($parent_menu as $parent)
			{
				if(!empty($menu[$parent]))
				{
				  foreach($menu[$parent] as $key=>$menu_item)
				  {
					if(!empty($menu_item))
					{
						if(isset($curr_menu) && !empty($curr_menu) && is_array($curr_menu))
						{
						  $this->edit_menu_item($curr_menu,'my_ecmall',$menu_item['name'],$menu_item['text'],$menu_item['url'],$menu_item['icon']);				
						}
					}
					
				  }
				}
			}
		  $this->assign('_member_menu',$curr_menu);
	        
			
	}
	
	function edit_menu_item(& $menu ,$parent_menu_name,$menu_name,$menu_text,$menu_url,$menu_icon)
	{
	  $menu_item=array('text'=>$menu_text,'url'=>$menu_url,'name'=>$menu_name,'icon'=>$menu_icon);	  
	  $menu[$parent_menu_name]['submenu'][$menu_name]=$menu_item;
	
	}
    
	
	
	function getAssign($key)
	{
	  $app=&cc();
	  $app->_init_view();
	  return $app->_view->_var[$key];
	}
	function assign($key,$value)
	{
	  $app=&cc();
	  $app->assign($key,$value);
	
	}
	
	function display($file)
	{
	  $app=&cc();
	  $app->display($file);
	}
}

?>