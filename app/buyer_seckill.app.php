<?php
//买家对秒杀商品的处理类

class Buyer_seckillApp extends StorebaseApp{
    
    //秒杀商品

    function seckill_goods(){
        $seckill_file = ROOT_PATH . '/data/seckill.inc.php';
    	if(!file_exists($seckill_file)){
    		$this->show_warning('the_action_has_not_exists');
    		return ;
    	}
        /* 参数 id */
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if (!$id)
        {
            $this->show_warning('Hacking Attempt');
            return;
        }
        $this->get_seckill_info($id);
        /* 更新浏览次数 */
        
        $this->_update_views($id);
       
        /*
         * 更新秒杀商品浏览次数
         */
        $seckill_mod = & m('seckill');
        $seckill_mod->edit($sid,'views=views+1');
        //是否开启验证码
        if (Conf::get('captcha_status.goodsqa'))
        {
            $this->assign('captcha', 1);
        }

        $this->assign('guest_comment_enable', Conf::get('guest_comment'));
    	$this->display('goods.index.html');
    }
    
    //秒杀商品页
    
    function seckill_list(){
    	//秒杀
        $seckill_configs = file_exists(ROOT_PATH."/data/seckill.inc.php") ? include(ROOT_PATH."/data/seckill.inc.php") : '';
    	if(!file_exists(ROOT_PATH . '/data/seckill.inc.php') || !is_array($seckill_configs)){
    		$this->show_warning('the_action_has_not_exists');
    		return ;
    	}
    	$prev_seckill_info = array();
    	$this->_curlocal(
		LANG::get('seckill'));
    	//获取秒杀商品信息
    	$seckill_mod = & m('seckill');
	    $sTime = explode(':',$seckill_configs['start_time']);
	    $sTimeSec = $sTime[0]*3600+$sTime[1]*60+$sTime[2];
    	$today = getdate(gmtime());
        $end_time = mktime(0,0,0,$today['mon'],$today['mday'],$today['year'])+$sTimeSec+$seckill_configs['period'];
      	if(($end_time - gmtime()) <= 0){
      		$leave_time = Lang::get('seckill_end');
      	}
      	else if(($end_time - gmtime()) > 0 && (gmtime()-$sTimeSec)>0){
      		$leave_time = $end_time - gmtime();
      	}
      	
      	if(($sTimeSec - ($today['hours']*3600 + $today['minutes']*60 + $today['seconds'])) > 0){	
      		$start_leave_time = $sTimeSec - ($today['hours']*3600 + $today['minutes']*60 + $today['seconds']);
      	}
    	$seckill_info = $seckill_mod->find(array(
    	     'join' => 'belongs_to_subject,belong_goods,belong_store',
    	     'order' => 'add_time asc',
    	   	 'fields' => 'this.*,ss.subject_name,g.price,g.default_image,s.store_name',     
      	));
      	$seckill_info = empty($seckill_info) ? array() : $seckill_info;
      	foreach ($seckill_info as $sk => $sv){ 
      		$seckill_info[$sk]['sec_price'] = unserialize($sv['sec_price']);
      		$seckill_info[$sk]['sec_price'] = empty($seckill_info[$sk]['sec_price']) ? array() : $seckill_info[$sk]['sec_price'];
      		$i = 0;	  
            foreach ($seckill_info[$sk]['sec_price'] as $k => $v){
        	    $seckill_info[$sk]['sec_price'][$i] = array('spec_id' => $k,'spec_price' => $v['price']);
                $i ++ ;
            }
            if($seckill_info[$sk]['sec_state'] == SECKILL_APPLY || $seckill_info[$sk]['sec_state'] == SECKILL_REFUCE){
            	continue;
            }
            //今天秒杀活动商品
            if(is_numeric($leave_time) && $leave_time > 0 && $seckill_info[$sk]['sec_state'] != SECKILL_END ){
                if($seckill_info[$sk]['start_time'] >= (mktime(0,0,0,$today['mon'],$today['mday'],$today['year'])) && $seckill_info[$sk]['start_time'] <= $end_time && $start_leave_time <=0 && count($current_seckill_info) <= 3){
            	    $current_seckill_info[$sk] = $seckill_info[$sk];
            	    //更新状态为开始
            	    $seckill_curr_id[$sk] = $sk; 
                }
            }
            //下场秒杀活动商品
            
            if($seckill_info[$sk]['start_time'] >= (mktime(0,0,0,$today['mon'],$today['mday'],$today['year'])+86400) && $seckill_info[$sk]['start_time'] <= ($end_time+86400) && count($next_seckill_info) <= 7){
            	$next_seckill_info[$sk] = $seckill_info[$sk];
            }
            
            //秒杀历史
      	    if(/*$seckill_info[$sk]['start_time'] >= (mktime(0,0,0,$today['mon'],$today['mday'],$today['year'])-86400) && */$seckill_info[$sk]['start_time'] <= ($end_time) && count($prev_seckill_info) <= 7){
            	$prev_seckill_info[$sk] = $seckill_info[$sk];
            }
            
      	}
      	if(isset($seckill_curr_id)){
      		$seckill_mod->edit($seckill_curr_id,'sec_state='.SECKILL_START);
      	}
      	krsort($prev_seckill_info);
    	$time_alert_title = ($today['hours']*3600 + $today['minutes']*60 + $today['seconds']) > $sTimeSec ? Lang::get('seckill_leave_time') : Lang::get('seckill_stime_leave');
    	$this->assign('start_leave_time',(int)($start_leave_time/3600).':'.(int)($start_leave_time%3600/60).':'.$start_leave_time%3600%60);
    	$this->assign('time_alert_title',$time_alert_title);
      	$this->assign('leave_time',is_numeric($leave_time) == TRUE ? (int)($leave_time/3600).':'.(int)($leave_time%3600/60).':'.$leave_time%3600%60 : $leave_time);
       	$this->assign('seckill_lists',$current_seckill_info);
      	$this->assign('next_seckill_info',$next_seckill_info);
      	$this->assign('prev_seckill_info',$prev_seckill_info);
      	$this->assign('period',(int)($seckill_configs['period']/3600).':'.(int)($seckill_configs['period']%3600/60).':'.(int)($seckill_configs['period']%3600%60));
      	$this->assign('page_title', Lang::get('seckill') . ' - ' . Conf::get('site_title'));
    	$this->display('seckill.goods.html');
    }
    
