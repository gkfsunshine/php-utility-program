<?php declare(strict_types=1);
/**
 *
 * @author apple
 * @date 2020-05-02
 */

namespace MeiquickLib\Lib\Curd\Grid;

use Hyperf\Database\Model\SoftDeletingScope;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Context;
use MeiquickLib\Lib\Curd\Column\Columns;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @property columns|select_raw|condition|condition_in|order_by|limit|get|count|page #能使用的属性值
 *
 * Class Select
 * @package MeiquickLib\Lib\Curd\Grid
 */
class Select extends GridAbstract
{
    /**
     * limit
     *
     * @param int $limitNum
     */
    protected function limit(int $limitNum=30) : void
    {
        $this->modelBuilder->limit($limitNum);
    }

    /**
     * get
     *
     * @return \Hyperf\Database\Model\Builder[]|\Hyperf\Database\Model\Collection
     */
    private function get()
    {
        return $this->modelBuilder->get();
    }

    /**
     * count
     *
     * @return int
     */
    private function countNum()
    {
        return $this->modelBuilder->count(['*']);
    }

    /**
     * paginate
     *
     * @return array
     */
    private function paginate()
    {
        $serverRequest = $this->serverRequest;
        $perPage = isset($serverRequest['per_page'])?(int)$serverRequest['per_page']:null;
        $page = isset($serverRequest['page'])?(int)$serverRequest['page']:null;
        $paginate = $this->modelBuilder->paginate($perPage, ['*'],  'page', $page);

        return [
            'data'         => $paginate->items(),
            'current_page' => $paginate->currentPage(),
            'last_page'    => $paginate->lastPage(),
            'per_page'     => $paginate->perPage(),
            'total'        => $paginate->total()
        ];
    }

    /**
     * 执行操作结果
     *
     * @return array|\Hyperf\Database\Model\Builder[]|\Hyperf\Database\Model\Collection|int
     */
    private function scopes()
    {
        $switchScopes = 'get';
        $eloquents = $this->eloquentBuilder;
        foreach ($eloquents as $method=>$parameters) {
            if((string)strtolower($method) === 'page' && $parameters === true){
                $switchScopes = $method;
                break;
            }
            if((string)strtolower($method) === 'count' && $parameters === true){
                $switchScopes = $method;
                break;
            }
        }
        switch ($switchScopes)
        {
            case 'get':
                return $this->get();
                break;
            case 'page':
                return $this->paginate();
                break;
            case 'count':
                return $this->countNum();
                break;
        }

        return $this->$switchScopes();
    }

    /**
     * 执行
     *
     * @return \Hyperf\Contract\LengthAwarePaginatorInterface|\Hyperf\Database\Model\Builder|\Hyperf\Database\Model\Builder[]|\Hyperf\Database\Model\Collection|\Hyperf\Database\Model\Model|int|object|null
     */
    public function execute()
    {
        $this->analysisCall();

        $this->customCallback();

        return $this->scopes();
    }
}
