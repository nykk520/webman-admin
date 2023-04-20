<?php
namespace app\admin\service;
use app\admin\model\PayOrder;
use app\admin\model\PayMerchUser;
use app\admin\service\PaySuccessService;
use Workerman\Timer;
use support\Log;
class MerchNotifyService
{
    //主动给商户回调
    public function notify($orid,$type=1)
    {
        $row = PayOrder::where('orid',$orid)->findOrEmpty();
        Log::channel('notify')->info("订单号:".$orid."开始回调商户,方式:".($type == 1 ? '通道回调':'后台补单'));
        if(!$row->isEmpty() && $row['order_status'] == 2){
            $user = PayMerchUser::where('merch_id',$row['merch_id'])->find();
            $notifyData = [
                'mchid' => $user['merch_num'],
                'orid' => $orid,
                'out_trade_no' => $row['merch_order'],
                'money' => $row['order_money'],
                'notify_time' => date('Y-m-d H:i:s',time()),
                'attach' => $row['attach'],
                'status' => 2,
            ]; 
            $notifyData['sign'] = md5($notifyData['mchid'].$notifyData['orid'].$notifyData['out_trade_no'].$notifyData['money'].$notifyData['notify_time'].$user['sign']);
            $return = $this->post(json_encode($notifyData),$row['merch_callback_notify']);
            Log::channel('notify')->info("订单号:".$orid."子通道：<<".$row['sub_channel_name'].">>的通知结果：".json_encode($return));
            if(trim($return) == "SUCCESS"){
                //回调成功
                PayOrder::where('orid',$orid)->inc('merch_callback_num',1)->update(['merch_callback_status'=>1,'merch_callback_time'=>time()]);
                 //开始上分
                $paySuccessService = new PaySuccessService;
                while(true){
                    $result = $paySuccessService->paySuccess($orid,$type);
                    if($result){
                        break;
                    }
                    sleep(1);
                }
            }else{
                
                if($row['merch_callback_num'] < 5){  //开始回调通知商户 10s 20s 30s 40s 50s;
                    PayOrder::where('orid',$orid)->inc('merch_callback_num',1)->update();
                    Timer::add(10*($row['merch_callback_num']+1), [$this, 'notify'],[$orid,$type],false);
                }else{
                    PayOrder::where('orid',$orid)->inc('merch_callback_num',1)->update(['merch_callback_status'=>2,'merch_callback_time'=>time()]);
                }
               
            }
        }
    }
    
    protected function post($data,$url)
    {
        $curl = curl_init();
		$headers = ["Content-Type:application/json;charset=utf-8",];
        
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        if (!empty($data)){
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }
}