    //秒杀商品详细页
    
    function seckill_search(){
        $seckill_configs = file_exists(ROOT_PATH."/data/seckill.inc.php") ? include(ROOT_PATH."/data/seckill.inc.php") : '';
       	if(!file_exists(ROOT_PATH . '/data/seckill.inc.php') || !is_array($seckill_configs)){
    		$this->show_warning('the_action_has_not_exists');
    		return ;
    	}
    	$conditions = empty($_GET['id']) ? '' : ' AND sk.subject_id='.intval($_GET['id']);
    	if(empty($conditions)){
    		$this->_curlocal(
		    LANG::get('seckill'),'index.php?app=buyer_seckill&act=seckill_list',Lang::get('details'));
    	}
		else{
    		$this->_curlocal(
		    LANG::get('seckill'),'index.php?app=buyer_seckill&act=seckill_search',trim($_GET['sub_name']));
		}
		$max_per_page = 20;
		if(!empty($_GET['max_page'])){
			$max_per_page = intval($_GET['max_page']);
		}
		else{
			$max_per_page = isset($_SESSION['page_per_num']) ? $_SESSION['page_per_num'] : $max_per_page;
		}
		if(isset($_SESSION['page_per_num'])){
			setcookie('page_per_num',$max_per_page,gmtime()+3600);
		}
		$this->assign('page_per_num',$max_per_page);
		$page = $this->_get_page($max_per_page);
	    $sTime = explode(':',$seckill_configs['start_time']);
	    $sTimeSec = $sTime[0]*3600+$sTime[1]*60+$sTime[2];
	    
    	$today = getdate(gmtime());
    	$nowTimeSec = $today['hours']*3600+$today['minutes']*60+$today['seconds'];
    	if($sTimeSec > $nowTimeSec){
    		$this->show_warning('the_seckill_not_start');
    		return ;
    	}
        $end_time = mktime(0,0,0,$today['mon'],$today['mday'],$today['year'])+$sTimeSec+$seckill_configs['period'];
    	$seckill_mod = & m('seckill');
    	$order = '';
    	if(!empty($_GET['order_by'])){
    		$order = trim($_GET['order_by']);
    	}
        $today_seckill_goods = $seckill_mod->find(array(
             'conditions' => "sk.start_time >=".(mktime(0,0,0,$today['mon'],$today['mday'],$today['year'])).' AND start_time <='.$end_time.$conditions.' AND sec_state='.SECKILL_START,
    	     'join' => 'belongs_to_subject,belong_goods',
    	     'order' => $order,
    	   	 'fields' => 'this.*,ss.subject_name,ss.subject_id,g.price,g.default_image',    
             'limit' => $page['limit'],
             'count' => true
      	));
        $page['item_count'] =$seckill_mod->getCount();
        $this->_format_page($page);
    	$this->assign('page_info', $page);
      	$today_seckill_goods = empty($today_seckill_goods) ? array() : $today_seckill_goods;
      	foreach ($today_seckill_goods as $sk => $sv){
      		$subject_list[$sv['subject_id']]['subject_id'] = $sv['subject_id'];
      		$subject_list[$sv['subject_id']]['subject_name'] = $sv['subject_name'];  //对主题进行统计
      		$subject_list[$sv['subject_id']]['subject_count']++;
      	    $today_seckill_goods[$sk]['sec_price'] = unserialize($sv['sec_price']);
      		$today_seckill_goods[$sk]['sec_price'] = empty($today_seckill_goods[$sk]['sec_price']) ? array() : $today_seckill_goods[$sk]['sec_price'];
      		$i = 0;	  
            foreach ($today_seckill_goods[$sk]['sec_price'] as $k => $v){
        	    $today_seckill_goods[$sk]['sec_price'][$i] = array('spec_id' => $k,'spec_price' => $v['price']);
                $i ++ ;
            }
      	}
        if(($end_time - gmtime()) <= 0){
      		$leave_time = Lang::get('seckill_end');
      	}
      	else if(($end_time - gmtime()) > 0 && (gmtime()-$sTimeSec)>0){
      		$leave_time = $end_time - gmtime();
      	}
      	$order_data = array(
      	   'add_time' => Lang::get('add_time'),
      	   'recommended' => Lang::get('recommended')
      	);
      	$this->assign('current_seckill',true);
      	$this->assign('orders',$order_data);
      	$this->assign('page_title', Lang::get('seckill') . ' - ' . Conf::get('site_title'));
      	$this->assign('seckill_lists',$today_seckill_goods);
      	$this->assign('leave_time',is_numeric($leave_time) ? (int)($leave_time/3600).':'.(int)($leave_time%3600/60).':'.$leave_time%3600%60 : $leave_time);
      	$this->assign('subject_lists',$subject_list);
    	$this->display('seckill.goods.search.html');
    }
    
    
    /**
     * 取得公共信息
     *
     * @param   int     $id
     * @return  false   失败
     *          array   成功
     */
    function _get_common_info($id)
    {
        $cache_server =& cache_server();
        $key = 'page_of_goods_' . $id;
        $data = $cache_server->get($key);
        $cached = true;
        if ($data === false)
        {
            $cached = false;
            $data = array('id' => $id);

            /* 商品信息 */
            $goods_mod = & m('goods');
            $goods = $goods_mod->get_info($id);
            if (!$goods || $goods['if_seckill'] == 0 || $goods['closed'] == 1 || $goods['state'] != 1)
            {
                $this->show_warning('goods_not_seckill');
                exit();
                return false;
            }
            $data['goods'] = $goods;

            /* 店铺信息 */
            if (!$goods['store_id'])
            {
                $this->show_warning('store of goods is empty');
                exit();
                return false;
            }
            $this->set_store($goods['store_id']);
            $data['store_data'] = $this->get_store_data();

            /* 当前位置 */
            $data['cur_local'] = $this->_get_curlocal($goods['cate_id']);

            $cache_server->set($key, $data, 1800);
        }
        if ($cached)
        {
            $this->set_store($data['goods']['store_id']);
        }
        return $data;
    }
    
