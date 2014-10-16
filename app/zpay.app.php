<?php
class ZpayApp extends MemberbaseApp
{


    function ZpayApp()
    {
        parent::__construct();
        $this->mod_epay =& m('zpay');
		$this->mod_epaylog =& m('zpaylog');
		$this->mod_order =& m('order');
    }

	function exits()
    {
		//执行关闭页面
		echo "<script language='javascript'>window.opener=null;window.close();</script>";
	}

 	function logall()
    {
	    $user_id = $this->visitor->get('user_id');
        /* 当前位置 */
        $this->_curlocal(LANG::get('member_center'),   'index.php?app=member',

                         LANG::get('money_log')
                         );
        /* 当前用户中心菜单 */
        $this->_curitem('money_log');
	    $this->_config_seo('title',Lang::get('member_center'). ' - ' .Lang::get('money_log'));

		$conditions = $this->_get_query_conditions(array(
			array(
                'field' => 'add_time',
                'name'  => 'add_time_from',
                'equal' => '>=',
                'handler'=> 'gmstr2time',
            ),array(
                'field' => 'add_time',
                'name'  => 'add_time_to',
                'equal' => '<=',
                'handler'   => 'gmstr2time_end',
            ),
			array(      //按订单号
                'field' => 'order_sn',
                'equal' => 'LIKE',
                'name'  => 'order_sn',
            ),
        ));
        $page = $this->_get_page(10);
		$index=$this->mod_epaylog->find(array(
	        'conditions' => 'user_id='. $this->visitor->get('user_id') . ' and complete=1' . $conditions,
            'limit' => $page['limit'],
			'order' => "id desc",
			'count' => true));
		$page['item_count'] = $this->mod_epaylog->getCount();
        $this->_format_page($page);
		$this->assign('search_options', $search_options);
		 $this->assign('filtered', $conditions? 1 : 0); //是否有查询条件
		$this->assign('page_info', $page);
        $this->assign('index', $index);

		$this->import_resource(array(
            'script' => array(
                array(
                    'path' => 'dialog/dialog.js',
                    'attr' => 'id="dialog_js"',
                ),
                array(
                    'path' => 'jquery.ui/jquery.ui.js',
                    'attr' => '',
                ),
                array(
                    'path' => 'jquery.ui/i18n/' . i18n_code() . '.js',
                    'attr' => '',
                ),
                array(
                    'path' => 'jquery.plugins/jquery.validate.js',
                    'attr' => '',
                ),
            ),
            'style' =>  'jquery.ui/themes/ui-lightness/jquery.ui.css',
        ));
        $this->display('epay.logall.html');
    }

	//转出查询
   	function outlog()
    {
        $user_id = $this->visitor->get('user_id');
        /* 当前位置 */
        $this->_curlocal(LANG::get('member_center'),   'index.php?app=member',

                         LANG::get('outlog')
                         );
        /* 当前用户中心菜单 */
        $this->_curitem('money_log');
	    $this->_config_seo('title',Lang::get('member_center'). ' - ' .Lang::get('outlog'));
	    $conditions = $this->_get_query_conditions(array(
			array(
                'field' => 'add_time',
                'name'  => 'add_time_from',
                'equal' => '>=',
                'handler'=> 'gmstr2time',
            ),array(
                'field' => 'add_time',
                'name'  => 'add_time_to',
                'equal' => '<=',
                'handler'   => 'gmstr2time_end',
            ),
			array(      //按订单号
                'field' => 'order_sn',
                'equal' => 'LIKE',
                'name'  => 'order_sn',
            ),
        ));
        $page = $this->_get_page(10);
		$index=$this->mod_epaylog->find(array(
	        'conditions' => 'user_id='. $this->visitor->get('user_id') . ' and type=50 or type=40' . $conditions,
            'limit' => $page['limit'],
			'order' => "id desc",
			'count' => true));
		$page['item_count'] = $this->mod_epaylog->getCount();
        $this->_format_page($page);
		 $this->assign('filtered', $conditions? 1 : 0); //是否有查询条件
		$this->assign('page_info', $page);
        $this->assign('index', $index);

		$this->import_resource(array(
            'script' => array(
                array(
                    'path' => 'dialog/dialog.js',
                    'attr' => 'id="dialog_js"',
                ),
                array(
                    'path' => 'jquery.ui/jquery.ui.js',
                    'attr' => '',
                ),
                array(
                    'path' => 'jquery.ui/i18n/' . i18n_code() . '.js',
                    'attr' => '',
                ),
                array(
                    'path' => 'jquery.plugins/jquery.validate.js',
                    'attr' => '',
                ),
            ),
            'style' =>  'jquery.ui/themes/ui-lightness/jquery.ui.css',
        ));
        $this->display('epay.outlog.html');
    }

