<?php

namespace ThemeName\Layout;

use ThemeName\Constants;
use ThemeName\Traits\{ Classnames, Util };
use ThemeName\Assets\{ Scripts };
use ThemeName\Layout\{ Head, Header, Footer };
use ThemeName\Layout\Block\Block;

class Page {
    use Classnames, Util;

    /**
     * Wordpress Post Id
     */
    protected string $postId;

    /**
     * Page Attributes
     *   - id, name, type
     */
    protected object $attributes;

    /**
     * Array of Blocks/Sections/Modules
     */
    protected array $blocks;

    /**
     * Construct
     *
     * @param WP_Post $post
     */
    public function __construct(
        public \WP_Post $post,
        public \Twig\Environment $twig,
    ) {
        $this->postId = $this->post->ID;
        $this->attributes = $this->getPageAttributes();
        $this->blocks = $this->getBlocks();
    }

    /**
     * Get Blocks
     *
     * TODO: Error Handling for this.
     *
     * @return Array<Block>
     */
    protected function getBlocks(): array {
        return \__::reduce(get_field('blocks', $this->postId), 
            function ($blocks, $block, $index) {
                $layout = array_splice($block, 0, 1)[ 'acf_fc_layout' ];
                return \__::concat($blocks, [ 
                    $layout => new Block(
                        twig: $this->twig,
                        post: $this->post,
                        fields: $block,
                        index: $index,
                        layout: $layout,
                    )
                ]);
            }, []);
    }

    /**
     * Get Page Attributes
     * 
     * @return object
     */
    protected function getPageAttributes(): object {
        return (object)[
            'id' => $this->postId,
            'name' => $this->post->post_name,
            'type' => $this->getPageType(),
        ];
    }

    /**
     * Get Page Type
     * 
     * @return array|string
     */
    protected function getPageType(): array|string {
        if ($this->isHomePage()) {
            return 'home';
        }

        if (\is_single($this->postId)) {
            return 'single';
        }

        if (\is_post_type_archive($this->postId)) {
            return 'archive';
        }

        if (\is_404()) {
            return 'error';
        }

        return [];
    }

    /**
     * Is this the home page?
     * 
     * @return bool
     */
    protected function isHomePage(): bool {
        return (\is_home() || \is_front_page());
    }

    /**
     * Get Page Classnames
     *
     * @param object $attributes
     * @return string
     */
    protected function getPageClassnames(object $attributes): string {
        $classnames = [
            "page",
            "page-id-{$post->id}",
            "post-name-{$post->name}"
        ];

        if (is_array($post->type)) {
            $classnames[] = "page-type-{$attributes->type[0]}";
            $classnames[] = "page-post-type-{$attributes->type[1]}";
        }

        else {
            $classnames[] = "page-type-{$attributes->type}";
        }

        return $this->getClassnames($classnames);
    }

    /**
     * Get all modules/blocks/sections for current page
     *
     * TODO: write a template to show errors in development?
     * 
     * @return
     */
    protected function renderBlocks(): string {
        if ($this->attributes?->type === 'single') {
            $Block = new Block(
                twig: $this->twig,
                post: $this->post,
                fields: get_fields($this->post->ID) ?: [],
                layout: $this->attributes->type,
            );

            return $Block->render();
        }

        return $this->mapJoin($this->blocks, fn ($Block) => $Block->render());
    }

    /**
     * Render
     * 
     * Need to auto-inject these kinds of settings, probably through something 
     * like theme-settings/config.php => via php-di
     * 
     * @return string
     */
    public function render(): string {
        return $this->twig->render('index.html', [
            'languageAttributes' => get_language_attributes(),
            'head' => (new Head([ 'title' => 'Pavilion Data' ]))->render(),
            'header' => (new Header())->render(),
            'footer' => (new Footer())->render(),
            'wp_footer' => Scripts::getWPFooter(),
            'blocks' => $this->renderBlocks(),
            'classnames' => $this->getPageClassnames($this->attributes),
        ]);
    }
}