    /* 赋值公共信息 */
    function _assign_common_info($data)
    {
        /* 商品信息 */
        $goods = $data['goods'];

        $this->assign('goods', $goods);
        $this->assign('sales_info', sprintf(LANG::get('sales'), $goods['sales'] ? $goods['sales'] : 0));
        $this->assign('comments', sprintf(LANG::get('comments'), $goods['comments'] ? $goods['comments'] : 0));
     
        /* 店铺信息 */
        $this->assign('store', $data['store_data']);

        /* 浏览历史 */
        $this->assign('goods_history', $this->_get_goods_history($data['id']));

        /* 默认图片 */
        $this->assign('default_image', Conf::get('default_goods_image'));

        /* 当前位置 */
        $this->_curlocal($data['cur_local']);

        /* 页面标题 */
        $this->assign('page_title', $goods['goods_name'] . ' - ' . Conf::get('site_title'));

        $this->import_resource(array(
            'script' => 'jquery.jqzoom.js',
            'style' => 'res:jqzoom.css'
        ));
    }
    
    /* 更新浏览次数 */
    function _update_views($id)
    {
        $goodsstat_mod =& m('goodsstatistics');
        $goodsstat_mod->edit($id, "views = views + 1");
    }
    
    /**
     * 取得当前位置
     *
     * @param int $cate_id 分类id
     */
    function _get_curlocal($cate_id)
    {
        $parents = array();
        if ($cate_id)
        {
            $gcategory_mod =& bm('gcategory');
            $parents = $gcategory_mod->get_ancestor($cate_id, true);
        }

        $curlocal = array(
            array('text' => LANG::get('all_categories'), 'url' => url('app=category')),
        );
        foreach ($parents as $category)
        {
            $curlocal[] = array('id' => $category['cate_id'], 'text' => $category['cate_name'], 'url' => url('app=search&cate_id=' . $category['cate_id']));
        }
        $curlocal[] = array('text' => LANG::get('goods_detail'));

        return $curlocal;
    }
    /* 取得浏览历史 */
    function _get_goods_history($id, $num = 9)
    {
        $goods_list = array();
        $goods_mod = & m('goods');
        $goods_ids  = ecm_getcookie('goodsBrowseHistory');
        $goods_ids  = $goods_ids ? explode(',', $goods_ids) : array();
        if ($goods_ids)
        {
            $rows = $goods_mod->find(array(
                'conditions' => $goods_ids,
                'fields'     => 'goods_name,default_image',
            ));
            foreach ($goods_ids as $goods_id)
            {
                if (isset($rows[$goods_id]))
                {
                    empty($rows[$goods_id]['default_image']) && $rows[$goods_id]['default_image'] = Conf::get('default_goods_image');
                    $goods_list[] = $rows[$goods_id];
                }
            }
        }
        $goods_ids[] = $id;
        if (count($goods_ids) > $num)
        {
            unset($goods_ids[0]);
        }
        ecm_setcookie('goodsBrowseHistory', join(',', array_unique($goods_ids)));

        return $goods_list;
    }
    
