<?php
namespace App\Http\Controllers\Response;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class Success extends Controller
{
    public static function code($code)
    {
        $path = storage_path() . "/response/SuccessCode.json";
        $json = json_decode(file_get_contents($path), true);
        $json = collect($json)->values();
        $json = $json->keyBy('success_code');
        $json = $json[$code];
        return $json['success_msg'];
    }
}
