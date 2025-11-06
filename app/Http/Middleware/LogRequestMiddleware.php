<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LogRequestMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // 记录请求开始
        $startTime = microtime(true);

        // 启用SQL查询日志
        DB::enableQueryLog();

        // 打印请求信息
        $this->logRequestInfo($request);

        // 处理请求
        $response = $next($request);

        // 记录SQL查询
        $this->logSqlQueries();

        // 记录请求耗时
        $endTime = microtime(true);
        $executionTime = round(($endTime - $startTime) * 1000, 2);
        Log::info("请求耗时: {$executionTime}ms");
        Log::info(str_repeat('-', 80));

        return $response;
    }

    /**
     * 记录请求信息
     */
    protected function logRequestInfo($request)
    {
        Log::info(str_repeat('=', 80));
        Log::info('【请求信息】');
        Log::info("请求方法: " . $request->method());
        Log::info("请求URL: " . $request->fullUrl());
        Log::info("请求路由: " . ($request->route() ? $request->route()->getName() : '未命名路由'));

        // 记录控制器和方法
        $this->logControllerAction($request);

        Log::info("客户端IP: " . $request->ip());

        // 记录请求参数
        $params = $request->all();
        if (!empty($params)) {
            // 过滤敏感信息
            $filteredParams = $this->filterSensitiveData($params);
            Log::info("请求参数: " . json_encode($filteredParams, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        } else {
            Log::info("请求参数: 无");
        }

        // 记录请求头（可选）
//        $headers = $request->header();
//        if (!empty($headers)) {
//            Log::info("请求头: " . json_encode($headers, JSON_UNESCAPED_UNICODE));
//        }
    }

    /**
     * 记录控制器和方法信息
     */
    protected function logControllerAction($request)
    {
        $route = $request->route();

        if (!$route) {
            Log::info("处理器: 无路由信息");
            return;
        }

        $action = $route->getAction();

        // 获取控制器信息
        if (isset($action['controller'])) {
            $controller = $action['controller'];

            // 解析控制器和方法
            if (is_string($controller)) {
                // 格式: App\Http\Controllers\UsersController@index
                if (strpos($controller, '@') !== false) {
                    list($controllerClass, $method) = explode('@', $controller);

                    // 获取简短的控制器名称
                    $controllerName = class_basename($controllerClass);

                    Log::info("处理器: {$controllerName}@{$method}");
                    Log::info("完整类名: {$controllerClass}");
                } else {
                    Log::info("处理器: " . class_basename($controller));
                }
            } elseif (is_array($controller)) {
                // 如果是数组形式 [ControllerClass::class, 'method']
                $controllerClass = is_object($controller[0]) ? get_class($controller[0]) : $controller[0];
                $method = $controller[1] ?? '未知方法';
                $controllerName = class_basename($controllerClass);

                Log::info("处理器: {$controllerName}@{$method}");
                Log::info("完整类名: {$controllerClass}");
            }
        } elseif (isset($action['uses']) && $action['uses'] instanceof \Closure) {
            Log::info("处理器: 闭包函数 (Closure)");
        } else {
            Log::info("处理器: 未知类型");
        }
    }

    /**
     * 记录SQL查询
     */
    protected function logSqlQueries()
    {
        $queries = DB::getQueryLog();

        if (empty($queries)) {
            Log::info("SQL查询: 无");
            return;
        }

        Log::info("【SQL查询】 共执行 " . count($queries) . " 条SQL");

        foreach ($queries as $index => $query) {
            $sql = $query['query'];
            $bindings = $query['bindings'];
            $time = $query['time'];

            // 将绑定参数替换到SQL中
            $fullSql = $this->interpolateQuery($sql, $bindings);

            Log::info("SQL #" . ($index + 1) . " (耗时: {$time}ms):");
            Log::info($fullSql);

            // 标记慢查询
            if ($time > 1000) {
                Log::warning("⚠️  慢查询警告：此查询耗时超过1秒！");
            }
        }
    }

    /**
     * 将SQL绑定参数替换到查询中
     */
    protected function interpolateQuery($query, $bindings)
    {
        if (empty($bindings)) {
            return $query;
        }

        foreach ($bindings as $binding) {
            // 处理不同类型的绑定值
            if (is_string($binding)) {
                $value = "'" . addslashes($binding) . "'";
            } elseif (is_null($binding)) {
                $value = 'NULL';
            } elseif (is_bool($binding)) {
                $value = $binding ? '1' : '0';
            } else {
                $value = $binding;
            }

            // 替换第一个问号
            $query = preg_replace('/\?/', $value, $query, 1);
        }

        return $query;
    }

    /**
     * 过滤敏感数据
     */
    protected function filterSensitiveData($data)
    {
        $sensitiveKeys = ['password', 'password_confirmation', 'token', 'secret', 'api_key'];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->filterSensitiveData($value);
            } elseif (in_array(strtolower($key), $sensitiveKeys)) {
                $data[$key] = '***已过滤***';
            }
        }

        return $data;
    }
}
