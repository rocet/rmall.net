<?php
class Sys_orderModel extends BaseModel
{

    var $table  = 'sys_order';
    var $alias  = 'sys_order';
    var $prikey = 'order_id';

    function add_order($buyer_id,$buyer_name,$goods_price,$order_price,$discount,$payment_id="",$seller_id=0,$seller_name="")
	{
		$pay_mod=&m("payment");
		$p=array();
		if($payment_id!="" && $payment_id>0)
		{
		 $p=$pay_mod->get($payment_id);
		}else{
		$p['payment_name']="";
		$p['payment_code']="";
		}
		$data=array(
				'order_sn'=>gmtime()."".rand(1000,9999),
				'type'=>'material',
				'extension'=>'normal',
				'seller_id'=>$seller_id,
				'seller_name'=>$seller_name,
				'buyer_id'=>$buyer_id,
				'buyer_name'=>$buyer_name,
				'buyer_email'=>'',
				'status'=>'11',
				'add_time'=>gmtime(),
				'payment_id'=>$payment_id,
				'payment_name'=>$p['payment_name'],
				'payment_code'=>$p['payment_code'],
				'out_trade_sn'=>'',
				'pay_time'=>'',
				'pay_message'=>'',
				'ship_time'=>'',
				'invoice_no'=>'',
				'finished_time'=>'0',
				'goods_amount'=>$goods_price,
				'discount'=>$discount,
				'order_amount'=>$order_price,
				'evaluation_status'=>'0',
				'evaluation_time'=>'0',
				'anonymous'=>'0',
				'postscript'=>'',
				);

		$id=$this->add($data);
		return $id;
		
	}
}

?>