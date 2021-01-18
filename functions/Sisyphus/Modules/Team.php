<?php

namespace ThemeName\Modules;

use ThemeName\Modules\Module;
use ThemeName\Traits\{ Classnames, Util, Fields };
use ThemeName\Components\Modals\Modals;
use ThemeName\Functions\Wordpress\PostsFactory;

class Team extends Module {
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
    private array $fieldSchema = [];

    /**
     * Default Team Post Args
     * 
     * @var array
     */
    private array $postArgs = [
        'post_type' => 'team',
    ];

    /**
     * Default Team Post Schema Mappings
     * 
     * @var array
     */
    private array $postSchema = [
        'id' => 'ID',
        'name' => 'post_title',
        'content' => 'post_content',
        'position' => 'fields.position',
        'imageUrl' => 'image.url',
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

        $this->posts = $this->getPosts();
    }

    /**
     * Could I use some DI here via container...?
     */
    protected function getPosts() {
        return (PostsFactory::create([
            'args' => $this->postArgs,
            'schema' => [
                'extends' => 'extended',
                'name' => 'post_title',
                'position' => 'fields.position',
            ],
            'options' => [
                'partition' => 'terms',
            ],
        ]))->findAll();
    }

    /**
     * Render Modals
     * 
     * @return void
     */
    protected function renderModals() {
        foreach ($this->posts as $type => $types) {
            foreach ($types as $index => $post) {
                Modals::add('team', [
                    'template' => 'team',
                    'variables' => $post,
                ]);
            }
        }
    }

    /**
     * Get Posts by Group
     * 
     * @param  array  $posts
     * @param  string $group
     * @return string
     */
    protected function getPostsByGroup(array $posts, string $group): string {
        return $this->mapJoin($posts, fn ($post) => (
            $this->twig->render("Team/items/{$group}.html", $post)
        ));
    }

    /**
     * Render
     * 
     * @return string
     */
    public function render(): string {
        $this->renderModals();

        $primary = $this->posts['primary'];
        $secondary = $this->posts['secondary'];
        $tertiary = $this->posts['tertiary'];

        return $this->twig->render('Team/index.html', [
            'sections' => [
                'primary' => $this->getPostsByGroup($primary, 'primary'),
                'secondary' => $this->getPostsByGroup($secondary, 'secondary'),
                'tertiary' => $this->getPostsByGroup($tertiary, 'tertiary'),
            ],
        ]);
    }
}
