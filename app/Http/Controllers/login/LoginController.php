<?php

namespace App\Http\Controllers\login;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserModel;
use Session;
use App\Tools\Wechat;
use App\Tools\Curl;
use Illuminate\Support\Facades\Cache;

class LoginController extends Controller
{
    //
    public function login(Request $request)
    {
        return view('login/login');
    }

    public function do_login(Request $request)
    {
        $all = $request->all();
        // dd($all);
        if($all['name']=="" || $all['pwd']=="")
        {
            $arr['code'] = 1;
            $arr['msg'] = '参数错误，用户名或者密码不能为空';
            $arr['data'] = [];
            return json_encode($arr);
        }
        
        $info = UserModel::where('name',$all['name'])->first();
        // dd($info);
        if(!$info)
        {
            $arr['code'] = 1;
            $arr['msg'] = '输入的用户名不存在，请核对';
            $arr['data'] = [];
            return json_encode($arr);
        }

        if($info)
        {
            if($info['pwd']==$all['pwd'] && time()>($info['locktime']+600))
            {
                $id = $info['u_id'];
                $data['u_id'] = $info['u_id'];
                $data['locknum'] = 3;
                $data['sessionid'] = Session::getid();
                // dd($data);
                $data['sessiontime'] = time()+20;
                // dd($data);
                UserModel::where('u_id',$id)->update($data);
                $request->session()->put('userinfo', $info);
                $arr['code'] = 200;
                $arr['msg'] = '登录成功';
                $arr['data'] = [];
                return json_encode($arr);
            }else{
                    //如果登录的次数小于1    则不让登录
                if($info['locknum']<=1){
                    $data['locknum'] = 0;
                    $data['u_id'] = $info['u_id'];
                    $data['locktime'] = time()+600;
                    // dd($data['locktime']);
                    $id = $info['u_id'];
                    UserModel::where('u_id',$id)->update($data);
                    // dd($res);
                    $info = UserModel::where('name',$all['name'])->first();
                    // dd($info);
                    $endtime = $info['locktime'];
                    // dd($starttime);
                    $arr['code'] = 1;
                    $arr['msg'] = "您的账号已被停封，停封至:".date('Y年m月d日 H时i分s秒',$endtime);
                    $arr['data'] = [];
                    return json_encode($arr);
                }else{
                    $data['locknum'] = $info['locknum']-1;
                    $data['u_id'] = $info['u_id'];
                    $data['locktime'] = time();
                    $id = $info['u_id'];
                    UserModel::where('u_id',$id)->update($data);
                    $arr['code'] = 1;
                    $arr['msg'] = "您的密码有误，还可以输入".$info['locknum']."次";
                    $arr['data'] = [];
                    return json_encode($arr);
                }
            }
        }
    }

    //注销登录
    public function logout(Request $request){
        //退出登录
        session(['userinfo'=>null]);
        //跳转到登录页面
        return redirect('/login/login')->withErrors(['账号退出成功']);
    }


    //微信扫码
    public function wechatlogin(Request $request)
    {
        //获取access_token
        $access_token = Wechat::access_token();
        // dd($access_token);
        $url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=$access_token";
        $status = md5(uniqid());
        $data = [
            'expire_seconds'=>60,
            'action_name'=>'QR_STR_SCENE',
            'action_info'=>[
                'scene'=>[
                    'scene_str'=>$status
                ],
            ],
        ];

        $data = json_encode($data);
        //调用封装的CURLPSOT方法，拿到换取二维码
        $data = Curl::Post($url,$data);
        $data = json_decode($data,1);
        // dd($data);
        $ticket = $data['ticket'];
        $img_url = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=$ticket";
        // dd($img_url);
        return view('login/wechatlogin',['img_url'=>$img_url,'status'=>$status]);
    }

    // 执行微信扫码登录
    public function do_wechatlogin(Request $request)
    {
        $all = $request->all();
        // dd($all);
        //查看缓存 如果缓存存在  则登录成功
        $openid = Cache::get($all);
        if(!$openid){
            return json_encode(['ret'=>0,'msg'=>"请先扫码在再操作"]);
        }else{
            $request->session()->put('userinfo', $all);
            
            return json_encode(['ret'=>1,'msg'=>"登录成功"]);
        }


    }


}
