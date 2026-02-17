<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;

class LogHelper
{
    /**
     * Log Message For Error or Exception
     * @author shreyasm@neosao    
     */
    public static function logError($message, $exception, $function, $file, $line, $path, $id = '')
    {
        $data = [
            'user_id' => auth()->id(),
            'function' => $function,
            'file' => $file,
            'line' => $line,
            'path' =>  $path,
            'exception' => $exception,
        ];

        if ($id !== '') {
            $data['id'] = $id;
        }
        Log::error($message, $data);
    }

    /**
     * Log Message For Success
     * @author shreyasm@neosao    
     */
    public static function logSuccess($message, $function, $file, $line, $path, $id = '')
    {
        $data = [
            'user_id' => auth()->id(),
            'function' => $function,
            'file' => $file,
            'line' => $line,
            'path' =>  $path,
        ];
        if ($id !== '') {
            $data['id'] = $id;
        }
        Log::info($message, $data);
    }
}
