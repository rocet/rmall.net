<?php
class SeckillApp extends StoreadminbaseApp{
	var $_seckill_mod;
	var $_goods_mod;
	function __construct(){
		parent::__construct();
		$this->_goods_mod = & m('goods');
		$this->_seckill_mod = & m('seckill');
	}
	function index(){
		/* 检测是否已经安装了秒杀或者店铺是否有秒杀功能*/
	    $seckill_configs = file_exists(ROOT_PATH."/data/seckill.inc.php") ? include(ROOT_PATH."/data/seckill.inc.php") : '';
    	if(!file_exists(ROOT_PATH . '/data/seckill.inc.php') || !is_array($seckill_configs)){
    		$this->show_warning('the_action_has_not_exists');
    		return ;
    	}

    	$store_mod = & m('store');
    	$store_seckill = $store_mod->get(array(
    	    'conditions' => 'store_id='.$this->visitor->get('user_id'),
    	    'join' => 'belongs_to_sgrade',
    	    'fields' => 'sgrade.functions'
    	));
    	if(!empty($store_seckill)){
    		$store_function = explode(',',$store_seckill['functions']);
    	}
    	$store_function= empty($store_function) ? array() : $store_function;
        $store_function = array_flip($store_function);
        if(!array_key_exists('seckill',$store_function)){
        	$this->show_warning('the_store_bear_seckill');
        	return ;
        }
		/* 当前位置 */
		$this->_curlocal(LANG::get('member_center'),    'index.php?app=member',
		LANG::get('seckill_manage'), 'index.php?app=seckill',
		LANG::get('goods_list'));

		/* 当前用户中心菜单 */

		$type = (isset($_GET['type']) && $_GET['type'] != '') ? trim($_GET['type']) : 'all_orders';
		$this->_curitem('seckill_manage');
		$this->_curmenu($type);

		$this->assign('page_title', Lang::get('member_center') . ' - ' . Lang::get('store_concume'));
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
	    $conditions = $this->_get_query_conditions(array(
    	    array(
    	       'field' => 'sk.goods_name',
    	       'equal' => 'like',
    	       'name' => 'goods_name',
    	    ),
    	));
    	if(isset($_GET['state']) && trim($_GET['state'] != 'nosel')){  		
    		$conditions .= ' AND sk.sec_state='.trim($_GET['state']);
    	}
    	
		//查找秒杀活动信息
       if(!empty($conditions)){
       	   $this->assign('filtered',true);
       }
		$page = $this->_get_page(10);
		$seckill_info = $this->_seckill_mod->find(array(
		    'conditions' => '1 = 1'.$conditions.' AND sk.store_id='.$this->visitor->get('user_id'),
		    'join' => 'belong_goods',
		    'fields' => 'this.*,g.default_image',
		    'limit' => $page['limit'],
		    'order' => 'add_time desc',
		    'count' => true
		));
        $page['item_count'] = $this->_seckill_mod->getCount();
        $this->_format_page($page);
    	$this->assign('page_info', $page);
        $state_info = array(
            SECKILL_APPLY => Lang::get('seckill_apply'),
            SECKILL_START => Lang::get('seckill_start'),
            SECKILL_NOT_START => Lang::get('seckill_not_start'),
            SECKILL_REFUCE => Lang::get('seckill_refuce'),
            SECKILL_END => Lang::get('seckill_end')
        );
        $seckill_info = empty($seckill_info) ? array() : $seckill_info;
        foreach($seckill_info as $k=>$v){
        	$seckill_info[$k]['sec_state'] = $state_info[$v['sec_state']];
        }
        $this->assign('state',$state_info);
		$this->assign('seckill_list',$seckill_info);
		$this->display('seckill.index.html');
	}

