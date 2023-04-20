<?php


namespace app\admin\service;
use app\admin\model\PayOrder;
use app\admin\model\PayFlow;
use app\admin\model\PayMerchUser;
use app\admin\model\PayChannelSub;
use app\admin\model\PayChannel;
use think\facade\Db;
//支付成功处理
class PaySuccessService
{
    public function paySuccess($orid,$type=1)
    {
        bcscale(2);
        $order = PayOrder::where('orid',$orid)->findOrEmpty();
        if(!$order->isEmpty()){
            $flow = PayFlow::where('from_orid',$order['orid'])->findOrEmpty();
            if($flow->isEmpty()){
                 //没有流水，生成流水
                $user = PayMerchUser::where('merch_id',$order['merch_id'])->find();
                $flow_data = [
                    'merch_id' => $order['merch_id'],
                    'from_orid' => $order['orid'],
                    'bef' => $user['money'],
                    'aft' => bcadd($user['money'],$order['receipt_money']),
                    'real_money'=> $order['receipt_money'],
                    'order_money' => $order['order_money'],
                    'class' => 1, //代收
                    'create_time' => time(),
                    'attach' => $type == 1 ? '支付回调上分':'后台补单'
                ];
                
                Db::startTrans();
                try {
                    $save1 = PayMerchUser::where([['merch_id','=',$order['merch_id']],['money','=',$user['money']]])->update(['money'=>$flow_data['aft']]);
                    $save3 = PayFlow::insert($flow_data);
                    $save4 = PayChannelSub::where('id',$order['channel_sub'])->inc('total_money',$order['order_money'])->inc('day_money', $order['order_money'])->update();
                    $save5 = PayChannel::where('id',$order['channel'])->inc('total_money',$order['order_money'])->inc('day_money', $order['order_money'])->update();
                    Db::commit(); 
                } catch (\Exception $e) {
                    Db::rollback();
                    return false;
                }
            }else{
                //有流水，说明加了moneyle  所有请求都是先改订单状态后再来了，先有状态后又流水
                // $save2 = PayOrder::where('orid',$orid)->update(['order_status'=>2]);
                return true;
            }
        }
    }
}