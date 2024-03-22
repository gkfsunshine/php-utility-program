<?php
namespace App\Utils;

class Arr
{

    /**
     * 删除某个元素
     *
     * @param array $arr
     * @param $field
     */
    final static function arrayPop(&$arr=[],$field)
    {
        $key = array_search($field, $arr);
        if ($key !== false)
            array_splice($arr, $key, 1);
    }


    /**
     * 判断是否是一维数组
     *
     * @param array $array
     * @return bool
     */
    public static function isDimensionalArray($array = [])
    {
        return is_array($array) && count($array) == count($array, 1) ? true : false;
    }

    /**
     * 数组分页
     * @param arr_item 数组
     * @param page_size 条目
     * @param page_arr_callback 回调
     *
     * @param array $params
     * @return array
     */
    final static function arrayPage($params=[])
    {
        if(!isset($params['arr_item'])){
            return [];
        }
        $arrItem  = $params['arr_item'];/*分页数组*/
        $pageSize = $params['page_size']??15;/*页数默认15*/
        $arrItemCount = count($arrItem);/*总条目*/
        $pageCount    = ceil($arrItemCount / $pageSize);/*总页数*/
        $maxPageCount = 1000;/*最大页码 */
        $pageNum = 1;
        while ($pageNum <= $pageCount) {
            $arrItemSplice = array_slice($arrItem, ($pageNum - 1) * $pageSize, $pageSize);
            if(isset($params['page_arr_callback']) && is_callable($params['page_arr_callback'])){
                call_user_func($params['page_arr_callback'],$arrItemSplice);
            }
            if ($pageNum > $maxPageCount) {
                break;/*防止死循环*/
            }
            $pageNum++;
        }
    }

    /**
     * 分页
     *
     * @param array $params
     * @return array
     */
    final static function page($params=[])
    {
        $page=$params['page']??1;
        $data=$params['data']??[];
        $pageNum=$params['page_num']??20;
        $offset=max(0,($page-1)*$pageNum-1);
        $totals=count($data);
        $countpage=ceil($totals/$pageNum);
        $pagedata=array_slice($data,$offset,$pageNum);

        return [
            'current_page'=>$page,
            'data'=>array_values((array)$pagedata),
            'from'=>($page-1)*$pageNum+1,
            'last_page'=>$countpage,
            'per_page'=>$pageNum,
            'to'=>$page*$pageNum,
            'total'=>$totals
        ];
    }

    /**
     * 深度比较
     *
     * @param array $params
     * @return bool
     */
    final static function depthFieldInArray($params=[])
    {
        if(!isset($params['data'],$params['check_fields'])){
            return false;
        }
        foreach ($params['check_fields'] as $val){
            if(!array_key_exists($val,(is_object($params['data']))?$params['data']->toArray():$params['data'])){
                return false;
            }
        }

        return true;
    }

    /**
     * 可以全部为空 或者不为空
     *
     * @param array $data
     * @return bool
     */
    public static function checkDataNotSingleNull($data=[])
    {
        $fields = $data['fields']??[];
        $validateData = $data['value']??[];
        if(empty($fields) || empty($validateData)){
            return false;
        }
        $fields = explode(',',$fields);
        $tmpData = array_filter(array_keys($validateData),function($key) use($fields,$validateData,$data){
            $value = $validateData[$key]??'';
            return in_array($key,$fields) && (isset($data['callback']) && is_callable($data['callback']) ?  $data['callback']($value) : !empty($value));
        });

        return count($tmpData) === count($fields) || count($tmpData) === 0;
    }
}
