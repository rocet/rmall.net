<?php
class Seckill_subjectModel extends BaseModel{
    var $table  = 'seckill_subject';
    var $alias  = 'ss';
    var $prikey = 'subject_id';
    var $_name  = 'seckill_subject';
    var $_relation = array(
        // 一个秒杀主题有多个秒杀商品
        'has_seckill' => array(
            'model'         => 'seckill',
            'type'          => HAS_MANY,
            'foreign_key'   => 'subject_id',
            'dependent'     => true
        ),

        );
}