<?php
namespace app\admin\middleware;

use Webman\MiddlewareInterface;
use Webman\Http\Response;
use Webman\Http\Request;
use app\admin\service\AuthService;
use app\admin\service\SystemLogService;
class SystemLog implements MiddlewareInterface
{
    /**
     * 敏感信息字段，日志记录时需要加密
     * @var array
     */
    protected $sensitiveParams = [
        'password',
        'password_again',
        'phone',
        'mobile'
    ];

    public function process(Request $request, callable $handler) : Response
    {
        $params = $request->all();
        if (isset($params['s'])) {
            unset($params['s']);
        }
        foreach ($params as $key => $val) {
            in_array($key, $this->sensitiveParams) && $params[$key] = "***********";
        }
        $method = strtolower($request->method());
        if ($request->isAjax()) {
            if (in_array($method, ['post', 'put', 'delete'])) {
                $data = [
                    'admin_id'    => session('admin.id'),
                    'url'         => $request->url(),
                    'method'      => $method,
                    'ip'          => $request->getRealIp(),
                    'content'     => json_encode($params, JSON_UNESCAPED_UNICODE),
                    'useragent'   => $request->header('user-agent'),
                    'create_time' => time(),
                ];
                SystemLogService::instance()->save($data);
            }
        }
        return $handler($request);
    }
}