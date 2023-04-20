<?php

namespace app\admin\service;
use app\admin\model\PayOrder;
use app\admin\model\PayChannel;
class OrderQueryService
{
    public static function query($id)
    {
        //后台主动查询订单状态
        $row = PayOrder::where('id',$id)->field('orid,channel')->find();
        $channelCode = PayChannel::where('id',$row['channel'])->value('channel_code');
        $class = "app\api\controller\pay\\".$channelCode;
        $controller = new $class;
        $status = $controller->query($row['orid']);
        return $status;
    }
}