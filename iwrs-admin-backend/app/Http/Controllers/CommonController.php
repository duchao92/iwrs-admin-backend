<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Overtrue\EasySms\EasySms;

use App\Models\Adminer;
use App\Models\SmsCode;

class CommonController extends Controller
{

    /**
     * 获取验证码
     *
     * @param Request $request
     * @return void
     */
    public function getSms(Request $request)
    {
        $validator  = Validator::make($request->all(), [
            'phone'=>'regex:/^1[34578][0-9]{9}$/',
            'type' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['code'=>301, 'msg'=>'手机号有误']);
        }
        
        $conditions = [[ 'status', '=', 1], ['times', '=', 1],
                ['type', '=', $request->input('type')],
                ['phone', '=', $request->input('phone')]
                ];
        
        $smsCode = SmsCode::query($request->header('evn'))->where($conditions)->first();
        
        if (!$smsCode) {
            return response()->json(['code'=>201, 'msg'=>'获取失败']);
        }
        return response()->json(['code'=>200, 'msg'=>'获取成功']);
    }

    /**
     * 发送短信验证码
     *
     * @param Request $request
     * @return void
     */
    public function sendSms(Request $request)
    {
        $validator  = Validator::make($request->all(), [
            'phone' => 'regex:/^1[34578][0-9]{9}$/',
            'type' => 'bail|required|integer',
        ]);
   
        if ($validator->fails()) {
            $errors = $validator->errors();
            if ($errors->has('phone')) {
                return response()->json(['code'=>301, 'msg'=>'手机号格式错误']);
            } else {
                return response()->json(['code'=>301, 'msg'=>'类型有误']);
            }
        }
    
        $now = Carbon::now();

        $alreadySend = SmsCode::query($request->header('evn'))->where([
            ['phone', '=', $request->input('phone')],
            ['expire_in', '>', $now->toDateTimeString()],
            ['status', '=', 1],
            ['type', '=', $request->input('type', 2)]
        ])->select('code', 'created_at', 'times')->first();
        
        if (!!$alreadySend) {
            return 123;
            if ($now->subMinutes(1)->lt(Carbon::createFromFormat('Y-m-d H:i:s', $alreadySend->created_at)) || $alreadySend->times >= 3) {
                return response()->json(['code'=>303, 'msg'=>'请勿频繁发送验证码']);
            }
            
            if ($this->send($request->input('phone'), $alreadySend->code)) {
                $alreadySend->updated_at = $now->toDateTimeString();
                $alreadySend->times = $alreadySend->times + 1;
                if ($alreadySend->save()) {
                    return response()->json(['code'=>400, 'msg'=>'验证码已发送']);
                } else {
                    return response()->json(['code'=>201, 'msg'=>'发送失败']);
                }
            } 
        } else {
            
            $code = str_pad(random_int(1,999999),6,0,STR_PAD_LEFT);

            if ($this->send($request->input('phone'),$code)) {
                
                $bool = SmsCode::query($request->header('evn'))->insert([
                    'code' => $code,
                    'type' => $request->input('type'),
                    'phone' => $request->input('phone'),
                    'created_at' => $now->toDateTimeString(),
                    'updated_at' => $now->toDateTimeString(),
                    'expire_in' => $now->addMinutes(5)->toDateTimeString(),
                    'status' => 1
                ]);
            
                return response()->json(['code'=>200, 'msg'=>'发送成功']);
            } else {

                return response()->json(['code'=>201, 'msg'=>'发送失败']);   
            }
        }

    }

    public function send($phone,$code)
    {
        $easySms = new EasySms(config('sms'));

        if ($easySms->send($phone,
            ['content'=>"【医墨医学】您的验证码是{$code}。如非本人操作，请忽略本短信"])) {
            
            return true;
        } 
        return false;
    }

}