    /* 商品评论 */
    function comments()
    {
    	
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if (!$id)
        {
            $this->show_warning('Hacking Attempt');
            return;
        }
        $this->get_seckill_info($id);
        $data = $this->_get_common_info($id);
        if ($data === false)
        {
            return;
        }
        else
        {
            $this->_assign_common_info($data);
        }

        /* 赋值商品评论 */
        $data = $this->_get_goods_comment($id, 10);
        $this->_assign_goods_comment($data);

        $this->display('goods.comments.html');
    }
    
    /* 取得商品评论 */
    function _get_goods_comment($goods_id, $num_per_page)
    {
    	
        $data = array();

        $page = $this->_get_page($num_per_page);
        $order_goods_mod =& m('ordergoods');
        $comments = $order_goods_mod->find(array(
            'conditions' => "goods_id = '$goods_id' AND evaluation_status = '1'",
            'join'  => 'belongs_to_order',
            'fields'=> 'buyer_id, buyer_name, anonymous, evaluation_time, comment, evaluation',
            'count' => true,
            'order' => 'evaluation_time desc',
            'limit' => $page['limit'],
        ));
        $data['comments'] = $comments;

        $page['item_count'] = $order_goods_mod->getCount();
        $this->_format_page($page);
        $data['page_info'] = $page;
        $data['more_comments'] = $page['item_count'] > $num_per_page;

        return $data;
    }
    
        /* 赋值商品评论 */
    function _assign_goods_comment($data)
    {
        $this->assign('goods_comments', $data['comments']);
        $this->assign('page_info',      $data['page_info']);
        $this->assign('more_comments',  $data['more_comments']);
    }
    
    /*秒杀信息*/
    
