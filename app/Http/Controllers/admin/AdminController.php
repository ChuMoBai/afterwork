<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserModel;


class AdminController extends Controller
{
    //

    public function index(Request $request)
    {
        $u_id = session('userinfo')['u_id'];
        $userinfo = UserModel::where("u_id",$u_id)->first();
        // dd($userinfo);
        if(!$userinfo['mobile'])
        {
            return view("admin/create_mobile");
        }else{
            return view("admin/index");
        }
    }

    public function do_create_mobile(Request $request)
    {
        $all = $request->all();
        // dd($all);
        $u_id = session('userinfo')['u_id'];    
        $mobile = UserModel::where('mobile',$all['mobile'])->first();
        // var_dump($userinfo);
        if($mobile)
        {
            return redirect('/admin/index')->withErrors(['您输入的手机号已经绑定其他账户']);
        }else{
            $all = UserModel::where('u_id',$u_id)->update(['mobile'=>$all['mobile']]);
            if($all)
            {
                return redirect('/admin/index')->withErrors(['账号绑定成功，谢谢您的支持']);
            }
        }
            
        
        

    }
}
