<?php
// 类名必须为 “ 唯一标识首字母大写 ” +Module ，并且从 AdminbaseModule 继承
class SeckillModule extends AdminbaseModule
{
	

	// 构造函数
	function __construct()
	{
		// 运行基础类的构造函数进行一些初始化工作
		parent::__construct();
		
	}

	// 实现您在 module.info.php中指定的 menu中的act为index的方法,index即为函数名称
	function index()
	{
		// 与主体程序控制器一样，使用 $this->assign 方法来给模板赋值
		//$this->set_goods_qty();
		header('location:index.php?module=seckill&act=set_goods_qty');
	}

	function set_goods_qty()
	{
		$seckill_configs = file_exists(ROOT_PATH."/data/seckill.inc.php") ? include(ROOT_PATH."/data/seckill.inc.php") : '';
		if(is_array($seckill_configs))
		{
			if(!IS_POST)
			{
				$this->import_resource(array('script' => 'jquery.plugins/jquery.validate.js'));
				$this->assign('goods_qty',$seckill_configs['goods_qty']);
				$this->display('goods_qty.html');
			}
			else
			{
				$config_file = ROOT_PATH . '/data/seckill.inc.php';
				$datas = var_export(array(
	            	'kill_allow' => $seckill_configs['kill_allow'],
					'goods_qty' => $_POST['goods_qty'],
					'period' => $seckill_configs['period'],
				    'start_time' => $seckill_configs['start_time']
				),true);
				/* 写入*/
				file_put_contents($config_file, "<?php\r\n\r\nreturn {$datas};\r\n\r\n?>");
				$this->show_message('edit_goods_qty_successed');
			}
		}
	}

	function set_period()
	{
		$seckill_configs = file_exists(ROOT_PATH."/data/seckill.inc.php") ? include(ROOT_PATH."/data/seckill.inc.php") : '';
		if(is_array($seckill_configs))
		{
			if(!IS_POST)
			{
				$this->import_resource(array('script' => 'jquery.plugins/jquery.validate.js'));
				$this->assign('period',$seckill_configs['period']);
				$this->display('period.html');
			}
			else
			{
				$config_file = ROOT_PATH . '/data/seckill.inc.php';
				$datas = var_export(array(
	            	'kill_allow' => $seckill_configs['kill_allow'],
					'goods_qty' => $seckill_configs['goods_qty'],
					'period' => $_POST['period'],
				    'start_time' => $seckill_configs['start_time']
				),true);
				/* 写入*/ 
				file_put_contents($config_file, "<?php\r\n\r\nreturn {$datas};\r\n\r\n?>");
				$this->show_message('edit_period_successed');
			}
		}
	}

	function set_store_seckill(){

		$conditions = $this->_get_query_conditions(array(
		array(
                'field' => 'grade_name',
                'equal' => 'like',
    	        'name' => 'sgrade_name'
    	        )
    	        ));
    	        $state_info = isset($_GET['state']) ? intval($_GET['state']) : '';
    	        if(!empty($state_info) && $state_info == 1){
    	        	$conditions .= ' AND functions REGEXP "seckill"';
    	        }
    	        elseif(!empty($state_info) && $state_info == 2){
    	        	$conditions .= ' AND functions NOT REGEXP "seckill"';
    	        }
    	        else{
    	        	$conditions .= '';
    	        }
    	        if(!empty($conditions)){
    	        	$this->assign('filtered',true);
    	        }
    	        $page = $this->_get_page(10);
    	        $sgrade_mod = & m('sgrade');
    	        $sgrade_info = $sgrade_mod->find(array(
		    	   'conditions' => '1=1'.$conditions,
		    	   'field' => 'this.*',
		    	   'limit' => $page['limit'],
    	        ));

    	        $store_stage = array(
    	        STORE_APPLYING => Lang::get('store_apply'),
    	        STORE_OPEN => Lang::get('store_open'),
    	        STORE_CLOSED => Lang::get('store_close')
    	        );
    	        $state = array(
    	           '1' => Lang::get('state_yes'),
    	           '2' => Lang::get('state_no')
    	        );
    	        $this->assign('state',$state);
    	        $has_seckill = false;
    	        
    	        foreach($sgrade_info as $k=>$v){
    	        	$sgrade_info[$k]['functions'] = empty($sgrade_info[$k]['functions'])? array() : explode(',',$sgrade_info[$k]['functions']);
    	        	foreach($sgrade_info[$k]['functions'] as $fun_key => $fun_val){
    	        		if($fun_val == 'seckill'){
    	        			$has_seckill = true;
    	        		}
    	        		else{
    	        			$has_seckill = false;
    	        		}
    	        		$sgrade_info[$k]['functions'][$fun_key] = Lang::get($fun_val);
    	        	}
    	        	$sgrade_info[$k]['has_seckill'] = $has_seckill;
    	        }
    	        $this->assign('stores',$sgrade_info);
    	        $this->display('store_acc.html');
	}

