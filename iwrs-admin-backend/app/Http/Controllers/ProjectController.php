<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;

use App\Models\Project;
use App\Models\ProjectOrganization;

class ProjectController extends Controller
{
	/**
     * 获取项目列表
     *
     * @param Request $request
     * @return void
     */
	public function projects(Request $request)
	{
        $pageNum = $request->get('page', 1);
        $pageSize = $request->get('size', 20);
        $keyword = $request->get('keywords', null);

        $conditions = [];
        if ($keyword) {
            $conditions[] = [
                'name', 'like', '%'.$keyword.'%'
            ];
        }

        $total = Project::query($request->header('env'))->where($conditions)->count();

        $projects = Project::query($request->header('env'))->where($conditions)
            ->select('id', 'name', 'status', 'sample', 'conductor_id', 'sponsor')
            ->with(['sponsor', 'adminer', 'adminer.organization'])
            ->offset(($pageNum - 1) * $pageSize)
            ->limit($pageSize)
            ->get();

        if (!empty($projects)) {
            $result = ['code'=>200, 'msg'=>'获取成功','data'=>[
            'total' => $total,
            'list' => $projects
            ]];
        }else{
            $result = ['code'=>201, 'msg'=>'获取失败'];
        }
        return $result;
	}

	/**
     * 获取项目详情
     *
     * @param Request $request
     * @return void
     */
	public function details($projectId, Request $request)
	{
		$projectDetails = Project::query($request->header('env'))->where('id', $projectId)
            ->select('id', 'name', 'status', 'scheme_no', 'number', 'start_at', 'sample', 'duration', 'field', 'conductor_id', 'sponsor')
            ->with(['sponsor','adminer','adminer.organization','field'])
            ->orderBy('')
            ->get();
        foreach ($projectDetails as $projects) {
            $project = $projects;
        }
		if ($projectDetails) {
			$result = ['code'=>200, 'msg'=>'获取成功','data'=>$project];
        }else{
            $result = ['code'=>201, 'msg'=>'获取失败'];
        }
        return $result;
	}

    /**
     * 编辑项目信息
     *
     * @param Request $request
     * @return void
     */
    public function edit($projectId,Request $request)
    {   
        $name = $request->input('name');
        $sponsor_id = $request->input('sponsor_id');
        $status = $request->input('status');

        $updateInfo = ['name'=>$name,'sponsor'=>$sponsor_id, 'status'=>$status];

        $res = Project::query($request->header('env'))->where('id', $projectId)
                    ->update($updateInfo);
        
        if ($res) {
            $projectOrganization = ProjectOrganization::query($request->header('evn'))->where([['project_id', $projectId],['important', 1]])->update(['organization_id' => $sponsor_id]);

            $result = ['code'=>200, 'msg'=>'修改成功'];
        }else{
            $result = ['code'=>201, 'msg'=>'修改失败'];
        }

        return $result;
    }
}