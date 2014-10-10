<?php

class NewgoodsApp extends MallbaseApp
{
    function __construct()
    {
        $this->Newgoods();
    }
    function Newgoods()
    {
        parent::__construct();
    }
    function index(){


        $this->assign('rocet_adv_newgoodsbanner', m('arads')->find(array( 
            'conditions'  => '`aradps_id`=' . key(m('aradps')->find(array('conditions' => '`identify`='."'newgoodsbanner'")) ),
            'limit'   => '0, 4',
            'order'   => 'arads_id desc', 
        )) );

        $this->assign('rocet_adv_newgoodsadleft', current( m('arads')->find(array( 
            'conditions'  => '`aradps_id`=' . key(m('aradps')->find(array('conditions' => '`identify`='."'newgoodsadleft'")) ),
            'limit'   => '0, 1',
            'order'   => 'arads_id desc', 
        ))) );

        


        $this->assign('rocet_newgoods_topsell', m('goods')->find(array(
            'join' => 'be_recommend, belongs_to_store, has_goodsstatistics',
            'fields' => 'g.default_image, g.price, g.goods_name, s.store_id, s.store_name, g.cate_name, g.brand, recommended_goods.recommend_pic, recommended_goods.sort_order, g.closed, g.if_show, views',
            'conditions' => "recommended_goods.recom_id = '".key(m('recommend')->find(array(
                'conditions'  => ' `recom_name`=\'本月推荐\'',
                'fields' => 'recom_id',
            )))."'",
            'limit' => '0, 3',
            'order' => 'recommended_goods.sort_order',
        )) );


        $this->assign('rocet_newgoods_hot', m('goods')->find(array(
            'join' => 'be_recommend, belongs_to_store, has_goodsstatistics',
            'fields' => 'g.default_image, g.price, g.goods_name, s.store_id, s.store_name, g.cate_name, g.brand, recommended_goods.recommend_pic, recommended_goods.sort_order, g.closed, g.if_show, views',
            'conditions' => "recommended_goods.recom_id = '".key(m('recommend')->find(array(
                'conditions'  => ' `recom_name`=\'本月推荐\'',
                'fields' => 'recom_id',
            )))."'",
            'limit' => '0, 8',
            'order' => 'recommended_goods.sort_order',
        )) );


        $this->assign('rocet_newgoods_goods', m('goods')->find(array(
            'join' => 'be_recommend, belongs_to_store, has_goodsstatistics',
            'fields' => 'g.default_image, g.price, g.goods_name, s.store_id, s.store_name, g.cate_name, g.brand, recommended_goods.recommend_pic, recommended_goods.sort_order, g.closed, g.if_show, views',
            'conditions' => "recommended_goods.recom_id = '".key(m('recommend')->find(array(
                'conditions'  => ' `recom_name`=\'本月推荐\'',
                'fields' => 'recom_id',
            )))."'",
            'limit' => '0, 30',
            'order' => 'recommended_goods.sort_order',
        )) );

    	$this->display('goods.new.html');
    }
}