<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2014/10/15
 * Time: 10:22
 */
class SalesintoApp extends MemberbaseApp
{
    var $_memberrecommendorder_mod;
    function __construct()
    {
        $this->SalesintoApp();
    }
    function SalesintoApp()
    {
        parent::__construct();
        $this->_memberrecommendorder_mod =& m('memberrecommendorder');
    }
    function index(){
        /* 当前位置 */
        $this->_curlocal(LANG::get('member_center'),   'index.php?app=member',
            LANG::get('message'),         'index.php?app=salesinto&amp;act=index',
            LANG::get('salesinto')
        );

        /* 当前所处子菜单 */
        $this->_curmenu('salesinto');
        /* 当前用户中心菜单 */
        $this->_curitem('salesinto');
        $this->import_resource(array(
            'script' => array(
                array(
                    'path' => 'dialog/dialog.js',
                    'attr' => 'id="dialog_js"',
                ),
                array(
                    'path' => 'jquery.ui/jquery.ui.js',
                    'attr' => '',
                ),
                array(
                    'path' => 'jquery.plugins/jquery.validate.js',
                    'attr' => '',
                ),
            ),
            'style' =>  'jquery.ui/themes/ui-lightness/jquery.ui.css',
        ));
        // dump($this->_list_salesinto()); // salesinto_price   seller_name     buyer_name      salesinto_price     order_sn     goods_amount     order_amount
        $this->assign('salesintos', $this->_list_salesinto());
        $this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('salesinto'));
        $this->display('salesinto.index.html');
    }
    function _list_salesinto()
    {
        $page = $this->_get_page(10);
        $salesintos = $this->_memberrecommendorder_mod->find(array(
            'conditions' => 'memberrecommendorder.recommendorder_id = order_alias.order_id AND memberrecommendorder.user_id = '.$this->visitor->get('user_id'),
            'join' => 'belongs_to_order',
            'limit' => $page['limit'],
            'count' => true,
        ));
        $page['item_count'] = $this->_memberrecommendorder_mod->getCount();
        $this->_format_page($page);
        $this->assign('page_info', $page);
        return $salesintos;
    }
    function _get_member_submenu()
    {
        $menus = array(
            array(
                'name' => 'salesinto',
                'url'  => 'index.php?app=salesinto',
            ),
        );
        return $menus;
    }
}