<?php

namespace ThemeName\Layout;

use ThemeName\Modules\Module;

class Container {

    /**
     * Container Classnames
     */
    public static array $classnames = [
        'container' => [
            'container',
        ],
        'wrapper' => [
            'block__wrapper'
        ],
    ];

    /**
     * Default options
     */
    private static array $defaultOptions = [
        'container' => [],
        'wrapper' => [],
    ];

    /**
     * Instance Options
     */
    private static array $options = [];

    /**
     * Factory
     * 
     * @param  string $html
     * @return callable
     */
    public static function create(Module $module, array $options = []): string {
        self::$options = array_merge(self::$defaultOptions, $options);

        return self::withWrapper($module);
    }

    /**
     * Wraps container div around the inner module.
     * 
     * @param  Module $module
     * @return string
     */
    public static function withContainer(string $html): string {
        if (self::$options[ 'wrapper' ] === false) {
            return $html;
        }

        $containerClassnames = self::getContainerClassnames();
        return ("
            <div class='{$containerClassnames}'>
                {$html} 
            </div>
        ");
    }

    /**
     * Wraps wrapper div around the inner module.
     * 
     * @param  Module $module
     * @return string
     */
    public static function withWrapper(Module $module): string {
        if (self::$options[ 'wrapper' ] === false) {
            return self::withContainer($module->render());
        }

        $wrapperClassnames = self::getWrapperClassnames($module);
        return self::withContainer("
            <div class='{$wrapperClassnames}'>
                {$module->render()} 
            </div>
        ");
    }

    /**
     * Temporary workaround for Classnames, since this is all static, 
     * and annoying to get around.
     * 
     * @param  array $classnames
     * @return string
     */
    protected static function getContainerClassnames(): string {
        $defaultClassnames = self::$classnames[ 'container' ];
        $optionalClassnames = self::$options[ 'container' ][ 'classnames' ] ?? [];
        $classnames = array_merge($defaultClassnames, $optionalClassnames);

        return implode(' ', $classnames);
    }

    /**
     * Temporary workaround for Classnames, since this is all static, 
     * and annoying to get around.
     * 
     * @param Module $module
     * @return string
     */
    protected static function getWrapperClassnames(Module $module): string {
        $defaultClassnames = self::$classnames[ 'wrapper' ];
        $optionalClassnames = self::$options[ 'wrapper' ][ 'classnames' ] ?? [];
        $componentDefaultClassname = "{$module->id}__wrapper";
        $classnames = array_merge($defaultClassnames, $optionalClassnames);
        $classnames[] = $componentDefaultClassname;

        return implode(' ', $classnames);
    }
}
