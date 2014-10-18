<?php

class Cashier_sysApp extends ShoppingbaseApp
{
/**
	 *    根据提供的订单信息进行支付
	 *
	 *    @author    Garbin
	 *    @param    none
	 *    @return    void
	 */
	function index()
	{
		/* 外部提供订单号 */
		$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
		
		 if (!$order_id)
		 {
		 $this->show_warning('no_such_order');

		 return;
		 }
		/* 内部根据订单号收银,获取收多少钱，使用哪个支付接口 */
		/*  $order_model =& m('order');
		 $order_info  = $order_model->get("order_id={$order_id} AND buyer_id=" . $this->visitor->get('user_id'));
		 if (empty($order_info))
		 {
		 $this->show_warning('no_such_order');

		 return;
		 }*/
		$order_info=$this->get_orders($order_id);
		$payment_model =& m('payment');
		if (!$order_info['payment_id'])
		{
			/* 若还没有选择支付方式，则让其选择支付方式 */
			$payments = $payment_model->get_enabled($order_info['seller_id']);
			if (empty($payments))
			{
				$this->show_warning('store_no_payment');

				return;
			}

			$all_payments = array('online' => array(), 'offline' => array());
			foreach ($payments as $key => $payment)
			{
				if ($payment['is_online'])
				{
					$all_payments['online'][] = $payment;
				}
				else
				{
					$all_payments['offline'][] = $payment;
				}
			}
			$order_info['order_id']=$order_id;

			$this->assign('order', $order_info);
			$this->assign('payments', $all_payments);
			$this->_curlocal(
			LANG::get('cashier')
			);

			$this->assign('page_title', Lang::get('confirm_payment') . ' - ' . Conf::get('site_title'));
			$this->display('cashier_sys.payment.html');
		}
		else
		{
			/* 否则直接到网关支付 */
			/* 验证支付方式是否可用，若不在白名单中，则不允许使用 */
			if (!$payment_model->in_white_list($order_info['payment_code']))
			{
				$this->show_warning('payment_disabled_by_system');

				return;
			}

			$payment_info  = $payment_model->get("payment_code = '{$order_info['payment_code']}' AND store_id={$order_info['seller_id']}");
			/* 若卖家没有启用，则不允许使用 */
			if (!$payment_info['enabled'])
			{
				$this->show_warning('payment_disabled');

				return;
			}
            $order_info['order_id']="v_".$order_info['order_id'];
			/* 生成支付URL或表单 */
			$payment    = $this->_get_payment($order_info['payment_code'], $payment_info);
			$payment_form = $payment->get_payform($order_info);

			 

			/* 线下付款的 */
			if (!$payment_info['online'])
			{
				$this->_curlocal(
				Lang::get('post_pay_message')
				);
			}

			/* 跳转到真实收银台 */
			$this->assign('page_title', Lang::get('cashier'));
			$this->assign('payform', $payment_form);
			$this->assign('payment', $payment_info);
			$this->assign('order', $order_info);
			header('Content-Type:text/html;charset=' . CHARSET);
			$this->display('cashier_sys.payform.html');
		}
	}




	/**
	 *    确认支付
	 *
	 *    @author    Garbin
	 *    @return    void
	 */
	function goto_pay()
	{
		$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
		$payment_id = isset($_POST['payment_id']) ? intval($_POST['payment_id']) : 0;
		/*if (!$order_id)
		 {
		 $this->show_warning('no_such_order');

		 return;
		 }*/
		 
		if (!$payment_id)
		{
			$this->show_warning('no_such_payment');

			return;
		}
		$order_info=$this->get_orders($order_id);
		$order_info['order_id']=$order_id;
		
		if (empty($order_info))
		{
			$this->show_warning('no_such_order');

			return;
		}

	
        
		/* 验证支付方式 */
		$payment_model =& m('payment');
		$payment_info  = $payment_model->get($payment_id);
		if (!$payment_info)
		{
			$this->show_warning('no_such_payment');

			return;
		}

		/* 保存支付方式 */
		$edit_data = array(
            'payment_id'    =>  $payment_info['payment_id'],
            'payment_code'  =>  $payment_info['payment_code'],
            'payment_name'  =>  $payment_info['payment_name'],
		);

		/* 如果是货到付款，则改变订单状态 */
		if ($payment_info['payment_code'] == 'cod')
		{
			$edit_data['status']    =   ORDER_SUBMITTED;
		}

		 
		$order_info['payment_id']=$payment_info['payment_id'];
		$order_info['payment_code']=$payment_info['payment_code'];
		$order_info['payment_name']=$payment_info['payment_name'];
		$this->save_orders($order_info);
		// $order_model->edit($order_id, $edit_data);

		/* 开始支付 */
		$this->_goto_pay($order_id);
	}

