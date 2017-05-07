<?php
namespace KyleBlanker\Routing;

class Router
{
    const ROUTE_HANDLE = 0;
    const ROUTE_PARAMETERS = 1;

    /**
     * List of supported Http methods
     *
     * @var array $methods
     */
    private $methods = [
        'GET',
        'POST',
        'PUT',
        'DELETE',
        'HEAD',
        'PATCH',
        'OPTIONS',
        'CONNECT'
    ];

    /**
     * The list of the router's routes keyed by the HTTP method
     *
     * @var array $routes
     */
    private $routes = [
        'GET' => [],
        'POST' => [],
        'PUT' => [],
        'DELETE' => [],
        'HEAD' => [],
        'PATCH' => [],
        'OPTIONS' => [],
        'CONNECT' => []
    ];

    /**
     * The group prefix, used for route groups
     *
     * @var string $groupPrefix
     */
    private $groupPrefix;

    /**
     * Calls the createRoute method with the correct HTTP methods
     *
     * @param  string $methods array of the route's http methods
     * @param  string $path    the route path
     * @param  mixed $handle  the route handle
     * @return self
     */
    public function route($methods,$path,$handle)
    {
        $methods = (array)$methods;

        //If any of the methods are 'any' then all methods will be included.
        if(in_array('any', array_map('strtolower', $methods)))
        {
            $methods = $this->methods;
        }

        foreach($methods as $method)
        {
            $this->createRoute($method,$path,$handle);
        }

        return $this;
    }

    /**
     * Returns the routes property array
     * @return array the list of routes
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * Will set the routes property with the array provided.
     * @param array $routes List of http routes
     * @return self
     */
    public function setRoutes(array $routes)
    {
        $this->routes = $routes;

        return $this;
    }

    /**
     * Creates a route object and stores it in the routes property
     *
     * @param  string $method [description]
     * @param  string $path   [description]
     * @param  mixed $handle the route handle, could be a closure or anything.
     * @return void
     */
    private function createRoute($method,$path,$handle)
    {
        $method = strtoupper($method);

        if(!in_array($method,$this->methods))
        {
            $msg = sprintf('%s is not a supported HTTP method.',$method);
            throw new Exceptions\UnsupportedHttpMethodException($msg);
        }

        $route = $this->groupPrefix . $path;

        $this->routes[$method][$route] = new Route($method,$route,$handle);
    }

    /**
     * Creates routes with a group pefix
     *
     * @param  string $prefix  what to prefix to the route uri
     * @param  Closure $closure
     * @return self
     */
    public function group($prefix,$closure)
    {
        $original = $this->groupPrefix;
        $this->groupPrefix = $original . $prefix;
        $closure($this);
        $this->groupPrefix = $original;

        return $this;
    }

    /**
     * Dispatches the method and uri to retrieve the appropriate route
     *
     * @param  string $method The HTTP request method
     * @param  string $uri    The request uri
     * @return mixed         An array of the [handle,params] or false
     */
    public function dispatch($method,$uri)
    {
        $uri_parts = array_values(array_filter(explode('/',$uri)));
        $route = $this->findMethodMatch($method,$uri_parts);

        if($route === false)
        {
            $route = $this->findAnyMatch($method,$uri_parts);

            if($route === false)
            {
                $msg = sprintf('Unable to match a route for: "%s".',$uri);
                throw new Exceptions\RouteNotFoundException($msg);
            }

            $msg = sprintf('Method: %s not allowed for route: %s',$method,$route->getPath());
            throw new Exceptions\MethodNotAllowedException($msg);
        }

        return [$route->getHandle(),$route->getParameters()];
    }

    /**
     * Returns a matching route that uses the provided HTTP Method
     *
     * @param  string $request_method The HTTP request method
     * @param  string $uri_parts      The request uri split into an array
     * @return mixed                 Route or false
     */
    public function findMethodMatch($request_method,$uri_parts)
    {
        foreach($this->routes[$request_method] as $route)
        {
            $match = $route->matches($uri_parts);

            if($match)
            {
                return $route;
            }
        }

        return false;
    }

    /**
     * Returns a matching route that doesn't use the provided HTTP Method
     * @param  string $request_method The HTTP request method
     * @param  string $uri_parts      The request uri split into an array
     * @return mixed                 Route or false
     */
    public function findAnyMatch($request_method,$uri_parts)
    {
        foreach($this->routes as $method => $routes)
        {
            if($method === $request_method)
            {
                continue;
            }

            foreach($routes as $route)
            {
                $match = $route->matches($uri_parts);

                if($match)
                {
                    return $route;
                }
            }
        }

        return false;
    }

    /**
     * Allows the ability to call $router->{HTTP_METHOD} instead of using the route method
     *
     * @param string $method HTTP method
     * @param array $arguments the route and route handle
     * @return self
     */
    public function __call($method,$arguments = array())
    {
        array_unshift($arguments,$method);
        return call_user_func_array(array($this,'route'), $arguments);
    }
}
