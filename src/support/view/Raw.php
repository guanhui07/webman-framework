<?php
/**
 * This file is part of webman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link      http://www.workerman.net/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace support\view;

use Throwable;
use Webman\View;
use function app_path;
use function array_merge;
use function base_path;
use function config;
use function extract;
use function is_array;
use function ob_end_clean;
use function ob_get_clean;
use function ob_start;
use function request;

/**
 * Class Raw
 * @package support\view
 */
class Raw implements View
{
    /**
     * @var array
     */
    protected static $vars = [];

    /**
     * Assign.
     * @param string|array $name
     * @param mixed $value
     */
    public static function assign($name, $value = null)
    {
        static::$vars = array_merge(static::$vars, is_array($name) ? $name : [$name => $value]);
    }

    /**
     * Render.
     * @param string $template
     * @param array $vars
     * @param string|null $app
     * @return false|string
     */
    public static function render(string $template, array $vars, string $app = null): string
    {
        $request = request();
        $plugin = $request->plugin ?? '';
        $configPrefix = $plugin ? "plugin.$plugin." : '';
        $viewSuffix = config("{$configPrefix}view.options.view_suffix", 'html');
        $app = $app === null ? $request->app : $app;
        $baseViewPath = $plugin ? base_path() . "/plugin/$plugin/app" : app_path();
        $__template_path__ = $app === '' ? "$baseViewPath/view/$template.$viewSuffix" : "$baseViewPath/$app/view/$template.$viewSuffix";

        extract(static::$vars);
        extract($vars);
        ob_start();
        // Try to include php file.
        try {
            include $__template_path__;
        } catch (Throwable $e) {
            static::$vars = [];
            ob_end_clean();
            throw $e;
        }
        static::$vars = [];
        return ob_get_clean();
    }

}
