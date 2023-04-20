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

use app\admin\model\SystemUploadfile;
use app\admin\service\MenuService;
use EasyAdmin\upload\Uploadfile;
use think\db\Query;
use think\facade\Cache;
use support\Request;
class Ajax extends Base
{

    /**
     * 初始化后台接口地址
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function initAdmin()
    {
        $cacheData = Cache::get('initAdmin_' . session('admin.id'));
        if (!empty($cacheData)) {
            return json($cacheData);
        }
        $menuService = new MenuService();
        $data = [
            'logoInfo' => [
                'title' => sysconfig('site', 'logo_title'),
                'image' => sysconfig('site', 'logo_image'),
                'href'  => '/admin/index/index',
            ],
            'homeInfo' => $menuService->getHomeInfo(),
            'menuInfo' => $menuService->getMenuTree(session('admin.id')),
        ];
        Cache::tag('initAdmin')->set('initAdmin_' . session('admin.id'), $data);
        return json($data);
    }

    /**
     * 清理缓存接口
     */
    public function clearCache()
    {
        Cache::clear();
        $this->success('清理缓存成功');
    }
    
    
    
    public function upload(Request $request)
    {
        $file = $request->file('file');
        if ($file && $file->isValid()) {
            $filename = md5(md5($file).time()) . '.'.$file->getUploadExtension();
            $path = public_path().'/files/'.$filename;
            $file->move($path);
            $this->success('上传成功',['url' => "http://".$request->host().'/files/'.$filename]);
        }
       $this->error('上传失败');
    }
    
    
    
    /**
     * 上传文件
     */
    public function upload2()
    {
        $this->checkPostRequest();
        $file = request()->file('file');
        if ($file && $file->isValid()) {
            $data = [
                'upload_type' =>$file->getUploadExtension(),
                'file'        =>request()->file('file'),
            ];
            $uploadConfig = sysconfig('upload');
            empty($data['upload_type']) && $data['upload_type'] = $uploadConfig['upload_type'];
            $rule = [
                'upload_type|指定上传类型有误' => "in:{$uploadConfig['upload_allow_type']}",
                'file|文件'              => "require|file|fileExt:{$uploadConfig['upload_allow_ext']}|fileSize:{$uploadConfig['upload_allow_size']}",
            ];
            $this->validate($data, $rule);
            try {
                $upload = Uploadfile::instance()
                    ->setUploadType($data['upload_type'])
                    ->setUploadConfig($uploadConfig)
                    ->setFile($data['file'])
                    ->save();
            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }
            if ($upload['save'] == true) {
                $this->success($upload['msg'], ['url' => $upload['url']]);
            } else {
                $this->error($upload['msg']);
            }
        }
        
    }
    
    public function uploadEditor(Request $request)
    {
         $file = $request->file('upload');
        if ($file && $file->isValid()) {
            $filename = md5(md5($file).time()) . '.'.$file->getUploadExtension();
            $path = public_path().'/files/'.$filename;
            $file->move($path);
            return json([
                'error'    => [
                    'message' => '上传成功',
                    'number'  => 201,
                ],
                'fileName' => '',
                'uploaded' => 1,
                'url'      => "http://".$request->host().'/files/'.$filename,
            ]);
        }
       $this->error('上传失败');
    }
    
    
    /**
     * 上传图片至编辑器
     * @return \think\response\Json
     */
    public function uploadEditor2()
    {
        $data = [
            'upload_type' =>request()->post('upload_type'),
            'file'        =>request()->file('upload'),
        ];
        $uploadConfig = sysconfig('upload');
        empty($data['upload_type']) && $data['upload_type'] = $uploadConfig['upload_type'];
        $rule = [
            'upload_type|指定上传类型有误' => "in:{$uploadConfig['upload_allow_type']}",
            'file|文件'              => "require|file|fileExt:{$uploadConfig['upload_allow_ext']}|fileSize:{$uploadConfig['upload_allow_size']}",
        ];
        $this->validate($data, $rule);
        try {
            $upload = Uploadfile::instance()
                ->setUploadType($data['upload_type'])
                ->setUploadConfig($uploadConfig)
                ->setFile($data['file'])
                ->save();
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        if ($upload['save'] == true) {
            return json([
                'error'    => [
                    'message' => '上传成功',
                    'number'  => 201,
                ],
                'fileName' => '',
                'uploaded' => 1,
                'url'      => $upload['url'],
            ]);
        } else {
            $this->error($upload['msg']);
        }
    }

    /**
     * 获取上传文件列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getUploadFiles()
    {
        $get =request()->get();
        $page = isset($get['page']) && !empty($get['page']) ? $get['page'] : 1;
        $limit = isset($get['limit']) && !empty($get['limit']) ? $get['limit'] : 10;
        $title = isset($get['title']) && !empty($get['title']) ? $get['title'] : null;
        $this->model = new SystemUploadfile();
        $count = $this->model
            ->where(function (Query $query) use ($title) {
                !empty($title) && $query->where('original_name', 'like', "%{$title}%");
            })
            ->count();
        $list = $this->model
            ->where(function (Query $query) use ($title) {
                !empty($title) && $query->where('original_name', 'like', "%{$title}%");
            })
            ->page($page, $limit)
            ->order($this->sort)
            ->select();
        $data = [
            'code'  => 0,
            'msg'   => '',
            'count' => $count,
            'data'  => $list,
        ];
        return json($data);
    }

}