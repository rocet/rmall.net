<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2014/10/13
 * Time: 12:35
 */
class MemberRecommendModel extends BaseModel
{
    var $table  = 'memberrecommend';
    var $prikey = 'memberrecommend_id';
    var $_name  = 'memberrecommend';

    /* 与其它模型之间的关系 */
    var $_relation = array(
        'belongs_to_member'  => array(
            'type'          => BELONGS_TO,
            'foreign_key'   => 'user_id',
            'reverse'       => 'has_recommend',
            'model'         => 'member',
        ),
    );
}