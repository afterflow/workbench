<?php

namespace Afterflow\Recipe;

use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Translation\FileLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\FileViewFinder;

class Recipe
{

    protected $input = [];
    protected $props = [];
    protected $template;
    /**
     * Use another class to render the result
     * @var string
     */
    protected $renderOn;

    /**
     * Recipe constructor.
     *
     * @param array $input
     */
    public function __construct($input = [])
    {
        $this->input = array_replace($this->input, $input);
    }

    public function __toString()
    {
        return $this->render();
    }

    public function input($key = null, $value = null)
    {

        if ($value !== null) {
            $this->input[ $key ] = $value;

            return $this;
        }

        if ($key) {
            if (! isset($this->input[ $key ])) {
                throw new \Exception($key . ' was not provided in the input');
            }

            return $this->input[ $key ];
        }

        return $this->input;
    }

    public function data($key = null)
    {

        if ($key) {
            // apply prop to input
            $prop  = $this->props[ $key ];
            $value = null;

            // apply default
            if (! isset($this->input[ $key ])) {
                // this parameter is absent, let's check if we have a default

                if (isset($prop[ 'default' ])) {
                    $value = $prop[ 'default' ];
                }
                // no value and no default
            } else {
                $value = $this->input($key);
            }
            //          if( $key == 'arguments')
            //          {
            //              dd($prop, $value);
            //          }

            // do some validation

            $rules      = $prop[ 'rules' ] ?? [];
            $validation = $this->validation($key, $value, $rules);
            if ($validation->fails()) {
                throw new \Exception(static::class . ': ' . $validation->errors()->first());
            }

            return $value;
        }

        // For inline recipes
        if (empty($this->props)) {
            return $this->input();
        }

        $data = [];
        foreach (array_keys($this->props) as $key) {
            $data[ $key ] = $this->data($key);
        }

        return $data;
    }

    protected function validation($key, $value = null, $rules = [])
    {

        $loader     = new FileLoader(new Filesystem(), 'lang');
        $translator = new Translator($loader, 'en');
        $validation = new Factory($translator, new Container());

        return $validation->make([ $key => $value ], [ $key => $rules ]);
    }


    /**
     * @param array $input
     *
     * @return static
     */
    public static function make($input = [])
    {
        return new static($input);
    }

    /**
     * @param array $input
     * @param null $to
     *
     * @return string
     */
    public static function quickRender($input = [], $to = null)
    {
        return static::make($input)->render($to);
    }

    /**
     * @param array $input
     *
     * @return array
     */
    public static function quickBuild($input = [])
    {
        return static::make($input)->build();
    }

    /**
     * @param $string
     * @param int $spaces
     *
     * @return string
     */
    public static function indent($string, $spaces = 4)
    {
        $tab = '';
        for ($i = 0; $i < $spaces; $i++) {
            $tab .= ' ';
        }

        return $tab . collect(explode(PHP_EOL, $string))->implode(PHP_EOL . $tab);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function build()
    {

        return $this->data();
    }

    /**
     * @param $template
     *
     * @return $this
     */
    public function template($template)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * @param $input
     *
     * @return $this
     */
    public function with($input)
    {
        $this->input = array_merge($this->input, $input);

        return $this;
    }

    public static function sequence($things, $glue = ', ')
    {
        return implode($glue, $things);
    }

    public static function array($things, $tags = [ '[', ']' ])
    {

        return $tags[ 0 ] . static::sequence($things) . $tags[ 1 ];
    }

    public function map(array $fields)
    {

        return collect($this->data())->only($fields)->toArray();
    }

    /**
     * @param null $saveTo
     *
     * @return string
     */
    public function render()
    {
        $input = $this->build();

        if ($renderOn = $this->renderOn) {
            return $renderOn::quickRender($input);
        }

        if (! $this->template && ( get_class($this) != self::class )) {
            throw new \Exception(static::class . ' does not provide a template');
        }

        if (method_exists($this, 'dataForTemplate')) {
            $input = $this->dataForTemplate();
        }

        // Initialize Blade
        $viewResolver = new EngineResolver();
        $viewResolver->register('blade', function () {
            $blade_compiler = new BladeCompiler(new Filesystem(), '/tmp');
            $blade_compiler->directive('startPhp', function ($v) {
                return '{!! "<?p"."hp" !!}';
            });
            $blade_compiler->directive('sequence', function ($v) {
                return '\Afterflow\Recipe\Recipe::sequence(' . $v . ')';
            });
            $blade_compiler->directive('indent', function ($v) {
                return '\Afterflow\Recipe\Recipe::indent(' . $v . ')';
            });

            return new CompilerEngine($blade_compiler);
        });
        $viewFactory = new \Illuminate\View\Factory($viewResolver, new FileViewFinder(new Filesystem(), [ '/tmp' ]), new Dispatcher(new Container()));

        // Render
        return $viewFactory->file($this->template, $input)->render();
    }
}