	//无刷新更新店铺等级的FUNCTIONS
	function ajax_update_functions(){
		$sgrade_id = isset($_GET['id']) ? intval($_GET['id']) : '';
		if(empty($sgrade_id)){
			$this->show_warning('no_such_sgrade');
			return;
		}
        $sgrade_mod = & m('sgrade');
        $sgrade_info = $sgrade_mod->get(array(
            'conditions' => 'grade_id='.$sgrade_id,
            'fields' => 'functions'
        ));
		$action = isset($_GET['handle']) ? $_GET['handle'] : '';
		if($action == 'add' && !empty($sgrade_info['functions'])){
			$data = 'functions = CONCAT(functions,",seckill")';
		}
		else if($action == 'add'){
			$data = 'functions = "seckill"';
		}
		if($action == 'del' && $sgrade_info['functions'] == 'seckill'){
			$data = 'functions = REPLACE(functions,"seckill","")';
		}
		else if($action == 'del'){
			$data = 'functions = REPLACE(functions,",seckill","")';
		}
		
		if(!$sgrade_mod->edit($sgrade_id,$data)){
			$this->show_warning($this->get_error());

		}
	}

	//批量修改店铺等级的FUNCTIONS
	function batch_update(){
		$sgrade_id = isset($_GET['id']) ? $_GET['id'] : '' ;
		if(empty($sgrade_id)){
			return;
		}
			
		$ids = explode(',',$sgrade_id);
		
		if(is_array($ids)){
			foreach ($ids as $k => $v){
				$ids[$k] = intval($v);
			}
		}
		else{
			$ids = intval($ids);
		}
		$action_ids = $ids;
		$ids = implode(',',$ids);
		$sgrade_mod = & m('sgrade');
		$sgrade_info = $sgrade_mod -> find(array(
    	    'conditions' => 'grade_id in('.$ids.')',
    	    'fields' => 'functions',
		));
		if(count($sgrade_info) > 1){
			$sgrade_function = '';
			$data = '';
			foreach ($sgrade_info as $sgrade_key => $sgrade_val){
				if((preg_match('/seckill/',$sgrade_val['functions']) && !preg_match('/seckill/',$sgrade_function) ||
				!preg_match('/seckill/',$sgrade_val['functions']) && preg_match('/seckill/',$sgrade_function)) && !empty($sgrade_function)){
					$this->show_warning('must_sort_first');
					return;
				}
				$sgrade_function = $sgrade_val['functions'];
				if(preg_match('/seckill/',$sgrade_val['functions'])){
				    $data = 'functions = REPLACE(functions,",seckill","")';
			    }

			    else{
				    $data = 'functions = CONCAT(functions,",seckill")';
			    }
                if($sgrade_val['functions'] == 'seckill'){
			    	$data = 'functions = REPLACE(functions,"seckill","")';
			    }
			    if(empty($sgrade_val['functions'])){
			    	$data = 'functions = "seckill"';
			    }
			    $sgrade_mod->edit($sgrade_key,$data);
			}
		}
		if($this->get_error()){
			$this->show_warning($this->get_error());
		}
		else{
			$this->show_message('update_success');
		}
	}
	//设置开始时间
	 
