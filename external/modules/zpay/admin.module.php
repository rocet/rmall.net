<?php

class ZpayModule extends AdminbaseModule
{
    function __construct()
    {
        $this->ZpayModule();
    }

    function ZpayModule()
    {
        parent::__construct();
		
        $this->mod_epay =& m('zpay');
		$this->mod_epaylog =& m('zpaylog');	
    }
	
	//用户资金列表 含搜索
 	function index()
	{
		$conditions = $this->_get_query_conditions(array(array(
                'field' => 'user_name',
                'equal' => 'LIKE',
                'name'  => 'search_name',
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
            ),array(
                'field' => 'money',
                'name'  => 'order_amount_from',
                'equal' => '>=',
                'type'  => 'numeric',
            ),array(
                'field' => 'money',
                'name'  => 'order_amount_to',
                'equal' => '<=',
                'type'  => 'numeric',
            ),
        ));
        $page = $this->_get_page(10);		
		$index=$this->mod_epay->find(array(
	        'conditions' => '1=1' . $conditions,
            'limit' => $page['limit'],
			'order' => "id desc",
			'count' => true));
		$page['item_count'] = $this->mod_epaylog->getCount();
        $this->_format_page($page);
		 $this->assign('filtered', $conditions? 1 : 0); //是否有查询条件
	    $this->assign('page_info', $page);
	    $this->assign('index', $index);
        $this->display('index.html'); 
	}

    function change_status()
	{
	  
	}
	//增加用户资金   
 	function money_add()
    {
	   if($_POST)
	   {
		   $user_name= trim($_POST['user_name']);
		   $post_money= trim($_POST['post_money']);
		   $jia_or_jian= trim($_POST['jia_or_jian']);
		   $log_text= trim($_POST['log_text']);	   
		   if(empty($user_name) or empty($post_money) or empty($jia_or_jian))
		   {
				$this->show_warning('cuowu_notnull');
				return;
		   }
		   if (preg_match("/[^0.-9]/",$post_money))
		   {
			   $this->show_warning('cuowu_nishurudebushishuzilei'); 
			   return;
		   }
			$money_row=$this->mod_epay->getrow("select * from ".DB_PREFIX."zpay where user_name='$user_name'");	
			$user_ids=$money_row['user_id'];  
			$my_money=$money_row['money'];
		   if(empty($user_ids))
		   {
				$this->show_warning('cuowu_no_user');
				return;
		   }
		   if($jia_or_jian=="jia")
		   {
				$money=$my_money+$post_money;
		   }
		   if($jia_or_jian=="jian")
		   {
			   if($my_money>=$post_money)
			   {	   
					$money=$my_money-$post_money;
			   }
			   else
			   {
					$this->show_warning('cuowu_moeny_low');
					return;
			   }
		   } 
		   //写入LOG记录
		   $dq_time="10".date("Ymdhis",time());
		   
		   $logs_array=array(
				   'user_id'=>$user_ids,
				   'user_name'=>$user_name,
				   'order_sn'=>$dq_time,
				   'type'=>10,
				   'admin_name' =>$this->visitor->get('user_name'),
				   'money'=>$post_money, 
				   'money_zj'=>$post_money,
				   'states'=>40,
				   'complete'=>1,
				   'log_text'=>$log_text,
				   'add_time'=>time(),
			   );
		   $this->mod_epaylog->add($logs_array);
		   //写入LOG记录
		   $money_array=array(
		   		'money'=>$money,
		   );
		   $this->mod_epay->edit('user_id='.$user_ids,$money_array);
	
				$this->show_message('add_money_ok','返回列表','index.php?module=zpay');
				return;
		   }
		else
		{
			$user_id = isset($_GET['user_id']) ? trim($_GET['user_id']) : '';
			$user_name = isset($_GET['user_name']) ? trim($_GET['user_name']) : '';
			if(!empty($user_id))
			{
				$index=$this->mod_epay->find('user_id='.$user_id);
			}
			$this->assign('index', $index);
			$this->display('money_add.html'); 
		}
		return;
	}


