<?php
/**
 * This file is part of webman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link      http://www.workerman.net/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */

use Webman\Route;

Route::fallback(function(){
    return view(config('admin.dispatch_error_tmpl'), ['code'=>0,'msg' => '页面不存在','url'=>'http://www.baidu.com','data'=>'','wait'=>3])->withStatus(404);
});