	/**
	 *    线下支付消息
	 *
	 *    @author    Garbin
	 *    @return    void
	 */
	function offline_pay()
	{
		if (!IS_POST)
		{
			return;
		}
		$order_id       = isset($_GET['order_id']) ? intval(str_replace("v_","",$_GET['order_id'])) : 0;
		$pay_message    = isset($_POST['pay_message']) ? trim($_POST['pay_message']) : '';
		/* if (!$order_id)
		 {
		 $this->show_warning('no_such_order');
		 return;
		 }*/
		if (!$pay_message)
		{
			$this->show_warning('no_pay_message');

			return;
		}
		
		$order_info=$this->get_orders($order_id);
		if (empty($order_info))
		{
			$this->show_warning('no_such_order');

			return;
		}
		$edit_data = array(
            'pay_message' => $pay_message
		);


		$order_info['order_id']=$order_id;
		$order_info['pay_message']=$edit_data['pay_message'];
		$order_info['status']= ORDER_FINISHED;
		$order_info['finished_time'] =gmtime();
		$this->save_orders($order_info);
        header('Location:index.php?app=cashier_sys&act=offline_finish_order&order_id=' . $order_id);
		
	}

	function offline_finish_order()
	{
	  $order_id   = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0; //哪个订单
        if (!$order_id)
        {
            /* 无效的通知请求 */
            $this->show_warning('forbidden');

            return;
        }
      
        /* 获取订单信息 */
        $order_mod=&m("sys_order");
        $order=$order_mod->get($order_id);
        if (empty($order))
        {
            /* 没有该订单 */
            $this->show_warning('no_order');

            return;
        }
		 
		$rd=$order_mod->get(array('conditions'=>' order_id='.$order_id.' and status=11'));
		if(!$rd)
		{		
			$re=$order_mod->edit(" order_id=".$order_id,array('status'=>11));
			if(!$re)
			{
			  $this->show_message($order_mod->get_error(), 'index.php?app=zpay&act=czlist');
				return;		
			}
		}
		
     /* 线下支付完成并留下pay_message,发送给卖家付款完成提示邮件 */
		$model_member =& m('member');
		$seller_info   = $model_member->get($order_info['seller_id']);
		$mail = get_mail('toseller_offline_pay_notify', array('order' => $order_info, 'pay_message' => $pay_message));
		$this->_mailto($seller_info['email'], addslashes($mail['subject']), addslashes($mail['message']));

		$this->show_message('pay_message_successed',
            'view_order',   'index.php?app=zpay&act=offline_finish_order&order_id='.$order_id,
            'close_window', 'javascript:window.close();');
	
	}
	function finish_order()
	{
		

		$order_id   = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0; //哪个订单
        if (!$order_id)
        {
            /* 无效的通知请求 */
            $this->show_warning('forbidden');

            return;
        }
      
        /* 获取订单信息 */
        $order_mod=&m("sys_order");
        $order=$order_mod->get($order_id);
        if (empty($order))
        {
            /* 没有该订单 */
            $this->show_warning('no_order');

            return;
        }
		 
		$rd=$order_mod->get(array('conditions'=>' order_id='.$order_id.' and status=11'));
		if(!$rd)
		{		
			$re=$order_mod->edit(" order_id=".$order_id,array('status'=>11));
			if(!$re)
			{
			  $this->show_message($order_mod->get_error(), 'index.php?app=zpay&act=czlist');
				return;		
			}
		}
		
     /* 线下支付完成并留下pay_message,发送给卖家付款完成提示邮件 */
		$model_member =& m('member');
		$seller_info   = $model_member->get($order_info['seller_id']);
		$mail = get_mail('toseller_offline_pay_notify', array('order' => $order_info, 'pay_message' => $pay_message));
		$this->_mailto($seller_info['email'], addslashes($mail['subject']), addslashes($mail['message']));

		$this->show_message('pay_message_successed',
            'view_order',   'index.php?app=zpay&act=czlist',
            'close_window', 'javascript:window.close();');
	
	}


	function _goto_pay($order_id)
	{
		header('Location:index.php?app=cashier_sys&order_id=' . $order_id);
	}
	function get_orders($order_id=0)
	{
		 
		$order=&m("sys_order");
		$data=null;
		if($order_id>0)
		{
            $data=$order->get(array('conditions'=>" order_id=".$order_id));
		    return $data;
		}
		return $data;

         
	}
	function save_orders($order_info)
	{
		$order=&m("sys_order");
		if(isset($order_info['order_id'])&&$order_info['order_id']>0)
		{
           $res=$order->edit('order_id='.$order_info['order_id'],$order_info);		
			if (!$res)
			{
				$this->show_message($order->get_error(), APP.'_'.ACT);
				return;
			}		   
		}
		 
	}
}
?>