<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2014/10/13
 * Time: 12:39
 */
class MemberRecommendOrderModel extends BaseModel
{
    var $table = 'memberrecommendorder';
    var $prikey = 'memberrecommendorder_id';
    var $_name = 'memberrecommendorder';

    /* 与其它模型之间的关系 */
    var $_relation = array(
        'belongs_to_member'  => array(
            'type'          => BELONGS_TO,
            'foreign_key'   => 'user_id',
            'reverse'       => 'has_recommendorder',
            'model'         => 'member',
        ),
    );
}