	function set_start_time(){
		$seckill_configs = file_exists(ROOT_PATH."/data/seckill.inc.php") ? include(ROOT_PATH."/data/seckill.inc.php") : '';
		if(is_array($seckill_configs))
		{
			if(!IS_POST)
			{
				$this->import_resource(array('script' => 'jquery.plugins/jquery.validate.js'));
			
				$this->assign('sTime',$seckill_configs['start_time']);
				$this->display('start_time.html');
			}
			else
			{
				$config_file = ROOT_PATH . '/data/seckill.inc.php';
				$datas = var_export(array(
	            	'kill_allow' => $seckill_configs['kill_allow'],
					'goods_qty' => $seckill_configs['goods_qty'],
					'period' => $seckill_configs['period'],
				    'start_time' => $_POST['start_time']
				),true);
				/* 写入*/ 
				file_put_contents($config_file, "<?php\r\n\r\nreturn {$datas};\r\n\r\n?>");
				$this->show_message('edit_stime_successed');
			}
		}
	}
	
    
    //秒杀商品管理
    
    function seckill_manage(){
    	$page = $this->_get_page(10);
    	$seckill_mod = & m('seckill');
    	$conditions = '';
    	$conditions .= $this->_get_query_conditions(array(
    	    array(
                'field' => 'sk.goods_name',
                'equal' => 'like',
    	        'name' => 'goods_name'
    	        
    	    ),
    	    array(
                'field' => 'sk.sec_state',
                'equal' => '=',
    	        'name' => 'state',
    	    )
    	));
    	if(!empty($conditions)){
    		$this->assign('filtered',true);
    	}
    	$seckill_goods_list = $seckill_mod->find(array(
    	    'conditions' => '1=1'.$conditions.' AND sk.sec_state !='.SECKILL_APPLY,
    	    'join' => 'belongs_to_subject,belong_goods,belong_store',
    	    'fields' => 'this.*,g.goods_name,ss.subject_name,s.store_name,g.price',
    	    'limit' => $page['limit'],
    	    'order' => 'add_time desc',
    	    'count' => true
    	));
        $page['item_count']=$seckill_mod->getCount();
        $this->_format_page($page);
    	$this->assign('page_info', $page);
    	   	
    	$seckill_goods_list = empty($seckill_goods_list) ? array() : $seckill_goods_list;
    	$seckill_state = array(
    	    SECKILL_NOT_START => Lang::get('seckill_not_start'),
    	    SECKILL_REFUCE => Lang::get('seckill_refuce'),
    	    SECKILL_START => Lang::get('seckill_start'),
    	    SECKILL_END => Lang::get('seckill_end'),
    	);
    	$this->assign('state',$seckill_state);
    	foreach ($seckill_goods_list as $k => $v){
    		$seckill_goods_list[$k]['sec_price'] =  unserialize($v['sec_price']); 
    		$seckill_goods_list[$k]['sec_price'] = empty($seckill_goods_list[$k]['sec_price']) ? array() : $seckill_goods_list[$k]['sec_price'];
    		$i=0;
    		foreach ($seckill_goods_list[$k]['sec_price'] as $sk => $sv){
    			$seckill_goods_list[$k]['sec_price'][$i]['price'] = $sv['price'];
    			$i++;
    		}
    		$seckill_goods_list[$k]['sec_state'] = $seckill_state[$v['sec_state']];
    	}

    	$this->assign('seckill_goods_lists',$seckill_goods_list);
    	$this->display('seckill_manage.html');
    	
    }
    