	//查看资金流水
 	function money_log()
    {
		$search_options = array(
            'user_name'   => Lang::get('user_name'),
            'log_text'   => Lang::get('log_text'),
            'order_sn'   => Lang::get('order_sn'),
        );
		/* 默认搜索的字段是操作名 */
        $field = 'user_name';
        array_key_exists($_GET['field'], $search_options) && $field = $_GET['field'];
		$conditions = $this->_get_query_conditions(array(array(
                'field' => $field,       //按用户名,店铺名,支付方式名称进行搜索
                'equal' => 'LIKE',
                'name'  => 'search_name',
            ),array(
                'field' => 'type',
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
            ),array(
                'field' => 'money',
                'name'  => 'order_amount_from',
                'equal' => '>=',
                'type'  => 'numeric',
            ),array(
                'field' => 'money',
                'name'  => 'order_amount_to',
                'equal' => '<=',
                'type'  => 'numeric',
            ),
        ));
        $page = $this->_get_page(10);		
		$index=$this->mod_epaylog->find(array(
	        'conditions' => 'complete=1' . $conditions,
            'limit' => $page['limit'],
			'order' => "id desc",
			'count' => true));
		$page['item_count'] = $this->mod_epaylog->getCount();
        $this->_format_page($page);
		$this->assign('search_options', $search_options);
		 $this->assign('filtered', $conditions? 1 : 0); //是否有查询条件
		$this->assign('money_status_list', array(
            10 => Lang::get('status_admin'), //手工操作
			20 => Lang::get('status_buy_good'), //购买商品
            30 => Lang::get('status_sell_good'), //出售商品
            40 => Lang::get('status_in'),//账户转入
            50 => Lang::get('status_out'),//账户转出
			60 => Lang::get('status_cz'),//账户充值
			70 => Lang::get('status_tx'),//账户提现
        ));
	    $this->assign('page_info', $page);
	    $this->assign('index', $index);
        $this->display('money_log.html'); 
	   return;
	}
	
    //提现记录
 	function txlog()
    {
		$search_options = array(
            'user_name'   => Lang::get('user_name'),
            'order_sn'   => Lang::get('order_sn'),
        );
		/* 默认搜索的字段是操作名 */
        $field = 'user_name';
        array_key_exists($_GET['field'], $search_options) && $field = $_GET['field'];
		$conditions = $this->_get_query_conditions(array(array(
                'field' => $field,       //按用户名,店铺名,支付方式名称进行搜索
                'equal' => 'LIKE',
                'name'  => 'search_name',
            ),array(
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
            ),array(
                'field' => 'money',
                'name'  => 'order_amount_from',
                'equal' => '>=',
                'type'  => 'numeric',
            ),array(
                'field' => 'money',
                'name'  => 'order_amount_to',
                'equal' => '<=',
                'type'  => 'numeric',
            ),
        ));
        $page = $this->_get_page(10);		
		$index=$this->mod_epaylog->find(array(
	        'conditions' => 'type=70' . $conditions,
            'limit' => $page['limit'],
			'order' => "id desc",
			'count' => true));
		$page['item_count'] = $this->mod_epaylog->getCount();
        $this->_format_page($page);
		$this->assign('search_options', $search_options);
		 $this->assign('filtered', $conditions? 1 : 0); //是否有查询条件
		$this->assign('tx_status_list', array(
            70 => Lang::get('tx_weishenhe'), //未审核
            71 => Lang::get('tx_yishenhe'), //已审核
        ));
	    $this->assign('page_info', $page);
	    $this->assign('index', $index);
        $this->display('txlog.html'); 
	}

	//审核操作	
	function tx_view()
    {
		$log_id = $_GET['log_id'];
		$user_id = $_GET['user_id'];
		$order_id = trim($_POST['order_id']);
		$tx_money = trim($_POST['money']);
		$admin_time = time();
		if(!IS_POST)
		{
			if(empty($log_id) or empty($user_id))
			{
				$this->show_warning('feifacanshu');
				return;
			}
			$logs_data = $this->mod_epaylog->find('id='.$log_id);
			$user_data=$this->mod_epay->find('user_id='.$user_id);
			$this->assign('log', $logs_data);
			$this->assign('user', $user_data);
			$this->display('tx_view.html');
			return;
		}
		else
		{
			$edit_log=array(
				'admin_name' =>$this->visitor->get('user_name'),
				'admin_time'=>$admin_time,		
				'states'=>71,//改变状态为已审核	
				'to_id'=>$order_id,															
			);
			$this->mod_epaylog->edit('id='.$log_id,$edit_log);
		
			$money_row=$this->mod_epay->getrow("select money_dj from ".DB_PREFIX."zpay where user_id='$user_id'");
			$row_money_dj=$money_row['money_dj'];
				
			if($row_money_dj<$tx_money)
			{
					$this->show_warning('feifacanshu');
					return;
			}
			
			$new_money_dj=$row_money_dj-$tx_money;
			$new_money=array(
					'money_dj'=>$new_money_dj,																	
			);
			$this->mod_epay->edit('user_id='.$user_id,$new_money);//读取所有数据库
		}
		$this->show_message('shenhechenggong',
				'fanhuiliebiao',    'index.php?module=zpay&act=txlog');
	}
	
	function setting()
    {
       
    }
			
}
?>