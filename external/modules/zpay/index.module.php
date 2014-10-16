<?php

class ZpayModule extends IndexbaseModule
{
    function __construct()
    {
        $this->ZpayModule();
    }

    function ZpayModule()
    {
        parent::__construct();		
        $this->mod_epay =& m('zpay');
		$this->mod_epaylog =& m('zpaylog');
		$this->mod_order =& m('sys_order');
		
    }
	

	function index()
	{
	   
	
	}
	
	function installauto()
	{
	  include "listdir.php";
	    $curpath="/external/modules/".MODULE."/source/";
		$path=ROOT_PATH.$curpath;
		
		$list=listDir($path);
		$srcpath="\$src=ROOT_PATH.\"/external/modules/\".\$_GET['id'].\"/source/";
		$tarpath="\$des=ROOT_PATH.\"/themes/mall/\".Conf::get('template_name').\"/";

		foreach($list as $f)
		{
		  if(!is_array($f))
		  {
		    if(stripos(".model.php",$f))
			{
			 $tarpath="\$des=ROOT_PATH.\"/includes/models/";
			}elseif(stripos(".app.php",$f))
			{
			$tarpath="\$des=ROOT_PATH.\"/app/";
			
			}elseif(stripos(".lang.php",$f))
			{
			$tarpath="\$des=ROOT_PATH.\"/languages/\".LANG.\"/";
			
			}
		   echo $srcpath.$f."\";<br>";
		   echo $tarpath.$f."\";<br>";
		   echo "copy(\$src,\$des);<br>";
		  }
		
		}	
	
	}
	
	function uninstallauto()
	{
	
	 include "listdir.php";
	    $curpath="/external/modules/".MODULE."/source/";
		$path=ROOT_PATH.$curpath;
		
		$list=listDir($path);
		$tarpath="\$del[]=ROOT_PATH.\"/themes/mall/\".Conf::get('template_name').\"/";

		foreach($list as $f)
		{
		  if(!is_array($f))
		  {
		    if(stripos(".model.php",$f))
			{
			 $tarpath="\$del[]=ROOT_PATH.\"/includes/models/";
			}elseif(stripos(".app.php",$f))
			{
			$tarpath="\$del[]=ROOT_PATH.\"/app/";
			
			}elseif(stripos(".lang.php",$f))
			{
			$tarpath="\$del[]=ROOT_PATH.\"/languages/\".LANG.\"/";
			
			}
		   echo $tarpath.$f."\";<br>";
		  }
		
		}	
	}
	
	function installtemplate()
	{
	 include "listdir.php";
	    $curpath="/external/modules/".MODULE."/source/template/";
		$path=ROOT_PATH.$curpath;
		
		$list=listDir($path);
		$srcpath="\$src=ROOT_PATH.\"/external/modules/\".\$_GET['id'].\"/source/template/";
		$tarpath="\$des=ROOT_PATH.\"/themes/mall/\".Conf::get('template_name').\"/";

		foreach($list as $f)
		{
		  if(!is_array($f))
		  {
		   echo $srcpath.$f."\";<br>";
		   echo $tarpath.$f."\";<br>";
		   echo "copy(\$src,\$des);<br>";
		  }
		
		}	
	
	}
	function unstalltemplate()
	{
	   include "listdir.php";
	    $curpath="/external/modules/".MODULE."/source/template/";
		$path=ROOT_PATH.$curpath;		
		$list=listDir($path);	
		$tarpath="\$del[]=ROOT_PATH.\"/themes/mall/\".Conf::get('template_name').\"/";
		foreach($list as $f)
		{
		  if(!is_array($f))
		  {		 
		   echo $tarpath.$f."\";<br>";
		  }
		
		}	
	
	}
   


	    
			
}
?>