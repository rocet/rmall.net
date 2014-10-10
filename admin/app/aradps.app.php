<?php

define('MAX_LAYER', 2);

class AradpsApp extends BackendApp{
	function __construct()
    {
        $this->AradpsApp();
    }
    function AradpsApp()
    {
        parent::BackendApp();
        $this->_aradps_mod =& m('aradps');
    }
	function index()
    {
        $page   =   $this->_get_page(10); 
        $aradps=$this->_aradps_mod->find(array(
            'limit'         => $page['limit'],
            'order'         => "aradps_id desc",
            'count'         => true
        ));
        $page['item_count']=$this->_aradps_mod->getCount();
        $this->_format_page($page);
        $this->assign('page_info', $page);
        $this->assign('aradps', $aradps);
		$this->display('aradps.index.html');
	}
	function add()
    {
        if (!IS_POST)
        {
            $this->import_resource(array('script' => 'jquery.plugins/jquery.validate.js,change_upload.js'));
            $this->display('aradps.form.html');
        }
        else
        {
            $data = array();
            $data['title']      =   $_POST['title'];
            $data['identify']    =   $_POST['identify'];
            $data['if_show']    =   $_POST['if_show'];
            $data['time']   =   date('Y-m-d H:i:s', time());

            if (!$aradps_id = $this->_aradps_mod->add($data))  //获取aradps_id
            {
                $this->show_warning($this->_aradps_mod->get_error());

                return;
            }
            $this->show_message('add_aradps_successed',
                'back_list',    'index.php?app=aradps',
                'continue_add', 'index.php?app=aradps&amp;act=add'
            );
        }
    }
	function edit()
    {
        $aradps_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if (!$aradps_id)
        {
            $this->show_warning('no_such_aradps');
            return;
        }
        if (!IS_POST)
        {
            $find_data     = $this->_aradps_mod->find($aradps_id);
            if (empty($find_data))
            {
                $this->show_warning('no_such_aradps');

                return;
            }
            $aradps    =   current($find_data);
            $this->assign('aradps', $aradps);
            $this->import_resource(array('script' => 'jquery.plugins/jquery.validate.js,change_upload.js'));
        	$this->display('aradps.form.html');
        }
        else
        {
            $data = array();
            $data['title']          =   $_POST['title'];
            $data['identify']       =   $_POST['identify'];
            $data['if_show']        =   $_POST['if_show'];

            $rows=$this->_aradps_mod->edit($aradps_id, $data);
            if ($this->_aradps_mod->has_error())
            {
                $this->show_warning($this->_aradps_mod->get_error());

                return;
            }

            $this->show_message('edit_aradps_successed',
                'back_list',        'index.php?app=aradps',
                'edit_again',    'index.php?app=aradps&amp;act=edit&amp;id=' . $aradps_id);
        }
    }
	function drop()
    {
        $aradps_ids = isset($_GET['id']) ? trim($_GET['id']) : '';
        if (!$aradps_ids)
        {
            $this->show_warning('no_such_aradps');

            return;
        }
        $aradps_ids=explode(',',$aradps_ids);
        $this->_aradps_mod->drop($aradps_ids);
        if ($this->_aradps_mod->has_error())
        {
            $this->show_warning($this->_aradps_mod->get_error());

            return;
        }

        $this->show_message('drop_aradps_successed');
    }
}