    //秒杀申请
    function seckill_apply(){
    	$page = $this->_get_page(10);
    	$conditions = $this->_get_query_conditions(array(
		array(
                'field' => 's.store_name',
                'equal' => 'like',
    	        'name' => 'store_name'
    	        )
    	));
    	if(!empty($conditions)){
    		$this->assign('filtered',true);
    	}
    	$seckill_mod = & m('seckill');
    	$seckill_goods_list = $seckill_mod->find(array(
    	    'conditions' => 'sk.sec_state='.SECKILL_APPLY.$conditions,
    	    'join' => 'belongs_to_subject,belong_goods,belong_store',
    	    'fields' => 'this.*,g.goods_name,ss.subject_name,s.store_name,s.owner_name,g.price,g.default_image',
    	    'limit' => $page['limit'],
    	    'count' => true
    	));
        $page['item_count']=$seckill_mod->getCount();
        $this->_format_page($page);
    	$this->assign('page_info', $page);
    	$seckill_goods_list = empty($seckill_goods_list) ? array() : $seckill_goods_list;
    	foreach ($seckill_goods_list as $sec_key => $sec_val){
    		$store_list[$sec_val['store_id']]['store_id'] = $sec_val['store_id'];
    		$store_list[$sec_val['store_id']]['store_name'] = $sec_val['store_name'];
    		$store_list[$sec_val['store_id']]['owner_name'] = $sec_val['owner_name'];
    		$store_list[$sec_val['store_id']]['count']++;
    		$store_list[$sec_val['store_id']]['goods_name'] = $sec_val['goods_name'];
    		$store_list[$sec_val['store_id']]['subject_name'] = $sec_val['subject_name'];
    		$store_list[$sec_val['store_id']]['default_image'] = $sec_val['default_image'];
    	}

    	$this->assign('seckill_goods_lists',$store_list);
    	$this->display('seckill_apply.html');
    }
    
    //查看秒杀商品列表
    
    function seckill_goods_list(){
    	$page = $this->_get_page(10);
    	if(empty($_GET['id'])){
    		$this->show_warning('no_such_store');
    		return ;
    	}
    	$conditions = $this->_get_query_conditions(array(
		array(
                'field' => 'sk.goods_name',
                'equal' => 'like',
    	        'name' => 'goods_name'
    	        )
    	));
    	$id = intval($_GET['id']);
    	$seckill_mod = & m('seckill');
    	$seckill_goods_list = $seckill_mod->find(array(
    	    'conditions' => 'sk.sec_state='.SECKILL_APPLY.' AND sk.store_id='.$id.$conditions,
    	    'join' => 'belongs_to_subject,belong_goods,belong_store',
    	    'fields' => 'this.*,ss.subject_name,s.store_name,s.owner_name,g.price',
    	    'limit' => $page['limit'],
    	    'count' => true
    	));
    	$page['item_count']=$seckill_mod->getCount();
        $this->_format_page($page);
    	$this->assign('page_info', $page);
    	$seckill_goods_list = empty($seckill_goods_list) ? array() : $seckill_goods_list;
    	//查找商品图片
    	$godds_image_mod = & m('goodsimage');
    	$goods_image_info = $godds_image_mod->find(array(
    	   'fields' => 'goods_id,image_url',
    	));
    	
    	$goods_image_info = empty($goods_image_info) ? array() : $goods_image_info;
    	foreach($goods_image_info as $image_key => $image_val){
    		$image_data[$image_val['goods_id']][$image_val['image_id']]['image_url'] = $image_val['image_url'];
    	}
    	foreach ($seckill_goods_list as $k => $v){
    		$seckill_goods_list[$k]['sec_price'] =  unserialize($v['sec_price']); 
    		$seckill_goods_list[$k]['sec_price'] = empty($seckill_goods_list[$k]['sec_price']) ? array() : $seckill_goods_list[$k]['sec_price'];
    		$i=0;
    		foreach ($seckill_goods_list[$k]['sec_price'] as $sk => $sv){
    			$seckill_goods_list[$k]['sec_price'][$i]['price'] = $sv['price'];
    			$i++;
    		}
    		$seckill_goods_list[$k]['image'] = $image_data[$v['goods_id']];
    	}
    	$this->assign('filtered',empty($conditions) ? false : true);
    	$this->assign('seckill_goods_lists',$seckill_goods_list);
    	$this->display('seckill_goods_list.html');
    }
    
