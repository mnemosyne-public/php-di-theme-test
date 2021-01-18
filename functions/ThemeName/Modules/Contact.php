<?php

namespace ThemeName\Modules;

use ThemeName\Modules\Module;
use ThemeName\Traits\{ Classnames, Util };
use ThemeName\Elements\{ Image, Button, SocialMedia };
use ThemeName\Elements\Image\ImageFactory;

class Contact extends Module {
    use Classnames, Util;

    /**
     * Template for Item
     */
    protected string $template = 'Contact.html';

    /**
     * Schema Mappings for Twig
     */
    private array $schema = [];

    /**
     * Construct
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
        return $this->twig->render($this->template, [
            'title' => $this->post->post_title,
            'content' => $this->post->post_content,
            'items' => $this->getItems(),
        ]);
    }
}