    function get_seckill_info($id){
        /* 参数 id */
        $sid = empty($_GET['sid']) ? 0 : intval($_GET['sid']);
        if (!$id || !$sid)
        {
            $this->show_warning('Hacking Attempt');
            exit();
            return;
        }
    	
        $seckill_mod = & m('seckill');
        
        $seckill_info = $seckill_mod -> get(array(
            'conditions' => 'goods_id='.$id.' AND sec_id='.$sid,
            'fields' => 'sec_price,sec_quantity,sec_state'
        ));
        
        if($seckill_info['sec_state'] == SECKILL_APPLY){
        	$this->show_warning('the_seckill_apply');
        	exit();
        	return;
        }
        if($seckill_info['sec_state'] == SECKILL_NOT_START){
        	$this->show_warning('the_seckill_not_start');
        	exit();
        	return;
        }
        if($seckill_info['sec_state'] == SECKILL_REFUCE){
        	$this->show_warning('the_seckill_refuce');
        	exit();
        	return;
        }
         //获取秒杀结束时间
        
        $seckill_configs = file_exists(ROOT_PATH."/data/seckill.inc.php") ? include(ROOT_PATH."/data/seckill.inc.php") : '';
        $nowTime = getdate(gmtime());
        $nowTimeSec = $nowTime['hours']*3600+$nowTime['minutes']*60+$nowTime['seconds'];
        if(is_array($seckill_configs)){
        	$seckill_configs['start_time'] = explode(':',$seckill_configs['start_time']);
         	$end_time = $seckill_configs['start_time'][0]*3600 + $seckill_configs['start_time'][1] * 60 + $seckill_configs['start_time'][2] + $start_time_num + $seckill_configs['period'];
         	$leve_time = $end_time - $nowTimeSec;
         	if($leve_time <= 0){
         		$leve_time = Lang::get('seckill_end');
         		$this->show_warning('seckill_end');
         		exit();
         		return;
         		
         	}
         	else{
         		$leve_time = (int)($leve_time / 3600) . ':' .  (int)(($leve_time % 3600) / 60) . ':' . (int)(($leve_time % 3600) % 60);
         	}
        	$this->assign('leve_time',$leve_time);
        }


        /* 可缓存数据 */
        $data = $this->_get_common_info($id);
        if ($data === false)
        {
            return;
        }
        else
        {
            $this->_assign_common_info($data);
        }
        //获取秒杀信息

        $seckill_info['sec_price'] = unserialize($seckill_info['sec_price']);
        $seckill_info['sec_price'] = empty($seckill_info['sec_price']) ? array() : $seckill_info['sec_price'];
        $i = 0;
        foreach ($seckill_info['sec_price'] as $k => $v){
        	$seckill['sec_price'][$i] = array('spec_id' => $k,'spec_price' => $v['price']);
            $i++;
        }
        foreach ($seckill_info as $sk => $sv){
        	if($sk != 'sec_price'){
        		$seckill[$sk] = $seckill_info[$sk];
        	}
        }
        
          
        $this->assign('seckill_info',$seckill);
    }
    /* 销售记录 */
    function saleslog()
    {
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if (!$id)
        {
            $this->show_warning('Hacking Attempt');
            return;
        }
        $this->get_seckill_info($id);
        $data = $this->_get_common_info($id);
        if ($data === false)
        {
            return;
        }
        else
        {
            $this->_assign_common_info($data);
        }

        /* 赋值销售记录 */
        $data = $this->_get_sales_log($id, 10);
        $this->_assign_sales_log($data);

        $this->display('goods.saleslog.html');
    }
    
    /* 取得销售记录 */
    function _get_sales_log($goods_id, $num_per_page)
    {
        $data = array();

        $page = $this->_get_page($num_per_page);
        $order_goods_mod =& m('ordergoods');
        $sales_list = $order_goods_mod->find(array(
            'conditions' => "goods_id = '$goods_id' AND status = '" . ORDER_FINISHED . "'",
            'join'  => 'belongs_to_order',
            'fields'=> 'buyer_id, buyer_name, add_time, anonymous, goods_id, specification, price, quantity, evaluation',
            'count' => true,
            'order' => 'add_time desc',
            'limit' => $page['limit'],
        ));
        $data['sales_list'] = $sales_list;

        $page['item_count'] = $order_goods_mod->getCount();
        $this->_format_page($page);
        $data['page_info'] = $page;
        $data['more_sales'] = $page['item_count'] > $num_per_page;

        return $data;
    }
    /* 赋值销售记录 */
    function _assign_sales_log($data)
    {
        $this->assign('sales_list', $data['sales_list']);
        $this->assign('page_info',  $data['page_info']);
        $this->assign('more_sales', $data['more_sales']);
    }
    
