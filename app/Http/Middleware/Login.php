<?php

namespace App\Http\Middleware;

use Closure;
use Session;
use App\Models\UserModel;


class Login
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // var_dump(session('userinfo'));die;
        if(empty(session('userinfo')))
        {
            // echo 1;die;
            return redirect('/login/login')->withErrors(['您还没有登录，请先登录在进行浏览']);
        }
        $u_id = session('userinfo')['u_id'];
        $sessionid = Session::getid();
        $info = UserModel::where('u_id',$u_id)->first();
        //判断是否在别的浏览器登录
        if($sessionid!=$info['sessionid'])
        {
            session()->forget('userinfo');
            return redirect('/login/login')->withErrors(['账号异地登录，如果不是本人请尽快更改密码']);
        }
        //如果20秒没有操作，就会自动退出
        if(time()>$info['sessiontime'])
        {
            session()->forget('userinfo');
            return redirect('/login/login')->withErrors(['因为您的长时间没有操作，系统判定您已经下线']);
        }
        //延长操作时间
        $sessiontime = $info['sessiontime'];
        UserModel::where('u_id',$u_id)->update(['sessiontime'=>time()+20]);
        return $next($request);
    }
}
