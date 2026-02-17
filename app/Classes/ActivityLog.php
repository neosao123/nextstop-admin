<?php 
namespace App\Classes;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
class ActivityLog
{
	public function activity_log($data)
    {
        $file = 'log-' . date("d-m-Y") . '.txt';
        $destinationPath = 'log_file/' . $file; // Destination path
        Storage::disk('public')->append($destinationPath, $data . PHP_EOL);
    }
}