    function qa()
    {
        $goods_qa =& m('goodsqa');
         $id = intval($_GET['id']);
         if (!$id)
         {
            $this->show_warning('Hacking Attempt');
            return;
         }
         $this->get_seckill_info($id);
        if(!IS_POST)
        {
            $data = $this->_get_common_info($id);
            if ($data === false)
            {
                return;
            }
            else
            {
                $this->_assign_common_info($data);
            }
            $data = $this->_get_goods_qa($id, 10);
            $this->_assign_goods_qa($data);

            //是否开启验证码
            if (Conf::get('captcha_status.goodsqa'))
            {
                $this->assign('captcha', 1);
            }
            $this->assign('guest_comment_enable', Conf::get('guest_comment'));
            /*赋值产品咨询*/
            $this->display('goods.qa.html');
        }
        else
        {
            /* 不允许游客评论 */
            if (!Conf::get('guest_comment') && !$this->visitor->has_login)
            {
                $this->show_warning('guest_comment_disabled');

                return;
            }
            $content = (isset($_POST['content'])) ? trim($_POST['content']) : '';
            //$type = (isset($_POST['type'])) ? trim($_POST['type']) : '';
            $email = (isset($_POST['email'])) ? trim($_POST['email']) : '';
            $hide_name = (isset($_POST['hide_name'])) ? trim($_POST['hide_name']) : '';
            if (empty($content))
            {
                $this->show_warning('content_not_null');
                return;
            }
            //对验证码和邮件进行判断

            if (Conf::get('captcha_status.goodsqa'))
            {
                if (base64_decode($_SESSION['captcha']) != strtolower($_POST['captcha']))
                {
                    $this->show_warning('captcha_failed');
                    return;
                }
            }
            if (!empty($email) && !is_email($email))
            {
                $this->show_warning('email_not_correct');
                return;
            }
            $user_id = empty($hide_name) ? $_SESSION['user_info']['user_id'] : 0;
            $conditions = 'g.goods_id ='.$id;
            $goods_mod = & m('goods');
            $ids = $goods_mod->get(array(
                'fields' => 'store_id,goods_name',
                'conditions' => $conditions
            ));
            extract($ids);
            $data = array(
                'question_content' => $content,
                'type' => 'goods',
                'item_id' => $id,
                'item_name' => $goods_name,
                'store_id' => $store_id,
                'email' => $email,
                'user_id' => $user_id,
                'time_post' => gmtime(),
            );
            if ($goods_qa->add($data))
            {
                header("Location: index.php?app=goods&act=qa&id={$id}#module\n");
                exit;
            }
            else
            {
                $this->show_warning('post_fail');
                exit;
            }
        }
    }

    /* 取得商品咨询 */
    function _get_goods_qa($goods_id,$num_per_page)
    {
        $page = $this->_get_page($num_per_page);
        $goods_qa = & m('goodsqa');
        $qa_info = $goods_qa->find(array(
            'join' => 'belongs_to_user',
            'fields' => 'member.user_name,question_content,reply_content,time_post',
            'conditions' => '1 = 1 AND item_id = '.$goods_id . " AND type = 'goods'",
            'limit' => $page['limit'],
            'order' =>'time_post desc',
            'count' => true
        ));
        $page['item_count'] = $goods_qa->getCount();
        $this->_format_page($page);

        //如果登陆，则查出email
        if (!empty($_SESSION['user_info']))
        {
            $user_mod = & m('member');
            $user_info = $user_mod->get(array(
                'fields' => 'email',
                'conditions' => '1=1 AND user_id = '.$_SESSION['user_info']['user_id']
            ));
            extract($user_info);
        }

        return array(
            'email' => $email,
            'page_info' => $page,
            'qa_info' => $qa_info,
        );
    }
    
    /* 赋值商品咨询 */
    function _assign_goods_qa($data)
    {
        $this->assign('email',      $data['email']);
        $this->assign('page_info',  $data['page_info']);
        $this->assign('qa_info',    $data['qa_info']);
    }
    
