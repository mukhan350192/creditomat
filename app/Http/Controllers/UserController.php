<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index()
    {
        $users = DB::table('users')->get();
        return response()->json($users);
    }

    public function getProfile(Request $request)
    {
        $token = $request->input('token');
        $result['success'] = false;

        do {
            if (!$token) {
                $result['message'] = 'Не передан токен';
                break;
            }
            $user = User::where('remember_token', $token)->first();
            if (!$user) {
                $result['message'] = 'Не найден пользователь';
                break;
            }
            $result['name'] = $user->name;
            $result['email'] = $user->email;
            $result['id'] = $user->id;
            $result['roles'] = $this->getUserRole($token);
            $result['permissions'] = $this->getUserPermission($token);
            $result['success'] = true;
        } while (false);
        return response()->json($result);
    }

    public function editOwn(Request $request)
    {
        $token = $request->input('token');
        $email = $request->input('email');
        $user_name = $request->input('user_name');
        $result['success'] = false;
        do {
            if (!$token) {
                $result['message'] = 'Не передан токен';
                break;
            }
            if (!$email) {
                $result['message'] = 'Не передан почта';
                break;
            }
            if (!$user_name) {
                $result['message'] = 'Не передан уч запись';
                break;
            }
            $user = User::where('remember_token', $token)->first();
            if (!$user) {
                $result['message'] = 'Не найден пользователь';
                break;
            }
            $user->email = $email;
            $user->name = $user_name;
            $user->save();
            $result['success'] = true;
            $result['message'] = 'Успешно обновлен';
        } while (false);

        return response()->json($result);
    }

    public function login(Request $request)
    {
        $email = $request->input('email');
        $password = $request->input('password');
        $result['success'] = false;

        do {
            if (!$email) {
                $result['message'] = 'Не передан почта';
                break;
            }
            if (!$password) {
                $result['message'] = 'Не передан пароль';
                break;
            }
            $user = User::where('email', $email)->first();
            if (!$user) {
                $result['message'] = 'Неправильный логин или пароль';
                break;
            }
            $check = Hash::check($password, $user->password);
            if (!$check) {
                $result['message'] = 'Неправильный логин или пароль';
                break;
            }
            $token = Str::random(60);
            $token = sha1($token);
            $user->remember_token = $token;
            $user->save();
            $result['success'] = true;
            $result['name'] = $user->name;
            $result['email'] = $user->email;
            $result['token'] = $token;
        } while (false);

        return response()->json($result);
    }

    public function logout(Request $request){
        $email = $request->input('email');
        $result['success'] = false;
        do{
            if (!$email){
                $result['message'] = 'Не передан почта';
                break;
            }
            $user = User::where('email',$email)->first();
            if (!$user){
                $result['message'] = 'Не найден пользователь';
                break;
            }
            $user->remember_token = '';
            $user->save();
            $result['success'] = true;
        }while(false);
        return response()->json($result);
    }

    public function changePassword(Request $request){
        $token = $request->input('token');
        $password = $request->input('password');
        $result['success'] = false;
        do{
            if (!$token){
                $result['message'] = 'Не передан токен';
                break;
            }
            if (!$password){
                $result['message'] = 'Не передан новый пароль';
                break;
            }
            $user = User::where('remember_token',$token)->first();
            if (!$user){
                $result['message'] = 'Не найден пользователь';
                break;
            }
            $user->password = bcrypt($password);
            $user->save();
            $result['success'] = true;
        }while(false);
        return response()->json($result);
    }

    public function getUserRole($token)
    {
        $user = User::where('remember_token', $token)->get();
        return $user->roles[0]->id;
    }

    public function getUserPermission($token)
    {
        $permissions = [];
        $user = User::where('remember_token', $token)->first();
        $permission = $user->getAllPermissions();
        foreach ($permission as $p) {
            array_push($permissions, $p->pivot->permission_id);
        }
        return $permissions;
    }
}
