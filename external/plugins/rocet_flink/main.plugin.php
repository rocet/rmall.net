<?php

/**
 * 全站友情链接插件
 *
 * @return  array
 */
class Rocet_flinkPlugin extends BasePlugin
{
    function execute()
    {
        if (defined('IN_BACKEND') && IN_BACKEND === true)
        {
            return; // 后台无需执行
        }
        else
        {
            $this->assign('rocet_flink', array(array('a' => 'dd'),array('a' => 'ff')));
        }
    }
}

?>