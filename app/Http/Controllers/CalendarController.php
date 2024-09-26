<?php

namespace App\Http\Controllers;

use Validator;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use App\Models\RatePlans;
use App\Models\Rooms;
use App\Models\Calendars;
use App\Http\Controllers\Response\Errors;
use App\Http\Controllers\Response\Success;
use Illuminate\Support\Str;

use function PHPUnit\Framework\isEmpty;

class CalendarController extends Controller
{
    public function getListCalendar(Request $request)
    {
        $skip = 0;
        $limit = 0;

        $page = $request->query('page');
        $size = $request->query('size');

        if (isEmpty($page) && $page < 1) {
            $page = 1;
        }

        if (isEmpty($size) && $size < 1) {
            $size = 10;
        }

        $skip = $size * ($page - 1);
        $limit = $size;

        $data = Calendars::skip($skip)->limit($limit);
        $all = new Calendars();

        $res = $data->orderBy('created_at', 'desc')->get()->toArray();
        $all = $all->get()->toArray();

        if ($res) {
            $code = 20000;
            $response = Success::code($code);
            return response()->json([
              'response' => [
                'data' => $res,
                'page' => intval($page),
                'size' => intval($size),
                'total_page' => ceil(count($all) / $size),
                'count' => count($all),
                'success_code' => strval($code),
                'success_msg' => $response,
              ]
            ], 200);
        } else {
            $code = 20100;
            $response = Errors::code($code);
            return $response;
        }
    }

    public function createCalendar(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'room_id' => 'required',
                'rateplan_id' => 'required',
                'date' => 'required|date',
                'availability' => 'nullable|numeric',
                'price' => 'numeric',
            ]
        );

        if ($validator->fails()) {
            $code = 20101;
            $response = Errors::code($code, $validator->errors()->first());
            return $response;
        }

        try {

            $checkRoom = Rooms::where('id', $request->input('room_id'))->get()->toArray();
            if(count($checkRoom)< 1 ){
                $code = 20100;
                $response = Errors::code($code);
                return $response;
            }
            $checkRateplan = RatePlans::where('id', $request->input('rateplan_id'))->get()->toArray();
            if(count($checkRateplan)< 1 ){
                $code = 20100;
                $response = Errors::code($code);
                return $response;
            }

            $calendar = new Calendars();
            $calendar->id = Str::uuid()->toString();
            $calendar->room_id = $request->input('room_id');
            $calendar->rateplan_id = $request->input('rateplan_id');
            $calendar->date = $request->input('date');
            $calendar->availability = $request->input('availability') ?? $checkRoom[0]['availability'];
            $calendar->price = $request->input('price') ?? $checkRateplan[0]['price'];
            $calendar->save();

        }catch (\Exception $ex) {
            $code = 20102;
            $response = Errors::code($code, $ex->getMessage());
            return $response;
        }

        $code = 20001;
        $response = Success::code($code);
        return response()->json([
            'response' => [
                'success_code' => strval($code),
                'success_msg' => $response,
            ]
        ], 200);
    }

    public function updateCalendar(Request $request, $idCalendar)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'room_id' => 'required',
                'rateplan_id' => 'required',
                'date' => 'required|date',
                'availability' => 'nullable|numeric',
                'price' => 'numeric',
            ]
        );
        if ($validator->fails()) {
            $code = 20101;
            $response = Errors::code($code, $validator->errors()->first());
            return $response;
        }

        $dataValidation = [
            'id_calendar' => $idCalendar,
        ];
        $validator = Validator::make(
            $dataValidation,
            [
                'id_calendar' => 'required|uuid',
            ]
        );
        if ($validator->fails()) {
            $code = 20101;
            $response = Errors::code($code, $validator->errors()->first());
            return $response;
        }

        try {

            $checkRoom = Rooms::where('id', $request->input('room_id'))->get()->toArray();
            if(count($checkRoom)< 1 ){
                $code = 20100;
                $response = Errors::code($code);
                return $response;
            }
            $checkRateplan = RatePlans::where('id', $request->input('rateplan_id'))->get()->toArray();
            if(count($checkRateplan)< 1 ){
                $code = 20100;
                $response = Errors::code($code);
                return $response;
            }

            $update = $request->all();
            $update['availability'] = $request->input('availability') ?? $checkRoom[0]['availability'];
            $update['price'] = $request->input('price') ?? $checkRoom[0]['price'];
            Calendars::findOrFail($idCalendar)->update($update);

        }catch (\Exception $ex) {
            $code = 20102;
            $response = Errors::code($code, $ex->getMessage());
            return $response;
        }

        $code = 20002;
        $response = Success::code($code);
        return response()->json([
            'response' => [
                'success_code' => strval($code),
                'success_msg' => $response,
            ]
        ], 200);
    }

    public function deleteCalendar($idCalendar)
    {
        $dataValidation = [
            'id_calendar' => $idCalendar,
        ];
        $validator = Validator::make(
            $dataValidation,
            [
                'id_calendar' => 'required|uuid',
            ]
        );
        if ($validator->fails()) {
            $code = 20101;
            $response = Errors::code($code, $validator->errors()->first());
            return $response;
        }

        //check current id
        $check = Calendars::where('id', $idCalendar)->get()->toArray();
        if(count($check) < 1 ){
            $code = 20100;
            $response = Errors::code($code);
            return $response;
        }

        try {
            $delete = Calendars::where('id', $idCalendar)->delete();
        }catch (\Exception $ex) {
            $code = 20102;
            $response = Errors::code($code, $ex->getMessage());
            return $response;
        }

        $code = 20002;
        $response = Success::code($code);
        return response()->json([
            'response' => [
                'success_code' => strval($code),
                'success_msg' => $response,
            ]
        ], 200);
    }
}
