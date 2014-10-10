<?php
class SeckillModel extends BaseModel{
    var $table  = 'seckill';
    var $alias  = 'sk';
    var $prikey = 'sec_id';
    var $_name  = 'seckill';
    var $_relation = array(
        // 一个秒杀活动属于一个商品
        'belong_goods' => array(
            'model'         => 'goods',
            'type'          => BELONGS_TO,
            'foreign_key'   => 'goods_id',
            'reverse'       => 'has_seckill',
        ),
        //一个秒杀活动属于一个店铺
        'belong_store' => array(
            'model' => 'store',
            'type' => BELONGS_TO,
            'foreign_key' => 'store_id',
            'reverse' => 'has_seckill',
        ),
        // 一个秒杀商品只能属于一个秒杀主题
        'belongs_to_subject' => array(
            'model'         => 'seckill_subject',
            'type'          => BELONGS_TO,
            'foreign_key'   => 'subject_id',
            'reverse'       => 'has_seckill',
        ),
        );
}