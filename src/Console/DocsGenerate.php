<?php

namespace Ronanflavio\LaradocsGenerate\Console;

use Illuminate\Console\Command;

class DocsGenerate extends Command
{
    /**
     * Regex pattern to clean PHPDocs text
     *
     * @var string
     */
    protected $docsPattern = '([a-zA-Z@]+\s*[a-zA-Z0-9, ()_].*)';

    /**
     * Path from JSON file used to store routes parameters
     *
     * @var string
     */
    protected $jsonFile;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'docs:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates the API docs of the application';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->jsonFile = base_path('resources/routes.json');
    }

    /**
     * Execute the console command.
     *
     * @return mixed|void
     */
    public function handle()
    {
        $routes = $this->getRoutes();
        $routes = $this->filterRoutes($routes);
        $routes = $this->setRoutes($routes);

        file_put_contents(
            $this->jsonFile,
            \GuzzleHttp\json_encode($routes)
        );

        $this->info('The documentation has been generated successfully.');
    }

    protected function setRoutes(array $routes)
    {
        foreach ($routes as $key => &$route) {
            $action = explode('@', $route->action);
            if (sizeof($action) == 2) {
                $reflector = new \ReflectionClass($action[0]);
                $route->method = $this->setMethod($route->method);
                $route->docs = $this->setDocs($reflector, $action);
                $route->group = $this->setGroups($reflector);
                $route->headers = $this->setHeaders($route);
            }
        }

        return $routes;
    }

    protected function setMethod(string $method)
    {
        $methods = explode('|', $method);

        if ($methods[0] == 'GET') {
            return $methods[0];
        }

        return $method;
    }

    protected function setHeaders($route)
    {
        $middleware = explode(',', $route->middleware);

        foreach ($middleware as $m) {
            if ($m == 'auth:api') {
                return [
                    'name' => 'Authorization',
                    'value' => '(token)'
                ];
            }
        }

        return null;
    }

    protected function setDocs(\ReflectionClass $reflector, $action)
    {
        $docComment = $reflector->getMethod($action[1])->getDocComment();
        preg_match_all($this->docsPattern, $docComment, $docs);
        $docs = $this->setDtos($docs[0]);
        return $docs;
    }

    protected function setGroups(\ReflectionClass $reflector)
    {
        preg_match_all($this->docsPattern, $reflector->getDocComment(), $group);
        $group = $group[0];
        $group = is_array($group)
            ? (!empty($group) ? trim($group[0]) : $reflector->getShortName())
            : trim($group);

        return $group;
    }

    protected function filterRoutes(array $routes)
    {
        $config = config('docs');

        foreach ($routes as $key => $route) {

            if (!empty($config['excluded'])) {
                foreach ($config['excluded'] as $excluded) {
                    /** If has asterisk, it must consider the substring value */
                    if (strpos($excluded, '*') !== false) {
                        if (strpos($route->uri, str_replace('*', '', $excluded)) !== false) {
                            unset($routes[$key]);
                        }
                    } else {
                        if ($route->uri == $excluded) {
                            unset($routes[$key]);
                        }
                    }
                }
            }
        }

        return $routes;
    }

    protected function setDtos($docs)
    {
        $result = [];

        foreach ($docs as $doc) {
            if (strpos($doc, '@request') !== false) {
                $class = explode('@request ', $doc)[1];
                $result['request'] = $this->setProperties(trim($class));
            } elseif (strpos($doc, '@response') !== false) {
                $class = explode('@response ', $doc)[1];
                $result['response'] = $this->setProperties(trim($class));
            } elseif (strpos($doc, '@param') !== false) {
                $class = explode('@param ', $doc)[1];
                $result['params'][] = $this->setProperties(trim($class));
            } else {
                $result['description'] = trim($doc);
            }
        }

        return $result;
    }

    protected function setProperties(string $class)
    {
        if (class_exists($class)) {
            $reflector = new \ReflectionClass($class);
            $properties = $reflector->getProperties(\ReflectionProperty::IS_PUBLIC);
            return $this->setRequestResponse($properties);

        } elseif (strpos($class, ' $') !== false) {
            return $this->setParams($class);
        }

        return $class;
    }

    protected function setParams(string $params)
    {
        $param = explode(' $', $params);
        return [
            'type' => $param[0],
            'name' => $param[1]
        ];
    }

    protected function setRequestResponse(array $properties)
    {
        $obj = [];
        /** @var \ReflectionProperty $property */
        foreach ($properties as $property) {
            /** Clean the PHPDocs */
            preg_match_all($this->docsPattern, $property->getDocComment(), $docs);
            /** Initializing the vars */
            $type = $example = null;
            /** Iterating list of PHPDocs for each attribute of the class */
            foreach ($docs[0] as $doc) {
                if (strpos($doc, '@var') !== false) {
                    /** Find which is the var type */
                    $type = explode('@var ', $doc)[1];
                } elseif (strpos($doc, '@example') !== false) {
                    /** Find the attribute example */
                    $example = explode('@example ', $doc)[1];
                }
            }
            /** Sets the default object */
            $prop = [
                'type' => trim($type),
                'example' => trim($example),
                'name' => $property->getName()
            ];

            $attributesFromClass = $this->classNameCheck($type);
            if (!empty($attributesFromClass)) {
                $prop['attributes'] = $attributesFromClass;
            }

            $obj[] = $prop;
        }

        return $obj;
    }

    protected function classNameCheck(string $type)
    {
        $type = trim($type);

        $attributes = [];
        /** If the attribute is a class, find the attributes of that class */
        if (class_exists(trim($type))) {
            $attributes = $this->setProperties($type);
        } else {
            /** Check if string starts with slash and remove, if it is the case */
            if ($type[0] === '\\') {
                $type = substr($type, 1);
                if (class_exists($type)) {
                    $attributes = $this->setProperties($type);
                }
            }
        }

        return $attributes;
    }

    protected function getRoutes()
    {
        \Artisan::call('route:list --json');
        return \GuzzleHttp\json_decode(\Artisan::output());
    }
}
