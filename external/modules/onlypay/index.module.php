<?php

class OnlypayModule extends IndexbaseModule
{


    function __construct()
    {
        $this->OnlypayModule();
    }

    function OnlypayModule()
    {
        parent::__construct();
       
    }

    function index()
    {
      $this->display("onlypay.index.html");
    }
	
	function goto_pay()
	{
	  $goods_price=isset($_POST['goods_price']) && intval(trim($_POST['goods_price']))>0 ?$_POST['goods_price']:0;
	  $order_price=isset($_POST['order_price']) && intval(trim($_POST['order_price']))>0 ?$_POST['order_price']:$goods_price;
	  $payment_id=isset($_POST['payment_id']) && intval(trim($_POST['payment_id']))>0 ?$_POST['payment_id']:0;
	  if($goods_price>0 && $order_price>0 && $payment_id>0 )
	  {
	     $this->_goto_pay($goods_price,$order_price,$payment_id);	  
	  }	
	}
	
	function _goto_pay($goods_price,$order_price,$payment_id)
	{
	  if(isset($this->visitor) && $this->visitor->get('user_id')>0)
	  {
		  $model=&m("sys_order");
		  $order_id=$model->add_order($this->visitor->get('user_id'),$this->visitor->get('user_name'),$goods_price,$order_price,0,$payment_id);
		  header('location:index.php?app=cashier_sys&order_id='.$order_id); 
	  }else{
	       echo "非法支付";
		   exit;
	  }
	
	
	}

   


}

?>