    //下一场预告
    function seckill_next_search(){
        $seckill_configs = file_exists(ROOT_PATH."/data/seckill.inc.php") ? include(ROOT_PATH."/data/seckill.inc.php") : '';
       	if(!file_exists(ROOT_PATH . '/data/seckill.inc.php') || !is_array($seckill_configs)){
    		$this->show_warning('the_action_has_not_exists');
    		return ;
    	}
    	$conditions = empty($_GET['id']) ? '' : ' AND sk.subject_id='.intval($_GET['id']);
    	if(empty($conditions)){
    		$this->_curlocal(
		    LANG::get('seckill'),'index.php?app=buyer_seckill&act=seckill_list',Lang::get('seckill_next_title'));
    	}
		else{
    		$this->_curlocal(
		    LANG::get('seckill'),'index.php?app=buyer_seckill&act=seckill_search',trim($_GET['sub_name']));
		}
		$max_per_page = 20;
		if(!empty($_GET['max_page'])){
			$max_per_page = intval($_GET['max_page']);
		}
		else{
			$max_per_page = isset($_SESSION['page_per_num']) ? $_SESSION['page_per_num'] : $max_per_page;
		}
		if(isset($_SESSION['page_per_num'])){
			setcookie('page_per_num',$max_per_page,gmtime()+3600);
		}
		$this->assign('page_per_num',$max_per_page);
		$page = $this->_get_page($max_per_page);
	    $sTime = explode(':',$seckill_configs['start_time']);
	    $sTimeSec = $sTime[0]*3600+$sTime[1]*60+$sTime[2];
    	$today = getdate(gmtime());
        $end_time = mktime(0,0,0,$today['mon'],$today['mday'],$today['year'])+$sTimeSec+$seckill_configs['period'];
    	$seckill_mod = & m('seckill');
    	$order = '';
    	if(!empty($_GET['order_by'])){
    		$order = trim($_GET['order_by']);
    	}
        $today_seckill_goods = $seckill_mod->find(array(
             'conditions' => "sk.start_time >=".(mktime(0,0,0,$today['mon'],$today['mday'],$today['year'])+86400).' AND start_time <='.($end_time+86400),
    	     'join' => 'belongs_to_subject,belong_goods',
    	     'order' => $order,
    	   	 'fields' => 'this.*,ss.subject_name,ss.subject_id,g.price,g.default_image',    
             'limit' => $page['limit'],
             'count' => true
      	));

        $page['item_count'] = $seckill_mod->getCount();
        $this->_format_page($page);
    	$this->assign('page_info', $page);
      	$today_seckill_goods = empty($today_seckill_goods) ? array() : $today_seckill_goods;
      	foreach ($today_seckill_goods as $sk => $sv){
      		$subject_list[$sv['subject_id']]['subject_id'] = $sv['subject_id'];
      		$subject_list[$sv['subject_id']]['subject_name'] = $sv['subject_name'];  //对主题进行统计
      		$subject_list[$sv['subject_id']]['subject_count']++;
      	    $today_seckill_goods[$sk]['sec_price'] = unserialize($sv['sec_price']);
      		$today_seckill_goods[$sk]['sec_price'] = empty($today_seckill_goods[$sk]['sec_price']) ? array() : $today_seckill_goods[$sk]['sec_price'];
      		$i = 0;	  
            foreach ($today_seckill_goods[$sk]['sec_price'] as $k => $v){
        	    $today_seckill_goods[$sk]['sec_price'][$i] = array('spec_id' => $k,'spec_price' => $v['price']);
                $i ++ ;
            }
      	}
      	$order_data = array(
      	   'add_time' => Lang::get('add_time'),
      	   'recommended' => Lang::get('recommended')
      	);
      	$this->assign('current_seckill',true);
      	$this->assign('page_title', Lang::get('seckill') . ' - ' . Conf::get('site_title'));
      	$this->assign('orders',$order_data);
      	$this->assign('seckill_lists',$today_seckill_goods);
      	$this->assign('leave_time',Lang::get('the_seckill_not_start'));
      	$this->assign('subject_lists',$subject_list);
    	$this->display('seckill.goods.search.html');
    }
    
