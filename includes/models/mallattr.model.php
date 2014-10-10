<?php

/* 商品属性 mallattr */
class MallattrModel extends BaseModel
{
    var $table  = 'mall_attr';
    var $prikey = 'mattr_id';
    var $_name  = 'mallattr';

    var $_relation  = array(
        // 一个商品属性只能属于一个商品
        'belongs_to_goods' => array(
            'model'         => 'goods',
            'type'          => BELONGS_TO,
            'foreign_key'   => 'goods_id',
            'reverse'       => 'has_mallattr',
        ),
        // 一个商品属性只能属于一个分类属性
        'belongs_to_attr' => array(
            'model'         => 'attr',
            'type'          => BELONGS_TO,
            'foreign_key'   => 'attr_id_2',
            'reverse'       => 'has_mallattr',
        ),
    );
    function find($params = array()){
        $res = parent::find($params);
        // array_walk_recursive($res, function(&$v, $k){
        //     if( in_array($k, array('attr_id_1','attr_id_2'))  ){
        //         $v =  & m('attr')->find(array( 'conditions' => 'attr_id='.$v, 'fields' => 'attr_id, attr_name' )) ;
        //     }
        // });
        return $this->resReplace($res);
    }
    function findAll($params = array()){
        $res = parent::findAll($params);
        // array_walk_recursive($res, function(&$v, $k){
        //     if( in_array($k, array('attr_id_1','attr_id_2'))  ){
        //         $v =  & m('attr')->find(array( 'conditions' => 'attr_id='.$v, 'fields' => 'attr_id, attr_name' )) ;
        //     }
        // });
        return $this->resReplace($res);
    }
    function resReplace($res){
        if( is_array($res) ){
            foreach ($res as $key => & $value) {
                if( is_array($value) ){
                    $this->resReplace($value);
                } else {
                    if( in_array($key, array('attr_id_1','attr_id_2')) ){
                        $value = & m('attr')->find(array( 'conditions' => 'attr_id='.$value, 'fields' => 'attr_id, attr_name' )) ;
                    }
                }
                
            }
        }
        return $res;
    }
}

?>