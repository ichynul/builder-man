<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2021 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yunwuxin <448901948@qq.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace think\exception;

use Webman\Http\Request;
use Webman\Http\Response;
use Webman\Exception\BusinessException;

/**
 * HTTP响应异常
 */
class HttpResponseException extends BusinessException
{
    /**
     * @var Response
     */
    protected $response;

    public function __construct(Response $response)
    {
        $this->response = $response;
    }

    /**
     * Undocumented function
     *
     * @return Response|null
     */
    public function getResponse(): ?Response
    {
        return $this->response;
    }

     /**
     * Render an exception into an HTTP response.
     * @param Request $request
     * @return Response|null
     */
    public function render(Request $request): ?Response
    {
        //返回一个新对象，
        //避免 App::exceptionResponse() 中 $response->exception($e) 造成循环引用
        return new Response($this->response->getStatusCode(), $this->response->getHeaders(), $this->response->rawBody());
    }
}
