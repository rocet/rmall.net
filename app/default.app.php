<?php

class DefaultApp extends MallbaseApp
{
    function index()
    {
        $this->assign('index', 1); // 标识当前页面是首页，用于设置导航状态
        $this->assign('icp_number', Conf::get('icp_number'));

        /* 热门搜素 */
        

        $this->_config_seo(array(
            'title' => Lang::get('mall_index') . ' - ' . Conf::get('site_title'),
        ));
        $this->assign('page_description', Conf::get('site_description'));
        $this->assign('page_keywords', Conf::get('site_keywords'));


        // CREATE TABLE IF NOT EXISTS `ecm_attr` (
        //   `attr_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        //   `attr_pid` int(10) unsigned NOT NULL DEFAULT '0',
        //   `attr_name` varchar(80) NOT NULL,
        //   `sort_order` int(10) unsigned NOT NULL DEFAULT '255',
        //   PRIMARY KEY (`attr_id`),
        //   UNIQUE KEY `attr_name` (`attr_name`)
        // ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=56 ;

        // -- --------------------------------------------------------

        // --
        // -- 表的结构 `ecm_mall_attr`
        // --

        // CREATE TABLE IF NOT EXISTS `ecm_mall_attr` (
        //   `mattr_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        //   `goods_id` int(10) unsigned NOT NULL,
        //   `attr_id_1` tinyint(3) unsigned NOT NULL,
        //   `attr_id_2` tinyint(3) unsigned NOT NULL,
        //   `color_rgb` varchar(7) NOT NULL,
        //   `price` decimal(10,2) NOT NULL DEFAULT '0.00',
        //   `stock` int(11) NOT NULL DEFAULT '0',
        //   `sku` varchar(60) NOT NULL,
        //   PRIMARY KEY (`mattr_id`)
        // ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;




        // $this->_D( m('seckill')->find(array('join'=>'belong_goods')) );
        // $this->_D( m('groupbuy')->find(array('join'=>'belong_goods')) );

        // $this->_D( m('goods')->get_list() );

        //分类筛选  cate_id=  当前分类筛选  cate_id_1=   顶级分类筛选   cate_id_2= 2级分类筛选  cate_id_3=  3级分类筛选
        //排序      views  浏览量  sales 销量  comments  评论量  add_time  时间  price  价格
        array(
            'conditions'  => 'cate_id=1',   
            'limit'   => '',
            'order'   => ''
        );
        
        $indexgroup = current( m('groupbuy')->find(array('order' => 'views desc', 'limit' => '0, 1', 'join'=>'belong_goods')));
        if($indexgroup){
            $group_price = current( unserialize($indexgroup['spec_price']) );
            $indexgroup['group_price'] = $group_price['price'];
        }
        $this->assign('rocet_group_indextopgroup', $indexgroup );
        
        $this->assign('rocet_goods_indexnewgoods', m('goods')->find(array(
            'join' => 'be_recommend, belongs_to_store, has_goodsstatistics',
            'fields' => 'g.default_image, g.price, g.goods_name, s.store_id, s.store_name, g.cate_name, g.brand, recommended_goods.recommend_pic, recommended_goods.sort_order, g.closed, g.if_show, views',
            'conditions' => "recommended_goods.recom_id = '".key(m('recommend')->find(array(
                'conditions'  => ' `recom_name`=\'新品首发\'',
                'fields' => 'recom_id',
            )))."'",
            'limit' => '0, 8',
            'order' => 'recommended_goods.sort_order',
        )) );

        $cate_roots = m('gcategory')->find(array(
            'conditions' => '`parent_id`=0 AND `store_id` = 0',
            'limit' => '0, 6'
        ));
        $cate_roots_keys = array_keys($cate_roots);
        $index_cate_datas = array();
        for ($i=1; $i <= count($cate_roots_keys) ; $i++) {
            $current = $cate_roots[$cate_roots_keys[$i-1]];
            if(!empty($current)){
                $index_cate_datas[$cate_roots_keys[$i-1]]['cate_name'] = $cate_roots[$cate_roots_keys[$i-1]]['cate_name'];
                $index_cate_datas[$cate_roots_keys[$i-1]]['iterationnum'] = $i+2;
                $index_cate_datas[$cate_roots_keys[$i-1]]['rocet_goods_indexchannel'] = m('gcategory')->find(array(
                    'conditions' => "parent_id=".$current['cate_id'],
                    'limit' => '0, 6',
                    'order' => 'sort_order asc',
                ));

                $index_cate_datas[$cate_roots_keys[$i-1]]['rocet_goods_indexchannelgoods'] = m('goods')->find(array(
                    'join' => 'be_recommend, belongs_to_store, has_goodsstatistics',
                    'fields' => 'g.default_image, g.price, g.goods_name, s.store_id, s.store_name, g.cate_name, g.brand, recommended_goods.recommend_pic, recommended_goods.sort_order, g.closed, g.if_show, views',
                    'conditions' => "g.cate_id_1=".$current['cate_id']." AND recommended_goods.recom_id = '".key(m('recommend')->find(array(
                        'conditions'  => ' `recom_name`=\'首页推荐\'',
                        'fields' => 'recom_id',
                    )))."'",
                    'limit' => '0, 7',
                    'order' => 'recommended_goods.sort_order',
                ));

                $index_cate_datas[$cate_roots_keys[$i-1]]['rocet_goods_indexchannelslidgoods'] = m('goods')->find(array(
                    'join' => 'be_recommend, belongs_to_store, has_goodsstatistics',
                    'fields' => 'g.default_image, g.price, g.goods_name, s.store_id, s.store_name, g.cate_name, g.brand, recommended_goods.recommend_pic, recommended_goods.sort_order, g.closed, g.if_show, views',
                    'conditions' => "g.cate_id_1=".$current['cate_id']." AND recommended_goods.recom_id = '".key(m('recommend')->find(array(
                        'conditions'  => ' `recom_name`=\'首页栏目推荐\'',
                        'fields' => 'recom_id',
                    )))."'",
                    'limit' => '0, 3',
                    'order' => 'recommended_goods.sort_order',
                ));
            }
        }
        $this->assign('cate_roots_keys', $index_cate_datas);

        $this->assign('rocte_indexbrands', m('brand')->find(array(
            'limit' => '0, 14',
            'order' => 'sort_order',
        )) ); 


        $this->assign('rocet_goods_indexshipgoods', m('goods')->find(array(
            'join' => 'be_recommend, belongs_to_store, has_goodsstatistics',
            'fields' => 'g.default_image, g.price, g.goods_name, s.store_id, s.store_name, g.cate_name, g.brand, recommended_goods.recommend_pic, recommended_goods.sort_order, g.closed, g.if_show, views',
            'conditions' => "recommended_goods.recom_id = '".key(m('recommend')->find(array(
                'conditions'  => ' `recom_name`=\'精品推荐\'',
                'fields' => 'recom_id',
            )))."'",
            'limit' => '0, 5',
            'order' => 'recommended_goods.sort_order',
        )) );



		
        $this->assign('rocet_goods_indexweekgoods', m('goods')->find(array(
            'join' => 'be_recommend, belongs_to_store, has_goodsstatistics',
            'fields' => 'g.default_image, g.price, g.goods_name, s.store_id, s.store_name, g.cate_name, g.brand, recommended_goods.recommend_pic, recommended_goods.sort_order, g.closed, g.if_show, views',
            'conditions' => "recommended_goods.recom_id = '".key(m('recommend')->find(array(
                'conditions'  => ' `recom_name`=\'每周推荐\'',
                'fields' => 'recom_id',
            )))."'",
            'limit' => '0, 3',
            'order' => 'recommended_goods.sort_order',
        )) );
		
		


        $this->assign('rocet_goods_indexbestgoods', m('goods')->find(array(
            'join' => 'be_recommend, belongs_to_store, has_goodsstatistics',
            'fields' => 'g.default_image, g.price, g.goods_name, s.store_id, s.store_name, g.cate_name, g.brand, recommended_goods.recommend_pic, recommended_goods.sort_order, g.closed, g.if_show, views',
            'conditions' => "recommended_goods.recom_id = '".key(m('recommend')->find(array(
                'conditions'  => ' `recom_name`=\'精品推荐\'',
                'fields' => 'recom_id',
            )))."'",
            'limit' => '0, 8',
            'order' => 'recommended_goods.sort_order',
        )) );


        $this->assign('rocet_goods_indexshops', m('store')->find(array(
            'conditions' => "recommended = 1",
            'limit' => '0, 16',
            'order' => 'sort_order',
        )) );
        

        $this->assign('rocet_goods_indexrecommend', m('goods')->find(array(
            'join' => 'be_recommend, belongs_to_store, has_goodsstatistics',
            'fields' => 'g.default_image, g.price, g.goods_name, s.store_id, s.store_name, g.cate_name, g.brand, recommended_goods.sort_order, g.closed, g.if_show, views',
            'limit' => '0, 3',
            'order' => 'recommended_goods.sort_order',
        )));



        $this->assign('rocet_adv_indexbannerfocus', m('arads')->find(array( 
            'conditions'  => '`aradps_id`=' . key(m('aradps')->find(array('conditions' => '`identify`='."'indexbannerfocus'")) ),
            'limit'   => '0, 6',
            'order'   => 'arads_id desc', 
        )) );

        $this->assign('rocet_adv_indexleft', current( m('arads')->find(array( 
            'conditions'  => '`aradps_id`=' . key(m('aradps')->find(array('conditions' => '`identify`='."'indexleft'")) ),
            'limit'   => '0, 1',
            'order'   => 'arads_id desc', 
        ))) );

        $indexkuaixun = m('article')->find(array( 
            'conditions'  => '`cate_id`='.key(m('acategory')->find(array('conditions' => '`cate_name`='."'快讯'")) ),
            'limit'   => '0, 15',
            'order'   => 'sort_order desc', 
        ));
        $indexkuaixun_top1 = current($indexkuaixun);
        $indexkuaixun_top1_pic = current( m('uploadedfile')->find(array( 
            'conditions'  => '`belong`=1 AND item_id='.$indexkuaixun_top1['article_id'] ,
            'fields' => 'file_path',
            'limit'   => '0, 1',
        )));
        $this->assign('rocet_art_indexkuaixun_top1_pic',  $indexkuaixun_top1_pic['file_path']);
        $this->assign('rocet_art_indexkuaixun',  $indexkuaixun);















        $this->display('index.html');
    }


    function index2()
    {
        $this->assign('index', 1); // 标识当前页面是首页，用于设置导航状态
        $this->assign('icp_number', Conf::get('icp_number'));

        /* 热门搜素 */
        $this->assign('hot_keywords', $this->_get_hot_keywords());

        $this->_config_seo(array(
            'title' => Lang::get('mall_index') . ' - ' . Conf::get('site_title'),
        ));
        $this->assign('page_description', Conf::get('site_description'));
        $this->assign('page_keywords', Conf::get('site_keywords'));
        $this->display('index-default.html');
    }

}

?>
