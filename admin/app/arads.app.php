<?php

/**
 *    合作伙伴控制器
 *
 *    @author    Garbin
 *    @usage    none
 */
class AradsApp extends BackendApp
{
    var $_arads_mod;

    function __construct()
    {
        $this->AradsApp();
    }

    function AradsApp()
    {
        parent::BackendApp();

        $this->_arads_mod =& m('arads');
        $this->_aradps_mod =& m('aradps');
    }

    /**
     *    管理
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function index()
    {
        $conditions = $this->_get_query_conditions(array(array(
                'field' => '`title`',         //可搜索字段title
                'equal' => 'LIKE',          //等价关系,可以是LIKE, =, <, >, <>
            ),
        ));
        $page   =   $this->_get_page(10);    //获取分页信息
        $aradss = $this->_arads_mod->find(array(
            'conditions'    => $conditions,
            'limit'         => $page['limit'],  //获取当前页的数据
            'order'         => 'sort_order,arads_id ASC',
            'count'         => true             //允许统计
        )); //找出所有商城的合作伙伴
        foreach ($aradss as $key => $arads)
        {
            $arads['pic']&&$aradss[$key]['pic'] = dirname(site_url()) . '/' . $arads['pic'];
            $arads['aradps_id']&&$aradss[$key]['aradps'] = $this->_aradps_mod->get_info($arads['aradps_id']);
        }

        //exit(  var_dump($aradss  ));
        $page['item_count'] = $this->_arads_mod->getCount();   //获取统计的数据
        $this->_format_page($page);
        $this->import_resource(array('script' => 'inline_edit.js'));
        $this->assign('filtered', $conditions? 1 : 0); //是否有查询条件
        $this->assign('page_info', $page);          //将分页信息传递给视图，用于形成分页条
        $this->assign('aradss', $aradss);
        $this->display('arads.index.html');
    }
    /**
     *    新增
     *
     *    @author    Garbin
     *    @return    void
     */
    function add()
    {
        if (!IS_POST)
        {
            /* 显示新增表单 */
            $arads = array(
            'sort_order'    => '255',
            'link'          => 'http://',
            );
            $this->assign('arads' , $arads);
            $this->assign('aradps', $this->_get_options());
            $this->import_resource('jquery.plugins/jquery.validate.js');
            $this->display('arads.form.html');
        }
        else
        {
            $data = array();
            $data['aradps_id']   =   $_POST['aradps_id'];
            $data['title']      =   $_POST['title'];
            $data['link']       =   $_POST['link'];
            $data['sort_order'] =   $_POST['sort_order'];

            if (!$arads_id = $this->_arads_mod->add($data))  //获取arads_id
            {
                $this->show_warning($this->_arads_mod->get_error());

                return;
            }

            /* 处理上传的图片 */
            $pic       =   $this->_upload_pic(uniqid());
            if ($pic === false)
            {
                return;
            }
            $pic && $this->_arads_mod->edit($arads_id, array('pic' => $pic)); //将pic地址记下

            $this->show_message('add_arads_successed',
                'back_list',    'index.php?app=arads',
                'continue_add', 'index.php?app=arads&amp;act=add'
            );
        }
    }

    /**
     *    编辑
     *
     *    @author    Garbin
     *    @return    void
     */
    function edit()
    {
        $arads_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if (!$arads_id)
        {
            $this->show_warning('no_such_arads');

            return;
        }
        if (!IS_POST)
        {
            $find_data     = $this->_arads_mod->find($arads_id);
            if (empty($find_data))
            {
                $this->show_warning('no_such_arads');

                return;
            }
            $arads    =   current($find_data);
            if ($arads['pic'])
            {
                $arads['pic']  =   dirname(site_url()) . "/" . $arads['pic'];
            }
            $this->assign('arads', $arads);
            $this->assign('aradps', $this->_get_options());
            $this->import_resource('jquery.plugins/jquery.validate.js');
            $this->display('arads.form.html');
        }
        else
        {
            $data = array();
            $data['aradps_id']   =   $_POST['aradps_id'];
            $data['title']      =   $_POST['title'];
            $data['link']       =   $_POST['link'];
            $data['sort_order'] =   $_POST['sort_order'];
            $pic               =   $this->_upload_pic(uniqid());
            $pic && $data['pic'] = $pic;
            if ($pic === false)
            {
                return;
            }
            $rows = $this->_arads_mod->edit($arads_id, $data);
            if ($this->_arads_mod->has_error())    //有错误
            {
                $this->show_warning($this->_arads_mod->get_error());

                return;
            }

            $this->show_message('edit_arads_successed',
                'back_list',     'index.php?app=arads',
                'edit_again', 'index.php?app=arads&amp;act=edit&amp;id=' . $arads_id);
        }
    }

    //异步修改数据
   function ajax_col()
   {
       $id     = empty($_GET['id']) ? 0 : intval($_GET['id']);
       $column = empty($_GET['column']) ? '' : trim($_GET['column']);
       $value  = isset($_GET['value']) ? trim($_GET['value']) : '';
       $data   = array();

       if (in_array($column ,array('title', 'sort_order')))
       {
           $data[$column] = $value;
           $this->_arads_mod->edit($id, $data);
           if(!$this->_arads_mod->has_error())
           {
               echo ecm_json_encode(true);
           }
       }
       else
       {
           return ;
       }
       return ;
   }

    function drop()
    {
        $arads_ids = isset($_GET['id']) ? trim($_GET['id']) : 0;
        if (!$arads_ids)
        {
            $this->show_warning('no_such_arads');

            return;
        }
        $arads_ids = explode(',', $arads_ids);//获取一个类似array(1, 2, 3)的数组
        if (!$this->_arads_mod->drop($arads_ids))    //删除
        {
            $this->show_warning($this->_arads_mod->get_error());

            return;
        }

        $this->show_message('drop_arads_successed');
    }

    /* 更新排序 */
    function update_order()
    {
        if (empty($_GET['id']))
        {
            $this->show_warning('Hacking Attempt');
            return;
        }

        $ids = explode(',', $_GET['id']);
        $sort_orders = explode(',', $_GET['sort_order']);
        foreach ($ids as $key => $id)
        {
            $this->_arads_mod->edit($id, array('sort_order' => $sort_orders[$key]));
        }

        $this->show_message('update_order_ok');
    }

    /**
     *    处理上传标志
     *
     *    @author    Garbin
     *    @param     int $arads_id
     *    @return    string
     */
    function _upload_pic($arads_id)
    {
        $file = $_FILES['pic'];
        if ($file['error'] == UPLOAD_ERR_NO_FILE) // 没有文件被上传
        {
            return '';
        }
        import('uploader.lib');             //导入上传类
        $uploader = new Uploader();
        $uploader->allowed_type(IMAGE_FILE_TYPE); //限制文件类型
        $uploader->addFile($_FILES['pic']);//上传pic
        if (!$uploader->file_info())
        {
            $this->show_warning($uploader->get_error() , 'go_back', 'index.php?app=arads&amp;act=edit&amp;id=' . $arads_id);
            return false;
        }
        /* 指定保存位置的根目录 */
        $uploader->root_dir(ROOT_PATH);

        /* 上传 */
        if ($file_path = $uploader->save('data/files/mall/arads', $arads_id))   //保存到指定目录，并以指定文件名$arads_id存储
        {
            return $file_path;
        }
        else
        {
            return false;
        }
    }
    function _get_options()
    {
        $aradps = $this->_aradps_mod->find();
        $options = array();
        foreach ($aradps as $value) {
            $options[$value['aradps_id']] = $value['title'];
        }
        return $options;
    }
}

?>