    //秒杀商品查看
    
    function seckill_view(){
    	if(empty($_GET['id'])){
    		$this->show_warning('no_such_seckill');
    		return;
    	}
    	else{
    		$id = intval($_GET['id']);
    	}
    	$seckill_mod = & m('seckill');
    	$seckill_info = $seckill_mod->get(array(
    	    'conditions' => 'sec_id='.$id,
    	    'join' => 'belong_store,belongs_to_subject,belong_goods',
    	    'fields' => 'this.*,g.goods_name,g.price,g.default_image,s.store_name,ss.subject_name'
    	));
        
    	//获取商品规格详细
    	$goodsspec_mod = & m('goodsspec');
    	$goodsspec_info = $goodsspec_mod->find(array(
    	   'conditions' => 'goods_id='.$seckill_info['goods_id'],
    	   'fields' => '*',
    	)); 
    	$seckill_info['sec_price'] = unserialize($seckill_info['sec_price']);
    	$seckill_info['sec_price'] = empty($seckill_info['sec_price']) ? array() : $seckill_info['sec_price'];
    	foreach ($seckill_info['sec_price'] as $k=>$v){
    		$goodsspec_info[$k]['seckill_price'] = $v['price'];
    	}
    	$seckill_info['_spec'] = $goodsspec_info;
    	$this->assign('seckill_info',$seckill_info);
    	$this->display('seckill_view.html');
    }
    
    //通过审核
    function seckill_agree(){
    	if(empty($_GET['id'])){
    		$this->show_warning('no_such_seckill');
    		return ;
    	}
    	$id = intval($_GET['id']);
    	$seckill_mod = & m('seckill');
        $ids = trim($_GET['id']);
        $ids = explode(',',$ids);
        foreach ($ids as $k => $v){
        	$ids[$k] = intval($v);
        }
    	$sekill_info = $seckill_mod->get(array(
    	   'conditions' => 'sec_id='.$id,
    	   'fields' => 'goods_name,store_id',
    	));
    	if(count($ids) < 2){
    		$title = sprintf(Lang::get('seckill_agree_msg_title'),$sekill_info['goods_name']);
    		$content = sprintf(Lang::get('seckill_agree_msg_cont'),$sekill_info['goods_name']);
    	}
    	else{
    		$title = sprintf(Lang::get('seckill_agree_msg_title'),$sekill_info['goods_name'].'...');
    		$content = sprintf(Lang::get('seckill_agree_msg_cont'),$sekill_info['goods_name'].'...');
    	}
    	if($seckill_mod->edit($ids,'sec_state='.SECKILL_NOT_START)){
    		//发送系统消息
		    $ms =& ms();
		    $ms->pm->send(MSG_SYSTEM, $sekill_info['store_id'], $title, $content);
    		$this->show_warning('seckill_agree_succeful');
    		return;
    	}
    	else{
    		$this->show_warning($this->get_error());
    	}
    }
    
    //拒绝通过
    
    function seckill_refuce(){
    	if(!$_POST){
    		$this->display('seckill_refuce.html');
    		return;
    	}
    	$id = intval($_GET['id']);
    	$seckill_mod = & m('seckill');
    	$content = $_POST['refuce_content'];

    	$sekill_info = $seckill_mod->get(array(
    	   'conditions' => 'sec_id='.$id,
    	   'fields' => 'goods_name,store_id,goods_id',
    	));
    	$title = sprintf(Lang::get('seckill_refuce_msg_title'),$sekill_info['goods_name']);
    	$goods_mod = & m('goods');
        $goods_mod->db->query('START TRANSACTION');
        $edit_success = true;
        if(!$goods_mod->edit($sekill_info['goods_id'],'if_show=1,if_seckill=0')){
    		$this->show_warning($this->get_error());
    		$goods_mod->db->query('ROLLBACK');
    		 $edit_success= false;
    		return ;
    	}
    	if(!$seckill_mod->edit($id,'sec_state='.SECKILL_REFUCE)){
    		$this->show_warning($this->get_error());
    		$goods_mod->db->query('ROLLBACK');
    		$edit_success= false;
    		return ;
    	}
    	$goods_mod->db->query('COMMIT');
    	if(!$this->get_error() && $edit_success){
    		//发送系统消息
		    $ms =& ms();
		    $ms->pm->send(MSG_SYSTEM, $sekill_info['store_id'], $title, $content);
    		$this->show_warning('seckill_refuce_success',Lang::get('back_to_apply'),'index.php?module=seckill&act=seckill_goods_list&id='.$sekill_info['store_id']);
    	}
    	else{
    		$this->show_warning('seckill_refuce_fail',Lang::get('back_to_apply'),'index.php?module=seckill&act=seckill_goods_list');
    	}
    }
    
