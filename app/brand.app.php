<?php

class BrandApp extends MallbaseApp
{
    function index()
    {

        $brand_id = !empty($_GET['brand_id'])? intval($_GET['brand_id']) : null;
        $cate_id = !empty($_GET['cate_id'])? intval($_GET['cate_id']) : 1;


        //导航
        $this->assign('navs', $this->_get_navs());
        $_curlocal = array(
                array(
                'text' => Lang::get('index'),
                'url'  => 'index.php',),
                array(
                'text' => Lang::get('all_brands'),
                'url'  => '',),
            );
        $this->assign('_curlocal', $_curlocal);
        $recommended_stores = $this->_recommended_stores(5);
        $this->assign('recommended_stores', $recommended_stores);
        $recommended_brands = $this->_recommended_brands(10);
        $this->assign('recommended_brands', $recommended_brands);
        //对品牌重新组合排序
        $brand_mod =& m('brand');
        $brands_tmp = $brand_mod->find(array(
            'order' => "tag DESC,sort_order asc"));
        $brands_tmp = array_values($brands_tmp);
        $brands = array();
        $i = 0;
        foreach ($brands_tmp as $key => $val)
        {
            if (empty($key))
            {
               $brands[$i]['tag'] = $val['tag'];
               $brands[$i]['count'] = 1;
               $brands[$i]['brands'][] = $val;
               $i++;
            }
            else
            {
                if ($val['tag'] == $brands[$i-1]['tag'])
                {
                    $brands[$i-1]['count'] = $brands[$i-1]['count'] + 1;
                    $brands[$i-1]['brands'][] = $val;
                }
                else
                {
                    $brands[$i]['tag'] = $val['tag'];
                    $brands[$i]['count'] = 1;
                    $brands[$i]['brands'][] = $val;
                    $i++;
                }
            }
        }
        $brands_sort = array();
        foreach ($brands as $key => $val)
        {
            $brands_sort[$key] = $val['count'];
        }
        arsort($brands_sort);
        foreach ($brands_sort as $key => $val)
        {
            $brands_sort[$key] = $brands[$key];
        }
        $this->assign('brands', $brands_sort);
        $this->_config_seo('title', Lang::get('all_brands'));





        if(  $brand_id ){
            $brand_info = current( m('brand')->find(array( 'conditions' => '`brand_id`='."'$brand_id'")) );
//exit( var_dump( '' ));


            $this->assign('rocet_brand_goods', m('goods')->find(array(
                'join' => 'be_recommend, belongs_to_store, has_goodsstatistics',
                'fields' => 'g.default_image, g.price, g.goods_name, s.store_id, s.store_name, g.cate_name, g.brand, recommended_goods.recommend_pic, recommended_goods.sort_order, g.closed, g.if_show, views',
                'conditions' => " g.brand = '{$brand_info['brand_name']}' AND recommended_goods.recom_id = '".key(m('recommend')->find(array(
                    'conditions'  => ' `recom_name`=\'品牌推荐\'',
                    'fields' => 'recom_id',
                )))."'",
                'limit' => '0, 30',
                'order' => 'recommended_goods.sort_order',
            )));




            $this->assign('rocet_brand_tuijian', m('goods')->find(array(
                'join' => 'be_recommend, belongs_to_store, has_goodsstatistics',
                'fields' => 'g.default_image, g.price, g.goods_name, s.store_id, s.store_name, g.cate_name, g.brand, recommended_goods.recommend_pic, recommended_goods.sort_order, g.closed, g.if_show, views',
                'conditions' => " g.brand = '{$brand_info['brand_name']}' AND recommended_goods.recom_id = '".key(m('recommend')->find(array(
                    'conditions'  => ' `recom_name`=\'本月品牌推荐\'',
                    'fields' => 'recom_id',
                )))."'",
                'limit' => '0, 10',
                'order' => 'recommended_goods.sort_order',
            )));

            $this->assign('rocet_adv_brand_topad', current( m('arads')->find(array( 
                'conditions'  => '`aradps_id`=' . key(m('aradps')->find(array('conditions' => '`identify`='."'brandtopad'")) ),
                'limit'   => '0, 1',
                'order'   => 'arads_id desc', 
            ))) );


        } else {

            $this->assign('rocet_brand_goods', m('goods')->find(array(
                'join' => 'be_recommend, belongs_to_store, has_goodsstatistics',
                'fields' => 'g.default_image, g.price, g.goods_name, s.store_id, s.store_name, g.cate_name, g.brand, recommended_goods.recommend_pic, recommended_goods.sort_order, g.closed, g.if_show, views',
                'conditions' => "recommended_goods.recom_id = '".key(m('recommend')->find(array(
                    'conditions'  => ' `recom_name`=\'品牌推荐\'',
                    'fields' => 'recom_id',
                )))."'",
                'limit' => '0, 30',
                'order' => 'recommended_goods.sort_order',
            )));

            $cate_indexs = array_keys( m('gcategory')->find(array('conditions' => '`parent_id`=0 AND `store_id` = 0', 'order' => 'cate_id asc')));
            // 轮播图
            $this->assign('rocet_adv_catindexbanners', m('arads')->find(array( 
                'conditions'  => '`aradps_id`=' . key(m('aradps')->find(array('conditions' => '`identify`='."'cate".(array_search($cate_id, $cate_indexs) + 1)."banner'")) ),
                'limit'   => '0, 4',
                'order'   => 'arads_id desc', 
            )) );
            $topcates = $this->_get_catsub_navs($cate_id);
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
                    'limit' => '0, 9',
                    'order' => 'recommended_goods.sort_order',
                ));
                // 销售排行
                $subcats[$value]['tops'] = m('goods')->get_list(array(
                    'conditions' => "g.cate_id_2 = $value",
                    'limit' => '0, 3',
                    'order' => 'gst.sales desc',

                ));
            }

            // 主内容
            $this->assign('rocet_subcats', $subcats);
            // 今日团购
            $indexgroup = current( m('groupbuy')->find(array('order' => 'views desc', 'conditions' => 'g.cate_id_1='.$cate_id, 'limit' => '0, 1', 'join'=>'belong_goods')));
            if($indexgroup){
                $group_price = current( unserialize($indexgroup['spec_price']) );
                $indexgroup['group_price'] = $group_price['price'];
            }
            $this->assign('rocet_today_groupbuy', $indexgroup );
            // 特惠
            $this->assign('rocet_catindex_goodsships', m('goods')->get_list(array(
                'conditions' => 'g.cate_id_1='.$cate_id,
                'order' => 'gst.sales desc',
                'limit' => '0,2',
            )) );
            // 人气
            $this->assign('rocet_catindex_goodsviews', m('goods')->get_list(array(
                'conditions' => 'g.cate_id_1='.$cate_id,
                'order' => 'gst.views desc',
                'limit' => '0,5',
            )) );
            // 新品
            $this->assign('rocet_catindex_goodsnews', m('goods')->get_list(array(
                'conditions' => 'g.cate_id_1='.$cate_id,
                'order' => 'g.add_time desc',
                'limit' => '0,5',
            )) );
            // 侧面导航
            $this->assign('rocet_catindex', $topcates);

            // 促销广告
            $this->assign('rocet_adv_catindexactive', m('arads')->find(array( 
                'conditions'  => '`aradps_id`=' . key(m('aradps')->find(array('conditions' => '`identify`='."'cate".(array_search($cate_id, $cate_indexs) + 1)."ads'")) ),
                'limit'   => '0, 6',
                'order'   => 'arads_id desc', 
            )) );


            // 文章知识
            $knowlegs = m('article')->find(array(
                'join' => 'belongs_to_acategory',
                'conditions' => "gcate_id_1 =".$cate_id,
                'limit' => '0, 8',
                'order' => 'article_id desc',
            ));
            foreach ($knowlegs as $key => & $value) {
                $value['descript'] = html_entity_decode( strip_tags($value['content']) );
            }
            $this->assign('rocet_art_catindexknowleg', $knowlegs );






        }







        $this->display( $brand_id ? 'brand.goods.html' :'brand.index.html');
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

    function _recommended_brands($num)
    {
        $brand_mod =& m('brand');
        $brands = $brand_mod->find(array(
            'conditions' => 'recommended = 1 AND if_show = 1',
            'order' => 'sort_order',
            'limit' => '0,' . $num));
        return $brands;
    }

    function _recommended_stores($num)
    {
        $store_mod =& m('store');
        $goods_mod =& m('goods');
        $stores = $store_mod->find(array(
            'conditions'    => 'recommended=1 AND state = 1',
            'order'         => 'sort_order',
            'join'          => 'belongs_to_user',
            'limit'         => '0,' . $num,
        ));
        foreach ($stores as $key => $store){
            empty($store['store_logo']) && $stores[$key]['store_logo'] = Conf::get('default_store_logo');
            $stores[$key]['goods_count'] = $goods_mod->get_count_of_store($store['store_id']);
        }
        return $stores;
    }
}

?>
