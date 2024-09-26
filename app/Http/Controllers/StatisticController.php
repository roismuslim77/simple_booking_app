<?php

namespace App\Http\Controllers;

use Validator;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use App\Models\RatePlans;
use App\Models\Rooms;
use App\Models\Calendars;
use App\Models\Bookings;
use App\Http\Controllers\Response\Errors;
use App\Http\Controllers\Response\Success;
use Illuminate\Support\Str;

use function PHPUnit\Framework\isEmpty;

class StatisticController extends Controller
{
    public function getRevenueCount(Request $request)
    {
        $date = $request->query('date');

        $data = Bookings::selectRaw('sum(total) as total')->where('payment_status', 'paid');

        if (!empty($date)) {
            $data->whereRaw("date(created_at) = '". $date."'");
        }

        $res = $data->get();
        $code = 20000;
        $response = Success::code($code);
        return response()->json([
            'response' => [
                'data' => $res[0],
                'success_code' => strval($code),
                'success_msg' => $response,
            ]
        ], 200);
    }

}
