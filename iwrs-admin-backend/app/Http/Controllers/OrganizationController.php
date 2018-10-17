<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;

use App\Models\Organization;

class OrganizationController extends Controller
{
    
    /**
     * 获取单位列表
     *
     * @param Request $request
     * @return void
     */
	public function list(Request $request)
	{
        $pageNum = $request->get('page', 1);
        $pageSize = $request->get('size', 20);
        $keyword = $request->get('keywords', null);

		$conditions = [[ 'status', '=', 1 ]];
        if ($keyword) {
            $conditions[] = [
                'name', 'like', '%'.$keyword.'%'
            ];
        }
        
        $total = Organization::query($request->header('env'))->where($conditions)->count();
        
        $organizations = Organization::query($request->header('env'))->where($conditions)
            ->select('id', 'name', 'created_at')
            ->offset(($pageNum - 1) * $pageSize)
            ->limit($pageSize)
            ->get();

        if (!empty($organizations)) {
            $result = ['code'=>200, 'msg'=>'获取成功', 'data'=>[
            'total' => $total,
            'list' => $organizations
            ]];
        }else{
            $result = ['code'=>201, 'msg'=>'获取失败'];
        }
        return $result;
	}

    

	/**
     * 编辑单位
     *
     * @param integer $id
     * @param Request $request
     * @return void
     */
    public function edit($organizationId, Request $request)
    {
        $validator  = Validator::make($request->all(), [
            'name' => 'required',
        ]);

        if ($validator->fails()) {
            return $result = ['code'=>301, 'msg'=>'单位名称不能为空'];
        }

        $organization = Organization::query($request->header('env'))->find($organizationId);

        $organization->name = $request->input('name');
        if($organization->save())
        {
            $result = ['code'=>200, 'msg'=>'修改成功'];            
        } else {
            $result = ['code'=>201, 'msg'=>'修改失败'];            
        }
        return $result;
    }
}
