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

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Html;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ExcelService
{

    public static function exportData(
        $list = [],
        $header = [],
        $filename = '',
        $suffix = 'xlsx',
        $path = '',
        $image = []
    ){
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        //写入头部
        $hk = 1;
        foreach ($header as $k => $v) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($hk) . '1', $v[0]);
            $hk += 1;
        }
        
        //写入内容
        $column = 2;
        $size = ceil(count($list) / 500);
        for ($i = 0; $i < $size; $i++) {
            $buffer = array_slice($list, $i * 500, 500);

            foreach ($buffer as $k => $row) {
                $span = 1;

                foreach ($header as $key => $value) {
                    // 解析字段
                    $realData = self::formatting($header[$key], trim(self::formattingField($row, $value[1])), $row);
                    // 写入excel
                    $rowR = Coordinate::stringFromColumnIndex($span);
                    $sheet->getColumnDimension($rowR)->setWidth(20);
                    if (in_array($span, $image) || in_array($rowR, $image)) { // 如果这一列应该是图片
                        if (file_exists($realData)) { // 本地文件
                            $drawing = new Drawing();
                            $drawing->setName('image');
                            $drawing->setDescription('image');
                            try {
                                $drawing->setPath($realData);
                            } catch (\Exception $e) {
                                echo $e->getMessage();
                                echo '<br>可能是图片丢失了或者无权限';
                                break;
                            }

                            $drawing->setWidth(80);
                            $drawing->setHeight(80);
                            $drawing->setCoordinates($rowR . $column);//A1
                            $drawing->setOffsetX(12);
                            $drawing->setOffsetY(12);
                            $drawing->setWorksheet($spreadsheet->getActiveSheet());
                        } else { // 可能是 网络文件
                            $img = self::curlGet($realData);
                            $file_info = pathinfo($realData);
                            $extension = $file_info['extension'];// 文件后缀
                            $dir = '.' . DIRECTORY_SEPARATOR . 'execlImg' . DIRECTORY_SEPARATOR . \date('Y-m-d') . DIRECTORY_SEPARATOR;// 文件夹名
                            $basename = time() . mt_rand(1000, 9999) . '.' . $extension;// 文件名
                            is_dir($dir) or mkdir($dir, 0777, true); //进行检测文件夹是否存在
                            file_put_contents($dir . $basename, $img);
                            $drawing = new Drawing();
                            $drawing->setName('image');
                            $drawing->setDescription('image');
                            try {
                                $drawing->setPath($dir . $basename);
                            } catch (\Exception $e) {
                                echo $e->getMessage();
                                echo '<br>可能是图片丢失了或者无权限';
                                break;
                            }

                            $drawing->setWidth(80);
                            $drawing->setHeight(80);
                            $drawing->setCoordinates($rowR . $column);//A1
                            $drawing->setOffsetX(12);
                            $drawing->setOffsetY(12);
                            $drawing->setWorksheet($spreadsheet->getActiveSheet());
                        }
                    } else {
                        // $sheet->setCellValue($rowR . $column, $realData);
                        // 写入excel
                        $sheet->setCellValueExplicit(Coordinate::stringFromColumnIndex($span) . $column, $realData, DataType::TYPE_STRING);
                    }


                    $span++;
                }

                $column++;
                unset($buffer[$k]);
            }
        }
        
        
        
         // 直接输出下载
        switch ($suffix) {
            case 'xlsx' :
                $writer = new Xlsx($spreadsheet);
                break;
            case 'xls' :
                $writer = new Xls($spreadsheet);
                break;
            case 'csv' :
                $writer = new Csv($spreadsheet);
                break;
            case 'html' :
                $writer = new Html($spreadsheet);
                break;
        }
        
        if (!empty($path)) {
            $writer->save($path);
            //下载文件
            return response()->download($path, $filename.'.'.$suffix);
        }else{
            $path =  runtime_path().'/exportExcels/';
            @mkdir($path);
            $file_path = $path . $filename.'.'.$suffix;
            $writer->save($file_path);
            //下载文件
            return response()->download($file_path, $filename.'.'.$suffix);
        }
        return true;
        
       
        
    }
    /**
     * 格式化内容
     *
     * @param array $array 头部规则
     * @return false|mixed|null|string 内容值
     */
    protected static function formatting(array $array, $value, $row)
    {
        !isset($array[2]) && $array[2] = 'text';

        switch ($array[2]) {
            // 文本
            case 'text' :
                return $value;
                break;
            // 日期
            case  'date' :
                return !empty($value) ? date($array[3], $value) : null;
                break;
            // 选择框
            case  'selectd' :
                return $array[3][$value] ?? null;
                break;
            // 匿名函数
            case  'function' :
                return isset($array[3]) ? call_user_func($array[3], $row) : null;
                break;
            // 默认
            default :

                break;
        }

        return null;
    }
    
    /**
     * 解析字段
     *
     * @param $row
     * @param $field
     * @return mixed
     */
    protected static function formattingField($row, $field)
    {
        $newField = explode('.', $field);
        if (count($newField) == 1) {
            if (isset($row[$field])) {
                return $row[$field];
            } else {
                return false;
            }
        }

        foreach ($newField as $item) {
            if (isset($row[$item])) {
                $row = $row[$item];
            } else {
                break;
            }
        }

        return is_array($row) ? false : $row;
    }

}