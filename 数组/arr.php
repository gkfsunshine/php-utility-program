<?php


namespace App\Utils;


class arr
{
    /**
     * 检测数组对应字段是否为空
     *
     * @param array $filed
     * @param $checkData
     * @return bool
     */
    public final static function checkAllNotEmpty($filed=[],$checkData)
    {
        $allNotEmpty=true;
        foreach ($filed as $it){
            if(!isset($checkData[$it]) || empty($checkData[$it])){
                $allNotEmpty=false;
                break;
            }
        }

        return $allNotEmpty;
    }

    /**
     * 对象转数组
     *
     * @param null $object
     * @return array|mixed
     */
    public final static function objToArr($object=null)
    {
        return $object?json_decode(json_encode($object), true):[];
    }

    /**
     * 判断是否是一维数组
     *
     * @param array $array
     * @return bool
     */
    public final static function isDimensionalArr($array = [])
    {
        return is_array($array)&& !is_array(array_first($array)) ? true : false;
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
    public final static function arrayPage($params=[])
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
}