	//转入查询
   	function intolog()
    {
        $user_id = $this->visitor->get('user_id');
        /* 当前位置 */
        $this->_curlocal(LANG::get('member_center'),   'index.php?app=member',

                         LANG::get('intolog')
                         );
        /* 当前用户中心菜单 */
		$this->_config_seo('title',Lang::get('member_center'). ' - ' .Lang::get('intolog'));
        $this->_curitem('money_log');
	    $conditions = $this->_get_query_conditions(array(
			array(
                'field' => 'states',
                'equal' => '=',
				'name'  => 'status',
                'type'  => 'numeric',
            ),array(
                'field' => 'add_time',
                'name'  => 'add_time_from',
                'equal' => '>=',
                'handler'=> 'gmstr2time',
            ),array(
                'field' => 'add_time',
                'name'  => 'add_time_to',
                'equal' => '<=',
                'handler'   => 'gmstr2time_end',
            ),
			array(      //按订单号
                'field' => 'order_sn',
                'equal' => 'LIKE',
                'name'  => 'order_sn',
            ),
        ));
        $page = $this->_get_page(10);
		$index=$this->mod_epaylog->find(array(
	        'conditions' => 'user_id='. $this->visitor->get('user_id') . ' and type=40' . $conditions,
            'limit' => $page['limit'],
			'order' => "id desc",
			'count' => true));
		$page['item_count'] = $this->mod_epaylog->getCount();
        $this->_format_page($page);
		 $this->assign('filtered', $conditions? 1 : 0); //是否有查询条件
		$this->assign('status_list', array(
            10 => Lang::get('states10'),
            20 => Lang::get('states20'),
            30 => Lang::get('states30'),
            40 => Lang::get('states40'),
            50 => Lang::get('states50'),
        ));
		$this->assign('page_info', $page);
        $this->assign('index', $index);

		$this->import_resource(array(
            'script' => array(
                array(
                    'path' => 'dialog/dialog.js',
                    'attr' => 'id="dialog_js"',
                ),
                array(
                    'path' => 'jquery.ui/jquery.ui.js',
                    'attr' => '',
                ),
                array(
                    'path' => 'jquery.ui/i18n/' . i18n_code() . '.js',
                    'attr' => '',
                ),
                array(
                    'path' => 'jquery.plugins/jquery.validate.js',
                    'attr' => '',
                ),
            ),
            'style' =>  'jquery.ui/themes/ui-lightness/jquery.ui.css',
        ));
        $this->display('epay.intolog.html');
    }

	//在线充值
 	function czlist()
    {
        $user_id = $this->visitor->get('user_id');
        /* 当前位置 */
        $this->_curlocal(LANG::get('member_center'),   'index.php?app=member',

                         LANG::get('zaixianchongzhi')
                         );
        /* 当前用户中心菜单 */
	    $this->_config_seo('title',Lang::get('member_center'). ' - ' .Lang::get('zaixianchongzhi'));
        $this->_curitem('chongzhitixian');


        $this->display('epay.czlist.html');
    }


	function online_finish_order()
	{


	}

