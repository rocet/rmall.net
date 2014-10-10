<?php
define('MAX_LAYER', 2);

/* 地区控制器 */
class AttrApp extends BackendApp
{
    var $_attr_mod;

    function __construct()
    {
        $this->AttrApp();
    }

    function AttrApp()
    {
        parent::__construct();
        $this->_attr_mod =& m('attr');
    }

    /* 管理 */
    function index()
    {
        /* 取得地区 */
        $attrs = $this->_attr_mod->get_list(0);
        foreach ($attrs as $key => $val)
        {
            $attrs[$key]['switchs'] = 0;
            if ($this->_attr_mod->get_list($val['attr_id']))
            {
                $attrs[$key]['switchs'] = 1;
            }
        }
        $this->assign('attrs', $attrs);

        $this->assign('max_layer', MAX_LAYER);

        $this->import_resource(array(
            'script' => 'inline_edit.js,jqtreetable.js',
            'style' => 'res:style/jqtreetable.css'
        ));
        $this->display('attr.index.html');
    }

    /* 新增 */
    function add()
    {
        if (!IS_POST)
        {
            /* 参数 */
            $pid = empty($_GET['pid']) ? 0 : intval($_GET['pid']);
            $attr = array('attr_pid' => $pid, 'sort_order' => 255);
            $this->assign('attr', $attr);

            $this->assign('parents', $this->_get_options());
            $this->display('attr.form.html');
        }
        else
        {
            $data = array(
                'attr_name' => $_POST['attr_name'],
                'attr_pid' => $_POST['attr_pid'],
                'sort_order' => $_POST['sort_order'],
            );

            /* 检查名称是否已存在 */
            if (!$this->_attr_mod->unique(trim($data['attr_name']), $data['attr_pid']))
            {
                $this->show_warning('name_exist');
                return;
            }

            /* 保存 */
            $attr_id = $this->_attr_mod->add($data);
            if (!$attr_id)
            {
                $this->show_warning($this->_attr_mod->get_error());
                return;
            }

            $this->show_message('add_ok',
                'back_list',    'index.php?app=attr',
                'continue_add', 'index.php?app=attr&amp;act=add&amp;pid=' . $data['attr_pid']
                );
        }
    }

    /* 编辑 */
    function edit()
    {
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if (!IS_POST)
        {
            /* 是否存在 */
            $attr = $this->_attr_mod->get_info($id);
            if (!$attr)
            {
                $this->show_warning('attr_empty');
                return;
            }
            $this->assign('attr', $attr);

            $this->assign('parents', $this->_get_options($id));
            $this->display('attr.form.html');
        }
        else
        {
            $attr = $this->_attr_mod->get_info($id);
            if (empty($attr))
            {
                $this->show_warning('no_such_attr');

                return;
            }

            $data = array(
                'attr_name' => $_POST['attr_name'],
                'attr_pid'   => $_POST['attr_pid'],
                'sort_order'  => $_POST['sort_order'],
            );

            /* 检查名称是否已存在 */
            if (!$this->_attr_mod->unique(trim($data['attr_name']), $data['attr_pid'], $id))
            {
                $this->show_warning('name_exist');
                return;
            }

            /* 当移动节点时检查移动后的结构是否合法 */
            if ($attr['attr_pid'] != $data['attr_pid'])
            {
                /* 获取新的节点信息 */
                $all_children = $this->_attr_mod->get_descendant($id);
                $all_parents  = $this->_attr_mod->get_parents($data['attr_pid']);
                $new_attrs = $this->_attr_mod->find(array('conditions' => array_merge($all_children, $all_parents)));
                $new_attrs[$id]['attr_pid'] = $data['attr_pid'];

                /* 判断深度是否合法 */
                $tree = &$this->_tree($new_attrs);
                if (max($tree->layer) > MAX_LAYER)
                {
                    $this->show_warning('path_depth_error');

                    return;
                }
            }

            /* 保存 */
            $rows = $this->_attr_mod->edit($id, $data);
            if ($this->_attr_mod->has_error())
            {
                $this->show_warning($this->_attr_mod->get_error());
                return;
            }

            $this->show_message('edit_ok',
                'back_list',    'index.php?app=attr',
                'edit_again',   'index.php?app=attr&amp;act=edit&amp;id=' . $id
            );
        }
    }

