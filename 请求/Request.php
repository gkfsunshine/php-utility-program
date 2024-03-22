<?php

namespace App\Helper;

/**
 * 请求
 */
class Request
{
    /**
     * laravel版信息
     *
     * @param string $name 名称
     * @return mixed
     */
    final public static function laravelInfo($name='')
    {
        static $info;
        if(!$info){
            $request=request();
            $route=$request->route();
            $info=[
                'uri'=>$request->getUri(),
                'header'=>$request->header(),
                'request_uri'=>$request->getRequestUri(),
                'path_info'=>$request->getPathInfo(),
                'controller'=>$route ? str_replace('Controller','',explode('@',\Illuminate\Support\Arr::last(explode('\\',$route->getActionName())))[0]) : '',
                'action'=>$route ? $route->getActionMethod() : '',
            ];
        }
        return $name ? $info[$name] : $info;
    }

    /**
     * 获取 请求主体
     *
     * @return mixed
     */
    final public static function body()
    {
        return file_get_contents('php://input');
    }

    /**
     * 获取$_GET
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    final public static function get($key='',$default=null)
    {
        $all=isset($_GET) ? $_GET : [];
        return $key ? (array_key_exists($key,$all) ? $all[$key] : $default) : $all;
    }

    /**
     * 获取$_POST
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    final public static function post($key='',$default=null)
    {
        $all=isset($_POST) ? $_POST : [];
        return $key ? (array_key_exists($key,$all) ? $all[$key] : $default) : $all;
    }

    /**
     * 获取$_REQUEST
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    final public static function request($key='',$default=null)
    {
        $all=isset($_REQUEST) ? $_REQUEST : [];
        return $key ? (array_key_exists($key,$all) ? $all[$key] : $default) : $all;
    }

    /**
     * 获取客户端 请求时间
     *
     * @return float
     */
    final public static function time()
    {
        $result=0;
        if(array_key_exists('REQUEST_TIME_FLOAT',$_SERVER)){
            $result=$_SERVER['REQUEST_TIME_FLOAT'];
        }
        return $result;
    }

    /**
     * 获取运行时间，秒
     *
     * @param string $unit 单位
     * @return float
     */
    final public static function runTime($unit='s')
    {
        $result=microtime(true)-static::time();
        switch($unit){
            case 'mcs':/*微秒*/
                $result*=1000000;
                break;
            case 'ms':/*毫秒*/
                $result*=1000;
                break;
            case 's':/*秒*/
            default:
                break;
        }
        return $result;
    }

    /**
     * 获取客户端 请求方式
     *
     * @return string
     */
    final public static function method()
    {
        $result='';
        if(array_key_exists('REQUEST_METHOD',$_SERVER)){
            $result=$_SERVER['REQUEST_METHOD'];
        }
        return $result;
    }

    /**
     * 获取客户端 ip
     *
     * @return string
     */
    final public static function ip()
    {
        $result='unknown';
        if(isset($_SERVER)){
            if(array_key_exists('HTTP_X_FORWARDED_FOR',$_SERVER)){
                $result=$_SERVER['HTTP_X_FORWARDED_FOR'];
            }elseif(array_key_exists('HTTP_CLIENT_IP',$_SERVER)){
                $result=$_SERVER['HTTP_CLIENT_IP'];
            }else{
                $result=$_SERVER['REMOTE_ADDR'];
            }
        }else{
            if(getenv('HTTP_X_FORWARDED_FOR')){
                $result=getenv('HTTP_X_FORWARDED_FOR');
            }elseif(getenv('HTTP_CLIENT_IP')){
                $result=getenv('HTTP_CLIENT_IP');
            }else{
                $result=getenv('REMOTE_ADDR');
            }
        }
        if(trim($result)=='::1'){
            $result='127.0.0.1';
        }
        return $result;
    }

