<?php


namespace MeiquickLib\Lib\Utils;

use MeiquickLib\Constants\PayConst;
use MeiquickLib\Service\Redis\PayRedis;

/**
 * 工具锁
 *
 * Class Lock
 * @package MeiquickLib\Lib\Utils
 */
class Lock
{
    /**
     * 加锁
     * @return bool
     */
    public function setBalanceLock($lockKey): bool
    {
        return $this->redis()->set($lockKey, 1, ['nx', 'ex' => 20]);
    }

    /**
     * 删除锁
     * @return int
     */
    public function delBalanceLock($lockKey): int
    {
        return $this->redis()->del($lockKey);
    }

    private $redis = null;

    /**
     * 获取 UserRedis
     * @return PayRedis
     */
    private function redis(): PayRedis
    {
        if (!$this->redis) {
            $this->redis = container()->get(PayRedis::class);
        }
        return $this->redis;
    }
}