     //异步修改数据
   function ajax_col()
   {
       $id     = empty($_GET['id']) ? 0 : intval($_GET['id']);
       $column = empty($_GET['column']) ? '' : trim($_GET['column']);
       $value  = isset($_GET['value']) ? trim($_GET['value']) : '';
       $data   = array();

       if (in_array($column ,array( 'sort_order')))
       {
           $data[$column] = $value;
           $this->_attr_mod->edit($id, $data);
           if(!$this->_attr_mod->has_error())
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

    /* 异步取下一级地区  */
    function ajax_cate()
    {
        if(!isset($_GET['id']) || empty($_GET['id']))
        {
            echo ecm_json_encode(false);
            return;
        }
        $cate = $this->_attr_mod->get_list($_GET['id']);
        foreach ($cate as $key => $val)
        {
            $child = $this->_attr_mod->get_list($val['attr_id']);
            if (!$child || empty($child))
            {
                $cate[$key]['switchs'] = 0;
            }
            else
            {
                $cate[$key]['switchs'] = 1;
            }
        }
        header("Content-Type:text/html;charset=" . CHARSET);
        echo ecm_json_encode(array_values($cate));
        //$this->json_result($cate);
        return ;
    }

    /* 删除 */
    function drop()
    {
        $id = isset($_GET['id']) ? trim($_GET['id']) : '';
        if (!$id)
        {
            $this->show_warning('no_attr_to_drop');
            return;
        }

        $ids = explode(',', $id);
        if (!$this->_attr_mod->drop($ids))
        {
            $this->show_warning($this->_attr_mod->get_error());
            return;
        }

        $this->show_message('drop_ok');
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
            $this->_attr_mod->edit($id, array('sort_order' => $sort_orders[$key]));
        }

        $this->show_message('update_order_ok');
    }

    /* 导出数据 */
    function export()
    {
        // 目标编码
        $to_charset = (CHARSET == 'utf-8') ? substr(LANG, 0, 2) == 'sc' ? 'gbk' : 'big5' : '';

        if (!IS_POST)
        {
            if (CHARSET == 'utf-8')
            {
                $this->assign('note_for_export', sprintf(LANG::get('note_for_export'), $to_charset));
                $this->display('common.export.html');

                return;
            }
        }
        else
        {
            if (!$_POST['if_convert'])
            {
                $to_charset = '';
            }
        }

        $attrs = $this->_attr_mod->get_list();
        $tree =& $this->_tree($attrs);
        $csv_data = $tree->getCSVData(0, 'sort_order');
        $this->export_to_csv($csv_data, 'attr', $to_charset);
    }

    /* 导入数据 */
    function import()
    {
        if (!IS_POST)
        {
            $this->assign('note_for_import', sprintf(LANG::get('note_for_import'), CHARSET));
            $this->display('common.import.html');
        }
        else
        {
            $file = $_FILES['csv'];
            if ($file['error'] != UPLOAD_ERR_OK)
            {
                $this->show_warning('select_file');
                return;
            }
            if ($file['name'] == basename($file['name'],'.csv'))
            {
                $this->show_warning('not_csv_file');
                return;
            }

            $data = $this->import_from_csv($file['tmp_name'], false, $_POST['charset'], CHARSET);
            $parents = array(0 => 0); // 存放layer的parent的数组
            $fileds = array_intersect($data[0],array('attr_name', 'sort_order')); //第一行含有的字段
            $start_col = intval(array_search('attr_name', $fileds)); //主数据区开始列号
            foreach ($data as $row)
            {
                $layer = -1;
                 if(array_intersect($row,array('attr_name', 'sort_order')))
                {
                    continue;
                }
                $sort_order_col = array_search('sort_order', $fileds); //从表头得到sort_order的列号
                $sort_order = is_numeric($sort_order_col) && isset($row[$sort_order_col]) ? $row[$sort_order_col] : 255;
                for ($i = $start_col; $i < count($row); $i++)
                {
                    if (trim($row[$i]))
                    {
                        $layer = $i - $start_col;
                        $attr_name  = trim($row[$i]);
                        break;
                    }
                }

                // 没数据
                if ($layer < 0)
                {
                    continue;
                }

                // 只处理有上级的
                if (isset($parents[$layer]))
                {
                    $attr = $this->_attr_mod->get("attr_name = '$attr_name' AND attr_pid = '$parents[$layer]'");
                    if (!$attr)
                    {
                        // 不存在
                        $id = $this->_attr_mod->add(array(
                            'attr_name'   => $attr_name,
                            'attr_pid'     => $parents[$layer],
                            'sort_order'    => $sort_order,
                        ));
                        $parents[$layer + 1] = $id;
                    }
                    else
                    {
                        // 已存在
                        $parents[$layer + 1] = $attr['attr_id'];
                    }
                }
            }

            $this->show_message('import_ok',
                'back_list', 'index.php?app=attr');
        }
    }

    /* 构造并返回树 */
    function &_tree($attrs)
    {
        import('tree.lib');
        $tree = new Tree();
        $tree->setTree($attrs, 'attr_id', 'attr_pid', 'attr_name');
        return $tree;
    }

    /* 取得可以作为上级的地区数据 */
    function _get_options($except = NULL)
    {
        $attrs = $this->_attr_mod->get_list();
        $tree =& $this->_tree($attrs);
        return $tree->getOptions(MAX_LAYER - 1, 0, $except);
    }
}

?>
