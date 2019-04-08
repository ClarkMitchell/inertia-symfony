<?php

namespace Inertia;

use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class Inertia
{
    protected $rootView = 'app';
    protected $sharedProps = [];
    private $engine;

    public function __construct(EngineInterface $engine)
    {
        $this->engine = $engine;
    }

    public function setRootView($name)
    {
        $this->rootView = $name;
    }

    public function render($component, $props = [])
    {
        array_walk_recursive(
            $this->sharedProps,
            function (&$item, $key) {
                if (is_callable($item)) {
                    $item = app()->call($item);
                }
            }
        );

        $request = Request::createFromGlobals();

        if ($request->headers->get('X-Inertia')) {
            return new JsonResponse(
                [
                    'component' => $component,
                    'props' => array_merge($this->sharedProps, $props),
                    'url' => Request::getRequestUri(),
                ],
                200,
                [
                    'Vary' => 'Accept',
                    'X-Inertia' => true,
                ]
            );
        }

        return $this->engine->render(
            $this->rootView,
            [
                'component' => $component,
                'props' => array_merge($this->sharedProps, $props),
            ]
        );
    }
}
