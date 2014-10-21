<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2014/10/15
 * Time: 10:22
 */
class SalesintoApp extends MemberbaseApp
{
    var $_memberrecommendorder_mod;
    function __construct()
    {
        $this->SalesintoApp();
    }
    function SalesintoApp()
    {
        parent::__construct();
        $this->_memberrecommendorder_mod =& m('memberrecommendorder');
	    $this->_order_mod =& m('order');
    }
    function index(){
        /* 当前位置 */
        $this->_curlocal(LANG::get('member_center'),   'index.php?app=member',
            LANG::get('message'),         'index.php?app=salesinto&amp;act=index',
            LANG::get('salesinto')
        );

        /* 当前所处子菜单 */
        $this->_curmenu('salesinto');
        /* 当前用户中心菜单 */
        $this->_curitem('salesinto');
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
                    'path' => 'jquery.plugins/jquery.validate.js',
                    'attr' => '',
                ),
            ),
            'style' =>  'jquery.ui/themes/ui-lightness/jquery.ui.css',
        ));
        // dump($this->_list_salesinto()); // salesinto_price   seller_name     buyer_name      salesinto_price     order_sn     goods_amount     order_amount
	    $this->assign('salesintos', $this->_list_salesinto());
	    $this->assign('salesintos_not_settled', $this->_memberrecommendorder_mod->getOne('SELECT SUM(salesinto_price) FROM '.DB_PREFIX.'memberrecommendorder WHERE user_id='.$this->visitor->get('user_id').' AND salesinto_state=0'));
	    $this->assign('salesintos_total', $this->_memberrecommendorder_mod->getOne('SELECT SUM(salesinto_price) FROM '.DB_PREFIX.'memberrecommendorder WHERE user_id='.$this->visitor->get('user_id')));
	    $this->assign('salesintos_settled', $this->_memberrecommendorder_mod->getOne('SELECT SUM(salesinto_price) FROM '.DB_PREFIX.'memberrecommendorder WHERE user_id='.$this->visitor->get('user_id').' AND salesinto_state=1'));
        $this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('salesinto'));
        $this->display('salesinto.index.html');
    }
	//
	function settle(){
		if(isset($_GET['do']) && $_GET['do'] == '1'){
			header('Content-Type:text/html; charset=utf-8');
			$dones = array();
			$user_id       = $this->visitor->get('user_id');
			// $user_name     = $this->visitor->get('user_name');

			// 获取未结算提成总额
			// $cz_money      = $this->_memberrecommendorder_mod->getOne('SELECT SUM(salesinto_price) FROM '.DB_PREFIX.'memberrecommendorder WHERE user_id='.$user_id.' AND salesinto_state=0');
			// 获取所有未结算提成订单
			$memberrecommendorderids = $this->_memberrecommendorder_mod->getCol('SELECT recommendorder_id FROM '.DB_PREFIX.'memberrecommendorder WHERE user_id='.$user_id.' AND salesinto_state=0');
			foreach($memberrecommendorderids as $recommendorder_id){
				if($this->regZpay($recommendorder_id)){

					if($this->_memberrecommendorder_mod->edit(' recommendorder_id='.$recommendorder_id,array('salesinto_state'=>1))){
						$dones[] = $recommendorder_id;
					}
				}
			}
			dump($dones);
		} else {
			$this->show_warning('ss');
			return;
		}
	}
	/*  1,370.90
	UPDATE `ecm_memberrecommendorder` SET `salesinto_state`= 0;
	DELETE FROM `ecm_zpaylog` WHERE `id` > 16;
	DELETE FROM `ecm_sys_order` WHERE `order_id` > 24;
	 *
	 */
	private function regZpay($order_id){
		$done = 1;
		$order_info  = $this->_order_mod->get($order_id);
		if( $recommeder_arr = ms()->user->get( m('memberrecommend')->getOne('SELECT recommend_id FROM '.DB_PREFIX.'memberrecommend WHERE user_id='.$order_info['buyer_id']) )) {
			$recommeder_id = $recommeder_arr['user_id'];
			$recommeder_name = $recommeder_arr['user_name'];
			// 获取1个订单全部商品
			$goods_all = m('ordergoods')->find(array('conditions' => 'order_id=' . $order_id));
			// 获取商品默认分成百分比
			$salesinto = $GLOBALS['ECMALL_CONFIG']['site_defsalesinto'];
			$salesinto_total = 0;
			$seller_name = m('member')->getOne('SELECT user_name FROM ' . DB_PREFIX . 'member WHERE user_id=' . $order_info['seller_id']);
			foreach ($goods_all as $v) {
				// 获取商品分成百分比
				$salesinto = m('salesinto')->getOne('SELECT salesinto FROM ' . DB_PREFIX . 'salesinto WHERE goods_id=' . $v['goods_id']) ?: $salesinto;
				// 计算订单分成
				$salesinto_total += round(round($v['price'], 2) * round($salesinto, 4) / 100, 2) * $v['quantity'];
				// 分成后的商品价格
				$v['price'] = round(round($v['price'], 2) * round((100 - round($salesinto, 4)), 4) / 100, 2);
			}

			$seller_done = $this->regZpayOrder(' 订单' . $order_info['order_sn'].'销售', $order_info['seller_id'], $seller_name, round($order_info['order_amount'], 2) - $salesinto_total);
			if(!$seller_done['done']){
				$done = 0;
			}
			$recommend_done = $this->regZpayOrder(' 获得' . $order_info['seller_name'] . '订单' . $order_info['order_sn'] . '提成', $recommeder_id, $recommeder_name, $salesinto_total);
			if(!$recommend_done['done']){
				$done = 0;
			}
			if(!$done){
				$model_epay    =& m("sys_order");
				$model_epaylog =& m('zpaylog');
				$model_epay->drop('order_id='.$seller_done['order_id']);
				$model_epay->drop('order_id='.$recommend_done['order_id']);
				$model_epaylog->drop('order_id='.$seller_done['order_id']);
				$model_epaylog->drop('order_id='.$recommend_done['order_id']);
			}
		}
		return $done;
	}

	private function regZpayOrder($type, $user_id, $user_name, $cz_money ){
		$model_epay    =& m("sys_order");
		$model_epaylog =& m('zpaylog');
		$model_zpay    =& m('zpay');
		$order_id      = $model_epay->add_order($user_id,$user_name,$cz_money,$cz_money,0,0);
		$order_info    = $model_epay->get_info($order_id);
		$log_text      = $user_name.$type.$cz_money.'元';
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
		$model_epaylog->add($add_epaylog);
		$done = 1;
		// 修改用户余额
		$user_money = $model_zpay->getOne('SELECT money FROM '.DB_PREFIX.'zpay WHERE user_id='.$user_id);

		// 记录充值订单
		if(!$re=$model_epay->edit('order_id='.$order_id,array('status'=>40))){
			$done = 0;
		}
		// 记录充值
		if(!$relog=$model_epaylog->edit(' order_id='.$order_id,array( 'admin_time'=>time(),'states'=>61)))
		{
			$done = 0;
		}
		// 修改用户余额
		if($done && !$rezlog=$model_zpay->edit(' user_id='.$user_id, array( 'money'=> round($user_money+$cz_money,2) )))
		{
			$done = 0;
		}
		return array( 'done' => $done, 'order_id' => $order_id );
	}

    function _list_salesinto()
    {
        $page = $this->_get_page(10);
        $salesintos = $this->_memberrecommendorder_mod->find(array(
            'conditions' => 'memberrecommendorder.recommendorder_id = order_alias.order_id AND memberrecommendorder.user_id = '.$this->visitor->get('user_id'),
            'join' => 'belongs_to_order',
            'limit' => $page['limit'],
            'count' => true,
        ));
        $page['item_count'] = $this->_memberrecommendorder_mod->getCount();
        $this->_format_page($page);
        $this->assign('page_info', $page);
        return $salesintos;
    }
    function _get_member_submenu()
    {
        $menus = array(
            array(
                'name' => 'salesinto',
                'url'  => 'index.php?app=salesinto',
            ),
        );
        return $menus;
    }
}