<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2021 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace app\middleware;

use app\controller\ApiResponse;
use Closure;
use think\Request;
use think\Response;

/**
 * 验证用户信息
 */
class TokenValidation
{
    /**
     * 允许跨域请求
     * @access public
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle($request, Closure $next)
    {
        $token = $request->header('Authorization');
        if (empty($token)) {
            ApiResponse::fail([], 1101);
        }
        $tokenIsCorrect = $this->validateToken($token);
        if (!$tokenIsCorrect) {
            ApiResponse::fail([], 1102);
        }
        return $next($request);
    }

    public function validateToken($token)
    {
        // check if the token is correct
        return $token == 'Bearer token-admin';
    }
}