	//新增秒杀
	function add(){
		if(!$_POST){
			/* 当前位置 */
			$this->_curlocal(LANG::get('member_center'),    'index.php?app=member',
			LANG::get('seckill_manage'), 'index.php?app=seckill',
			LANG::get('seckill_add'));

			/* 当前用户中心菜单 */

			$type = (isset($_GET['type']) && $_GET['type'] != '') ? trim($_GET['type']) : 'all_orders';
			$this->_curitem('store_concume');
			$this->_curmenu($type);
			$this->assign('store_id',$this->visitor->get('user_id'));
			$this->assign('page_title', Lang::get('member_center') . ' - ' . Lang::get('store_concume'));
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
            //获取秒杀主题标题
            $subject_mod = & m('seckill_subject');
            $subject_lists = $subject_mod->find(array(
               'fields' => 'subject_id,subject_name'
            ));
            $subject_lists = empty($subject_lists) ? array() : $subject_lists;
            foreach ($subject_lists as $k => $v){
            	$subject_lists[$k] = $v['subject_name'];
            }
            $this->assign('subject_lists',$subject_lists);
			$this->display('seckill.form.html');
		}
		else{
			
			if(empty($_POST['goods_name'])){
				$this->show_warning('fill_goods');
				return;
			}
			else{
				$good_name = $_POST['goods_name'];
			}
			$goods_id = empty($_POST['goods_id']) ? '' : intval($_POST['goods_id']);
			if(!isset($_POST['min_quantity'])){
				$this->show_warning('fill_min_quantity');
				return;
			}
			else{
				$sec_quantity = intval($_POST['min_quantity']);
			}

			foreach ($_POST['spec_id'] as $key => $val)
			{
				if (empty($_POST['group_price'][$key]))
				{
					$this->_error('invalid_group_price');
					return false;
				}
				$spec_price[$val] = array('price' => number_format($_POST['group_price'][$key], 2, '.', ''));
			}
			if(empty($_POST['start_time'])){
				$this->show_warning('fill_start_date');
				return ;
			}
			$start_time = explode('-',$_POST['start_time']);
			$goods_mod = & m('goods');
			$seckill_data = array(
			    'goods_name' => $good_name,
			    'goods_id' => $goods_id,
			    'subject_id' => intval($_POST['seckill_subject']),
			    'store_id' => $this->visitor->get('user_id'),
			    'sec_quantity' => $sec_quantity,
			    'sec_price' => serialize($spec_price),
			    'add_time' => gmtime(),
			    'start_time' => mktime(0,0,0,$start_time[1],$start_time[2],$start_time[0]),
			    'sec_state' => SECKILL_APPLY,
			    'recommended' => SECKILL_NOT_RECOMMENDED,
			    'views' => 0,
			);
			$goods_mod->db->query('START TRANSACTION');
			if(!$this->_seckill_mod->add($seckill_data)){
				$this->show_warning($this->get_error());
				$goods_mod->db->query('ROLLBACK');
				return ;
			}
			if(!$goods_mod->edit($goods_id,'if_show=0,if_seckill=1')){
				$this->show_warning($this->get_error());
				$goods_mod->db->query('ROLLBACK');
				return ;
			}
			$goods_mod->db->query('COMMIT');
			if(!$this->get_error()){
				$this->show_message('add_seckill_success',Lang::get('back_to_seckill_list'),'index.php?app=seckill',Lang::get('back_to_seckill_add'),'index.php?app=seckill&act=add');
			}
		}
	}

	function drop(){
	    if(empty($_GET['id'])){
    		$this->show_warning('no_such_seckill');
    		return ;
    	}
    	$id = $_GET['id'];
    	$ids = explode(',',$id);   	
        foreach ($ids as $k=>$v){
    		$ids[$k] = intval($v);
    	}
    	//查找商品ID
    	$seckill_mod = & m('seckill');
    	$goods_mod = & m('goods');
    	$seckill_info = $seckill_mod->find(array(
    	    'conditions' => 'sec_id in('.implode(',',$ids).')',
    	    'fields' => 'goods_id,sec_state'
    	));
    	foreach ($seckill_info as $gk => $gv){
    		if($gv['sec_state'] == SECKILL_APPLY || $gv['sec_state'] == SECKILL_NOT_START || $gv['sec_state'] == SECKILL_START){
    			$gids[$gk] = $gv['goods_id'];
    		}
    	}
    	$conditions = !empty($_GET['sid']) ? 'store_id in ('.implode(',',$ids).')' : $ids; 
    	$goods_mod->db->query('START TRANSACTION');
    	if(!$seckill_mod->drop($conditions)){
    		$this->show_warning($this->get_error());
    		$goods_mod->db->query('ROLLBACK');
    		return ;
    	}
    	if(!empty($gids)){
    		if(!$goods_mod->edit($gids,'if_show=1,if_seckill=0')){
    			$this->show_warning($this->get_error());
    			$goods_mod->db->query('ROLLBACK');
    			return ;
    		}
    	}
    	$goods_mod->db->query('COMMIT');
    	if(!$this->get_error()){
			$this->show_message('seckill_del_success');
		}
	}
	
	//查看商品
	function show_goods(){
		
	}
	function query_goods_info()
	{
		$goods_id = empty($_GET['goods_id']) ? 0 : intval($_GET['goods_id']);
		if ($goods_id)
		{
			$goods = $this->_query_goods_info($goods_id);
			$this->json_result($goods);
		}
	}

	function _query_goods_info($goods_id)
	{
		$goods = $this->_goods_mod->get_info($goods_id);
		if ($goods['spec_qty'] ==1 || $goods['spec_qty'] ==2)
		{
			$goods['spec_name'] = htmlspecialchars($goods['spec_name_1'] . ($goods['spec_name_2'] ? ' ' . $goods['spec_name_2'] : ''));
		}
		else
		{
			$goods['spec_name'] = Lang::get('spec');
		}
		foreach ($goods['_specs'] as $key => $spec)
		{
			if ($goods['spec_qty'] ==1 || $goods['spec_qty'] ==2)
			{
				$goods['_specs'][$key]['spec'] = htmlspecialchars($spec['spec_1'] . ($spec['spec_2'] ? ' ' . $spec['spec_2'] : ''));
			}
			else
			{
				$goods['_specs'][$key]['spec'] = Lang::get('default_spec');
			}
		}
		$goods['default_image'] || $goods['default_image'] = Conf::get('default_goods_image');
		return $goods;
	}
}