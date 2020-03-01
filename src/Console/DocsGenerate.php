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
    protected $description = 'Gera documentação API da aplicação';

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
        $rotas = $this->getRoutes();
        $rotas = $this->filterRoutes($rotas);
        $rotas = $this->setRoutes($rotas);

        file_put_contents(
            $this->jsonFile,
            \GuzzleHttp\json_encode($rotas)
        );

        $this->info('The documentation has been generated successfully.');
    }

    protected function setRoutes(array $rotas)
    {
        foreach ($rotas as $key => &$rota) {
            $action = explode('@', $rota->action);
            if (sizeof($action) == 2) {
                $reflector = new \ReflectionClass($action[0]);
                $rota->method = $this->setMethod($rota->method);
                $rota->docs = $this->setDocs($reflector, $action);
                $rota->group = $this->setGroups($reflector);
                $rota->headers = $this->setHeaders($rota);
            }
        }

        return $rotas;
    }

    protected function setMethod(string $method)
    {
        $methods = explode('|', $method);

        if ($methods[0] == 'GET') {
            return $methods[0];
        }

        return $method;
    }

    protected function setHeaders($rota)
    {
        $middleware = explode(',', $rota->middleware);

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

    protected function filterRoutes(array $rotas)
    {
        $config = config('docs');

        foreach ($rotas as $key => $rota) {

            if (!empty($config['excluded'])) {
                foreach ($config['excluded'] as $excluded) {
                    /** If has asterisk, it must consider the substring value */
                    if (strpos($excluded, '*') !== false) {
                        if (strpos($rota->uri, str_replace('*', '', $excluded)) !== false) {
                            unset($rotas[$key]);
                        }
                    } else {
                        if ($rota->uri == $excluded) {
                            unset($rotas[$key]);
                        }
                    }
                }
            }
        }

        return $rotas;
    }

    protected function setDtos($docs)
    {
        $resultado = [];

        foreach ($docs as $doc) {
            if (strpos($doc, '@request') !== false) {
                $class = explode('@request ', $doc)[1];
                $resultado['request'] = $this->setProperties(trim($class));
            } elseif (strpos($doc, '@response') !== false) {
                $class = explode('@response ', $doc)[1];
                $resultado['response'] = $this->setProperties(trim($class));
            } elseif (strpos($doc, '@param') !== false) {
                $class = explode('@param ', $doc)[1];
                $resultado['params'][] = $this->setProperties(trim($class));
            } else {
                $resultado['description'] = trim($doc);
            }
        }

        return $resultado;
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

    protected function setRequestResponse($properties)
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
                    /** Busca qual o tipo da variável */
                    $type = explode('@var ', $doc)[1];
                } elseif (strpos($doc, '@example') !== false) {
                    /** Busca o exemplo de preenchimento do campo */
                    $example = explode('@example ', $doc)[1];
                }
            }
            /** Sets the default object */
            $prop = [
                'type' => trim($type),
                'example' => trim($example),
                'name' => $property->getName()
            ];
            /** If the attribute is a class, find the attributes of that class */
            if (class_exists($type)) {
                $prop['attributes'] = $this->setProperties($type);
            }
            $obj[] = $prop;
        }

        return $obj;
    }

    protected function getRoutes()
    {
        \Artisan::call('route:list --json');
        return \GuzzleHttp\json_decode(\Artisan::output());
    }
}
