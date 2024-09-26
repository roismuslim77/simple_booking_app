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

class BookingController extends Controller
{
    public function getListBooking(Request $request)
    {
        $reservation_date_from = $request->query('reservation_date_from');
        $reservation_date_to = $request->query('reservation_date_to');
        $check_in = $request->query('check_in');
        $check_out = $request->query('check_out');
        $name = $request->query('name');
        $country = $request->query('country');
        $payment_status = $request->query('payment_status');
        $reservation_number = $request->query('reservation_number');

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

        $data = Bookings::skip($skip)->limit($limit);
        $all = new Bookings();

        if (!empty($reservation_number)) {
            $data->where('reservation_number', 'ILIKE', "%$reservation_number%");
            $all = $all->where('reservation_number', 'ILIKE', "%$reservation_number%");
        }
        if (!empty($name)) {
            $data->where('name', 'ILIKE', "%$name%");
            $all = $all->where('name', 'ILIKE', "%$name%");
        }
        if (!empty($country)) {
            $data->where('country', 'ILIKE', "%$country%");
            $all = $all->where('country', 'ILIKE', "%$country%");
        }
        if (!empty($payment_status)) {
            $data->where('payment_status', $payment_status);
            $all = $all->where('payment_status', $payment_status);
        }
        if (!empty($check_in)) {
            $data->where('check_in', '>=', $check_in);
            $all = $all->where('check_in','>=', $check_in);
        }
        if (!empty($check_out)) {
            $data->where('check_out', '<=', $check_out);
            $all = $all->where('check_out' ,'<=', $check_out);
        }


        if (!empty($reservation_date_from)) {
            $data->where('reservation_date', '>=', $reservation_date_from);
            $all = $all->where('reservation_date','>=', $reservation_date_from);
        }
        if (!empty($reservation_date_to)) {
            $data->where('reservation_date', '<=', $reservation_date_to);
            $all = $all->where('reservation_date' ,'<=', $reservation_date_to);
        }

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

    public function createBooking(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'room_id' => 'required',
                'rateplan_id' => 'required',
                'calendar_id' => 'required',
                'reservation_date' => 'required|date',
                'check_in' => 'required|date_format:Y-m-d H:i|after_or_equal:' . date(DATE_ATOM),
                'check_out' => 'required|date_format:Y-m-d H:i|after_or_equal:' . date(DATE_ATOM),
                'name' => 'required',
                'email' => 'required|email',
                'phone_number' => 'required',
                'country' => 'required',
                'total' => 'required|numeric',
                'payment_status' => 'required|in:pending,paid,expired',
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
            $checkCalendar = Calendars::where('id', $request->input('calendar_id'))->get()->toArray();
            if(count($checkCalendar)< 1 ){
                $code = 20100;
                $response = Errors::code($code);
                return $response;
            }

            $idBooking = Str::uuid()->toString();
            $reservation_number = date('Ymd')."-".random_int(100000, 999999)."-".$idBooking;

            $Booking = new Bookings();
            $Booking->id = $idBooking;
            $Booking->room_id = $request->input('room_id');
            $Booking->rateplan_id = $request->input('rateplan_id');
            $Booking->calendar_id = $request->input('calendar_id');
            $Booking->reservation_number = $reservation_number;
            $Booking->reservation_date = $request->input('reservation_date');
            $Booking->check_in = $request->input('check_in');
            $Booking->check_out = $request->input('check_out');
            $Booking->name = $request->input('name');
            $Booking->email = $request->input('email');
            $Booking->phone_number = $request->input('phone_number');
            $Booking->country = $request->input('country');
            $Booking->total = $request->input('total');
            $Booking->payment_status = $request->input('payment_status');
            $Booking->save();

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

    public function updateBooking(Request $request, $idBooking)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'room_id' => 'required',
                'rateplan_id' => 'required',
                'calendar_id' => 'required',
                'check_in' => 'required|date_format:Y-m-d H:i:s|after_or_equal:' . date(DATE_ATOM),
                'check_out' => 'required|date_format:Y-m-d H:i:s|after_or_equal:' . date(DATE_ATOM),
                'name' => 'required',
                'email' => 'required|email',
                'phone_number' => 'required',
                'country' => 'required',
                'total' => 'required|numeric',
                'payment_status' => 'required',
            ]
        );
        if ($validator->fails()) {
            $code = 20101;
            $response = Errors::code($code, $validator->errors()->first());
            return $response;
        }

