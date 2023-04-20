<?php
namespace app\admin\middleware;

use Webman\MiddlewareInterface;
use Webman\Http\Response;
use Webman\Http\Request;
use app\admin\service\AuthService;
class AuthCheck implements MiddlewareInterface
{
    use \app\admin\traits\JumpTrait;
    public function process(Request $request, callable $handler) : Response
    {
        $adminId = session('admin.id');
        
        $adminConfig = config('admin');
      
        $expireTime = session('admin.expire_time');
        /** @var AuthService $authService */
        $authServiceClass = new AuthService($adminId);
        // $authService = app(AuthService::class, ['adminId' => $adminId]);
        $currentController = get_controller();
        $currentNode = $currentController .'/'. strtolower(request()->action);
        // 验证登录
        if (!in_array($currentController, $adminConfig['no_login_controller']) &&
            !in_array($currentNode, $adminConfig['no_login_node'])) {
            empty($adminId) &&  $this->error('请先登录后台', [], '/admin/login/index');
              
            

            // 判断是否登录过期
            if ($expireTime !== true && time() > $expireTime) {
                $session = $request->session();
                // 删除一项
                $session->forget('admin');
                $this->error('登录已过期，请重新登录', [], '/admin/login/index');
            }
        }

        // 验证权限
        if (!in_array($currentController, $adminConfig['no_auth_controller']) &&
            !in_array($currentNode, $adminConfig['no_auth_node'])) {
            $check = $authServiceClass->checkNode($currentNode);
            if(!$check){
                $this->error('无权限访问');
            } 

            

        }
        // 请求继续穿越
        return $handler($request);
    }
}