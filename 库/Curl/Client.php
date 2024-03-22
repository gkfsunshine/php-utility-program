<?php declare(strict_types=1);
/**
 *
 * @author apple
 * @date 2020-05-21
 */

namespace MeiquickLib\Lib\Curl;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use GuzzleHttp\Promise\Promise;
use MeiquickLib\Exception\BaseValidateException;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;
use Swoole\Coroutine;
use Hyperf\Utils\Context;

/**
 * Class Client
 * @package MeiquickLib\Lib\Curl
 */
class Client
{
    /** guzzle6 @var GuzzleClient**/
    protected $guzzleClient;

    protected $method='POST';/*默认请求方式*/
    protected $retry=false;/*是否重试默认 默认false不重试*/
    protected $isSync=false;/*是否异步请求*/
    protected $uri='';/*请求链接*/
    protected $headers=[];/*请求头信息*/
    protected $data=[];/*请求数据*/
    protected $multi=[];/*批量请求*/
    protected $options=[];/*数据配置*/
    protected $concurrency=25;/*并发最大请求数*/
    protected $retryCallback=null;/*重试条件*/
    const REQUEST_CID_KEY='request';/*上下文key*/

    /**
     * 初始化
     *
     * @param array $request
     */
    private function init($request=[]) : void
    {
        $this->guzzleClient = make(GuzzleClient::class);
        if(isset($request['method'])){/*请求方式*/
            $this->setMethod((string)$request['method']);
        }
        if(isset($request['is_sync'])){/*是否异步请求*/
            $this->sync((bool)$request['is_sync']);
        }
        if(isset($request['uri'])){/*请求链接*/
            $this->uri((string)$request['uri']);
        }
        if(isset($request['retry'])){/*失败重试次数 默认不重试支持失败重试规则 handler*/
            $this->retry((int)$request['retry']);
        }
        if(isset($request['headers'])){/*头部数据*/
            $this->headers((array)$request['headers']);
        }
        if(isset($request['data'])){/*请求数据*/
            $this->data((array)$request['data']);
        }
        if(isset($request['retry_callback'])){/*重试判断*/
            $this->retryCallback($request['retry_callback']);
        }
        $this->options((array)($request['options']??[]));
        if(isset($request['multi'])){
            $this->multi((array)$request['multi']);
        }
    }

    /**
     * 发起请求
     *
     * @return array
     */
    private function sendAction() : array
    {
        if(empty($this->multi)){
            $responseHandler = $this->isSync === false ? $this->guzzleClient->request($this->method, $this->uri,$this->options) : $this->guzzleClient->requestAsync($this->method, $this->uri,$this->options);
            $response = $this->response($responseHandler);
        }else{/*批量请求*/
            $response = $this->multiRequest();
        }

        return $response;
    }

    /**
     * 发起请求以及重试请求
     *
     * @return array
     */
    private function exceptionHandler()
    {
        $cid = Coroutine::getCid().self::REQUEST_CID_KEY;
        if(Context::has($cid)){
            $retryTimes = Context::get($cid);
            $retryTimes = Context::set($cid,$retryTimes+1);
        }else{
            $retryTimes = Context::set($cid,1);
        }
        try{
            $response = $this->sendAction();
            if(empty($this->multi) && $this->retry !== false && $retryTimes <= $this->retry){/*重试调用 只支持单个请求的重试*/
                if($this->retryCallback !== null && is_callable($this->retryCallback) && ($this->retryCallback)($response) === true){
                    $response = $this->sendAction();
                }elseif($response['code'] != 200 || empty($response['data'])){
                    $response = $this->sendAction();
                }
            }
        }catch (\Exception $exception){
            $response = [
                'reason' => $exception->getMessage(),
                'code' => $exception->getCode(),
                'data' => '',
            ];
            if($this->retry !== false  && $retryTimes <= $this->retry && empty($this->multi)){/*报错自动重试*/
                $response = $this->sendAction();
            }
        }

        return $response;
    }

    /**
     * 响应
     *
     * @param ResponseInterface $response
     * @return array
     */
    private function response($response) : array
    {
        $result = [];
        if($response instanceof ResponseInterface){
            $result = [
                'reason'=> $response->getReasonPhrase(),
                'code'  => $response->getStatusCode(),
                'data'  => $response->getBody()->getContents()
            ];
        }elseif($response instanceof Promise){
            $response->then(
                function (ResponseInterface $response) use (&$result){
                    $result = [
                        'reason'=> $response->getReasonPhrase(),
                        'code'  => $response->getStatusCode(),
                        'data'  => $response->getBody()->getContents()
                    ];
                },
                function (RequestException $reason) use (&$result){
                    $result = [
                        'reason'=> $reason->getMessage(),
                        'code'  => $reason->getCode(),
                        'data'  => [
                            'file'=>$reason->getFile(),
                            'line'=>$reason->getLine()
                        ]
                    ];
                }
            );
        }

        return $result;
    }

    /**
     * 请求
     *
     * @param array $request
     * @return array
     */
    final public static function request(array $request=[]) :array
    {
        $instance = make(self::class);
        $instance->init($request);

        $response = $instance->exceptionHandler();/*retry action*/

        return $response;
    }

    /**
     * 批量请求 未完善
     *
     * @return array
     */
    private function multiRequest()
    {
        $response = [];
        if(!empty($this->multi)){
            $requests = function(){
                foreach ($this->multi as $request){
                    yield new Request(
                        $request['method']??$this->method,
                        $request['uri']??$this->uri,
                        $request['header']??$this->headers,
                        $request['body']??null
                    );
                }
            };

            $pool = new Pool($this->guzzleClient, $requests(), [
                'options'=>$this->options,
                'concurrency' => $this->concurrency,
                'fulfilled' => function (ResponseInterface $responseHandler, $index) use(&$response){
                    $response[$index] = $this->response($responseHandler);
                },
                'rejected' => function (\Exception $reason, $index) {
                    $response[$index] = [
                        'reason'=> $reason->getMessage(),
                        'code'  => $reason->getCode(),
                        'data'  => [
                            'file'=>$reason->getFile(),
                            'line'=>$reason->getLine()
                        ]
                    ];
                },
            ]);

            $promise = $pool->promise();
            $promise->wait();
        }

        return $response;
    }


    private function setMethod(string $method='POST') : void
    {
        $this->method = $method;
    }

    private function sync(bool $isSync=false) : void
    {
        $this->isSync = $isSync;
    }

    private function uri(string $uri) : void
    {
        if(empty($uri)){
            throw new BaseValidateException(10044);
        }
        $this->uri = $uri;
    }

    private function retry(int $retry) : void
    {
        $this->retry = $retry;
    }

    private function headers(array $headers)
    {
        $this->headers = [
            'headers'=>$headers
        ];
    }

    private function data(array $data)
    {
        $this->data = $data;
    }

    private function multi(array $multi=[]) : void
    {
        $this->multi = $multi;
    }

    private function options($options=[])
    {
        $this->options = array_merge($this->headers,$this->data,$options);
        if(!isset($this->options['timeout'])) $this->options['timeout'] = 30;
    }

    private function retryCallback(callable $callback)
    {
        $this->retryCallback = $callback;
    }
}