        $dataValidation = [
            'id_booking' => $idBooking,
        ];
        $validator = Validator::make(
            $dataValidation,
            [
                'id_booking' => 'required|uuid',
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
            $checkCalendar = Calendars::where('id', $request->input('calendar_id'))->get()->toArray();
            if(count($checkCalendar)< 1 ){
                $code = 20100;
                $response = Errors::code($code);
                return $response;
            }

            // $update = $request->all();
            $update['rateplan_id'] = $request->input('rateplan_id');
            $update['calendar_id'] = $request->input('calendar_id');
            $update['reservation_date'] = $request->input('reservation_date');
            $update['check_in'] = $request->input('check_in');
            $update['check_out'] = $request->input('check_out');
            $update['name'] = $request->input('name');
            $update['email'] = $request->input('email');
            $update['phone_number'] = $request->input('phone_number');
            $update['country'] = $request->input('country');
            $update['total'] = $request->input('total');
            $update['payment_status'] = $request->input('payment_status');
            Bookings::findOrFail($idBooking)->update($update);

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

    public function deleteBooking($idBooking)
    {
        $dataValidation = [
            'id_booking' => $idBooking,
        ];
        $validator = Validator::make(
            $dataValidation,
            [
                'id_booking' => 'required|uuid',
            ]
        );
        if ($validator->fails()) {
            $code = 20101;
            $response = Errors::code($code, $validator->errors()->first());
            return $response;
        }

        //check current id
        $check = Bookings::where('id', $idBooking)->get()->toArray();
        if(count($check) < 1 ){
            $code = 20100;
            $response = Errors::code($code);
            return $response;
        }

        try {
            $delete = Bookings::where('id', $idBooking)->delete();
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

    public function requestBooking(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'room_id' => 'required',
                'rateplan_id' => 'required',
                'calendar_id' => 'required',
                'reservation_date' => 'required|date',
                'check_in' => 'required|date_format:Y-m-d H:i',
                'check_out' => 'required|date_format:Y-m-d H:i|after_or_equal:check_in',
                'name' => 'required',
                'email' => 'required|email',
                'phone_number' => 'required',
                'country' => 'required',
                'total' => 'required|numeric',
                'payment_status' => 'required|in:pending,paid,expired',
            ]
        );

        if ($validator->fails()) {
            $code = 20101;
            $response = Errors::code($code, $validator->errors()->first());
            return $response;
        }

        \DB::beginTransaction();
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
            $checkCalendar = Calendars::where('id', $request->input('calendar_id'))->get()->toArray();
            if(count($checkCalendar)< 1 ){
                $code = 20100;
                $response = Errors::code($code);
                return $response;
            }

            $idBooking = Str::uuid()->toString();
            $reservation_number = date('Ymd')."-".random_int(100000, 999999)."-".$idBooking;

            $datediff = strtotime($request->input('check_out')) - strtotime($request->input('check_in'));
            $reserveDay = round($datediff / (60 * 60 * 24));

            //validate availability calendar
            if($checkCalendar[0]['availability'] < $reserveDay) {
                $code = 20104;
                $response = Errors::code($code);
                return $response;
            }

            $Booking = new Bookings();
            $Booking->id = $idBooking;
            $Booking->room_id = $request->input('room_id');
            $Booking->rateplan_id = $request->input('rateplan_id');
            $Booking->calendar_id = $request->input('calendar_id');
            $Booking->reservation_number = $reservation_number;
            $Booking->reservation_date = $request->input('reservation_date');
            $Booking->check_in = $request->input('check_in');
            $Booking->check_out = $request->input('check_out');
            $Booking->name = $request->input('name');
            $Booking->email = $request->input('email');
            $Booking->phone_number = $request->input('phone_number');
            $Booking->country = $request->input('country');
            $Booking->total = $request->input('total');
            $Booking->payment_status = $request->input('payment_status');
            $Booking->save();


            $update['availability'] = $checkCalendar[0]['availability']-$reserveDay;
            Calendars::findOrFail($request->input('calendar_id'))->update($update);

            \DB::commit();
        }catch (\Exception $ex) {
            \DB::rollback();
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

    public function cancelBooking($idBooking)
    {
        $dataValidation = [
            'id_booking' => $idBooking,
        ];
        $validator = Validator::make(
            $dataValidation,
            [
                'id_booking' => 'required|uuid',
            ]
        );
        if ($validator->fails()) {
            $code = 20101;
            $response = Errors::code($code, $validator->errors()->first());
            return $response;
        }

        //check current id
        $check = Bookings::where('id', $idBooking)->get()->toArray();
        if(count($check) < 1 ){
            $code = 20100;
            $response = Errors::code($code);
            return $response;
        }
        $checkCalendar = Calendars::where('id', $check[0]['calendar_id'])->get()->toArray();
        if(count($checkCalendar)< 1 ){
            $code = 20100;
            $response = Errors::code($code);
            return $response;
        }

        \DB::beginTransaction();
        try {
            $datediff = strtotime($check[0]['check_out']) - strtotime($check[0]['check_in']);
            $reserveDay = round($datediff / (60 * 60 * 24));

            $delete = Bookings::where('id', $idBooking)->delete();

            $update['availability'] = $checkCalendar[0]['availability']+$reserveDay;
            Calendars::findOrFail($check[0]['calendar_id'])->update($update);


            \DB::commit();
        }catch (\Exception $ex) {
            \DB::rollback();
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
