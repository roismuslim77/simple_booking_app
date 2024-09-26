<?php

namespace App\Http\Controllers;

use Validator;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use App\Models\Rooms;
use App\Http\Controllers\Response\Errors;
use App\Http\Controllers\Response\Success;
use Illuminate\Support\Str;

use function PHPUnit\Framework\isEmpty;

class RoomController extends Controller
{
    public function getListRoom(Request $request)
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

        $data = Rooms::skip($skip)->limit($limit);
        $all = new Rooms();

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

    public function createRoom(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required',
                'description' => 'required',
                'feature' => 'required',
                'published' => 'required|boolean',
                'availability' => 'required|numeric',
                'image' => 'required',
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
            $check = Rooms::where('slug', $slug)->get()->toArray();
            if(count($check)> 0 ){
                $code = 20103;
                $response = Errors::code($code);
                return $response;
            }

            $room = new Rooms();
            $room->id = Str::uuid()->toString();
            $room->name = $request->input('name');
            $room->slug = $slug;
            $room->description = $request->input('description');
            $room->feature = $request->input('feature');
            $room->published = $request->input('published');
            $room->availability = $request->input('availability');
            $room->image = $request->input('image');
            $room->save();

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

    public function updateRoom(Request $request, $idRoom)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required',
                'description' => 'required',
                'feature' => 'required',
                'published' => 'required|boolean',
                'availability' => 'required|numeric',
                'image' => 'required',
            ]
        );
        if ($validator->fails()) {
            $code = 20101;
            $response = Errors::code($code, $validator->errors()->first());
            return $response;
        }

        $dataValidation = [
            'id_room' => $idRoom,
        ];
        $validator = Validator::make(
            $dataValidation,
            [
                'id_room' => 'required|uuid',
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
            $check = Rooms::where('slug', $slug)->where('id', '!=', $idRoom)->get()->toArray();
            if(count($check)> 0 ){
                $code = 20103;
                $response = Errors::code($code);
                return $response;
            }

            $update = $request->all();
            $update['slug'] = $slug;
            Rooms::findOrFail($idRoom)->update($update);

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

    public function deleteRoom($idRoom)
    {
        $dataValidation = [
            'id_room' => $idRoom,
        ];
        $validator = Validator::make(
            $dataValidation,
            [
                'id_room' => 'required|uuid',
            ]
        );
        if ($validator->fails()) {
            $code = 20101;
            $response = Errors::code($code, $validator->errors()->first());
            return $response;
        }

        //check current slug
        $check = Rooms::where('id', $idRoom)->get()->toArray();
        if(count($check) < 1 ){
            $code = 20100;
            $response = Errors::code($code);
            return $response;
        }

        try {
            $delete = Rooms::where('id', $idRoom)->delete();
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
