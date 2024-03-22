<?php
namespace App\Utils;

/**
 * 对字符串的处理
 *
 * Class Str
 * @package App\Utils
 */
class Str
{
    /**
     * 过滤
     *
     * @param array $params
     * @return array
     */
    final static function filterArr($params = []): array
    {
        if (isset($params['filter_arr']) && is_array($params['filter_arr']) && isset($params['filter_fields'])) {
            $filterData = collect($params['filter_arr'])->filter(function ($item, $key) use ($params) {
                $checkKey = (String)$key;
                $fileFiledArr = is_array($params['filter_fields']) ? $params['filter_fields'] : explode(',', $params['filter_fields']);
                return in_array($checkKey, $fileFiledArr);
            });
            return $filterData->isNotEmpty() ? $filterData->toArray() : [];
        }
        return $params;
    }

    /**
     * array->json
     *
     * @param array $params
     * @return false|string
     */
    final static function echoJson($params = []): string
    {
        return json_encode($params, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * 反json->array
     *
     * @param string $str
     * @return array
     */
    final static function exJson($str = ''): array
    {
        return json_decode($str, true);
    }

    /**
     * 字符串参数传递
     *
     * @param array $params
     * @return string
     */
    final static function strSprintf($params = [])
    {
        return isset($params['format'],$params['arg']) ? sprintf($params['format'],...$params['arg']) : $params;
    }

    /**
     * 中文转码
     */
     final static function mbConvert($string = '')
     {
         return mb_convert_encoding($string, 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');
     }

    /**
     * 查找某个字符第几次出现的未知下标
     *
     * @param array $params
     * @return bool|int
     */
     final static function strFindIndex($params = [])
     {
         if(empty($params['string']) || empty($params['search'])){
             return false;
         }
         $string = $params['string']; $search = $params['search'];
         $times = $params['times']??1;
         $position = -1;
         $n = 0;
         do{
             $position = strpos($string, $search, $position+1);
             $n++;
             if($n==$times){
                 return $position;
                 break;
             }
         }while($position!==false);
         return false;
     }

    /**
     * 过滤空格
     *
     * @param string $str
     * @return string
     */
     final static function filterSpace($str = '')
     {
         $search  = ["\n","\t","\r"];
         $replace = ["","",""];
         return trim(str_replace($search,$replace,$str));
     }

    /**
     * 防止规格时间内不能再次请求 单位毫秒级
     *
     * @param array $params
     * @return bool
     */
     final static function preventConcurrent($params=[])
     {
        $preventKey = array_get($params,'prevent_concurrent_key','prevent_concurrent_key');/*缓存key*/
        isset($params['prevent_prefix_key']) &&  $preventKey .= '_'.$params['prevent_prefix_key'];
        $preventTimeoutLimit = (int) array_get($params,'prevent_time_limit',1800000)/60000;/*缓存默认过期时间 默认30分钟*/
        $preventData = array_get($params,'prevent_data',[
            'expire_time' => get_msectime()
        ]);
        //清除缓存
        if(isset($params['clear_cache'])){
            return \Cache::forget($preventKey);
        }
        if(\Cache::has($preventKey)){
            $cacheData = \Cache::get($preventKey);
            if(isset($cacheData['expire_time']) && $expireTime = $cacheData['expire_time']){
                if(get_msectime() - $expireTime > $preventTimeoutLimit*60000){
                    return true;/*防止缓存出现问题*/
                }
            }
            return false;
        }else{
            \Cache::put($preventKey,$preventData,$preventTimeoutLimit);
        }

        return true;
     }

    /**
     * 去掉空格
     *
     * @param string $str
     * @return string
     */
     final static function removeSpecialCharacters($str='')
     {
         return urldecode(str_replace('%E2%80%AC','',urlencode(trim($str))));
     }

    /**
     * 截取中文字段
     *
     * @param $text
     * @param $length
     * @return string
     */
     final static function strSubText($text,$length)
     {
         if(mb_strlen($text, 'utf8') > $length) {
             return mb_substr($text, 0, $length, 'utf8').'...';
         } else {
             return $text;
         }
     }

    /**
     * 字符串转义
     * @param $test
     * @return mixed
     */
     final static function strSense($str){
         $str = str_replace('\\','\\\\',$str);
         $str = str_replace('%','\%',$str);
         $str = str_replace("'","\'",$str);
         $str = str_replace('"','\"',$str);
         $str = str_replace('_','\_',$str);

         return $str;
     }

    /**
     * 字符转义
     *
     * @param $str
     * @param bool $pre
     * @return string
     */
     final static function strTransferredMeaning($str,$pre=false)
     {
         return $pre=== false ? htmlentities($str) : html_entity_decode($str);
     }

    /**
     * 唯一编码
     *
     * @param null $uniqueUid 用户ID
     * @return string
     */
     final static function uniqueStr($uniqueUid=null)
     {
         $key = $uniqueUid === null ? uniqid() : $uniqueUid;
         return sha1($key.time().microtime(true).mt_rand(1,1000000));
     }

    /**
     * 去掉特殊字符
     *
     * @param $str
     * @return string
     */
     final static function filterSpecialStr($str)
     {
        return trim(preg_replace('/[\n\t\r\xOB!@#$%^&* \.\?\']+/s', '', $str));
     }

    /**
     * 填充为数字类型
     *
     * @param $string
     * @param int $format
     * @return int
     */
     final static function strToFormat($string,$format=-1)
     {
         if((int)$string<=0) return (float)$string;
         return (float)($format  === -1 ? ('0.'.$string) : substr($string,0,$format) . '.' . substr($string,$format));
     }

    /**
     * 数组转xml
     *
     * @param array $params
     * @return string
     */
     final static function arrToXml($params=[])
     {
         if (!is_array($params) || count($params)<=0) {
             throw new PaymentException("数组数据异常！");
         }
         $xml = "<xml>";
         foreach ($params as $key => $val) {
             $xml .= is_numeric($val)?'<'.$key.'>'.$val.'</'.$key.'>':'<'.$key.'><![CDATA['.$val.']]></'.$key.'>';
         }
         $xml .= '</xml>';

         return $xml;
     }

    /**
     * xml转数组
     *
     * @param null $xml
     * @return mixed
     */
     final static function xmlToArr($xml=null)
     {
         if (empty($xml)) {
             throw new PaymentException("xml数据异常！");
         }
         $bPreviousValue = libxml_disable_entity_loader(true);
         $params = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
         libxml_disable_entity_loader($bPreviousValue);

         return $params;
     }

    /**
    * url取出参数
    *
    * @param $url
    * @return array
    */
    final static function urlQueryToParse($url)
    {
        $query = parse_url($url);
        $query = $query['query'] ?? [];
        $params = [];
        if (!empty($query)) {
            $queryParts = explode('&', $query);
            foreach ($queryParts as $param) {
                $item = explode('=', $param);
                $params[$item[0]] = $item[1];
            }
        }
        
        return $params;
    }
}
