<?php


namespace MeiquickLib\Helpers;


class Arr
{
    /**
     * 数组分页
     *
     * @param array $params
     * @return array
     */
    final static function arrayPage($params=[])
    {
        if (!isset($params['arr_item'])) {
            return [];
        }
        $arrItem = $params['arr_item'];/*分页数组*/
        $pageSize = $params['page_size'] ?? 15;/*页数默认15*/
        $arrItemCount = count($arrItem);/*总条目*/
        $pageCount = ceil($arrItemCount / $pageSize);/*总页数*/
        $maxPageCount = 1000;/*最大页码 */
        $pageNum = 1;
        while ($pageNum <= $pageCount) {
            $arrItemSplice = array_slice($arrItem, ($pageNum - 1) * $pageSize, $pageSize);
            if (isset($params['page_arr_callback']) && is_callable($params['page_arr_callback'])) {
                call_user_func($params['page_arr_callback'], $arrItemSplice);
            }
            if ($pageNum > $maxPageCount) {
                break;/*防止死循环*/
            }
            $pageNum++;
        }
    }
}