	function offline_finish_order()
	{
	                $user_id = $this->visitor->get('user_id');
                    $order_id=isset($_GET['order_id'])?$_GET['order_id']:0;
                    if(!$order_id)
					{
                     $this->show_warning("no_order");
                     return;
					}
                    $pay=&m("zpay");
                    $pay_log=&m("zpaylog");
	                $row_epay=$pay->getrow("select money from ".DB_PREFIX."zpay where user_id='$user_id'");
					$re_log=$pay_log->get(array(
					'conditions'=>'order_id='.$order_id.' and complete=0',
					));
					if(!$re_log)
					{
					   $this->show_warning("no_this_order");
                       return;
					}
					$total_fee=$re_log['money'];
					$old_money=$row_epay['money'];
					$new_money=$old_money+$total_fee;
					$edit_money=array(
							'money'=>$new_money,
						);
					$pay->edit('user_id='.$user_id,$edit_money);
					$edit_epaylog=array(
							'admin_time'=>time(),
							'money_zj'=>$total_fee,
							'complete'=>1,
							'states'=>60,
						);
						$pay_log->edit('order_sn='.'"'.$dingdan.'"',$edit_epaylog);
						$this->show_message('chongzhi_chenggong_jineyiruzhang',
						'chakancicichongzhi',  'index.php?app=zpay&act=czlog',
						'guanbiyemian', 'index.php?app=zpay&act=exits'
					);


	}
	//充值记录
   	function czlog()
    {
        $user_id = $this->visitor->get('user_id');
        /* 当前位置 */
        $this->_curlocal(LANG::get('member_center'),   'index.php?app=member',

                         LANG::get('intolog')
                         );
        /* 当前用户中心菜单 */
		$this->_config_seo('title',Lang::get('member_center'). ' - ' .Lang::get('chongzhijilu'));
        $this->_curitem('chongzhitixian');
	    $conditions = $this->_get_query_conditions(array(array(
                'field' => 'add_time',
                'name'  => 'add_time_from',
                'equal' => '>=',
                'handler'=> 'gmstr2time',
            ),array(
                'field' => 'add_time',
                'name'  => 'add_time_to',
                'equal' => '<=',
                'handler'   => 'gmstr2time_end',
            ),
			array(      //按订单号
                'field' => 'order_sn',
                'equal' => 'LIKE',
                'name'  => 'order_sn',
            ),
        ));
        $page = $this->_get_page(10);
		$index=$this->mod_epaylog->find(array(
	        'conditions' => 'user_id='. $this->visitor->get('user_id') . ' and type=60' . $conditions,
            'limit' => $page['limit'],
			'order' => "id desc",
			'count' => true));
		$page['item_count'] = $this->mod_epaylog->getCount();
        $this->_format_page($page);
		 $this->assign('filtered', $conditions? 1 : 0); //是否有查询条件
		$this->assign('page_info', $page);
        $this->assign('index', $index);

		$this->import_resource(array(
            'script' => array(
                array(
                    'path' => 'dialog/dialog.js',
                    'attr' => 'id="dialog_js"',
                ),
                array(
                    'path' => 'jquery.ui/jquery.ui.js',
                    'attr' => '',
                ),
                array(
                    'path' => 'jquery.ui/i18n/' . i18n_code() . '.js',
                    'attr' => '',
                ),
                array(
                    'path' => 'jquery.plugins/jquery.validate.js',
                    'attr' => '',
                ),
            ),
            'style' =>  'jquery.ui/themes/ui-lightness/jquery.ui.css',
        ));
        $this->display('epay.czlog.html');
    }

