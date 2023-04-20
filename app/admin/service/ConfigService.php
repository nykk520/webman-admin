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

namespace app\admin\service;

use think\facade\Cache;

class ConfigService
{

    public static function getVersion()
    {
        $version = Cache::get('version');
        if (empty($version)) {
            $version = sysconfig('site', 'site_version');
            Cache::set('site_version', $version);
            Cache::set('version', $version, 3600);
        }
        return $version;
    }

}