<?php

declare(strict_types=1);

namespace Verdient\Hyperf3\Di;

use Hyperf\Context\ApplicationContext;
use RuntimeException;

/**
 * 容器
 *
 * @author Verdient。
 */
class Container
{
    /**
     * 获取对象
     *
     * @template TClass of object
     *
     * @param string|class-string<TClass> $abstract
     *
     * @return ($abstract is class-string<TClass> ? TClass : mixed)
     * @author Verdient。
     */
    public static function get(string $abstract)
    {
        if (!ApplicationContext::hasContainer()) {
            throw new RuntimeException('The application context lacks the container.');
        }
        return ApplicationContext::getContainer()->get($abstract);
    }

    /**
     * 获取对象，当对象不存在时返回 Null
     *
     * @template TClass of object
     *
     * @param string|class-string<TClass> $abstract
     *
     * @return ($abstract is class-string<TClass> ? TClass|null : mixed)
     * @author Verdient。
     */
    public static function getOrNull(string $abstract)
    {
        if (!ApplicationContext::hasContainer()) {
            return null;
        }

        if (ApplicationContext::getContainer()->has($abstract)) {
            return ApplicationContext::getContainer()->get($abstract);
        }

        return null;
    }
}
