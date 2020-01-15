<?php

namespace App\Http\Controllers\wechat;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Tools\Wechat;

class WechatController extends Controller
{
    //
    public function index(Request $request)
    {
        $echostr = $request->input("echostr");
        // echo $echostr;die;
        $info = file_get_contents("php://input");
        // dd($info);
        // file_put_contents("1.txt",$info);
        //处理xml格式数据，将xml格式数据转换成对象
        $xml_obj = simplexml_load_string($info,'SimpleXMLElement',LIBXML_NOCDATA);
        // dd($xml_obj);
        $xml_arr = (array)$xml_obj;

        //判断用户是否登录
        if($xml_arr['MsgType'] == "event" && $xml_arr['Event']=="subscribe")
        {
            // echo 1;
            //储存二维码和用户的关系 ，首先 我们要获取到二维码的标识和微信用户的OPENid
            $openid = $xml_arr['FromUserName'];
            // dd($openid);

            $EventKey = $xml_arr['EventKey'];
            // dd($EventKey);
            $status = ltrim($EventKey,'qrscene_');
            // dd($status);
            // var_dump($status);die;
            //因为需要对比 所以我们要将数值存入到缓存
            if($status)
            {
                $c = Cache::put($status,$openid,60);
                // dd($c);
                //我们需要在用户扫码之后 发送消息，提示他扫码成功
                Wechat::Responsetext("正在登陆，请稍后",$xml_arr);
            }    
        }

        //用户关注过 ，触发SCAN事件

        if($xml_arr['MsgType'] == "event" && $xml_arr['Event'] == "SCAN")
        {
            //储存二维码和用户的关系 ，首先 我们要获取到二维码的标识和微信用户的OPENid
            $openid = $xml_arr['FromUserName'];
            // dd($openid);
            $status = $xml_arr['EventKey'];
            // dd($status);
           if($status)
           {
                Cache::put($status,$openid,60);
                //我们需要在用户扫码之后 发送消息，提示他扫码成功
                Wechat::Responsetext("正在登陆，请稍后",$xml_arr);
           }
        }
    }
}
