<?php

/**
 *    支付网关通知接口
 *
 *    @author    Garbin
 *    @usage    none
 */
class SyspaynotifyApp extends MallbaseApp
{
    /**
     *    支付完成后返回的URL，在此只进行提示，不对订单进行任何修改操作,这里不严格验证，不改变订单状态
     *
     *    @author    Garbin
     *    @return    void
     */
    function index()
    {
        //这里是支付宝，财付通等当订单状态改变时的通知地址
        $order_id   = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0; //哪个订单
        if (!$order_id)
        {
            /* 无效的通知请求 */
            $this->show_warning('forbidden');

            return;
        }

        /* 获取订单信息 */
        $model_order =& m('sys_order');
        $order_info  = $model_order->get($order_id);
        if (empty($order_info))
        {
            /* 没有该订单 */
            $this->show_warning('forbidden');

            return;
        }

        $model_payment =& m('payment');
        $payment_info  = $model_payment->get("payment_code='{$order_info['payment_code']}' AND store_id={$order_info['seller_id']}");
        if (empty($payment_info))
        {
            /* 没有指定的支付方式 */
            $this->show_warning('no_such_payment');

            return;
        }

        /* 调用相应的支付方式 */
        $payment = $this->_get_payment($order_info['payment_code'], $payment_info);
		$order_info['order_id']="v_".$order_id;
        /* 获取验证结果 */
        $notify_result = $payment->verify_notify($order_info);
        if ($notify_result === false)
        {
            /* 支付失败 */
            $this->show_warning($payment->get_error());

            return;
        }
      $order_info['order_id']=$order_id;
        #TODO 临时在此也改变订单状态为方便调试，实际发布时应把此段去掉，订单状态的改变以notify为准
        //$this->_change_order_status($order_id, $order_info['extension'], $notify_result);

        /* 只有支付时会使用到return_url，所以这里显示的信息是支付成功的提示信息 */
        $this->_curlocal(LANG::get('pay_successed'));
        $this->assign('order', $order_info);
        $this->assign('payment', $payment_info);
        $this->display('syspaynotify.index.html');
    }

    /**
     *    支付完成后，外部网关的通知地址，在此会进行订单状态的改变，这里严格验证，改变订单状态
     *
     *    @author    Garbin
     *    @return    void
     */
    function notify()
    {
        //这里是支付宝，财付通等当订单状态改变时的通知地址
        $order_id   = 0;
        if(isset($_POST['order_id']))
        {
            $order_id = intval($_POST['order_id']);
        }
        else
        {
            $order_id = intval($_GET['order_id']);
        }
        if (!$order_id)
        {
            /* 无效的通知请求 */
            $this->show_warning('no_such_order');
            return;
        }

        /* 获取订单信息 */
        $model_order =& m('sys_order');
        $order_info  = $model_order->get($order_id);
        if (empty($order_info))
        {
            /* 没有该订单 */
            $this->show_warning('no_such_order');
            return;
        }

        $model_payment =& m('payment');
        $payment_info  = $model_payment->get("payment_code='{$order_info['payment_code']}' AND store_id={$order_info['seller_id']}");
        if (empty($payment_info))
        {
            /* 没有指定的支付方式 */
            $this->show_warning('no_such_payment');
            return;
        }

        /* 调用相应的支付方式 */
        $payment = $this->_get_payment($order_info['payment_code'], $payment_info);
        $order_info['order_id']="v_".$order_id;
        /* 获取验证结果 */
        $notify_result = $payment->verify_notify($order_info, true);
        if ($notify_result === false)
        {
            /* 支付失败 */
            $payment->verify_result(false);
            return;
        }
		 $order_info['order_id']=$order_id;

        //改变订单状态
        $this->_change_order_status($order_id, $order_info['extension'], $notify_result);
		$order_info['order_id']="v_".$order_id;
        $payment->verify_result(true);
         $order_info['order_id']=$order_id;
        if ($notify_result['target'] == ORDER_ACCEPTED)
        {
            /* 发送邮件给卖家，提醒付款成功 */
            $model_member =& m('member');
            $seller_info  = $model_member->get($order_info['seller_id']);

            $mail = get_mail('toseller_online_pay_success_notify', array('order' => $order_info));
            $this->_mailto($seller_info['email'], addslashes($mail['subject']), addslashes($mail['message']));

            /* 同步发送 */
            $this->_sendmail(true);
        }
    }

    /**
     *    改变订单状态
     *
     *    @author    Garbin
     *    @param     int $order_id
     *    @param     string $order_type
     *    @param     array  $notify_result
     *    @return    void
     */
    function _change_order_status($order_id, $order_type, $notify_result)
    {
        /* 将验证结果传递给订单类型处理 */
      
        $this->respond_notify($order_id, $notify_result);    //响应通知
    }
	
	  function respond_notify($order_id, $notify_result)
    {
        $model_order =& m('sys_order');
        $where = "order_id = {$order_id}";
        $data = array('status' => $notify_result['target']);
        switch ($notify_result['target'])
        {
            case ORDER_ACCEPTED:
                $where .= ' AND status=' . ORDER_PENDING;   //只有待付款的订单才会被修改为已付款
                $data['pay_time']   =   gmtime();
            break;
            case ORDER_SHIPPED:
                $where .= ' AND status=' . ORDER_ACCEPTED;  //只有等待发货的订单才会被修改为已发货
                $data['ship_time']  =   gmtime();
            break;
            case ORDER_FINISHED:
                $where .= ' AND status=' . ORDER_SHIPPED;   //只有已发货的订单才会被自动修改为交易完成
                $data['finished_time'] = gmtime();
            break;
            case ORDER_CANCLED:                             //任何情况下都可以关闭
                /* 加回商品库存 */
               // $model_order->change_stock('+', $order_id);
            break;
        }

        return $model_order->edit($where, $data);
    }
}

?>
