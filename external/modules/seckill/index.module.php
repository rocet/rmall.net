<?php

class SeckillModule extends IndexbaseModule
{    
    function __construct()
    {
//        $this->DatacallModule();
    }

    function DatacallModule()
    {
//        parent::__construct();
//        $this->_datacall_mod = &af("datacall");
    }

    function index()
    {
    	echo 'this is only a test';
    	$this->display('test.html');
    }
    
	/**秒杀结束后，更改商品属性*/
    function ajax_goods_update()
    {
    	$goods_ids = isset($_GET['ids']) ? $_GET['ids'] : '';
    	$seckill_id = empty($_GET['secid']) ? $_GET['secid'] : '';
    	if(empty($goods_ids)){
    		return;
    	}
    	$data = array(
    	   'if_show' => 1,
    	   'if_seckill' => 0,
    	);
    	$goods_mod = & m('goods');
    	$seckill_mod = & m('seckill');

    	$goods_mod->db->query('START TRANSACTION');
    	if(!$goods_mod->edit($ids,$data)){
    		$goods_mod->db->query('ROLLBACK');
    	}
    	if(!$seckill_mod -> edit($seckill_id,'sec_state='.SECKILL_END)){
    		$goods_mod->db->query('ROLLBACK');
    	}
    	$goods_mod->db->query('COMMIT');
    	if(!$this->get_error()){
    		echo ecm_json_encode(true);
    	}
    }
    
    function get_stime(){
    	$nowTime = getdate();
    	$nowTimeSec = $nowTime['hours']*3600+$nowTime['minutes']*60+$nowTime['seconds'];
    	echo $nowTimeSec;
    }
    
    function get_seckill_data(){
    	//获取新的秒杀数据数据

       	$seckill_configs = file_exists(ROOT_PATH."/data/seckill.inc.php") ? include(ROOT_PATH."/data/seckill.inc.php") : '';

    	$data = array();
    	if (is_array($seckill_configs))
    	{
    		
	     	   if (empty($_GET['num']) || intval($_GET['num']) <= 0)
	        {
	            $num = 1;
	        }
	        else{
	        	$num = intval($_GET['num']);
	        }
	       $sTime = explode(':',$seckill_configs['start_time']);
	        $sTimeSec = $sTime[0]*3600+$sTime[1]*60+$sTime[2];
	        $today = getdate();
	        $end_time = $sTimeSec + $seckill_configs['period'];
	        
             $seckill_mod =& m('seckill');
          $nowTimeSec = $today['hours']*3600+$today['minutes']*60+$today['seconds'];
            $seckill_info = $seckill_mod->find(array(
                'conditions' => "sk.sec_state =".SECKILL_NOT_START." OR sk.sec_state =".SECKILL_START." AND sk.start_time <=".(mktime(0,0,0,$today['mon'],$today['mday'],$today['year'])+$end_time)." AND sk.start_time >=".(mktime(0,0,0,$today['mon'],$today['mday'],$today['year'])),
                'order' => 'sk.add_time asc',
                'join' => 'belong_goods',
                'fields' => 'this.*,g.default_image',

            ));

           $seckill_info = empty($seckill_info) ? array() : $seckill_info;
            foreach ($seckill_info as $sec_key=>$sec_val){
            	if($sec_val['recommended'] == SECKILL_RECOMMENDED && count($data) <= $num && $nowTimeSec >= $sTimeSec){
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
            	}
            	
            	$seckill_id[$sec_key] = $sec_key;	
            }
    	           //时间到,更新秒杀状态
    	    $seckill_id = empty($seckill_id) ? array() : $seckill_id;

            if($nowTimeSec >= $sTimeSec && !empty($seckill_id)){
            	$seckill_mod->edit($seckill_id,'sec_state='.SECKILL_START);
            }
            echo ecm_json_encode($data);
    	}   	
    }
}

?>
