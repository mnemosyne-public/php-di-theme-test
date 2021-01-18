<?php

namespace ThemeName\Layout\Block;

use ThemeName\Constants;
use Respect\Validation\Validator;
use zz\Html\HTMLMinify;
use ThemeName\Layout\Container;
use ThemeName\Layout\Block\BlockAttributes;
use ThemeName\Traits\{ ThemeUtil };

class Block {
    use ThemeUtil;

    /**
     * Default Attributes
     */
    protected array $defaultAttributes = [
        'id', 
        'classnames', 
        'background-color', 
        'background-image', 
        'styles',
    ];

    /**
     * Block Attributes
     *
     * @var BlockAttributes
     */
    protected $attributes;

    /**
     * Component Id
     */
    protected string $componentId;

    /**
     * Component Name
     */
    protected string $componentName;

    /**
     * Component: Module Class/View/Controller.
     * 
     * @var mixed
     */
    protected $component;

    /**
     * Construct
     *
     * @param WP_Post $post
     * @param array $fields
     * @param int $index
     * @param string $layout
     */
    public function __construct(
        protected \Twig\Environment $twig,
        protected \WP_Post $post,
        protected ?array $fields = [],
        protected ?int $index = 0,
        protected ?string $layout = '',
    ) {
        $this->componentId = $this->getComponentId();
        $this->componentName = $this->getComponentName();
        $this->attributes = $this->getBlockAttributes();
        $this->component = $this->getComponent();
    }

    /**
     * Get Component Id
     *   - Component names come from ACF in the form of, for example:
     *     - Banner-Hero
     *     - Content
     *  - So they need to be formatted to add to the respective classnames.
     *  - they are just converted to lowercase, basically.
     *
     * @return string
     */
    protected function getComponentId(): string {
        if ($this->isPageTemplate()) {
            return "{$this->layout}-{$this->post->post_type}";
        }

        if (str_contains($this->layout, '-')) {
            return implode('-', \__::chain($this->layout)
                ->split('-')
                ->map(fn ($string) => \__::lowerCase($string))
                ->value());
        }

        return \__::lowerCase($this->layout);
    }

    /**
     * Get Component Name
     *   This is the name of the module class that needs 
     *   to be fetched in order to render() component.
     * 
     * @return string
     */
    protected function getComponentName(): string {
        if ($this->isPageTemplate()) {
            return "{$this->layout}-{$this->post->post_type}";
        }

        if (str_contains($this->componentId, '-')) {
            return implode('', \__::chain($this->componentId)
                ->split('-')
                ->map(fn ($string) => \__::capitalize($string))
                ->value());
        }

        return \__::chain($this->componentId)
            ->camelCase()
            ->capitalize()
            ->value();
    }

    /**
     * Class that handles all attributes:
     *   - id
     *   - classnames
     *   - backgrounds (image/color)
     *   - styles
     *   
     * @return BlockAttributes
     */
    protected function getBlockAttributes(): BlockAttributes {
        $attributes = is_array($this->fields[ 'block-attributes' ])
            ? $this->fields[ 'block-attributes' ]
            : [];

        /**
         * For some reason this field is returning as an array with a single index, 
         * and the actual values are inside that index. So, array[0] = [...values],
         * instead of array[] = [...values].
         *
         * This function sets the attributes to the value of the first index if it exists 
         * and one of the default keys exists on it.
         */
        if (is_array($attrbutes) && array_key_exists(0, $attributes)) {
            foreach ($this->defaultAttributes as $attribute) {
                if (array_key_exists($attribute, $attributes[0])) {
                    $attributes = $this->fields[ 'block-attributes' ][0];
                    break;
                }
            }
        }

        return new BlockAttributes(
            attributes: $attributes,
            componentId: $this->componentId,
            index: $this->index,
        );
    }

    /**
     * If page is single or archived template.
     * 
     * @return bool
     */
    protected function isPageTemplate(): bool {
        return (
            $this->layout === 'single' || 
            $this->layout === 'archive'
        );
    }

    /**
     * Get Background Image
     * 
     * @return string
     */
    protected function getStyles(): string {
        if (!empty($this->backgroundImage['url'])) {
            return "style=\"background-image: url('{$this->backgroundImage['url']}')\"";
        }

        return '';
    }

    /**
     * Validate Component
     *   - Make sure the file/directory/class exists
     *   - Most components are located at modules/{className}, but some have 
     *   multiple component types, and their "entry point" is 
     *   modules/component/component.php. This also delineates between those to 
     *   ensure proper loading.
     * 
     * @param  string $name
     * @return string
     */
    protected function validateComponent(string $name): string {
        $path = Constants::getPath('app.modules', $name);

        // Is it a single/archive page?
        if ($this->isPageTemplate()) {
            $type = ucfirst($this->post->post_type);
            return "ThemeName\\Views\\{$type}\\Single";
        }

        // Is it a file?
        if (Validator::file()->exists()->validate("{$path}.php")) {
            return "ThemeName\\Modules\\{$name}";
        }

        // Is it a directory?
        if (Validator::directory()->exists()->validate($path)) {
            if (Validator::file()->exists()->validate("{$path}/{$name}.php")) {
                return "ThemeName\\Modules\\{$name}\\{$name}";
            }
        }

        throw new \Exception("
            Module with classname, {$path}, either does not exist, or does not have a render method.
        ");

        return '';
    }

    /**
     * Get Module/Component Class
     * 
     * @return mixed
     */
    protected function getComponent() {
        $component = $this->validateComponent($this->componentName);

        try {

            /**
             * Render Typical Blocks
             */
            if (class_exists($component)) {
                if (method_exists($component, 'render')) {
                    return new $component($this->fields, $this->twig, [
                        'id' => $this->componentId,
                        'layout' => $this->layout,
                        'index' => $this->index,
                        'post' => $this->post,
                    ]);
                }
            }
        } catch (\Exception $exception) {
            echo $exception->getMessage();
        }
    }

    /**
     * Render Component
     * 
     * @return string
     */
    protected function renderComponent(): string {
        return Container::create($this->component, []);
    }

    /**
     * Render
     * 
     * @return string
     */
    public function render(): string {
        return $this->twig->render('block.html', [
            'classnames' => $this->attributes->getBlockClassnames(),
            'id' => $this->attributes->getBlockId(),
            'styles' => $this->attributes->getBlockStyles(),
            'component' => $this->renderComponent(),
        ]);
    }
}