<?php

/**
 * 秒杀促销挂件
 *
 * @return  array   $goods_list
 */
class SeckillWidget extends BaseWidget
{
    var $_name = 'seckill';
    //var $_ttl  = 1;
   
	/**
	* 获取挂件显示数据
	* @author Jacken
	* @param   int     $num    数量
	* @return array    $data
	*/
    function _get_data()
    {
    	$seckill_configs = file_exists(ROOT_PATH."/data/seckill.inc.php") ? include(ROOT_PATH."/data/seckill.inc.php") : '';
    	$data = array();
    	if (is_array($seckill_configs))
    	{
	        if (empty($this->options['num']) || intval($this->options['num']) <= 0)
	        {
	            $this->options['num'] = 1;
	        }
	        $this->assign('goods_num',$this->options['num']);
	        $sTime = explode(':',$seckill_configs['start_time']);
	        $sTimeSec = $sTime[0]*3600+$sTime[1]*60+$sTime[2];
	        $today = getdate(gmtime());
	        $end_time = $sTimeSec + $seckill_configs['period'];
            $seckill_mod =& m('seckill');
            $nowTimeSec = $today['hours']*3600+$today['minutes']*60+$today['seconds'];
            $seckill_info = $seckill_mod->find(array(
                'conditions' => "sk.sec_state !=".SECKILL_REFUCE." AND sk.sec_state !=".SECKILL_END." AND sk.sec_state !=".SECKILL_APPLY." AND sk.start_time <=".(mktime(0,0,0,$today['mon'],$today['mday'],$today['year'])+$end_time)." AND sk.start_time >=".(mktime(0,0,0,$today['mon'],$today['mday'],$today['year'])),
                'order' => 'sk.add_time asc',
                'join' => 'belong_goods',
                'fields' => 'this.*,g.default_image',
            ));
            $seckill_info = empty($seckill_info) ? array() : $seckill_info;
            foreach ($seckill_info as $sec_key=>$sec_val){
            	if($sec_val['recommended'] == SECKILL_RECOMMENDED && count($data) <= $this->options['num'] && $nowTimeSec >= $sTimeSec){
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
            	$goods_ids[$sec_val['goods_id']] = $sec_val['goods_id'];
            }
            $seckill_id = empty($seckill_id) ? array() : $seckill_id;
            $goods_ids = empty($goods_ids) ? array() : $goods_ids;
            $goods_ids = implode(',',$goods_ids);
            $seckill_id = implode(',',$seckill_id);

          
           //时间到,更新秒杀状态
           if($nowTimeSec >= $sTimeSec && !empty($seckill_id)){
            	$seckill_mod->edit('sec_id in('.$seckill_id.')','sec_state='.SECKILL_START);
            }
          $start = false;
          if($nowTimeSec > $end_time && !empty($seckill_id) && !empty($goods_ids)){                               //秒杀结束后还原商品属性

    	      $goods_mod = & m('goods');
    	      $goods_mod->db->query('START TRANSACTION');
    	      if(!$goods_mod->edit('goods_id in('.$goods_ids.')','if_show=1,if_seckill=0')){
    		      $goods_mod->db->query('ROLLBACK');
    	      }
              if(!$seckill_mod -> edit('sec_id in('.$seckill_id.')','sec_state='.SECKILL_END)){
    		      $goods_mod->db->query('ROLLBACK');
    	      }
    	      $goods_mod->db->query('COMMIT');
           }
          if($nowTimeSec >= $sTimeSec && $nowTimeSec <= $end_time){
          	 $start = true;
          	 $leveTime = $sTimeSec - $nowTimeSec; 

          }
          else{
          	 $time_num = $sTimeSec-$nowTimeSec;
          	 $leveTime = (int)($time_num / 3600).':'.(int)(($time_num % 3600) / 60) . ':' . (int)(($time_num % 3600) % 60);
          	 $start = false;
          	 
          	 $split_time = $nowTimeSec - $sTimeSec;
          	 
          	 if($split_time < 0){
          	 	$end_time = $sTimeSec + $seckill_configs['period'];
          	 	
          	 }
          }
          $this->assign('show_num',$this->options['num']);
          $this->assign('lIntTime',$time_num);
          $this->assign('leveTime',$leveTime);
          $this->assign('start',$start);
          $this->assign('end_time',$end_time);
          $this->assign('nowTime',$nowTimeSec);
          $this->assign('period',$seckill_configs['period']);
    	}
        return $data;
    }
}

?>