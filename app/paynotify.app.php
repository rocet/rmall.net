<?php

/**
 *    支付网关通知接口
 *
 *    @author    Garbin
 *    @usage    none
 */
class PaynotifyApp extends MallbaseApp
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
        $model_order =& m('order');
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

        /* 获取验证结果 */
        $notify_result = $payment->verify_notify($order_info);
        if ($notify_result === false)
        {
            /* 支付失败 */
            $this->show_warning($payment->get_error());

            return;
        }

        #TODO 临时在此也改变订单状态为方便调试，实际发布时应把此段去掉，订单状态的改变以notify为准
        //$this->_change_order_status($order_id, $order_info['extension'], $notify_result);

        /* 只有支付时会使用到return_url，所以这里显示的信息是支付成功的提示信息 */
        $this->_curlocal(LANG::get('pay_successed'));
        $this->assign('order', $order_info);
        $this->assign('payment', $payment_info);
        $this->display('paynotify.index.html');
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
        $model_order =& m('order');
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

        /* 获取验证结果 */
        $notify_result = $payment->verify_notify($order_info, true);
        if ($notify_result === false)
        {
            /* 支付失败 */
            $payment->verify_result(false);
            return;
        }

        //改变订单状态
        $this->_change_order_status($order_id, $order_info['extension'], $notify_result);
        $payment->verify_result(true);

        // 存在推荐人
        if($recommeder_id = ms()->user->get( m('memberrecommend')->getOne('SELECT recommend_id FROM '.DB_PREFIX.'memberrecommend WHERE user_id='.$order_info['buyer_id']) ) ){
            // 获取1个订单全部商品
            $goods_all = m('ordergoods')->find(array('conditions'=>'order_id='.$order_id));
            // 获取商品分成百分比计算分成
            $salesinto = $GLOBALS['ECMALL_CONFIG']['site_defsalesinto'];
            foreach($goods_all as $v){
                $salesinto = m('salesinto')->getOne('SELECT salesinto FROM '.DB_PREFIX.'salesinto WHERE goods_id='.$v['goods_id']) ?: $salesinto;
                $v['price'] = $v['price'] / (100 - $salesinto) * 100;
            }

            // $GLOBALS['ECMALL_CONFIG']['site_defsalesinto']
            // 卖家支付给推荐者 ecm_order goods_amount order_amount   ecm_order_goods price quantity
            // $buyer = ms()->user->get($order_info['buyer_id']);
            // $seller = ms()->user->get($order_info['seller_id']);
            $this->regRecommendOrder($recommeder_id, $order_id, $order_info['goods_amount'] );
        }

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

    private function regRecommendOrder($user_id, $recommeduid){
        m('memberrecommendorder')->add(array('user_id'=>$user_id, 'recommendorder_id'=>$recommeduid, 'price'=>''));
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
        $order_type  =& ot($order_type);
        $order_type->respond_notify($order_id, $notify_result);    //响应通知
    }
}

?>
