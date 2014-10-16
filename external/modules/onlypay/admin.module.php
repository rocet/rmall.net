<?php

class OnlypayModule extends AdminbaseModule
{

    var $user_id=0;
    function __construct()
    {
        $this->OnlypayModule();
    }

    function OnlypayModule()
    {
       parent::__construct();
       $this->user_id = 0;    
         
    }

	
    function index()
    {
 
       $conditions="";
      $s_time=$_GET["add_time_from"];
	  $e_time=$_GET["add_time_to"];
	  $s_money=$_GET["order_amount_from"];
	  $e_money=$_GET["order_amount_to"];
	  $s_time?$conditions.=" add_time >".gmstr2time($s_time)." and ":"";
	  $e_time?$conditions.=" add_time <".gmstr2time($e_time)." and ":"";
	  $s_money?$conditions.=" order_amount >".$s_money." and ":"";
	  $s_money?$conditions.=" order_amount <".$s_money." and ":"";
	      $conditions.=" 1=1 ";
	       $order_mod=&m("sys_order");

		   $page = $this->_get_page();
		   	 $result=$order_mod->find(array(
            'conditions' => $conditions,          
            'count' => true,
			'order'=>'order_id desc',
			 'limit'=>$page['limit'],
        ));
	     $page['item_count'] = $order_mod->getCount();
	    $this->assign("order_list",$result);
         $this->_format_page($page);
        $this->assign('page_info', $page);
        $export_url="?module=onlypay&act=export&add_time_from=$s_time&add_time_to=$e_time&order_amount_from=$s_money&order_amount_to=$e_money";
        $this->assign('export_url',$export_url);
          $this->display('pay_center_order.html');
    }
	
	function pay()
	{
	  $order_id=isset($_GET['order_id'])?$_GET['order_id']:0;
	  if(!$order_id)
	  {
	    $this->show_warning('order_id_not_is_empty');
	    return;
	  }
	   $order=&m("sys_order");
	  $log=&m("zpaylog");
	  $order_info=$order->get_info($order_id);
	  if(!$order_info){
	   $this->show_warning('order_id_not_exists');
	    return;
	  }
	
	  $re=$order->edit('order_id='.$order_id,array('status'=>40));	  
	  if(!$re){
	   $this->show_warning('status_set_is_fail');
	    return;
	  }
	 
		
	  $logdata=array( 'admin_time'=>time(),'states'=>61);
	  $relog=$log->edit(' order_id='.$order_id,$logdata);
	  if(!$relog)
	  {
	   $this->show_warning('status_set_is_fail');
	    return;	  
	  }
	  $this->show_message('edit_ok','back_list','index.php?module=onlypay&act=index');
	}

	function export()
	{
		
	  $conditions="";
      $s_time=$_GET["add_time_from"];
	  $e_time=$_GET["add_time_to"];
	  $s_money=$_GET["order_amount_from"];
	  $e_money=$_GET["order_amount_to"];
	  $s_time?$conditions.=" add_time >".gmstr2time($s_time)." and ":"";
	  $e_time?$conditions.=" add_time <".gmstr2time($e_time)." and ":"";
	  $s_money?$conditions.=" order_amount >".$s_money." and ":"";
	  $s_money?$conditions.=" order_amount <".$s_money." and ":"";
	      $conditions.=" 1=1 ";

          $order_mod=&m("sys_order");


		   $page = $this->_get_page();
		   	 $result=$order_mod->find(array(
            'conditions' => " ".$conditions,          
            'count' => true,
			'order'=>'order_id desc',
			
        ));
				 

           $data=array();
		foreach($result as $v)
		{
          $da=array();
		  $da['order_id']=$v['order_id'];
		  $da['order_sn']=$v['order_sn'];
		  $da['buyer_name']=$v['buyer_name'];
		  $da['add_time']=local_date("Y-m-d H:i:s",$v['add_time']);
		  $da['payment_name']=$v['payment_name'];
		  $da['goods_amount']=$v['goods_amount'];
		  $da['order_amount']=$v['order_amount'];
		   if($v['status']=='40')
			{
			   $da['status']= LANG::get('pay_success');
			}else{
              $da['status']=LANG::get('pay_failure');
			}
		
		  $da['other']="";
		  $data[]=$da;
		}
		$fields=array(LANG::get('pay_order_id'),LANG::get('pay_order_sn'),LANG::get('pay_user_name'),LANG::get('pay_order_time'),LANG::get('pay_payment_name'),LANG::get('pay_goods_amount'),LANG::get('pay_order_amount'),LANG::get('pay_status'),LANG::get('pay_other'));
		$this->exportExcel($data,$fields);

	}
	
	
	function exportExcel($data,$fields=array(),$file_name = 'export')
	{		 
		 if(is_array($fields) && count($fields)>0)
		 {
			 header('Content-Type: text/xls'); 
			 header ( "Content-type:application/vnd.ms-excel;charset=utf-8" );
			 $str = mb_convert_encoding($file_name, 'utf-8', 'gbk');   
			 header('Content-Disposition: attachment;filename="' .$str . '.xls"');      
			 header('Cache-Control:must-revalidate,post-check=0,pre-check=0');        
			 header('Expires:0');         
			 header('Pragma:public');
			 
			 $table_data = '<table border="1">'; 
			 $table_data .= '<th>';
            foreach($fields as $f)
			 {
               $table_data .='<td>'.$f.'</td>';
			 }
		  
		     $table_data .='</th>';
			 foreach ($data as $line)         
			 {
				  $table_data .= '<tr>';
				   $table_data .= '<td>&nbsp;</td>';
				  foreach ($line as $key => &$item)
				  {
					  
				   //$item = mb_convert_encoding($item, 'utf-8', 'gbk'); 	  
				   $table_data .= '<td>' . $item . '&nbsp;</td>';
				  }
				  $table_data .= '</tr>';
			 }
		     $table_data .='</table>';
		     echo $table_data;    
		     die();
		}else{

            echo "标题不能为空!";
			exit;
		}
	}

	function getRecordByFields($data,$fields=array())
	{
        if(is_array($data) && count($data)>0)
		{
			$new_data=array();

           foreach($data as $k=>$d)
			{
                if(is_array($d))
				{
				   $new_tmp_data=array();
                   foreach($fields as $f)
					{
					   if(isset($d[$f]))
						{
                         $new_tmp_data[$f]=$d[$f];
						}
					}
					$new_data[]=$new_tmp_data;

				}else{
					foreach($fields as $f)
					{
                        if($f==$k)
						{
                           $new_data[$k]=$d;
						   break;
						}
					}
				}

			}
			return $new_data;

		}else{
           return false;
		}

	}
  
}

?>