    //秒杀历史
    function seckill_prev_search(){
        $seckill_configs = file_exists(ROOT_PATH."/data/seckill.inc.php") ? include(ROOT_PATH."/data/seckill.inc.php") : '';
       	if(!file_exists(ROOT_PATH . '/data/seckill.inc.php') || !is_array($seckill_configs)){
    		$this->show_warning('the_action_has_not_exists');
    		return ;
    	}
    	$conditions = empty($_GET['id']) ? '' : ' AND sk.subject_id='.intval($_GET['id']);
    	if(empty($conditions)){
    		$this->_curlocal(
		    LANG::get('seckill'),'index.php?app=buyer_seckill&act=seckill_list',Lang::get('seckill_prev_title'));
    	}
		else{
    		$this->_curlocal(
		    LANG::get('seckill'),'index.php?app=buyer_seckill&act=seckill_search',trim($_GET['sub_name']));
		}
		$max_per_page = 20;
		if(!empty($_GET['max_page'])){
			$max_per_page = intval($_GET['max_page']);
		}
		else{
			$max_per_page = isset($_SESSION['page_per_num']) ? $_SESSION['page_per_num'] : $max_per_page;
		}
		if(isset($_SESSION['page_per_num'])){
			setcookie('page_per_num',$max_per_page,gmtime()+3600);
		}
		$this->assign('page_per_num',$max_per_page);
		$page = $this->_get_page($max_per_page);
	    $sTime = explode(':',$seckill_configs['start_time']);
	    $sTimeSec = $sTime[0]*3600+$sTime[1]*60+$sTime[2];
    	$today = getdate(gmtime());
        $end_time = mktime(0,0,0,$today['mon'],$today['mday'],$today['year'])+$sTimeSec+$seckill_configs['period'];
    	$seckill_mod = & m('seckill');
    	$order = '';
    	if(!empty($_GET['order_by'])){
    		$order = trim($_GET['order_by']);
    	}
        $today_seckill_goods = $seckill_mod->find(array(
             'conditions' => 'start_time <='.($end_time),
    	     'join' => 'belongs_to_subject,belong_goods,belong_store',
    	     'order' => $order,
    	   	 'fields' => 'this.*,ss.subject_name,ss.subject_id,g.price,g.default_image,s.store_name',    
             'limit' => $page['limit'],
             'count' => true
      	));

        $page['item_count'] = $seckill_mod->getCount();
        $this->_format_page($page);
    	$this->assign('page_info', $page);
      	$today_seckill_goods = empty($today_seckill_goods) ? array() : $today_seckill_goods;
      	foreach ($today_seckill_goods as $sk => $sv){
      		$subject_list[$sv['subject_id']]['subject_id'] = $sv['subject_id'];
      		$subject_list[$sv['subject_id']]['subject_name'] = $sv['subject_name'];  //对主题进行统计
      		$subject_list[$sv['subject_id']]['subject_count']++;
      	    $today_seckill_goods[$sk]['sec_price'] = unserialize($sv['sec_price']);
      		$today_seckill_goods[$sk]['sec_price'] = empty($today_seckill_goods[$sk]['sec_price']) ? array() : $today_seckill_goods[$sk]['sec_price'];
      		$i = 0;	  
            foreach ($today_seckill_goods[$sk]['sec_price'] as $k => $v){
        	    $today_seckill_goods[$sk]['sec_price'][$i] = array('spec_id' => $k,'spec_price' => $v['price']);
                $i ++ ;
            }
      	}
      	$order_data = array(
      	   'add_time' => Lang::get('add_time'),
      	   'recommended' => Lang::get('recommended')
      	);

      	$this->assign('current_seckill',true);
      	$this->assign('orders',$order_data);
      	$this->assign('seckill_lists',$today_seckill_goods);
      	$this->assign('page_title', Lang::get('seckill') . ' - ' . Conf::get('site_title'));
      	$this->assign('leave_time',Lang::get('the_seckill_has_end'));
      	$this->assign('subject_lists',$subject_list);
    	$this->display('seckill.goods.search.html');
    }
    //秒杀时间到,异步获取秒杀数据
    function get_seckill_data(){
    	//获取新的秒杀数据数据
       	$seckill_configs = file_exists(ROOT_PATH."/data/seckill.inc.php") ? include(ROOT_PATH."/data/seckill.inc.php") : '';
  
    	$data = array();
    	if (is_array($seckill_configs))
    	{
    		
	     	   if (empty($this->options['num']) || intval($this->options['num']) <= 0)
	        {
	            $this->options['num'] = 1;
	        }
	        
	        $sTime = explode(':',$seckill_configs['start_time']);
	        $sTimeSec = $sTime[0]*3600+$sTime[1]*60+$sTime[2];
	        $today = getdate(gmtime());
	        $nowTimeSec = $today['hours']*3600+$today['minutes']*60+$today['seconds'];
	        $end_time = $sTimeSec + $seckill_configs['period'];
            $seckill_mod =& m('seckill');
            $seckill_info = $seckill_mod->find(array(
                'conditions' => "sk.sec_state !=".SECKILL_REFUCE." AND sk.sec_state !=".SECKILL_END." AND sk.sec_state !=".SECKILL_APPLY." AND sk.start_time <=".(mktime(0,0,0,$today['mon'],$today['mday'],$today['year'])+$end_time)." AND sk.start_time >=".(mktime(0,0,0,$today['mon'],$today['mday'],$today['year'])),
                'order' => 'sk.add_time asc',
                'join' => 'belong_goods,belongs_to_subject',
                'fields' => 'this.*,g.default_image,ss.subject_name',
            ));
           
           $seckill_info = empty($seckill_info) ? array() : $seckill_info;
            foreach ($seckill_info as $sec_key=>$sec_val){
            	if(count($data) <= 3){
            		$data[$sec_key] = $seckill_info[$sec_key];
            		$data[$sec_key]['sec_price'] = unserialize($data[$sec_key]['sec_price']);
            		$data[$sec_key]['sec_price'] = empty($data[$sec_key]['sec_price']) ? array() : $data[$sec_key]['sec_price'];
            		$i=0;
            		foreach ($data[$sec_key]['sec_price'] as $price_key=>$price_val){
            			if($i >= 1){
            				continue;
            			}
            			$data[$sec_key]['price'] = $price_val['price'];
            			$i++;
            		}
            		$data[$sec_key]['sec_price'] = '';
            	}
            	if($sec_val['sec_state'] == SECKILL_NOT_START){
            		$seckill_id[$sec_key] = $sec_key;
            	}
            	
            	
            }
                	           //时间到,更新秒杀状态
    	    $seckill_id = empty($seckill_id) ? array() : $seckill_id;  
            if(!empty($seckill_id)){
             	$seckill_mod->edit($seckill_id,'sec_state='.SECKILL_START);
            }

            echo ecm_json_encode($data);
    	}   	
    }
}