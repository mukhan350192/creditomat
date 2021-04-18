<?php

namespace App\Http\Controllers;

use App\Http\Resources\MfoDetailResource;
use App\Http\Resources\MfoMinResource;
use App\Http\Resources\MfoResource;
use App\Models\Mfo;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MfoController extends Controller
{
    public function index(Request $request){
        $page = $request->input('page');
        $period = $request->input('period');
        $amount = $request->input('amount');
        $sort = $request->input('sort');
        $rate = $request->input('rate');
        if (!$page || $page == 1){
            $page = 1;
            $skip = 0;
            $take = 15;
        }else{
            $skip = ($page-1)*15;
            $take = 15;
        }
        $sql = "SELECT * FROM mfos";
        if ($period){
            $sql = " AND srok_min<=$period AND srok_max>=$period";
        }
        if ($amount){
            $sql = " AND amount_min<=$amount AND amount_max>=$amount";
        }
        $sql .= " LIMIT $skip,$take";
        $data = DB::select($sql);
        if (isset($data)){
            $result['success'] = true;
            $result['data'] = MfoMinResource::collection($data);
        }else{
            $result['success'] = false;
        }

    }

    public function archive(Request $request){
        $token = $request->input('token');
        $id = $request->input('id');
        $user_role = null;
        $user_permissions = null;
        $us = new UserController();
        if  ($this->checkUser($token)){
            $user_role = $us->getUserRole($token);
            $user_permissions = $us->getUserPermission($token);
        }
        $result['success'] = false;
        do{
            if (!$token){
                $result['message'] = 'Не передан токен';
                break;
            }
            if (!$id){
                $result['message'] = 'Не передан айди организации';
                break;
            }
            if (is_null($user_permissions) && is_null($user_role)){
                $result['message'] = 'Нету прав';
                break;
            }
            if ($user_role !== 1 && $user_permissions !== 3){
                $result['message'] = 'У вас нету доступа сделать эту действие';
                break;
            }
            if ($user_role === 3 && !in_array('4',$user_permissions)){
                $result['message'] = 'У вас нету доступа сделать эту действие';
                break;
            }
            $mfo = DB::table('mfos')->where('id',$id)->first();
            if (!$mfo){
                $result['message'] = 'По МФО не найден данные';
                break;
            }

            $mf = DB::table('mfos')->where('id',$id)->select('active')->first();
            $status = $mf->active;
            $status_name = '';
            if ($status === 0){
                $status = true;
                $status_name = 'Отправлен в Актив';
            }else{
                $status = false;
                $status_name = 'Отправлен в Архив';
            }
            $mf->updated_at = Carbon::now();
            $mf->active = $status;
            $mf->save();

            $result['success'] = true;
            $result['message'] = $status_name;

        }while(false);

        return response()->json($result);
    }

    public function delete(Request $request){
        $token = $request->input('token');
        $id = $request->input('id');
        $user_role = null;
        $user_permissions = null;
        $us = new UserController();

        $result['success'] = false;
        do{
            if (!$token){
                $result['message'] = 'Не передан токен';
                break;
            }
            if (!$id){
                $result['message'] = 'Не передан айди';
                break;
            }
            $user = User::where('remember_token',$token)->first();
            if (!$user){
                $result['message'] = 'Не найден пользователь';
                break;
            }
            $user_role = $us->getUserRole($token);
            $user_permissions = $us->getUserPermission($token);
            if ($user_role !== 1 && $user_permissions !== 3){
                $result['message'] = 'У вас нету доступа на эту действие';
                break;
            }
            if ($user_role === 3 && !in_array('4',$user_permissions)){
                $result['message'] = 'У вас нету доступа на эту действие';
                break;
            }
            DB::table('mfos')->where('id',$id)->delete();
            DB::table('mfo_details')->where('mfo_id',$id)->delete();

            $result['success'] = true;

        }while(false);

        return response()->json($result);
    }

    public function mfo(Request $request){
        $id = $request->input('id');
        $result['success'] = false;
        do{
            if (!$id){
                $result['message'] = 'Не передан айди';
                break;
            }
            $result['success'] = true;
            $result['data'] = MfoResource::collection(Mfo::where('id',$id)->get());
        }while(false);
        return response()->json($result);
    }

    public function add(Request $request){
        $token = $request->input('token');
        $name = $request->input('name');
        $logo = $request->file('logo');
        $url = $request->input('url');
        $amount_min = $request->input('amount_min');
        $amount_max = $request->input('amount_max');
        $srok_min = $request->input('srok_min');
        $srok_max = $request->input('srok_max');
        $stavka = $request->input('stavka');
        $approve_percent = $request->input('approve_percent');
        $review_time = $request->input('review_time');
        $description = $request->input('description');
        $background_img = $request->file('background_img');
        $phone = $request->input('phone');
        $email = $request->input('email');
        $address = $request->input('address');
        $documents = $request->input('documents');
        $delay = $request->input('delay');
        $user_role = null;
        $user_permissions = null;
        $result['success'] = true;

        do{
            if (!$token){
                $result['message'] = 'Не передан токен';
                break;
            }
            if (!$this->checkUser($token)){
                $result['message'] = 'Не найден токен';
                break;
            }
            if (!$name){
                $result['message'] = 'Не передан имя';
                break;
            }
            if (!$logo){
                $result['message'] = 'Не передан лого';
                break;
            }
            if (!$url){
                $result['message'] = 'Не передан ссылка на сайт';
                break;
            }
            if (!$amount_min){
                $result['message'] = 'Не передан мин сумма';
                break;
            }
            if (!$amount_max){
                $result['message'] = 'Не передан макс сумма';
                break;
            }
            if (!$srok_min){
                $result['message'] = 'Не передан мин срок';
                break;
            }
            if (!$srok_max){
                $result['message'] = 'Не передан макс срок';
                break;
            }
            if (!$stavka){
                $result['message'] = 'Не передан ставка';
                break;
            }
            if (!$approve_percent){
                $result['message'] = 'Не передан процент одобрение';
                break;
            }
            if (!$review_time){
                $result['message'] = 'Не передан время расмотрение заявки';
                break;
            }
            if (!$description){
                $result['message'] = 'Не передан описание';
                break;
            }
            if (!$background_img){
                $result['message'] = 'Не передан фоновое изображение';
                break;
            }
            if (!$phone){
                $result['message'] = 'Не передан телефон';
                break;
            }
            if (!$email){
                $result['message'] = 'Не передан почта';
                break;
            }
            if (!$address){
                $result['message'] = 'Не передан адрес';
                break;
            }
            if (!$documents){
                $result['message'] = 'Не передан документы';
                break;
            }
            if (!$delay){
                $result['message'] = 'Не передан просрочка';
                break;
            }
           /* $us = new UserController();
            if ($this->checkUser($token)){
                $user_role = $us->getUserRole($token);
                $user_permissions = $us->getUserPermission($token);
            }
            if ($user_role !== 1 && $user_permissions !== 3) {
                $result['message'] = 'У вас нету доступа сделать эту действие';
                break;
            }
            if ($user_role === 3 && !in_array('4', $user_permissions)) {
                $result['message'] = 'У вас нету доступа сделать эту действие. Пожалуйста обращайтесь администратору!';
                break;
            }*/
            DB::beginTransaction();
            $allowedfileExtension = ['jpeg', 'jpg', 'png'];
            $extension = $logo->getClientOriginalExtension();
            $check = in_array($extension, $allowedfileExtension);
            if (!$check) {
                $result['message'] = 'Пожалуйста, загружайте только jpeg,jpg,png';
                break;
            }
            $path = $logo->store('public/images/');
            $logo_name = $logo->getClientOriginalName();
            $logo_name = sha1(time() . $logo_name) . '.' . $logo->extension();
            $logo->move($path, $logo_name);

            $mfo_id = DB::table('mfos')->insertGetId([
               'name' => $name,
                'amount_min' => $amount_min,
                'amount_max' => $amount_max,
                'srok_min' => $srok_min,
                'srok_max' => $srok_max,
                'logo' => $logo_name,
                'stavka' => $stavka,
                'approve_percent' => $approve_percent,
                'review_time' => $review_time,
                'delay' => $delay,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            if (!$mfo_id){
                DB::rollBack();
                $result['message'] = 'Попробуйте позже';
                break;
            }
            $extension = $background_img->getClientOriginalExtension();
            $check = in_array($extension, $allowedfileExtension);
            if (!$check) {
                $result['message'] = 'Пожалуйста, загружайте только jpeg,jpg,png';
                break;
            }
            $path = $background_img->store('public/images/');
            $background_img_name = $background_img->getClientOriginalName();
            $background_img_name = sha1(time() . $background_img_name) . '.' . $background_img->extension();
            $background_img->move($path, $background_img_name);
            $mfo_detail = DB::table('mfo_details')->insertGetId([
                'mfo_id' => $mfo_id,
                'description' => $description,
                'background_img' => $background_img_name,
                'address' => $address,
                'email' => $email,
                'phone' => $phone,
                'url' => $url,
                'documents' => $documents,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            if (!$mfo_detail){
                DB::rollBack();
                $result['message'] = 'Попробуйте позже';
                break;
            }

            DB::commit();
            $result['success'] = true;
        }while(false);
        return response()->json($result);

    }


    public function edit(Request $request){
        $token = $request->input('token');
        $name = $request->input('name');
        $url = $request->input('url');
        $amount_min = $request->input('amount_min');
        $amount_max = $request->input('amount_max');
        $srok_min = $request->input('srok_min');
        $srok_max = $request->input('srok_max');
        $stavka = $request->input('stavka');
        $approve_percent = $request->input('approve_percent');
        $review_time = $request->input('review_time');
        $description = $request->input('description');
        $phone = $request->input('phone');
        $email = $request->input('email');
        $address = $request->input('address');
        $documents = $request->input('documents');
        $delay = $request->input('delay');
        $id = $request->input('id');
        $user_role = null;
        $user_permissions = null;
        $result['success'] = true;

        do{
            if (!$token){
                $result['message'] = 'Не передан токен';
                break;
            }
            if (!$id){
                $result['message'] = 'Не передан айди';
                break;
            }
            if (!$this->checkUser($token)){
                $result['message'] = 'Не найден токен';
                break;
            }
            if (!$name){
                $result['message'] = 'Не передан имя';
                break;
            }
            if (!$url){
                $result['message'] = 'Не передан ссылка на сайт';
                break;
            }
            if (!$amount_min){
                $result['message'] = 'Не передан мин сумма';
                break;
            }
            if (!$amount_max){
                $result['message'] = 'Не передан макс сумма';
                break;
            }
            if (!$srok_min){
                $result['message'] = 'Не передан мин срок';
                break;
            }
            if (!$srok_max){
                $result['message'] = 'Не передан макс срок';
                break;
            }
            if (!$stavka){
                $result['message'] = 'Не передан ставка';
                break;
            }
            if (!$approve_percent){
                $result['message'] = 'Не передан процент одобрение';
                break;
            }
            if (!$review_time){
                $result['message'] = 'Не передан время расмотрение заявки';
                break;
            }
            if (!$description){
                $result['message'] = 'Не передан описание';
                break;
            }
            if (!$phone){
                $result['message'] = 'Не передан телефон';
                break;
            }
            if (!$email){
                $result['message'] = 'Не передан почта';
                break;
            }
            if (!$address){
                $result['message'] = 'Не передан адрес';
                break;
            }
            if (!$documents){
                $result['message'] = 'Не передан документы';
                break;
            }
            if (!$delay){
                $result['message'] = 'Не передан просрочка';
                break;
            }
            if ($user_role !== 1 && $user_permissions !== 3) {
                $result['message'] = 'У вас нету доступа сделать эту действие';
                break;
            }
            if ($user_role === 3 && !in_array('4', $user_permissions)) {
                $result['message'] = 'У вас нету доступа сделать эту действие. Пожалуйста обращайтесь администратору!';
                break;
            }
            DB::beginTransaction();
            $mfo_id = DB::table('mfos')->where('id',$id)->update([
                'name' => $name,
                'amount_min' => $amount_min,
                'amount_max' => $amount_max,
                'srok_min' => $srok_min,
                'srok_max' => $srok_max,
                'stavka' => $stavka,
                'approve_percent' => $approve_percent,
                'review_time' => $review_time,
                'delay' => $delay,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            if (!$mfo_id){
                DB::rollBack();
                $result['message'] = 'Попробуйте позже';
                break;
            }
            $mfo_detail = DB::table('mfo_details')->where('mfo_id',$id)->update([
                'description' => $description,
                'address' => $address,
                'email' => $email,
                'phone' => $phone,
                'url' => $url,
                'documents' => $documents,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            if (!$mfo_detail){
                DB::rollBack();
                $result['message'] = 'Попробуйте позже';
                break;
            }

            DB::commit();
            $result['success'] = true;
        }while(false);
        return response()->json($result);

    }

    private function checkUser($token)
    {
        $user = User::where('remember_token', $token)->first();
        if ($user) {
            return true;
        } else {
            return false;
        }
    }

}
