<?php

/**
 *    邮局汇款
 *
 *    @author    Garbin
 *    @usage    none
 */
class ZpayPayment extends BasePayment
{
    var $_code = 'zpay';
	var $_gateway='/index.php?app=zpay&act=do_pay';


	function verify_notify($order_info)
	{
	      if (empty($order_info))
			{
				$this->_error('order_info_empty');
				return false;
			}
			//=========================== 把商家的相关信息返回去 =======================
			
			
			//接收组件的加密
			$OrderInfo	=	$_POST['OrderMessage'];			//订单加密信息

			$signMsg 	=	$_POST['Digest'];				//密匙
			//接收新的md5加密认证
			
			//检查签名
			$key =  $this->_config['zpay_key'];   //<--支付密钥--> 注:此处密钥必须与商家后台里的密钥一致
			//$digest = $MD5Digest->encrypt($OrderInfo.$key);
			$digest = strtoupper(md5($OrderInfo.$key));
			if ($digest == $signMsg)
	        {
				//解密
				//$decode = $DES->Descrypt($OrderInfo, $key);
				$OrderInfo = $this->HexToStr($OrderInfo);
				//=========================== 分解字符串 ====================================
				$parm=explode("|", $OrderInfo);
              	
				$pay_info=array();
				$pay_info['order_sn']	=	$parm[0];
			    $pay_info['order_amount']	=	$parm[1];
			    $pay_info['return_url']		=	$parm[2];			
			    $pay_info['pay_date']		=	$parm[3];
			    $pay_info['state']	=	$parm[4];
			    $pay_order=& m("order");
				$use_order=$pay_order->get(array('conditions'=>" order_sn='".$pay_info['order_sn']."'"));
				if (empty($use_order))
				{
						$this->_error('order_info_empty');
						return false;
				}
				if ($pay_info['state'] == 2)
				{
						//echo "支付成功".'<br>';
					//	echo "商家名=".$use_order['seller_name'].'<br>';
						//echo "订单号=".$pay_info['order_sn'].'<br>';
						//echo "金额=".$pay_info['order_amount'].'<br>';
						
					return array(
                       'target'    => ORDER_ACCEPTED,
                         );
					}
				else 
					{
						$this->_error('paying_wrong');
						return false;
					}

			}else{
				 $this->_error('invalid_pay');
			     return false;
			}

		
	}

	function get_payform($order_info)
	{
	        $pay_info=array();
			$pay_info['order_sn']	=	$order_info['order_sn'];
			$pay_info['order_amount']	=	$order_info['order_amount'];
			$pay_info['return_url']		=	$this->_create_return_url($order_info['order_id']);	            			
			$pay_info['pay_date']		=	local_date("Y-m-d H:i:s");
			$pay_info['state']	= 	0;
			$pay_info['notify_url']		=	$this->_create_notify_url($order_info['order_id']);	
	        $OrderInfo = implode("|",$pay_info);          
			//订单信息先转换成HEX，然后再加密
			$key = $this->_config['zpay_key'];     //<--支付密钥--> 注:此处密钥必须与商家后台里的密钥一致
			$OrderInfo = $this->StrToHex($OrderInfo);			
			$digest = strtoupper(md5($OrderInfo.$key));        
				$params = array(
				  'digest'=>$digest,
				  'OrderMessage'=>$OrderInfo,
				);
				
			return $this->_create_payform('POST', $params);
	}

  // 公共函数定义
  function HexToStr($hex)
  {
     $string="";
     for ($i=0;$i<strlen($hex)-1;$i+=2)
         $string.=chr(hexdec($hex[$i].$hex[$i+1]));
     return $string;
  } 
  function StrToHex($string)
  {
     $hex="";
     for ($i=0;$i<strlen($string);$i++)
         $hex.=dechex(ord($string[$i]));
     $hex=strtoupper($hex);
     return $hex;
  }
 

}

?>