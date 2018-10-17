<?php

namespace App\Http\Controllers;


use JWTAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

use App\Models\User;
use App\Models\LoginLogs;
use App\Models\ProfileLogs;

class UserController extends Controller
{
	/**
     * 获取用户列表
     *
     * @param Request $request
     * @return void
     */
	public function list(Request $request)
	{  
        $pageNum = $request->get('page', 1);
        $pageSize = $request->get('size', 20);
        $keyword = $request->input('keywords', null);

		$conditions = [[ 'status', '=', 1 ]];
        if ($keyword) {
            $conditions[] = [
                'realname', 'like', '%'.$keyword.'%'
            ];
        }

        $total = User::query($request->header('env'))->where($conditions)->count();

        $userList = User::query($request->header('env'))->where($conditions)
            ->select('id', 'realname', 'phone', 'email', 'type', 'organization_id')
            ->with('organization')
            ->offset(($pageNum - 1) * $pageSize)
            ->limit($pageSize)
            ->get();

        if (!empty($userList)) {
            $result = ['code'=>200, 'msg'=>'获取成功','data'=>[
                'total' => $total,
                'list' => $userList,
                ]];
        }else{
            $result = ['code'=>201, 'msg'=>'获取失败'];
        }
        return $result;
	}

	/**
     * 获取用户信息
     *
     * @param Request $request
     * @return void
     */
	public function show($userId,Request $request)
	{
		$userList = User::query($request->header('env'))->where('id', $userId)
            ->select('id', 'type', 'realname', 'phone', 'email', 'actived_at', 'created_at', 'organization_id')
            ->with('organization')
            ->get();

        foreach ($userList as $user) {
            $userInfo = $user;    
        }
        
		if (!$userList) {
            
            return response()->json(['code'=>201, 'msg'=>'用户不存在']);
        }
        return response()->json(['code'=>200, 'msg'=>'获取成功','data'=> $userInfo]);
	}

	/**
     * 编辑用户信息
     *
     * @param Request $request
     * @return void
     */
	public function edit($userId,Request $request)
	{
        $validator  = Validator::make($request->all(), [
            'phone'=>'regex:/^1[34578][0-9]{9}$/',
            'realname' => 'required',
            'email' => 'required',
            'organization_id' => 'required',
            'type' => 'required',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            if ($errors->has('phone')) {
                return response()->json(['code'=>301, 'msg'=>'手机号格式错误']);
            } elseif ($errors->has('realname')) {
                return response()->json(['code'=>301, 'msg'=>'真实姓名格式错误']);
            } elseif ($errors->has('email')) {
                return response()->json(['code'=>301, 'msg'=>'email格式错误']);
            } elseif ($errors->has('organization_id')) {
                return response()->json(['code'=>301, 'msg'=>'单位id格式错误']);
            } else {
                return response()->json(['code'=>301, 'msg'=>'类型错误']);
            }
        }

        if ($validator->fails()) {

            return $result = ['code'=>301, 'msg'=>'请重新输入'];
        }

		$realname = $request->input('realname');
		$phone = $request->input('phone');
		$email = $request->input('email');
		$organization_id = $request->input('organization_id');
		$type = $request->input('type');

        $updateUser = ['realname' => $realname, 
                    'phone' => $phone, 
                    'email' => $email, 
                    'organization_id' => $organization_id, 
                    'type' => $type];

		$res = User::query($request->header('env'))->where('id',$userId)
			->update($updateUser);

        if ($res) {
            $result = ['code'=>200, 'msg'=>'修改成功'];
        }else{
            $result = ['code'=>201, 'msg'=>'修改失败'];
        }
        
		return $result;
	}

	/**
     * 重置密码
     *
     * @param Request $request
     * @return void
     */
	public function reset($userId,Request $request)
	{
		$res = User::query($request->header('env'))->where('id',$userId)->update(['password'=> Hash::make('1234567')]);

		if ($res) {
			$result = ['code'=>200, 'msg'=>'重置成功'];
		}else{
			$result = ['code'=>201, 'msg'=>'重置失败'];
		}
		return $result;
	}

	/**
     * 获取用户登录日志
     *
     * @param Request $request
     * @return void
     */
	public function loginLogs($userId,Request $request)
	{
        $pageNum = $request->get('page', 1);
        $pageSize = $request->get('size', 20);
        $created_at = $request->input('created_at',null);

        $conditions = [[ 'uid', '=', $userId]];
        if ($created_at) {
            $conditions[] = [
                'created_at', '>',$created_at
            ];
        }

        $total = LoginLogs::query($request->header('env'))->where($conditions)->count();
        
        $userLoginLogsList = LoginLogs::query($request->header('evn'))->where($conditions)
            ->select('agent', 'system', 'ip', 'version', 'created_at')
            ->offset(($pageNum - 1) * $pageSize)
            ->limit($pageSize)
            ->get();
        
        if ($userLoginLogsList) {
            $result = ['code'=>200, 'msg'=>'获取成功','data'=>[
                'total' => $total,
                'list' => $userLoginLogsList
                ]];
        }else{
            $result = ['code'=>201, 'msg'=>'获取失败'];
        }

        return $result;
	}

	/**
     * 获取用户操作日志
     *
     * @param Request $request
     * @return void
     */
	public function profileLogs($userId,Request $request)
	{
        $pageNum = $request->get('page', 1);
        $pageSize = $request->get('size', 20);
        $created_at = $request->input('created_at', null);
        $keywords = $request->input('keywords', null);

        $conditions = [[ 'uid', '=', $userId]];
        if ($created_at) {
            $conditions[] = [
                'created_at', '>', $keyword
            ];
        }

        if ($keywords) {
            $conditions[] = [
                'data',  'like', '%'.$keywords.'%'
            ];
        }

        $total = ProfileLogs::query($request->header('env'))->where($conditions)->count();
        
        $profileLogsList = ProfileLogs::query($request->header('env'))->where($conditions)
            ->select('uid', 'detail', 'ip','created_at')
            ->offset(($pageNum - 1) * $pageSize)
            ->limit($pageSize)
            ->get();
            
        $userInfo = User::query($request->header('env'))->where('id',$userId)->select('realname')->first();
        
        $userProfileLogs = [];
        
        foreach ($profileLogsList as $profileLogs) {
            $userProfileLogs[] = [
                'user_id' => $profileLogs->uid,
                'realname' => $userInfo->realname,
                'content' => $profileLogs->detail,
                'created_at' => $profileLogs->created_at->toDateTimeString(),
                'ip' => $profileLogs->ip,
                
            ];
        }
        
        if ($userProfileLogs) {
            $result = ['code'=>200, 'msg'=>'获取成功','data'=>[
                'total' => $total,
                'list' => $userProfileLogs
                ]];
        }else{
            $result = ['code'=>201, 'msg'=>'获取失败'];
        }
        
        return $result;
	}
    
    /**
     * 升级用户账号
     *
     * @param 
     * @return void
     */
    public function upgrade($userId,Request $request)
    {
        $validator  = Validator::make($request->all(), [
            'type' => 'required|integer',
        ]);
   
        if ($validator->fails()) {
            return response()->json(['code'=>301, 'msg'=>'类型有误']);
        }

        if ($request->input('type') == 1) {
            return response()->json(['code'=>201, 'msg'=>'升级失败,已是管理员']);
        }

        $userInfo = User::query($request->header('env'))->find($userId);
        
        $userInfo->type = 1;
        if (!$userInfo->save()) {
            return response()->json(['code'=>201, 'msg'=>'升级失败']);
        }
        return response()->json(['code'=>200, 'msg'=>'升级成功']);
    }
}