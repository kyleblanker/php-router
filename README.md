This is me just messing around, I probably wouldn't use this in a project

# PHP Router
A simple PHP router.

### Supported HTTP Methods
- GET
- POST
- PUT
- DELETE
- HEAD
- PATCH
- OPTIONS
- CONNECT

## Examples

### Creating the router
```PHP
$router = new \KyleBlanker\Router();
```

### Creating a basic route
```PHP
$router = new \KyleBlanker\Router();

$router->route('GET','/my-route',function(){
    echo 'This is my route';
});

```

### Route Variables

Route variables are enclosed in ```{}```.

```PHP
$router = new \KyleBlanker\Router();

$router->route('GET','/my-route/{variable}',function($variable){
    echo 'This is my route variable ' . $variable;
});
```
### Routing Groups

Routing groups prefix the group path on the route path.

```PHP
$router = new \KyleBlanker\Router();

$router->group('/my-group', function($router){
    $router->route('GET','/my-route',function(){
        echo 'This is my route';
    });
});

```

This will create a route with ```/my-group/my-route```.

### Regular Expressions

```PHP
$router = new \KyleBlanker\Router();

$router->route('GET','/my-route/{id:/[^0-9]/}',function($id){
    echo 'This is my route';
});

```

This route will only allow the numbers 0-9 after /my-route
The regular expressions key is defined from what's infront of the  ```:```

### Alternative route calls

```PHP
$router = new \KyleBlanker\Router();

$router->get('/get-route',function(){
    echo 'This is my get route';
});

$router->post('/post-route',function(){
    echo 'This is my post route';
});

$router->any('/any-route',function(){
    echo 'This is my any route';
});
```

The router is setup to allow you to call methods based on the HTTP methods supported.
It also allows you to call ```$router->any()``` which will create a route for all the http methods supported.

### Dispatch the router
```PHP
$router = new \KyleBlanker\Router();

$router->route('GET','/my-route/{id:/[^0-9]/}',function($id){
    echo 'This is my route';
});

try
{
    $response = $router->dispatch($_SERVER['REQUEST_METHOD'],$_SERVER['REQUEST_URI']);
}
catch(\KyleBlanker\Routing\Exceptions\RouteNotFoundException $e)
{
    // A route was not found
}
catch(\KyleBlanker\Routing\Exceptions\MethodNotAllowedException $e)
{
    // A route was found, but the http method was not supported.
}

$route_handler = $response[Router::ROUTE_HANDLE];
$route_parameters = $response[Router::ROUTE_PARAMETERS];
```
If no match is found then ```\KyleBlanker\Routing\Exceptions\RouteNotFoundException ``` will be thrown.
If a match is found but the route does not support that HTTP method then ```\KyleBlanker\Routing\Exceptions\MethodNotAllowedException``` will be thrown.
The route handler can be what ever you want, a string to call some sort of controller controller or maybe a closure. This package will leave that up to you.
The route parameters is an associative array with the keys being what were provided in the route path so ```/my-route/{name}{id:/[^0-9]/}``` might be ```['name' => 'kyle', 'id' => 1]```.
