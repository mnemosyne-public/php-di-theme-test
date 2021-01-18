<?php

namespace ThemeName;

use ThemeName\Constants;
use DI\Container;
use ThemeName\Layout\Page;

class App {

    /**
     * PHP-DI Container
     * 
     * @var DI\Container
     */
    private $container;

    /**
     * Construct
     * 
     * @param \WP_Post $post
     */
    public function __construct(public \WP_Post $post) {
        $this->container = Constants::getContainer();
    }

    /**
     * Render
     * 
     * @return string
     */
    public function render(): string {
        $this->container->set('post', $this->post);
        return $this->container->make('Page');
    }
}
