<?php

return array(
    'code'      => 'zpay',
    'name'      => Lang::get('zpay'),
    'desc'      => Lang::get('zpay_desc'),
    'is_online' => '1',
    'author'    => Lang::get('zpay_author'),
    'version'   => '1.0',
    'currency'  => Lang::get('zpay_currency'),
    'config'    => array(       
        'zpay_key'       => array(        //密钥
            'text'  => Lang::get('zpay_key'),
            'desc'  => Lang::get('zpay_key_desc'),
            'type'  => 'text',
        ),
	),
);

?>