	//提现申请
	function txlist()
	{
		$user_id = $this->visitor->get('user_id');

		if(!IS_POST)
		{
			/* 当前位置 */
			$this->_curlocal(LANG::get('member_center'),   'index.php?app=member',

							 LANG::get('tixianshenqing')
							 );
			/* 当前用户中心菜单 */
			$this->_config_seo('title',Lang::get('member_center'). ' - ' .Lang::get('tixianshenqing'));
			$this->_curitem('chongzhitixian');

			$index=$this->mod_epay->getAll("select * from ".DB_PREFIX."zpay where user_id=$user_id");

			$phone_valid=& af("settings");
			$valid=$phone_valid->getOne('phone_open_valid');
			$valid=$valid==1?1:0;
			$this->assign('phone_valid',$valid);
	       //phone
			$this->assign('phone_mob',$this->get_phone($user_id));
			//end phone
			$this->assign('index', $index);
			$this->display('epay.txlist.html');
		}
		else
		{
            $phone_valid=& af("settings");
		    $valid=$phone_valid->getOne('phone_open_valid');
			$valid=$valid==1?1:0;
			if($valid)
			{
				$phone=trim($_POST['phone_code']);
				if($phone!=$_SESSION['phone_send_valid_code'])
				{
				  $this->show_warning('valid_code_is_error');
				  return;

				}
			}
			$tx_money = trim($_POST['tx_money']);
			$money_row=$this->mod_epay->getrow("select * from ".DB_PREFIX."zpay where user_id='$user_id'");
			$post_zf_pass = trim($_POST['post_zf_pass']);
			if(empty($post_zf_pass))
			{
				$this->show_warning('cuowu_zhifumimabunengweikong');
				return;
			}
			$md5zf_pass=md5($post_zf_pass);
			if($money_row['zf_pass'] != $md5zf_pass)
			{
				$this->show_warning('cuowu_zhifumimayanzhengshibai');
				return;
			}
			//检测用户的银行信息
			if(empty($money_row['bank_sn']) or empty($money_row['bank_name']) or empty($money_row['bank_username']))
			{
				$this->show_warning('cuowu_nihaimeiyoushezhiyinhangxinxi');
				return;
			}
			if(empty($tx_money))
			{
				$this->show_warning('cuowu_tixianjinebunengweikong');
				return;
			}
			if(preg_match("/[^0.-9]/",$tx_money))
			{
				 $this->show_warning('cuowu_nishurudebushishuzilei');
				 return;
			}
			if($money_row['money']<$tx_money)
			{
				$this->show_warning('duibuqi_zhanghuyuebuzu');
				return;
			}
			//通过验证 开始操作数据
			$newmoney = $money_row['money']-$tx_money;
			$newmoney_dj =$money_row['money_dj']+$tx_money;

			//添加日志
			$order_sn = date('YmdHis',time());
			$log_text =$this->visitor->get('user_name').Lang::get('tixianshenqingjine').$tx_money.Lang::get('yuan');
			$add_epaylog=array(
				'user_id'=>$user_id,
				'user_name'=>$this->visitor->get('user_name'),
				'order_sn '=>'70'.$order_sn,
				'add_time'=>time(),
				'type'=>70,	//提现
				'money'=>$tx_money,
				'log_text'=>$log_text,
				'states'=>70,	//待审核
			);
			$this->mod_epaylog->add($add_epaylog);
			$edit_mymoney=array(
			'money_dj'=>$newmoney_dj,
			'money'=>$newmoney,
			);
			$this->mod_epay->edit('user_id='.$user_id,$edit_mymoney);
				$this->show_message('tixian_chenggong');
				return;

		}
	}

