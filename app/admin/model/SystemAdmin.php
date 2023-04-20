<?php

// +----------------------------------------------------------------------
// | EasyAdmin
// +----------------------------------------------------------------------
// | PHP交流群: 763822524
// +----------------------------------------------------------------------
// | 开源协议  https://mit-license.org 
// +----------------------------------------------------------------------
// | github开源项目：https://github.com/zhongshaofa/EasyAdmin
// +----------------------------------------------------------------------

namespace app\admin\model;



class SystemAdmin extends TimeModel
{

    protected $deleteTime = 'delete_time';

    public function getAuthList()
    {
        $list = (new SystemAuth())
            ->where('status', 1)
            ->where('id','<>',7)
            ->column('title', 'id');
        return $list;
    }
    public function merch()
    {
        return $this->hasOne('app\admin\model\PayMerchUser', 'merch_id', 'id');
    }
    
    
}