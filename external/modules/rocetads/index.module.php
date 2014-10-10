<?php

class RocetadsModule extends IndexbaseModule
{    
    function __construct()
    {
        $this->RocetadsModule();
    }

    function RocetadsModule()
    {
       parent::__construct();
       $this->_datacall_mod = &af("rocetads");
    }

    function index()
    {
    	echo 'this is only a test';
    	//$this->display('test.html');
    }
    
	/**秒杀结束后，更改商品属性*/
    function ajax_goods_update()
    {
    	$goods_ids = isset($_GET['ids']) ? $_GET['ids'] : '';
    	$rocetads_id = empty($_GET['secid']) ? $_GET['secid'] : '';
    	if(empty($goods_ids)){
    		return;
    	}
    	$data = array(
    	   'if_show' => 1,
    	   'if_rocetads' => 0,
    	);
    	$goods_mod = & m('goods');
    	$rocetads_mod = & m('rocetads');

    	$goods_mod->db->query('START TRANSACTION');
    	if(!$goods_mod->edit($ids,$data)){
    		$goods_mod->db->query('ROLLBACK');
    	}
    	if(!$rocetads_mod -> edit($rocetads_id,'sec_state='.SECKILL_END)){
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
    
    function get_rocetads_data(){
    	//获取新的秒杀数据数据

       	$rocetads_configs = file_exists(ROOT_PATH."/data/rocetads.inc.php") ? include(ROOT_PATH."/data/rocetads.inc.php") : '';

    	$data = array();
    	if (is_array($rocetads_configs))
    	{
    		
	     	   if (empty($_GET['num']) || intval($_GET['num']) <= 0)
	        {
	            $num = 1;
	        }
	        else{
	        	$num = intval($_GET['num']);
	        }
	       $sTime = explode(':',$rocetads_configs['start_time']);
	        $sTimeSec = $sTime[0]*3600+$sTime[1]*60+$sTime[2];
	        $today = getdate();
	        $end_time = $sTimeSec + $rocetads_configs['period'];
	        
             $rocetads_mod =& m('rocetads');
          $nowTimeSec = $today['hours']*3600+$today['minutes']*60+$today['seconds'];
            $rocetads_info = $rocetads_mod->find(array(
                'conditions' => "sk.sec_state =".SECKILL_NOT_START." OR sk.sec_state =".SECKILL_START." AND sk.start_time <=".(mktime(0,0,0,$today['mon'],$today['mday'],$today['year'])+$end_time)." AND sk.start_time >=".(mktime(0,0,0,$today['mon'],$today['mday'],$today['year'])),
                'order' => 'sk.add_time asc',
                'join' => 'belong_goods',
                'fields' => 'this.*,g.default_image',

            ));

           $rocetads_info = empty($rocetads_info) ? array() : $rocetads_info;
            foreach ($rocetads_info as $sec_key=>$sec_val){
            	if($sec_val['recommended'] == SECKILL_RECOMMENDED && count($data) <= $num && $nowTimeSec >= $sTimeSec){
            		$data[$sec_key] = $rocetads_info[$sec_key];
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
            	
            	$rocetads_id[$sec_key] = $sec_key;	
            }
    	           //时间到,更新秒杀状态
    	    $rocetads_id = empty($rocetads_id) ? array() : $rocetads_id;

            if($nowTimeSec >= $sTimeSec && !empty($rocetads_id)){
            	$rocetads_mod->edit($rocetads_id,'sec_state='.SECKILL_START);
            }
            echo ecm_json_encode($data);
    	}   	
    }
}

?>
