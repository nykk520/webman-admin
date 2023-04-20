<?php
/**
 * Here is your custom functions.
 */


use think\facade\Cache;
use app\admin\service\AuthService;
use think\facade\Db;
/**
 * Here is your custom functions.
 */
if (!function_exists('password')) {

    /**
     * 密码加密算法
     * @param $value 需要加密的值
     * @param $type  加密类型，默认为md5 （md5, hash）
     * @return mixed
     */
    function password($value)
    {
        $value = sha1('blog_') . md5($value) . md5('_encrypt') . sha1($value);
        return sha1($value);
    }

}

if (!function_exists('array_format_key')) {

    /**
     * 二位数组重新组合数据
     * @param $array
     * @param $key
     * @return array
     */
    function array_format_key($array, $key)
    {
        $newArray = [];
        foreach ($array as $vo) {
            $newArray[$vo[$key]] = $vo;
        }
        return $newArray;
    }

}
if (!function_exists('sysconfig')) {

    /**
     * 获取系统配置信息
     * @param $group
     * @param null $name
     * @return array|mixed
     */
    function sysconfig($group, $name = null)
    {
        $where = ['group' => $group];
        $value = empty($name) ? Cache::get("sysconfig_{$group}") : Cache::get("sysconfig_{$group}_{$name}");
        if (empty($value)) {
            if (!empty($name)) {
                $where['name'] = $name;
                $value = \app\admin\model\SystemConfig::where($where)->value('value');
                Cache::tag('sysconfig')->set("sysconfig_{$group}_{$name}", $value, 3600);
            } else {
                $value = \app\admin\model\SystemConfig::where($where)->column('value', 'name');
                Cache::tag('sysconfig')->set("sysconfig_{$group}", $value, 3600);
            }
        }
        return $value;
    }
}
if (!function_exists('auth')) {

    /**
     * auth权限验证
     * @param $node
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    function auth($node = null)
    {
        $authService = new AuthService(session('admin.id'));
        $check = $authService->checkNode($node);
        return $check;
    }

}


function parse_name(string $name, int $type = 0, bool $ucfirst = true): string
{
    if ($type) {
        $name = preg_replace_callback('/_([a-zA-Z])/', function ($match) {
            return strtoupper($match[1]);
        }, $name);

        return $ucfirst ? ucfirst($name) : lcfirst($name);
    }

    return strtolower(trim(preg_replace('/[A-Z]/', '_\\0', $name), '_'));
}

/**
 * Url生成
 * @param string      $url    路由地址
 * @param array       $vars   变量
 * @param bool|string $suffix 生成的URL后缀
 * @param bool|string $domain 域名
 * @return UrlBuild
 */
function url(string $url = '', array $vars = [], $suffix = true, $domain = false)
{
    // return Route::buildUrl($url, $vars)->suffix($suffix)->domain($domain);
    return '/'.request()->app . '/' . $url; 
}

//api 回调url
function api_url($url)
{
    return  "http://" . request()->host() . '/'. $url;
}

/**
 * 获取控制器名称转小写，如index
 * 
 */
function get_controller()
{
    $controller = request()->controller;
    $controller = strtolower(str_replace('\\', '/', $controller));
    $len = strpos($controller,'controller');
    $controller = substr($controller,$len+11); //controller 是10个字符
    return $controller;
}

function sub_controller($controller)
{
    $controller = strtolower(str_replace('\\', '/', $controller));
    $len = strpos($controller,'controller');
    $controller = substr($controller,$len+11); //controller 是10个字符
    return $controller;
}

function input($name='')
{
   return request()->input($name);
}


function makeorid(){
	$osn = intval(date('Y')) . strtoupper(dechex(date('m'))) . date('d') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('%02d', rand(0, 99));
	return $osn;
}


//后台配置 单选框内容改成数组
function get_item($str)
{
    $array = explode("\n", str_replace("\r\n", "\n", trim($str,"\r\n")));
    $items = [];
    foreach ($array as $val) {
        // code..
        list($k, $v) = explode('|', $val);
        $items[$k] = $v;
    }
   return $items;
}


function pwdlevel($str)
{
    $level = 0;
    if(preg_match("/[0-9]+/",$str))
    {
        $level = 1;
    }
    if(preg_match("/[a-z]+/",$str)){
        $level = $level + 1;
    }
    if(preg_match("/[_|\-|+|=|*|!|@|#|$|%|^|&|(|)]+/",$str)){
        $level = $level + 1;
    }
    return $level;
}
//获取配置
function get_config($name = null)
{
    return  Db::name('web_config')->where('name',$name)->cache('config_'.$name)->value('value');
}
// 金额处理
function feeHandle($fee){
    if (is_numeric($fee)) {
        $int = preg_replace('/(?<=[0-9])(?=(?:[0-9]{3})+(?![0-9]))/', ',',$fee);
        return $int;
    }
}

//function think-cache
if (!function_exists('cache')) {
    /**
     * 缓存管理
     * @param string $name    缓存名称
     * @param mixed  $value   缓存值
     * @param mixed  $options 缓存参数
     * @param string $tag     缓存标签
     * @return mixed
     */
    function cache(string $name = null, $value = '', $options = null, $tag = null)
    {
        if (is_null($name)) {
            return app('cache');
        }

        if ('' === $value) {
            // 获取缓存
            return 0 === strpos($name, '?') ? Cache::has(substr($name, 1)) : Cache::get($name);
        } elseif (is_null($value)) {
            // 删除缓存
            return Cache::delete($name);
        }

        // 缓存数据
        if (is_array($options)) {
            $expire = $options['expire'] ?? null; //修复查询缓存无法设置过期时间
        } else {
            $expire = $options;
        }

        if (is_null($tag)) {
            return Cache::set($name, $value, $expire);
        } else {
            return Cache::tag($tag)->set($name, $value, $expire);
        }
    }
}

