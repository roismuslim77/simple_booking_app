<?php

namespace App\Http\Controllers\Response;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class Errors extends Controller
{
  public static function code($code, $detail = "")
  {
    $path = storage_path() . "/response/ErrorCode.json";
    $json = json_decode(file_get_contents($path), true);
    $json = collect($json)->values();
    $json = $json->keyBy('err_code');
    $res = $json[$code];

    if ($detail != "") {
      $res['detail'] = $detail;
    }

    return response()->json([
      'response' => $res
    ], 200);
  }
}
