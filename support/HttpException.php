<?php

namespace support;
use Exception;
use Throwable;
use Webman\Http\Request;
use Webman\Http\Response;
use Webman\Exception\ExceptionHandler;
/**
 * Class BusinessException
 * @package support\exception
 */
class HttpException extends ExceptionHandler
{
    
    

    public function report(Throwable $exception)
    {
        $data = json_decode($exception->getMessage(),true);
        if(!isset($data['type'])){
            parent::report($exception);
        }
        
    }
    
    public function render(Request $request, Throwable $exception): Response
    {
       $data = json_decode($exception->getMessage(),true);
       if(isset($data['type'])){
           if($data['type'] == 'html'){
               return view($data['view'],$data);
           }else{
               return json($data);
           }
       }
       
       return parent::render($request, $exception);
    }
    
}