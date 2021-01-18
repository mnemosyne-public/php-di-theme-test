<?php

namespace ThemeName\Modules;

use ThemeName\Traits\{ Classnames, Util, Fields };

/**
 * Module Interface
 */
interface ModuleInterface {

    /**
     * Render
     * 
     * @return string
     */
    public function render(): string;
}

/**
 * Module Parent Class
 */
class Module implements ModuleInterface {
    use Classnames, Util, Fields;

    /**
     * Component Id
     */
    public string $componentId;

    /**
     * Component Layout
     */
    public string $layout;

    /**
     * Component Index
     */
    public int $index;

    /**
     * Construct
     *
     * @param array $fields :: ACF Fields
     * @param \Twig\Environment $twig :: Twig Rendering Engine
     * @param ?array $module :: Module meta information (id, index, layout, etc.)
     */
    public function __construct(
        protected array $fields, 
        protected \Twig\Environment $twig,
        protected ?array $module,
    ) {
        $this->getFieldValues();
        $this->getModuleValues();
    }

    /**
     * Set all passed module values on object.
     */
    private function getModuleValues() {
        if (property_exists($this, 'module') && is_array( $this->module )) {
            foreach ($this->module as $key => $value) {
                $this->{$key} = $value;
            }
        }
    }

    /**
     * Debug current module/block.
     * 
     * @return void
     */
    protected function debug(array|string $args = []) {
        if (empty( $args )) {
            \Kint\Kint::dump( $this );
        }

        else if (is_string( $args ) && $args === 'fields') {
            \Kint\Kint::dump($this->fields);
        }

        else {
            \Kint\Kint::dump($args);
        }
    }

    /**
     * Render
     * If no method exists in child, default to returning an empty string.
     * 
     * @return string
     */
    public function render(): string {
        return '';
    }
}
