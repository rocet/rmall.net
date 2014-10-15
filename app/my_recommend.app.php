<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2014/10/15
 * Time: 10:37
 */
class My_recommendApp extends MemberbaseApp
{
    var $_memberrecommend_mod;
    function __construct()
    {
        $this->My_recommendApp();
    }
    function My_recommendApp()
    {
        parent::__construct();
        $this->_memberrecommend_mod =& m('memberrecommend');
    }
    function index(){
        /* 当前位置 */
        $this->_curlocal(LANG::get('member_center'),   'index.php?app=member',
            LANG::get('message'),         'index.php?app=my_recommend&amp;act=index',
            LANG::get('my_recommend')
        );

        /* 当前所处子菜单 */
        $this->_curmenu('my_recommend');
        /* 当前用户中心菜单 */
        $this->_curitem('my_recommend');
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
        //dump($this->_list_my_recommend());
        $this->assign('my_recommend', $this->_list_my_recommend());
        $this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('my_recommend'));
        $this->display('my_recommend.index.html');
    }
    function _list_my_recommend()
    {
        $page = $this->_get_page(10);
        $memberrecommends = $this->_memberrecommend_mod->find(array(
            'conditions' => 'recommend_id = '.$this->visitor->get('user_id'),
            'join' => 'belongs_to_member',
            'limit' => $page['limit'],
            'count' => true,
        ));
        $page['item_count'] = $this->_memberrecommend_mod->getCount();
        $this->_format_page($page);
        $this->assign('page_info', $page);
        return $memberrecommends;
    }
    function _get_member_submenu()
    {
        $menus = array(
            array(
                'name' => 'my_recommend',
                'url'  => 'index.php?app=my_recommend',
            ),
        );
        return $menus;
    }
}