    /**
     * 获取客户端 ip
     */
    final public static function clientIp($param=[])
    {
        load_helper('Network');
        $param['iToLong']=array_key_exists('iToLong',$param) ? $param['iToLong'] : true;/*是否转成long类型*/
        $param['realIp']=array_key_exists('realIp',$param) ? $param['realIp'] : true;/*是否真实ip*/
        return get_client_ip($param['iToLong'],$param['realIp']);
    }

    /**
     * 获取客户端 agent
     *
     * @return string
     */
    final public static function agent()
    {
        $result='';
        if(array_key_exists('HTTP_USER_AGENT',$_SERVER)){
            $result=$_SERVER['HTTP_USER_AGENT'];
        }
        return $result;
    }

    /**
     * 判断是否https请求
     *
     * @return bool
     */
    final public static function isHttps()
    {
        if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] && strtolower($_SERVER['HTTPS'])!=='off'){
            return true;
        }else if(isset($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME']==='https'){
            return true;
        }else if(isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO']==='https'){
            return true;
        }else if(isset($_SERVER['HTTP_FRONT_END_HTTPS']) && $_SERVER['HTTP_FRONT_END_HTTPS'] && strtolower($_SERVER['HTTP_FRONT_END_HTTPS'])!=='off'){
            return true;
        }else if(isset($_SERVER['SERVER_PORT']) && (int)($_SERVER['SERVER_PORT'])===443){
            return true;
        }
        return false;
    }

    /**
     * 获取请求的url
     *
     * @param array $param 参数
     * @return string
     */
    final public static function url($param=[])
    {
        $param['query']=isset($param['query']) ? $param['query'] : [];/*请求参数*/
        $result='';
        if(isset($_SERVER['REQUEST_URI'])){
            if(static::isHttps()){
                $result='https';
            }else{
                if(isset($_SERVER['SERVER_PROTOCOL'])){
                    $tmp=explode('/',$_SERVER['SERVER_PROTOCOL']);
                    $result=strtolower(trim($tmp[0]));
                }
            }
            if(isset($_SERVER['HTTP_HOST'])){
                $result=($result ? $result.'://' : '').$_SERVER['HTTP_HOST'];
            }
            $result=rtrim($result,'/').'/'.ltrim($_SERVER['REQUEST_URI'],'/');
        }
        if($result && $param['query']){
            $query='';/*query参数*/
            $get=array_merge(static::get(),$param['query']);
            foreach($get as $k=>$v){
                $query.=($query ? '&' : '').$k.'='.urlencode($v);
            }
            $tmp=explode('?',$result);
            $result=$tmp[0].'?'.$query;
        }
        return $result;
    }

    /**
     * curl
     *
     * @param array $param
     * @return string
     */
    final public static function curl($param=[])
    {
        $param['url']=array_key_exists('url',$param) ? $param['url'] : '';/*url*/
        $param['postData']=array_key_exists('postData',$param) ? $param['postData'] : [];/*post的数据*/
        $param['httpBasicAuthUserPwd']=array_key_exists('httpBasicAuthUserPwd',$param) ? $param['httpBasicAuthUserPwd'] : '';/*http基本认证用户名密码*/
        $ch=curl_init();
        curl_setopt($ch,CURLOPT_URL,$param['url']);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,0);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,0);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        if($param['httpBasicAuthUserPwd']){
            curl_setopt($ch,CURLOPT_HTTPAUTH,CURLAUTH_BASIC);
            curl_setopt($ch,CURLOPT_USERPWD,$param['httpBasicAuthUserPwd']);
        }
        if($param['postData']){
            if(is_array($param['postData'])){
                $postFile=false;/*是否post文件*/
                foreach($param['postData'] as $k=>$v){
                    if((is_string($v) && substr($v,0,1)=='@') || (class_exists('CURLFile') && $v instanceof CURLFile)){
                        $postFile=true;
                        break;
                    }
                }
                if(!$postFile){
                    $param['postData']=http_build_query($param['postData']);
                }
            }
            curl_setopt($ch,CURLOPT_POST,1);
            curl_setopt($ch,CURLOPT_POSTFIELDS,$param['postData']);
        }
        $response=curl_exec($ch);
        curl_close($ch);
        return $response;
    }
}