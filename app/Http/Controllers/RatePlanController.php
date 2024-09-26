<?php

namespace App\Http\Controllers;

use Validator;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use App\Models\RatePlans;
use App\Models\Rooms;
use App\Http\Controllers\Response\Errors;
use App\Http\Controllers\Response\Success;
use Illuminate\Support\Str;

use function PHPUnit\Framework\isEmpty;

class RatePlanController extends Controller
{
    public function getListRatePlan(Request $request)
    {
        $slug = $request->query('slug');

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

        $data = RatePlans::skip($skip)->limit($limit);
        $all = new RatePlans();

        if (!empty($slug)) {
            $data->where('slug', 'ILIKE', "%$slug%");
            $all = $all->where('slug', 'ILIKE', "%$slug%");
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

    public function createRatePlan(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'room_id' => 'required',
                'name' => 'required',
                'detail' => 'required',
                'price' => 'required|numeric',
            ]
        );

        if ($validator->fails()) {
            $code = 20101;
            $response = Errors::code($code, $validator->errors()->first());
            return $response;
        }

        try {
            $slug = Str::slug($request->input('name'), "-");

            //check current slug
            $checkSlug = RatePlans::where('slug', $slug)->get()->toArray();
            if(count($checkSlug)> 0 ){
                $code = 20103;
                $response = Errors::code($code);
                return $response;
            }

            $checkRoom = Rooms::where('id', $request->input('room_id'))->get()->toArray();
            if(count($checkRoom)< 1 ){
                $code = 20100;
                $response = Errors::code($code);
                return $response;
            }

            $ratePlan = new RatePlans();
            $ratePlan->id = Str::uuid()->toString();
            $ratePlan->room_id = $request->input('room_id');
            $ratePlan->name = $request->input('name');
            $ratePlan->slug = $slug;
            $ratePlan->detail = $request->input('detail');
            $ratePlan->price = $request->input('price');
            $ratePlan->save();

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

    public function updateRatePlan(Request $request, $idRatePlan)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'room_id' => 'required',
                'name' => 'required',
                'detail' => 'required',
                'price' => 'required|numeric',
            ]
        );
        if ($validator->fails()) {
            $code = 20101;
            $response = Errors::code($code, $validator->errors()->first());
            return $response;
        }

        $dataValidation = [
            'id_rateplan' => $idRatePlan,
        ];
        $validator = Validator::make(
            $dataValidation,
            [
                'id_rateplan' => 'required|uuid',
            ]
        );
        if ($validator->fails()) {
            $code = 20101;
            $response = Errors::code($code, $validator->errors()->first());
            return $response;
        }

        try {
            $slug = Str::slug($request->input('name'), "-");

            //check current slug
            $checkSlug = RatePlans::where('slug', $slug)->where('id', '!=', $idRatePlan)->get()->toArray();
            if(count($checkSlug)> 0 ){
                $code = 20103;
                $response = Errors::code($code);
                return $response;
            }

            $checkRoom = Rooms::where('id', $request->input('room_id'))->get()->toArray();
            if(count($checkRoom)< 1 ){
                $code = 20100;
                $response = Errors::code($code);
                return $response;
            }

            $update = $request->all();
            $update['slug'] = $slug;
            RatePlans::findOrFail($idRatePlan)->update($update);

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

    public function deleteRatePlan($idRatePlan)
    {
        $dataValidation = [
            'id_rateplan' => $idRatePlan,
        ];
        $validator = Validator::make(
            $dataValidation,
            [
                'id_rateplan' => 'required|uuid',
            ]
        );
        if ($validator->fails()) {
            $code = 20101;
            $response = Errors::code($code, $validator->errors()->first());
            return $response;
        }

        //check current slug
        $check = RatePlans::where('id', $idRatePlan)->get()->toArray();
        if(count($check) < 1 ){
            $code = 20100;
            $response = Errors::code($code);
            return $response;
        }

        try {
            $delete = RatePlans::where('id', $idRatePlan)->delete();
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
