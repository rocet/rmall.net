<?php

!defined('ROOT_PATH') && exit('Forbidden');

class BasePlugin extends Object
{
    var $data = array();
    function __construct($data, $config)
    {
        $this->BasePlugin($data, $config);
    }
    function BasePlugin($data, $config)
    {
        $this->data     = $data;
        $this->config   = $config;
    }
    function execute()
    {
        # code...
    }
    function assign($k, $v)
    {
        $app =& cc();
        $app->assign($k, $v);
    }
    function display($f)
    {
        $app =& cc();
        $app->display($f);
    }
}

?>