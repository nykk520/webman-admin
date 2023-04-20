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

namespace app\admin\controller;


use app\admin\model\SystemAdmin;
use support\Request;
use Webman\Captcha\CaptchaBuilder;
use app\Google;
/**
 * Class Login
 * @package app\admin\controller
 */
class Login extends Base
{

    /**
     * 初始化方法
     */
     
    public function __construct()
    {
       
        $action = request()->action;
        if (!empty(session('admin')) && !in_array($action, ['out'])) {
            $this->success('已登录，无需再次登录', [], "/admin");
        }
    }
   

    /**
     * 用户登录
     * @return string
     * @throws \Exception
     */
    public function index()
    {
        $captcha = 1;
        if (request()->isAjax()) {
            $post = request()->post();
            $rule = [
                'username|用户名'      => 'require',
                'password|密码'       => 'require',
                'keep_login|是否保持登录' => 'require',
                'captcha|验证码' => 'require',
            ];
        
            $this->validate($post, $rule);
            
            // 对比session中的captcha值
            if (strtolower($post['captcha']) !== request()->session()->get('captcha')) {
                $this->error('验证码不正确');
            }
            
            $admin = SystemAdmin::where(['username' => $post['username']])->find();
            if (empty($admin)) {
                $this->error('用户不存在');
            }
            if (password($post['password']) != $admin->password) {
                $this->error('密码输入有误');
            }
            if ($admin->status == 0) {
                $this->error('账号已被禁用');
            }

            $login_error = cache('login_error');
            if($login_error){
                $this->error('google验证失败次数太多，请稍后重试');
            }
            // google验证
            if($admin->google == 1){
                $data =[
					'id'=>$admin->id,
					'keep_login' =>$post['keep_login'] == 1 ? true :time() + 7200,
					];
                session(['user'=>$data]);
                $this->success('请输入google验证码',[],url('login/google'));
            }
            $admin->login_num += 1;
            $admin->save();
            $admin = $admin->toArray();
            unset($admin['password']);
            $admin['expire_time'] = $post['keep_login'] == 1 ? true : time() + 7200;
            session(['admin'=> $admin]);
            $this->success('登录成功','',url('index/index'));
            
            
        }
        $this->assign('captcha', $captcha);
        $this->assign('demo', 1);
        return $this->fetch();
    }

    /**
     * 用户退出
     * @return mixed
     */
    public function out()
    {
        request()->session()->forget('admin');
        $this->success('退出登录成功');
    }
    
    
    /**
     * 验证码
     * @return \think\Response
     */
    public function captcha(Request $request)
    {
        // 初始化验证码类
        $builder = new CaptchaBuilder;
        // 生成验证码
        $builder->build();
        // 将验证码的值存储到session中
        $request->session()->set('captcha', strtolower($builder->getPhrase()));
        // 获得验证码图片二进制数据
        $img_content = $builder->get();
        // 输出验证码二进制数据
        return response($img_content, 200, ['Content-Type' => 'image/jpeg']);
    }
    
    
    public function google()
    {
    	if (request()->isAjax()) {
    		$post = request()->post();
    		$user = session('user');
    		$admin = SystemAdmin::where(['id' =>$user['id']])->find();
    		//google 验证
			if($admin['google_code']&&$admin['google']==1&&$post['gcode']){
				$ga = new Google();
	        	$checkResult = $ga->verifyCode($admin['google_code'], $post['gcode'], 2); // 2 = 2 * 30秒时钟容差
                $login_error = session("login_error")?session("login_error"):0;
				if (!$checkResult) {
                    session(["login_error"=>++$login_error]);
                    if($login_error >= 6 ){
                        request()->session()->forget('login_error');
                        cache('login_error', $admin['name'], 3600);
                        $this->error('谷歌验证失败超过最大次数',[], url('login/index'));
                    }
				    $this->error('谷歌验证失败'.$login_error);
				}
				$admin->login_num += 1;
	            $admin->save();
	            $admin = $admin->toArray();
	            unset($admin['password']);
	            $admin['expire_time'] = $user['keep_login'];
				session(['admin'=> $admin]);
				request()->session()->forget('user');
            	$this->success('登录成功','',url('index/index'));
			}
    	}
    	return $this->fetch();
    }
}
