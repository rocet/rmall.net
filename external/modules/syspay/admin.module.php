<?php

class SyspayModule extends AdminbaseModule
{

    var $user_id=0;
    function __construct()
    {
        $this->SyspayModule();
    }

    function SyspayModule()
    {
       parent::__construct();
       $this->user_id = 0;    
         
    }

	
    function index()
    {
 
        /* 读取已安装的支付方式 */
        $model_payment =& m('payment');
        $payments      = $model_payment->get_builtin();
        $white_list    = $model_payment->get_white_list();
        $installed     = $model_payment->get_installed($this->user_id);
        foreach ($payments as $key => $value)
        {
            $payments[$key]['system_enabled'] = in_array($key, $white_list);
            foreach ($installed as $installed_payment)
            {
                if ($installed_payment['payment_code'] == $key)
                {
                    $payments[$key]['installed']        =   1;
                    $payments[$key]['payment_id']       =   $installed_payment['payment_id'];
                }
            }
        }

        $this->assign('payments', $payments);
        $this->display('payment.index.html');
    }

  /**
     *    安装支付方式
     *
     *    @author    xiaozhuge
     *    @return    void
     */
    function install()
    {
        $code = isset($_GET['code']) ? trim($_GET['code']) : 0;
        if (!$code)
        {
            $this->show_warning(Lang::get('no_such_payment'));

            return;
        }
        $model_payment =& m('payment');
        $payment       = $model_payment->get_builtin_info($code);
        if (!$payment)
        {
            $this->show_warning(Lang::get('no_such_payment'));

            return;
        }
		
        $payment_info = $model_payment->get("store_id={$this->user_id} AND payment_code='{$code}'");
        if (!empty($payment_info))
        {
            $this->show_warning(Lang::get('already_installed'));

            return;
        }
        if (!IS_POST)
        {
            /* 默认启用 */
            $payment['enabled'] = 1;

            $this->assign('yes_or_no', array(Lang::get('no'), Lang::get('yes')));
            $this->assign('payment', $payment);
            $this->display('payment.form.html');
        }
        else
        {
            $data = array(
                'store_id'      =>-1,
                'payment_name'  => $payment['name'],
                'payment_code'  => $code,
                'payment_desc'  => $_POST['payment_desc'],
                'config'        => $_POST['config'],
                'is_online'     => $payment['is_online'],
                'enabled'       => $_POST['enabled'],
                'sort_order'    => $_POST['sort_order'],
            );
            if (!($payment_id = $model_payment->install($data)))
            {
                $this->show_warning($model_payment->get_error());
                return;
            }
            $this->show_message('install_payment_ok','back_list','index.php?module=syspay');
        }
    }
	
	
	 function config()
    {
        $payment_id =   isset($_GET['payment_id']) ? intval($_GET['payment_id']) : 0;
        if (!$payment_id)
        {
            $this->show_warning(Lang::get('no_such_payment'));

            return;
        }
        $model_payment =& m('payment');
        $payment_info  = $model_payment->get("store_id ={$this->user_id} AND payment_id={$payment_id}");
        if (!$payment_info)
        {
            $this->show_warning(Lang::get('no_such_payment'));

            return;
        }
        $payment = $model_payment->get_builtin_info($payment_info['payment_code']);
        if (!$payment)
        {
            $this->show_warning(Lang::get('no_such_payment'));

            return;
        }

        if (!IS_POST)
        {
            $payment['payment_id']  =   $payment_info['payment_id'];
            $payment['payment_desc']=   $payment_info['payment_desc'];
            $payment['enabled']     =   $payment_info['enabled'];
            $payment['sort_order']  =   $payment_info['sort_order'];
            $this->assign('yes_or_no', array(Lang::get('no'), Lang::get('yes')));
            $this->assign('config', unserialize($payment_info['config']));
            $this->assign('payment', $payment);
            $this->display('payment.form.html');
        }
        else
        {
            $data = array(
                'payment_desc'  =>  $_POST['payment_desc'],
                'config'        =>  $_POST['config'],
                'enabled'       =>  $_POST['enabled'],
                'sort_order'    =>  $_POST['sort_order'],
            );
            $model_payment->edit("store_id ={$this->user_id} AND payment_id={$payment_id}", $data);
            if ($model_payment->has_error())
            {
                $this->show_warning($model_payment->get_error());
                return;
            }
            $this->show_message('config_payment_successed','back_list','index.php?module=syspay');
        }
    }
	
	function uninstall()
    {
        $payment_id = isset($_GET['payment_id']) ? intval($_GET['payment_id']) : 0;
        if (!$payment_id)
        {
            $this->show_warning('no_such_payment');

            return;
        }

        $model_payment =& m('payment');
        $model_payment->uninstall($this->user_id, $payment_id);
        if ($model_payment->has_error())
        {
            $this->show_warning($model_payment->get_error());

            return;
        }

        $this->show_message('uninstall_payment_successed','back_list','index.php?module=syspay');
    }
	
	
	 /**
     *    启用
     *
     *    @author    Garbin
     *    @return    void
     */
    function enable()
    {
        $code = isset($_GET['code'])    ? trim($_GET['code']) : 0;
        if (!$code)
        {
            $this->show_warning('no_such_payment');

            return;
        }
        $model_payment =& m('payment');
        if (!$model_payment->enable_builtin($code))
        {
            $this->show_warning($model_payment->get_error());

            return;
        }

        $this->show_message('enable_payment_successed');

    }

    /**
     *    禁用
     *
     *    @author    Garbin
     *    @return    void
     */
    function disable()
    {
        $code = isset($_GET['code'])    ? trim($_GET['code']) : 0;
        if (!$code)
        {
            $this->show_warning('no_such_payment');

            return;
        }
        $model_payment =& m('payment');
        if (!$model_payment->disable_builtin($code))
        {
            $this->show_warning($model_payment->get_error());

            return;
        }

        $this->show_message('disable_payment_successed');
    }
}

?>