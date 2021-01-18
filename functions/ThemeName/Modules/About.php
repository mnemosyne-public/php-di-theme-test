<?php

namespace ThemeName\Modules;

use ThemeName\Modules\Module;
use ThemeName\Traits\{ Classnames, Util, Fields };

class About extends Module {
    use Classnames, Util, Fields;

    /**
     * ACF Field Schema
     *
     * These are the ACF field keys for the current module/block. 
     * Each of these keys is auto-converted into variables on the current 
     * module/object and are readibly accessible via $this->{name}.
     * 
     * @var array
     */
    private array $fieldSchema = [
        'title',
        'content',
        'image',
    ];

    /**
     * Construct
     *
     * @param array $fields :: ACF Fields
     * @param \Twig\Environment $twig :: Twig Rendering Engine
     */
    public function __construct(
        protected array $fields, 
        protected \Twig\Environment $twig,
        protected ?array $module,
    ) {
        parent::__construct($fields, $twig, $module);
    }

    /**
     * Render
     * 
     * @return string
     */
    public function render(): string {
        return ("
            <div
                class='about__background-image'
                style='background-image: url({$this->image[ 'url' ]})'
            ></div>
            <div class='container'>
                <div class='block__wrapper about__wrapper'>
                    <h1>About Us</h1>
                    <div class='content'>{$this->content}</div>
                </div>
            </div>
        ");
    }
}
