<?php

/**
 * 店铺地址简写插件
 *
 * @return  array
 */
class Cashier_zpay_paymentPlugin extends BasePlugin
{
    function execute()
    {
		 if($_GET['app']!="cashier")
		 {
		    return;
		 }
		    $order_id=isset($_GET['order_id'])?intval($_GET['order_id']):0;
			if(!$order_id){return ;}
		    $usefull_payment_code=array('zpay');//选择验证的支付方式 ,设定几种系统自定义支付
			$payment_model =& m('payment');
			$payment_way=$payment_model->get_enabled(0); //得到系统统一的所有支付方式
			$payment_usefull=array();
			foreach($payment_way as $pay)
			{
			  if(in_array(trim($pay['payment_code']),$usefull_payment_code))  //根据设定的全局自定义支付方式，得到可用的支付
			  {
			   $payment_usefull[trim($pay['payment_code'])]=$pay;
			  }
			}
			$order=& m('order');
			$order_data=$order->get_info($order_id);
			if(!$order_data)return;
			$seller_id=$order_data['seller_id'];
			$payments_zpay = $payment_model->find(array('conditions'=>" payment_code in (".$this->get_str($usefull_payment_code).") and store_id=".$seller_id.""));
			 
			if(!$payments_zpay)
			{
				  foreach($payment_usefull as $usefull)
				  {
					 unset($usefull['payment_id']);
					 $usefull['store_id']=$seller_id;
					 $payment_model->add($usefull);			  
				  }
			}else{			   
				     foreach($payments_zpay as $pay)
					 {
						$curr_payment_code=$pay['payment_code'];
						$curr_pay=isset($payment_usefull[$curr_payment_code])?$payment_usefull[$curr_payment_code]:null;			
						if($curr_pay && $curr_pay['config']!=$pay['config'])
						{
						 
						  unset($curr_pay['payment_id']);
						  $curr_pay['store_id']=$seller_id;
						  $curr_pay['config']=unserialize($curr_pay['config']);
						  $payment_id=$pay['payment_id'];
						  $payment_model->edit($payment_id,$curr_pay);					
						}
					 }		
				 
				}				 
			}			
			//$payments_zpay = $payment_model->get(array('conditions'=>" payment_code='zpay'"));
			 
			// if($mb=='cashier.payment.html')
			// {
				// $payment_model =& m('payment');
				// $payments_zpay = $payment_model->get(array('conditions'=>" payment_code='zpay'"));
				// if (empty($payments_zpay))
				// {
					// $this->show_warning('store_no_payment');

					// return;
				// }
	           // $re= $this->getAssign('payments');
			   // $re['online'][]=$payments_zpay;
			    // $this->assign('payments',$re);
				
				// $this->data['display_file']='cashier.payment.html';
				
				
			
			// }
    
	
	function get_str($arr)
	{
	  $str="";
	  foreach($arr as $av)
	  {
	    if($str=="")
		{
	    $str.="'".$av."'";
		}else{
		$str.=",'".$av."'";
		
		}
	  
	  }
	  return $str;
	
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