<?php

/* 商品属性 */
class AttrApp extends MallbaseApp
{

    function index(){

    }
    function ajax_option(){
    	$pid = isset($_REQUEST['pid']) && $_REQUEST['pid'] + 0 > 0  ?  $_REQUEST['pid'] : 0;
    	$response = array('success' => 'false', 'msg'=>'','data'=>'');
    	if($pid && !empty( $data = m('attr')->get_options($pid) ) ){
    		$response = array('success' => 'true', 'msg'=>'ok','data'=>$data);
    	}
    	exit( json_encode( $response  ) );
    }
}