	//提现记录
   	function txlog()
    {
        $user_id = $this->visitor->get('user_id');
        /* 当前位置 */
        $this->_curlocal(LANG::get('member_center'),   'index.php?app=member',

                         LANG::get('tixianjilu')
                         );
        /* 当前用户中心菜单 */
		$this->_config_seo('title',Lang::get('member_center'). ' - ' .Lang::get('tixianjilu'));
        $this->_curitem('chongzhitixian');
	    $conditions = $this->_get_query_conditions(array(array(
                'field' => 'add_time',
                'name'  => 'add_time_from',
                'equal' => '>=',
                'handler'=> 'gmstr2time',
            ),array(
                'field' => 'add_time',
                'name'  => 'add_time_to',
                'equal' => '<=',
                'handler'   => 'gmstr2time_end',
            ),
			array(      //按订单号
                'field' => 'order_sn',
                'equal' => 'LIKE',
                'name'  => 'order_sn',
            ),
        ));
        $page = $this->_get_page(10);
		$index=$this->mod_epaylog->find(array(
	        'conditions' => 'user_id='. $this->visitor->get('user_id') . ' and type=70' . $conditions,
            'limit' => $page['limit'],
			'order' => "id desc",
			'count' => true));
		$page['item_count'] = $this->mod_epaylog->getCount();
        $this->_format_page($page);
		 $this->assign('filtered', $conditions? 1 : 0); //是否有查询条件
		$this->assign('page_info', $page);
        $this->assign('index', $index);

		$this->import_resource(array(
            'script' => array(
                array(
                    'path' => 'dialog/dialog.js',
                    'attr' => 'id="dialog_js"',
                ),
                array(
                    'path' => 'jquery.ui/jquery.ui.js',
                    'attr' => '',
                ),
                array(
                    'path' => 'jquery.ui/i18n/' . i18n_code() . '.js',
                    'attr' => '',
                ),
                array(
                    'path' => 'jquery.plugins/jquery.validate.js',
                    'attr' => '',
                ),
            ),
            'style' =>  'jquery.ui/themes/ui-lightness/jquery.ui.css',
        ));
        $this->display('epay.txlog.html');
    }

//用户设置		
 	function set()
    {
        $user_id = $this->visitor->get('user_id');
		$user_name=$this->visitor->get('user_name');
		//读取帐户金额
		$index=$this->mod_epay->getAll("select * from ".DB_PREFIX."zpay where user_id=$user_id");
		$row_epay = $this->mod_epay->getrow("select * from ".DB_PREFIX."zpay where user_id=$user_id");
		$password = $row_epay['zf_pass'];
        /* 当前位置 */
        $this->_curlocal(LANG::get('member_center'),   'index.php?app=member',

                         LANG::get('epay_set')
                         );
        /* 当前用户中心菜单 */
        $this->_curitem('epay_set');
        $this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('epay_set'));
		if(!IS_POST)
		{
		    $phone_valid=& af("settings");
			$valid=$phone_valid->getOne('phone_open_valid');
			$valid=$valid==1?1:0;
			$this->assign('phone_valid',$valid);
		    //phone
			$this->assign('phone_mob',$this->get_phone($user_id));
			//end phone
			$this->assign('password',$password);
			$this->assign('index', $index);
			$this->display('epay.set.html');//对应风格文件
		}
		else
		{
		   $phone_valid=& af("settings");
		    $valid=$phone_valid->getOne('phone_open_valid');
			$valid=$valid==1?1:0;
			if($valid)
			{
				$phone=trim($_POST['phone_code']);
				if($phone!=$_SESSION['phone_send_valid_code'])
				{
				  $this->show_warning('valid_code_is_error');
				  return;

				}
			}
			$zf_pass = trim($_POST['zf_pass']);
			$zf_pass2 = trim($_POST['zf_pass2']);
			$md5_zf_pass = md5($zf_pass);
			if($password == "")
			{
				if(empty($zf_pass))
				{
					$this->show_warning('cuowu_zhifumimabunengweikong');
					return;
				}
				if($zf_pass != $zf_pass2)
				{
					$this->show_warning('cuowu_liangcishurumimabuyizhi');
					return;
				}

				$data = array(
					'bank_name'   => $_POST['bank_name'],
					'bank_sn' => $_POST['bank_sn'],
					'bank_add' => $_POST['bank_add'],
					'bank_username' => $_POST['bank_username'],
					'zf_pass' => $md5_zf_pass,
					'user_id'=>$user_id,
					'user_name'=>$user_name,
           		);

				$this->mod_epay->add($data);
				$this->show_message('set_ok','back_list','index.php?app=zpay&act=set');
			}
			else
			{
				if(empty($zf_pass))
				{
					$this->show_warning('cuowu_zhifumimabunengweikong');
					return;
				}
				if($md5_zf_pass != $row_epay['zf_pass'])
				{
					$this->show_warning('cuowu_zhifumimayanzhengshibai');
					return;
				}
				$data = array(
					'bank_name'   => $_POST['bank_name'],
					'bank_sn' => $_POST['bank_sn'],
					'bank_add' => $_POST['bank_add'],
					'bank_username' => $_POST['bank_username'],
					'user_name'=>$user_name,
           		);
				$this->mod_epay->edit('user_id='.$user_id,$data);
				$this->show_message('edit_ok','back_list','index.php?app=zpay&act=set');
			}
		}
    }
	//修改支付密码
	function editpassword()
	{
		$user_id = $this->visitor->get('user_id');
		if($_POST)//检测是否提交
		{
		  $phone_valid=& af("settings");
		    $valid=$phone_valid->getOne('phone_open_valid');
			$valid=$valid==1?1:0;
			if($valid)
			{
				$phone=trim($_POST['phone_code']);
				if($phone!=$_SESSION['phone_send_valid_code'])
				{
				  $this->show_warning('valid_code_is_error');
				  return;

				}
			}
			$y_pass = trim($_POST['y_pass']);
			$zf_pass = trim($_POST['zf_pass']);
			$zf_pass2 = trim($_POST['zf_pass2']);
			if(empty($zf_pass))
			{
				$this->show_warning('cuowu_zhifumimabunengweikong');
				return;
			}
			if($zf_pass != $zf_pass2)
			{
				$this->show_warning('cuowu_liangcishurumimabuyizhi');
				return;
			}
			//读原始密码
			$money_row=$this->mod_epay->getrow("select zf_pass from ".DB_PREFIX."zpay where user_id='$user_id'");
			if($money_row['zf_pass'] == "")
			{
				$this->show_warning('cuowu_noset');
				return;
			}
			//转换32位 MD5
			$md5y_pass=md5($y_pass);
			$md5zf_pass=md5($zf_pass);

			if($money_row['zf_pass'] != $md5y_pass)
			{
				$this->show_warning('cuowu_yuanzhifumimayanzhengshibai');
				return;
			}
			else
			{
				$newpass_array=array(
					'zf_pass'=>$md5zf_pass,
				);
				$this->mod_epay->edit('user_id='.$user_id,$newpass_array);
				$this->show_message('zhifumimaxiugaichenggong');
				return;
			}
		}
		else
		{
			$this->_curlocal(LANG::get('member_center'),   'index.php?app=member',

									 LANG::get('zhifumimaxiugai')
									 );
			$this->_curitem('epay_set');
			$this->_config_seo('title',Lang::get('member_center').' - '.Lang::get('zhifumimaxiugai'));
			 $phone_valid=& af("settings");
			$valid=$phone_valid->getOne('phone_open_valid');
			$valid=$valid==1?1:0;
			$this->assign('phone_valid',$valid);
			 //phone
			$this->assign('phone_mob',$this->get_phone($user_id));
			//end phone
			$this->display('epay.editpassword.html');
			return;
		}
	}







	//筛选充值方式
	function czfs()
	{
		if($_POST)
		{
			$user_id = $this->visitor->get('user_id');
			$user_name = $this->visitor->get('user_name');
			$cz_money     =trim($_POST['cz_money']);
			$model=&m("sys_order");
			$order_id=$model->add_order($user_id,$user_name,$cz_money,$cz_money,0,0);
			$order_info=$model->get_info($order_id);
			$log_text =$this->visitor->get('user_name')."充值".$cz_money.Lang::get('yuan');
			$dingdan = "60".date('Ymdhms');
			$add_epaylog=array(
						'user_id'=>$user_id,
						'user_name'=>$user_name,
						'order_sn'=>$order_info['order_sn'],
						'to_name' => $bank_name,
						'add_time'=>time(),
						'type'=>60,
						'money'=>$cz_money,
						'log_text'=>$log_text,
						'states'=>60,
                        'order_id'=>$order_id,
					);
			$this->mod_epaylog->add($add_epaylog);
            header('location:index.php?app=cashier_sys&order_id='.$order_id);




		}
	}







	//余额转帐
	function out()
	{
		$to_user = trim($_POST['to_user']);
		$to_money = trim($_POST['to_money']);
		$user_id = $this->visitor->get('user_id');
		if(!IS_POST)
		{
			/* 当前位置 */
			$this->_curlocal(LANG::get('member_center'),   'index.php?app=member',

							 LANG::get('yuezhuanzhang')
							 );
			/* 当前用户中心菜单 */
			$this->_curitem('chongzhitixian');
			$this->_config_seo('title',Lang::get('member_center'). ' - '.Lang::get('yuezhuanzhang'));
			$index=$this->mod_epay->getAll("select * from ".DB_PREFIX."zpay where user_id=$user_id");
			$this->assign('index', $index);
			$this->display('epay.out.html');
		}
		else
		{//检测有提交
			if (preg_match("/[^0.-9]/",$to_money))
			{
				$this->show_warning('cuowu_nishurudebushishuzilei');
				return;
			}


			$to_row=$this->mod_epay->getrow("select * from ".DB_PREFIX."zpay where user_name='$to_user'");
			$to_user_id=$to_row['user_id'];
			$to_user_name=$to_row['user_name'];
			$to_user_money=$to_row['money'];

			if($to_user_id==$user_id)
			{
				$this->show_warning('cuowu_bunenggeizijizhuanzhang');
				return;
			}

			if(empty($to_user_id))
			{
				$this->show_warning('cuowu_mubiaoyonghubucunzai');
				return;
			}
			$row_epay=$this->mod_epay->getrow("select * from ".DB_PREFIX."zpay where user_id='$user_id'");
			$user_money=$row_epay['money'];
			$user_zf_pass=$row_epay['zf_pass'];
			$zf_pass = md5(trim($_POST['zf_pass']));
			if($user_zf_pass != $zf_pass)
			{
				$this->show_warning('cuowu_zhifumimayanzhengshibai');
				return;
			}
			$order_sn = "40".date('YmdHis',time());
			if ($user_money < $to_money)
			{
				$this->show_warning('cuowu_zhanghuyuebuzu');
				return;
			}
			else
			{
				//添加日志
				$log_text =$this->visitor->get('user_name').Lang::get('gei').$to_user.Lang::get('zhuanchujine').$to_money.Lang::get('yuan');

				$add_epaylog=array(
					'user_id' => $this->visitor->get('user_id'),
					'user_name' => $this->visitor->get('user_name'),
					'to_id' => $to_user_id,
					'to_name' => $to_user_name,
					'order_sn' => $order_sn,
					'add_time' => time(),
					'type' => 50,	//转出
					'money' => $to_money,
					'money_zj' =>'-'.$to_money,
					'complete' =>1,
					'log_text' => $log_text,
					'states' => 40,
				);
				$this->mod_epaylog->add($add_epaylog);
				$log_text_to =$this->visitor->get('user_name').Lang::get('gei').$to_user_name.Lang::get('zhuanrujine').$to_money.Lang::get('yuan');
				$add_epaylog_to=array(
						'user_id' => $to_user_id,
						'user_name' => $to_user_name,
						'to_id' => $this->visitor->get('user_id'),
						'to_name' => $this->visitor->get('user_name'),
						'order_sn '=>$order_sn,
						'add_time'=>time(),
						'type'=>40,	//转入
						'money'=>$to_money,
						'money_zj' =>'+'.$to_money,
						'complete' =>1,
						'log_text'=>$log_text_to,
						'states'=>40,
				);
				$this->mod_epaylog->add($add_epaylog_to);

				$new_user_money = $user_money-$to_money;
				$new_to_user_money =$to_user_money+$to_money;

				$add_jia=array(
						'money'=>$new_to_user_money,
				);
				$this->mod_epay->edit('user_id='.$to_user_id,$add_jia);
				$add_jian=array(
						'money'=>$new_user_money,
				);
				$this->mod_epay->edit('user_id='.$user_id,$add_jian);

				$this->show_message('zhuanzhangchenggong');
				return;
			}
		}

	}

	function get_phone($user_id)
	{
	   $mem_mod=&m("member");
	   $row=$mem_mod->get_info($user_id);
	   if($row)
	   {
	     $mob=$row['phone_mob'];
		 return $mob;

	   }
	   return '';

	}
}
?>
