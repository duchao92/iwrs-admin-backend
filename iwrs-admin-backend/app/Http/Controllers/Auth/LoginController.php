<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;

use App\Models\Adminer;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * 用户登录
     * 
     * @param Request $request
     * @return void
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|max:11|integer',
            'password' => 'required|string',
            ]);

        $phone = $request->input('phone');
        $password = $request->input('password');

        $adminInfo = Adminer::where('phone',$phone)->first();

        if (!$adminInfo) {
            return response()->json(['code' => 301, 'msg' => '没有该用户']);
        }

        if (Auth::check($request->input('password'), $adminInfo->password)) {
            return response()->json(['code' => 301, 'msg' => '没有该用户或密码错误']);
        } 

        $token = JWTAuth::fromUser($adminInfo);
        $adminInfo->access_token = $token;

        return response()->json(['code'=>200, 'msg'=>'登陆成功', 'data'=> $adminInfo]);
    }

     /**
     * 获取登陆用户角色信息
     *
     * @param Request $request
     * @return void
     */
    public function getUserInfo()
    {   
        $user = JWTAuth::parseToken()->authenticate();
        $user->roles = $user->getRoleNames();
        return response()->json($user);
    }

    /**
     * 验证手机号是否注册
     *
     * @param Request $request
     * @return void
     */
    public function checkPhone(Request $request)
    {
        $validator  = Validator::make($request->all(), [
            'phone'=>'regex:/^1[34578][0-9]{9}$/',
        ]);

        if ($validator->fails()) {
            return $result = ['code'=>301, 'msg'=>'手机号有误'];
        }

        $count = Adminer::where('phone', $request->input('phone'))->count();

        if ($count) {
            $result = ['code'=>200, 'msg'=>'手机号已注册'];
        } else {
            $result = ['code'=>201, 'msg'=>'手机号未注册'];
        }

        return response()->json($result);
    }
}