    //删除秒杀
    
    function seckill_del(){
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
    
    //秒杀主题
    function seckill_subject(){
    	$page = $this->_get_page(10);
    	if(empty($_GET['subject_name'])){
    		$conditions = '';
    	}
    	else{
    		$this->assign('filtered',true);
    		$conditions = ' AND subject_name like "%'.trim($_GET['subject_name']).'%"';
    	}
    	$seckill_subject_mod = & m('seckill_subject');
    	$seckill_list = $seckill_subject_mod->find(array(
    	    'conditions' => '1=1'.$conditions,
    	    'fields' => '*',
    	    'limit' => $page['limit'],
    	    'count' => true
    	));
    	$page['item_count']=$seckill_subject_mod->getCount();
        $this->_format_page($page);
        $this->assign('page_info', $page);
    	$this->assign('subject_lists',$seckill_list);
    	$this->display('seckill_subject.html');
    }
    
    //添加秒杀主题
    
    function subject_add(){
    	if(!$_POST){
    		$this->display('seckill_subject_add.html');
    		return;
    	}
    	if(empty($_POST['subject_name'])){
    		$this->show_warning('fill_subject_name');
    		return ;
    	}
    	else{
    		$subject_name = trim($_POST['subject_name']);
    	}
    	if(empty($_POST['subject_detail'])){
    		$this->show_warning('fill_subject_detail');
    		return ;
    	}
    	else{
    		$subject_detail = trim($_POST['subject_detail']);
    	}
       $subject_mod = & m('seckill_subject');
       $subject_data = array(
          'subject_name' => $subject_name,
          'subject_desc' => $subject_detail,
          'subject_state' => intval($_POST['if_show'])
       );
       if($subject_mod->add($subject_data)){
          $this->show_message('sec_subject_add_success',Lang::get('back_to_subject_list'),'index.php?module=seckill&act=seckill_subject',
          Lang::get('continute_add'),'index.php?module=seckill&act=subject_add');
       }
       else{
       	  $this->show_warning($this->get_error());
       }
    }
    
    //检测主题名称唯一性
    function check_subject(){
    	$subject = empty($_GET['subject_name']) ? '' : trim($_GET['subject_name']);
    	if(!$subject){
    		echo ecm_json_encode(false);
    		return;
    	}
    	$subject_mod = & m('seckill_subject');
    	$subject_info = $subject_mod->get("subject_name='{$subject}'");
    	if(!empty($subject_info)){
    		echo ecm_json_encode(false);
    		return ;
    	}
    	echo ecm_json_encode(true);
    }
    
    //秒杀主题编辑
    
    function edit_subject(){
    	if(empty($_GET['id'])){
    		$this->show_warning('no_such_subject');
    		return;
    	}
    	$id = intval($_GET['id']);
    	$subject_mod = & m('seckill_subject');
    	$sec_subject_info = $subject_mod->get(array(
    		'conditions' => 'subject_id='.$id,
    		'fields' => '*'
    	));
    	if(!$_POST){

    		$this->assign('sec_subject_info',$sec_subject_info);
    		$this->display('seckill_subject_add.html');
    		return;
    	}
        	if(empty($_POST['subject_name'])){
    		$this->show_warning('fill_subject_name');
    		return ;
    	}
    	else{
    		$subject_name = trim($_POST['subject_name']);
    	}
    	if(empty($_POST['subject_detail'])){
    		$this->show_warning('fill_subject_detail');
    		return ;
    	}
    	else{
    		$subject_detail = trim($_POST['subject_detail']);
    	}
        $subject_data = array(
           'subject_name' => $subject_name,
           'subject_desc' => $subject_detail,
           'subject_state' => intval($_POST['if_show'])
        );
        if($subject_data['subject_name'] == $subject_name && $sec_subject_info['subject_desc'] == $subject_detail && $sec_subject_info['subject_state'] == intval($_POST['if_show'])){
        	$this->show_message('subject_no_change');
        	return;
        }
        if($subject_mod->edit($id,$subject_data)){
        	$this->show_message('subject_edit_success');
        }
        else{
        	$this->show_message($this->get_error());
        }
    }
    
    //秒杀主题删除
    
    function seckill_subject_del(){
    	$id = $_GET['id'];
    	if(empty($id)){
    		$this->show_warning('no_such_subject');
    		return ;
    	}
    	$ids = explode(',',$id);
    	foreach ($ids as $k=>$v){
    		$ids[$k] = intval($v);
    	}
    	
    	$subject_mod = & m('seckill_subject');
    	if($subject_mod->drop($ids)){
    		$this->show_message('subject_del_success');
    	}
    	else{
    		$this->show_warning($this->get_error());
    	}
    }
    
    //推荐秒杀商品
    
    function seckill_recommended(){
    	if(empty($_GET['id'])){
    		return ;
    	}
    	$seckill_mod = & m('seckill');
    	if(trim($_GET['ajax'])){
    		$id = intval($_GET['id']);
    		$edit_val = trim($_GET['type']) == 'active' ? 'recommended='.SECKILL_RECOMMENDED : 'recommended='.SECKILL_NOT_RECOMMENDED;
    		if($seckill_mod->edit($id,$edit_val)){
    			echo 'success';
    		}
    		else{
    			echo 'error';
    		}
    		return;
    	}
    	$id = $_GET['id'];
    	$ids = explode(',',$id);
    	foreach ($ids as $k=>$v){
    		$ids[$k] = intval($v);
    	}
    	//查找当前秒杀商品信息
    	$seckill_info = $seckill_mod->find(array( 
    	    'conditions' => 'sec_id in('.implode(',',$ids).')',
    	    'fields' => 'sec_state,recommended'
    	));
    	$end_seckill_state = array(
    	    SECKILL_END => SECKILL_END,
    	    SECKILL_REFUCE => SECKILL_REFUCE
    	);
    	$seckill_info = empty($seckill_info) ? array() : $seckill_info;
    	foreach ($seckill_info as $key => $val){
    		if(array_key_exists($val['sec_state'],$end_seckill_state)){
    			$this->show_warning('must_sort_first');
    			return;
    		}
    		if($val['recommended'] == SECKILL_RECOMMENDED){
    			$this->show_warning('goods_has_recommended');
    			return;
    		}
    	}
    	if($seckill_mod->edit($ids,'recommended='.SECKILL_RECOMMENDED)){
    		$this->show_message('seckill_recommended_success');
    	}
    	else{
    		$this->show_warning($this->get_error());
    	}
    }
    
    //更新主题是否显示
    
    function subject_ajax_update(){
        $id = empty($_GET['id']) ? '' : intval($_GET['id']);
        
    	$type = empty($_GET['type']) ? '' : trim($_GET['type']);
    	if(!empty($id) && !empty($type)){
    		if($type == 'show'){
    			$data = array(
    			   'subject_state' => 1,
    			);
    		}
    		else if($type == 'hide'){
    			$data = array(
    			   'subject_state' => 0,
    			);
    		}
    		$subject_mod = & m('seckill_subject');
    		if($subject_mod->edit($id,$data)){
    			echo 'true';
    		}
    		else{
    			echo 'false';
    		}
    	}
    }

}

?>