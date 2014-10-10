<?php

include dirname(__FILE__) . '/search.app.php';
/* 商品 */
class CateindexApp extends SearchApp
{
    function __construct()
    {
        $this->Catindex();
    }
    function Catindex()
    {
        parent::__construct();
    }
    function _extendindex($vars){
        
        $cate_indexs = array_keys( m('gcategory')->find(array('conditions' => '`parent_id`=0 AND `store_id` = 0', 'order' => 'cate_id asc')));
        // 轮播图
        $this->assign('rocet_adv_catindexbanners', m('arads')->find(array( 
            'conditions'  => '`aradps_id`=' . key(m('aradps')->find(array('conditions' => '`identify`='."'cate".(array_search($vars['cate_id'], $cate_indexs) + 1)."banner'")) ),
            'limit'   => '0, 5',
            'order'   => 'arads_id desc', 
        )) );
        $topcates = $this->_get_catsub_navs($vars['cate_id']);
        $subcatids = array_keys( $topcates['subcat'] );
        $subcats = array();
        foreach ($subcatids as $value ) {

            // 分类信息
            $subcats[$value] = $topcates['subcat'][$value];

            // 推荐
            $subcats[$value]['recoms'] = m('goods')->find(array(
                'join' => 'be_recommend, belongs_to_store, has_goodsstatistics',
                'fields' => 'g.default_image, g.discount, g.price, g.goods_name, s.store_id, s.store_name, g.cate_name, g.brand, recommended_goods.recommend_pic, recommended_goods.sort_order, g.closed, g.if_show, views',
                'conditions' => "g.cate_id_2 = $value AND recommended_goods.recom_id = '".key(m('recommend')->find(array(
                    'conditions'  => ' `recom_name`=\'首页推荐\'',
                    'fields' => 'recom_id',
                )))."'",
                'limit' => '0, 8',
                'order' => 'recommended_goods.sort_order',
            ));
            // 销售排行
            $subcats[$value]['tops'] = m('goods')->get_list(array(
                'conditions' => "g.cate_id_2 = $value",
                'limit' => '0, 5',
                'order' => 'gst.sales desc',

            ));
            // 文章
            $subcats[$value]['arts'] = m('article')->find(array(
                'conditions' => "gcate_id_2 = $value",
                'limit' => '0, 6',
                'order' => 'article_id desc',

            ));
        }

        // 主内容
        $this->assign('rocet_subcats', $subcats);
        // 今日团购
        $indexgroup = current( m('groupbuy')->find(array('order' => 'views desc', 'conditions' => 'g.cate_id_1='.$vars['cate_id'], 'limit' => '0, 1', 'join'=>'belong_goods')));
        if($indexgroup){
            $group_price = current( unserialize($indexgroup['spec_price']) );
            $indexgroup['group_price'] = $group_price['price'];
        }
        $this->assign('rocet_today_groupbuy', $indexgroup );
        // 特惠
        $this->assign('rocet_catindex_goodsships', m('goods')->get_list(array(
            'conditions' => 'g.cate_id_1='.$vars['cate_id'],
            'order' => 'gst.sales desc',
            'limit' => '0,2',
        )) );
        // 人气
        $this->assign('rocet_catindex_goodsviews', m('goods')->get_list(array(
            'conditions' => 'g.cate_id_1='.$vars['cate_id'],
            'order' => 'gst.views desc',
            'limit' => '0,4',
        )) );
        // 新品
        $this->assign('rocet_catindex_goodsnews', m('goods')->get_list(array(
            'conditions' => 'g.cate_id_1='.$vars['cate_id'],
            'order' => 'g.add_time desc',
            'limit' => '0,4',
        )) );
        // 侧面导航
        $this->assign('rocet_catindex', $topcates);

        // 促销广告
        $this->assign('rocet_adv_catindexactive', m('arads')->find(array( 
            'conditions'  => '`aradps_id`=' . key(m('aradps')->find(array('conditions' => '`identify`='."'cate".(array_search($vars['cate_id'], $cate_indexs) + 1)."ads'")) ),
            'limit'   => '0, 6',
            'order'   => 'arads_id desc', 
        )) );


        // 文章知识
        $knowlegs = m('article')->find(array(
            'join' => 'belongs_to_acategory',
            'conditions' => "gcate_id_1 =".$vars['cate_id'],
            'limit' => '0, 8',
            'order' => 'article_id desc',
        ));
        foreach ($knowlegs as $key => & $value) {
            $value['descript'] = html_entity_decode( strip_tags($value['content']) );
        }
        $this->assign('rocet_art_catindexknowleg', $knowlegs );



        $this->display('search.cateindex.html');
        exit();
    }

    function _get_catsub_navs($cate_id){
        $gcatgorynavs = array();
        $catlvl0 = m('gcategory')->find(array( 
            'conditions'  => '`cate_id`='.$cate_id.' AND `if_show`=1 AND `cate_id` < 1000',
            'limit'   => '0, 1',
        ));
        $static = 0;
        foreach ($catlvl0 as $key => & $value) {
            $static ++;
            $tmpsubcat = m('gcategory')->find(array( 
                'conditions'  => '`parent_id`='.$value['cate_id'].' AND `if_show`=1 AND `cate_id` < 1000',
                'limit'   => '0, 5',
                'order'   => 'sort_order asc', 
            ));
            foreach ($tmpsubcat as $k => & $v) {
                $tmpsubcatlast = m('gcategory')->find(array( 
                    'conditions'  => '`parent_id`='.$v['cate_id'].' AND `if_show`=1 AND `cate_id` < 1000',
                    'limit'   => '0, 30',
                    'order'   => 'sort_order asc', 
                ));
                if( $tmpsubcatlast ){
                    $v['subcat'] = $tmpsubcatlast;
                    $brands = m('goods')->getCol("SELECT DISTINCT `brand` FROM ".DB_PREFIX."goods WHERE `cate_id_2`=".$k." LIMIT 0, 4");
                    foreach ($brands as $brand) {
                        $v['brands'][] = current( m('brand')->find(array('conditions'=> 'brand_name=\''.$brand.'\'')) );
                    }
                }
            }
            if( $tmpsubcat ){
                $value['subcat'] = $tmpsubcat;
                
                $value['actives'] = m('arads')->find(array( 
                    'conditions'  => '`aradps_id`=' . key(m('aradps')->find(array('conditions' => '`identify`='."'cate".$static."ads'")) ),
                    'limit'   => '0, 3',
                    'order'   => 'arads_id desc', 
                ));
            }
        }
        $gcatgorynavs = $catlvl0;
        return current($gcatgorynavs);
    }
}