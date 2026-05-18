<?php

declare(strict_types=1);

namespace Verdient\Hyperf3\Di;

use FilesystemIterator;
use Hyperf\Di\ClassLoader as DiClassLoader;
use Hyperf\Di\ScanHandler\ScanHandlerInterface;
use Hyperf\Support\Composer;
use Override;

/**
 * 类加载器
 *
 * @author Verdient。
 */
class ClassLoader extends DiClassLoader
{
    /**
     * @author Verdient。
     */
    #[Override]
    public static function init(?string $proxyFileDirPath = null, ?string $configDir = null, ?ScanHandlerInterface $handler = null): void
    {
        if (defined('BASE_PATH')) {
            $classMap = [];

            if (is_dir(constant('BASE_PATH') . '/packages/classMap')) {
                $classMap = static::collectClassMap(constant('BASE_PATH') . '/packages/classMap');
            }

            if (!empty($classMap)) {
                Composer::getLoader()->addClassMap($classMap);
            }
        }

        parent::init($proxyFileDirPath, $configDir, $handler);
    }

    /**
     * 收集类映射
     *
     * @param string $path 路径
     *
     * @author Verdient。
     */
    protected static function collectClassMap(string $path): array
    {
        $result = [];

        if (is_dir($path)) {
            foreach (new FilesystemIterator($path) as $splFileInfo) {
                foreach (static::collectClassMap($splFileInfo->getPathname()) as $class => $path2) {
                    $result[$class] = $path2;
                }
            }
        } else {
            if (pathinfo($path, PATHINFO_EXTENSION) === 'php') {
                if ($className = static::getClassNameFromFile($path)) {
                    $result[$className] = $path;
                }
            }
        }

        return $result;
    }

    /**
     * 获取文件中的类名
     *
     * @param string $path 文件路径
     *
     * @author Verdient。
     */
    protected static function getClassNameFromFile(string $path): ?string
    {
        $contents = file_get_contents($path);
        $namespace = '';
        $tokens = token_get_all($contents);
        $count = count($tokens);

        for ($i = 0; $i < $count; $i++) {
            if ($tokens[$i][0] === T_NAMESPACE) {
                for ($j = $i + 1; $j < $count; $j++) {
                    if ($tokens[$j][0] === T_NAME_QUALIFIED || $tokens[$j][0] === T_STRING) {
                        $namespace .= $tokens[$j][1];
                    } else if ($tokens[$j] === '{' || $tokens[$j] === ';') {
                        break;
                    }
                }
            }

            if ($tokens[$i][0] === T_CLASS) {
                if ($tokens[$i - 1][0] !== T_PAAMAYIM_NEKUDOTAYIM) {
                    for ($j = $i + 1; $j < $count; $j++) {
                        if ($tokens[$j][0] === T_STRING) {
                            $class = $tokens[$j][1];
                            return $namespace ? $namespace . '\\' . $class : $class;
                        }
                    }
                }
            }
        }

        return null;
    }
}
