<?php

/* 属性 attr */
class AttrModel extends BaseModel
{
    var $table  = 'attr';
    var $prikey = 'attr_id';
    var $_name  = 'attr';
    var $_relation  = array(
        // 一个属性有多个子属性
        'has_attr' => array(
            'model'         => 'attr',
            'type'          => HAS_MANY,
            'foreign_key'   => 'attr_pid',
            'dependent'     => true
        ),
        // 一个属性有多商品属性
        'has_mallattr' => array(
            'model'         => 'mallattr',
            'type'          => HAS_MANY,
            'foreign_key'   => 'attr_id_2',
            'dependent'     => true
        ),
    );
    var $_autov = array(
        'attr_name' => array(
            'required'  => true,
            'filter'    => 'trim',
        ),
        'sort_order'    => array(
            'filter'    => 'intval',
        ),
    );
    /**
     * 取得属性筛选数据
     *
     * @return array
     */
    function get_tree()
    {
        $tree = array();
        if( $attr_heads = $this->get_list(0) ){
            $tree = $attr_heads;
            foreach ($tree as & $attr) {
                $attr['subattrs'] = $this->get_list($attr['attr_id']);
            }
        }
        return $tree;
    }
    /**
     * 取得属性列表
     *
     * @param int $attr_pid 大于等于0表示取某个属性的下级属性，小于0表示取所有属性
     * @return array
     */
    function get_list($attr_pid = -1)
    {
        if ($attr_pid >= 0)
        {
            return $this->find(array(
                'conditions' => "attr_pid = '$attr_pid'",
                'order' => 'sort_order, attr_id',
            ));
        }
        else
        {
            return $this->find(array(
                'order' => 'sort_order, attr_id',
            ));
        }
    }
    /*
     * 判断名称是否唯一
     */
    function unique($attr_name, $attr_pid, $attr_id = 0)
    {
        $conditions = "attr_pid = '" . $attr_pid . "' AND attr_name = '" . $attr_name . "'";
        $attr_id && $conditions .= " AND attr_id <> '" . $attr_id . "'";
        return count($this->find(array('conditions' => $conditions))) == 0;
    }
    /**
     * 取得options，用于下拉列表
     */
    function get_options($attr_pid = 0)
    {
        $res = array();
        $attrs = $this->get_list($attr_pid);
        foreach ($attrs as $attr)
        {
            $res[$attr['attr_id']] = $attr['attr_name'];
        }
        return $res;
    }
    /**
     * 取得某属性的所有子孙属性id
     */
    function get_descendant($id)
    {
        $ids = array($id);
        $ids_total = array();
        $this->_get_descendant($ids, $ids_total);
        return array_unique($ids_total);
    }

    /**
     * 取得某属性的所有父级属性
     *
     * @param  int $attr_id
     * @return void
     **/
    function get_parents($attr_id)
    {
        $parents = array();
        $attr = $this->get($attr_id);
        if (!empty($attr))
        {
            if ($attr['attr_pid'])
            {
                $tmp = $this->get_parents($attr['attr_pid']);
                $parents = array_merge($parents, $tmp);
                $parents[] = $attr['attr_pid'];
            }
            $parents[] = $attr_id;
        }

        return array_unique($parents);
    }

    function _get_descendant($ids, &$ids_total)
    {
        $childs = $this->find(array(
            'fields' => 'attr_id',
            'conditions' => "attr_pid " . db_create_in($ids)
        ));
        $ids_total = array_merge($ids_total, $ids);
        $ids = array();
        foreach ($childs as $child)
        {
            $ids[] = $child['attr_id'];
        }
        if (empty($ids))
        {
            return ;
        }
        $this->_get_descendant($ids, $ids_total);
    }
}

?>