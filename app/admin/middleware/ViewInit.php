<?php
namespace app\admin\middleware;

use Webman\MiddlewareInterface;
use Webman\Http\Response;
use Webman\Http\Request;
use app\admin\constants\AdminConstant;
use app\admin\service\ConfigService;
use support\View;
class ViewInit implements MiddlewareInterface
{
    public function process(Request $request, callable $handler) : Response
    {
        list($thisModule, $thisController, $thisAction) = [$request->app,sub_controller($request->controller), $request->action];
        list($thisControllerArr, $jsPath) = [explode('.', $thisController), null];
        foreach ($thisControllerArr as $vo) {
            empty($jsPath) ? $jsPath = parse_name($vo) : $jsPath .= '/' . parse_name($vo);
        }
        $autoloadJs = (public_path() . "static/{$thisModule}/js/{$jsPath}.js") ? true : false;
        $thisControllerJsPath = "{$thisModule}/js/{$jsPath}.js";
        // $adminModuleName = config('app.admin_alias_name');
        $adminModuleName = 'admin';
        $isSuperAdmin = session('admin.id') == AdminConstant::SUPER_ADMIN_ID ? true : false;
        $data = [
            'adminModuleName'      => $adminModuleName,
            'thisController'       => parse_name($thisController),
            'thisAction'           => $thisAction,
            'thisRequest'          => parse_name("{$thisModule}/{$thisController}/{$thisAction}"),
            'thisControllerJsPath' => "{$thisControllerJsPath}",
            'autoloadJs'           => $autoloadJs,
            'isSuperAdmin'         => $isSuperAdmin,
            'version'              => config('admin.debug') ? time() : ConfigService::getVersion(),
        ];
        View::assign($data);
        return $handler($request);
    }
}