<?php

define('MAX_LAYER', 2);

class ArflinksApp extends BackendApp{
    function __construct()
    {
        $this->ArflinksApp();
    }
    function ArflinksApp()
    {
        parent::BackendApp();
        $this->_arflinks_mod =& m('arflinks');
    }
    function index()
    {
        $page   =   $this->_get_page(10); 
        $arflinks=$this->_arflinks_mod->find(array(
            'limit'         => $page['limit'],
            'order'         => "arflinks_id desc",
            'count'         => true
        ));
        $page['item_count']=$this->_arflinks_mod->getCount();
        $this->_format_page($page);
        $this->assign('page_info', $page);
        $this->assign('arflinks', $arflinks);
        $this->display('arflinks.index.html');
    }
    function add()
    {
        if (!IS_POST)
        {
            $this->import_resource(array('script' => 'jquery.plugins/jquery.validate.js,change_upload.js'));
            $this->display('arflinks.form.html');
        }
        else
        {
            $data = array();
            $data['title']      =   $_POST['title'];
            $data['link']    =   $_POST['link'];
            $data['if_show']    =   $_POST['if_show'];
            $data['time']   =   date('Y-m-d H:i:s', time());

            if (!$arflinks_id = $this->_arflinks_mod->add($data))  //获取arflinks_id
            {
                $this->show_warning($this->_arflinks_mod->get_error());

                return;
            }
            $this->show_message('add_arflinks_successed',
                'back_list',    'index.php?app=arflinks',
                'continue_add', 'index.php?app=arflinks&amp;act=add'
            );
        }
    }
    function edit()
    {
        $arflinks_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if (!$arflinks_id)
        {
            $this->show_warning('no_such_arflinks');
            return;
        }
        if (!IS_POST)
        {
            $find_data     = $this->_arflinks_mod->find($arflinks_id);
            if (empty($find_data))
            {
                $this->show_warning('no_such_arflinks');

                return;
            }
            $arflinks    =   current($find_data);
            $this->assign('arflinks', $arflinks);
            $this->import_resource(array('script' => 'jquery.plugins/jquery.validate.js,change_upload.js'));
            $this->display('arflinks.form.html');
        }
        else
        {
            $data = array();
            $data['title']          =   $_POST['title'];
            $data['link']       =   $_POST['link'];
            $data['if_show']        =   $_POST['if_show'];

            $rows=$this->_arflinks_mod->edit($arflinks_id, $data);
            if ($this->_arflinks_mod->has_error())
            {
                $this->show_warning($this->_arflinks_mod->get_error());

                return;
            }

            $this->show_message('edit_arflinks_successed',
                'back_list',        'index.php?app=arflinks',
                'edit_again',    'index.php?app=arflinks&amp;act=edit&amp;id=' . $arflinks_id);
        }
    }
    function drop()
    {
        $arflinks_ids = isset($_GET['id']) ? trim($_GET['id']) : '';
        if (!$arflinks_ids)
        {
            $this->show_warning('no_such_arflinks');

            return;
        }
        $arflinks_ids=explode(',',$arflinks_ids);
        $this->_arflinks_mod->drop($arflinks_ids);
        if ($this->_arflinks_mod->has_error())
        {
            $this->show_warning($this->_arflinks_mod->get_error());

            return;
        }

        $this->show_message('drop_arflinks_successed');
    }
}