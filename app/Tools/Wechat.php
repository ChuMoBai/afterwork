<?php
namespace App\Tools;
use Illuminate\Support\Facades\Cache;
use App\Tools\Curl;
class Wechat{
	/**
	 * 回复文本消息
	 */
	public static function Responsetext($msg,$xml_arr){


	 		 echo "<xml><ToUserName><![CDATA[".$xml_arr['FromUserName']."]]></ToUserName>
                        <FromUserName><![CDATA[".$xml_arr['ToUserName']."]]></FromUserName>
                        <CreateTime>".time()."</CreateTime>
                        <MsgType><![CDATA[text]]></MsgType>
                        <Content><![CDATA[".$msg."]]></Content>
                    </xml>";die;
                }

    /**
     * access_token
     */
    public static function access_token(){

    	$access_token = Cache::get("access_token");
    	if(empty($access_token)){
    		//缓存里面没有东西，写入缓存
         	$re = file_get_contents('https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.env('WECHAT_APPID').'&secret='.env('WECHAT_SECRET'));
            $result = json_decode($re,true);
            $access_token = $result["access_token"];
            //储存两个小时
            // Cache::put("access_token",$access_token,7200);
    	}
    	// dd($access_token);
         return $access_token;
    }

    /**
     * 获取用户的基本信息
     */
   public static function getUserInfo($FromUserName)
   {
        $access_token = Self::access_token();
        $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=".Wechat::access_token()."&openid=".$FromUserName."&lang=zh_CN";
        $data = file_get_contents($url);
        $data =json_decode($data,1);
        // dd($data);
        return $data;
   }

   /**
    * 回复图片信息
    */
   public static function ResponseImg($media_id,$xml_arr)
   {
        echo "<xml>
                      <ToUserName><![CDATA[".$xml_arr['FromUserName']."]]></ToUserName>
                      <FromUserName><![CDATA[".$xml_arr['ToUserName']."]]></FromUserName>
                      <CreateTime>".time()."</CreateTime>
                      <MsgType><![CDATA[image]]></MsgType> 
                      <Image>
                        <MediaId><![CDATA[".$media_id."]]></MediaId>
                      </Image>
                </xml>";die;
   }

   /**
    * 天气
    */
   
   public static function getWeather($city)
    {
         $url = "http://api.k780.com/?app=weather.future&weaid={$con}&appkey=46444&sign=21c96326d39422120d37510530a74c30&format=json";
            //请求方式  git
        $data = file_get_contents($url);
        //解码  转为数组
        // dd($data);exit;
                $data =json_decode($data,1);
                // dd($data);
                $msg = "";
                foreach ($data['result'] as $key => $value) {
                    // dd($value);exit;
                    $msg .= "今天是：{$value['days']} {$value['week']} 城市：{$value['citynm']} 度数：{$value['temperature']} 天气: {$value['weather']} 风向：{$value['wind']} 风量：{$value['winp']} 最高气温: {$value['temp_high']}℃ 最低气温: {$value['temp_low']}℃";
        }
            return $msg;
    }


    /**
     * 无限极分类
     */
        public static function getCateInfo($cateInfo,$parent_id=0,$level=0){

         static $info=[];
         foreach($cateInfo as $k=>$v){
             if($v['parent_id']==$parent_id){
                 $v['level']=$level;
                 $info[]=$v;
                self::getCateInfo($cateInfo,$v['cate_id'],$level+1);
             }
         }
         return $info;
     }

      /**
     * 公众号标签列表
     * @return mixed
     */
    public static function tag_list()
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/tags/get?access_token='.Self::access_token();
        $re = Curl::Get($url);
        $result = json_decode($re,1);
        return $result;
    }



     /**
     * 网页授权获取用户openid
     * @return [type] [description]
     */
    public static function getOpenid()
    {
        //先去session里取openid 
        $openid = session('openid');
        //var_dump($openid);die;
        if(!empty($openid)){
            return $openid;
        }
        //微信授权成功后 跳转咱们配置的地址 （回调地址）带一个code参数
        $code = request()->input('code');
        if(empty($code)){
            //没有授权 跳转到微信服务器进行授权
            $host = $_SERVER['HTTP_HOST'];  //域名
            $uri = $_SERVER['REQUEST_URI']; //路由参数
            $redirect_uri = urlencode("http://".$host.$uri);  // ?code=xx
            $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".env('WECHAT_APPID')."&redirect_uri={$redirect_uri}&response_type=code&scope=snsapi_base&state=STATE#wechat_redirect";
            header("location:".$url);die;
        }else{
            //通过code换取网页授权access_token
            $url =  "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".env('WECHAT_APPID')."&secret=".env('WECHAT_SECRET')."&code={$code}&grant_type=authorization_code";
            $data = file_get_contents($url);
            $data = json_decode($data,true);
            $openid = $data['openid'];
            //获取到openid之后  存储到session当中
            session(['openid'=>$openid]);
            return $openid;
            //如果是非静默授权 再通过openid  access_token获取用户信息
        }   
    }



    /**
     * 网页授权获取用户基本信息
     * @return [type] [description]
     */
    public static function getOpenidByUserInfo()
     {
        //先去session里取openid 
        $userInfo = session('userInfo');
        //var_dump($openid);die;
        if(!empty($userInfo)){
            return $userInfo;
        }
        //微信授权成功后 跳转咱们配置的地址 （回调地址）带一个code参数
        $code = request()->input('code');
        if(empty($code)){
            //没有授权 跳转到微信服务器进行授权
            $host = $_SERVER['HTTP_HOST'];  //域名
            $uri = $_SERVER['REQUEST_URI']; //路由参数
            $redirect_uri = urlencode("http://".$host.$uri);  // ?code=xx
            $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".env('WECHAT_APPID')."&redirect_uri={$redirect_uri}&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect";
            header("location:".$url);die;
        }else{
            //通过code换取网页授权access_token
            $url =  "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".env('WECHAT_APPID')."&secret=".env('WECHAT_SECRET')."&code={$code}&grant_type=authorization_code";
            $data = file_get_contents($url);
            $data = json_decode($data,true);
            $openid = $data['openid'];
            $access_token = $data['access_token'];
            //获取到openid之后  存储到session当中
            // session(['openid'=>$openid]);
            // return $openid;
            //如果是非静默授权 再通过openid  access_token获取用户信息
            $url = "https://api.weixin.qq.com/sns/userinfo?access_token={$access_token}&openid={$openid}&lang=zh_CN";
            $userInfo = file_get_contents($url);
            $userInfo = json_decode($userInfo,true);
            //返回用户信息
            session(['userInfo'=>$userInfo]);
            // dd($userInfo);
            return $userInfo;
        }   
    }

    //获取二维码
    public static function getcode(Request $request)
    {
        
        if(isset($ticket)){//获取到了二维码
            $url = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=$ticket";
            return $url;
        }
        return false;

    }
}