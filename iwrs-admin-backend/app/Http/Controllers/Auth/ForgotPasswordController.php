<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\CommonController;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Validator;
use Carbon\Carbon;

use App\Models\Adminer;
use App\Models\SmsCode;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * 修改密码
     *
     * @return Response
     */
    public function changepwd(Request $request)
    {
        $validator  = Validator::make($request->all(), [
            'phone'=>'regex:/^1[34578][0-9]{9}$/',
            'code' => 'bail|required|integer',
            'password' => 'required',
            'password_confirmation' => 'required',
        ]);
        
        if ($validator->fails()) {
            $errors = $validator->errors();
            if ($errors->has('phone')) {
                return response()->json(['code'=>301, 'msg'=>'手机号格式错误']);
            } elseif ($errors->has('password')) {
                return response()->json(['code'=>301, 'msg'=>'密码格式错误']);
            } elseif ($errors->has('password_confirmation')) {
                return response()->json(['code'=>301, 'msg'=>'确认密码格式错误']);
            } else {
                return response()->json(['code'=>301, 'msg'=>'验证码错误']);
            }
        }

        $now = Carbon::now();

        $conditions = [['status', '=', 1 ],
                        ['type', '=', 2 ], 
                        ['times', '=', 1],
                        ['expire_in', '>', $now->toDateTimeString()]];

        if ($request->input('phone')) {
            $conditions[] = ['phone', '=', $request->input('phone') ];
        }           

        $smsCode = SmsCode::query($request->header('evn'))->where($conditions)->first();
        
        if ($request->input('code') != $smsCode->code ) {
            return response()->json(['code' => 302, 'msg' => '验证码不正确']);
        }
        
        if (time() > strtotime($smsCode->expire_in)) {
            return response()->json(['code' => 201, 'msg' => '验证码已过期']);
        }
        
        // 验证两次密码是否一致
        if ($request->input('password') != $request->input('password_confirmation')) {
            //密码不一致，失败
            return response()->json(['code'=>201,'msg'=>'两次密码不一致，请重新输入！']);
        }
    
        // 修改验证码状态
        $smsCode->updated_at = date('Y-m-d H:i:s');
        $smsCode->status = 2;

        // 修改密码
        $userInfo = Adminer::where('phone',$request->input('phone'))->first();
        $userInfo->password = Hash::make($request->input('password'));
        
        if($userInfo->save() && $smsCode->save()){
            return response()->json(['code'=>200,'msg'=>'修改密码成功']);
        }
            
        return response()->json(['code'=>201,'msg'=>'修改密码失败']);

    }

}
