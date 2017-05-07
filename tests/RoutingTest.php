<?php

use KyleBlanker\Routing\Router;
use KyleBlanker\Routing\Exceptions\RouteNotFoundException;
use KyleBlanker\Routing\Exceptions\MethodNotAllowedException;
use KyleBlanker\Routing\Exceptions\UnsupportedHttpMethodException;

class RoutingTest extends PHPUnit_Framework_TestCase
{
        private function getRouter()
        {
            return new Router();
        }

        public function testRoute()
        {
            $router = $this->getRouter();
            $router->route(['GET'],'route','handle');
            $this->assertEquals(['handle',[]],$router->dispatch('GET','/route'));
        }

        public function testMultipleMethodsRoute()
        {
            $router = $this->getRouter();
            $router->route(['GET','POST'],'route','handle');
            $this->assertEquals(['handle',[]],$router->dispatch('GET','/route'));
            $this->assertEquals(['handle',[]],$router->dispatch('POST','/route'));
        }

        public function testMultipleRoutes()
        {
            $router = $this->getRouter();
            $router->route(['GET'],'route','handle');
            $router->route(['POST'],'route2','handle');
            $this->assertEquals(['handle',[]],$router->dispatch('GET','/route'));
            $this->assertEquals(['handle',[]],$router->dispatch('POST','/route2'));
        }

        public function testMagicRoutes()
        {
            $router = $this->getRouter();
            $router->any('route','handle');
            $router->get('route2','handle');
            $this->assertEquals(['handle',[]],$router->dispatch('GET','/route'));
            $this->assertEquals(['handle',[]],$router->dispatch('POST','/route'));
            $this->assertEquals(['handle',[]],$router->dispatch('PUT','/route'));
            $this->assertEquals(['handle',[]],$router->dispatch('DELETE','/route'));
            $this->assertEquals(['handle',[]],$router->dispatch('HEAD','/route'));
            $this->assertEquals(['handle',[]],$router->dispatch('PATCH','/route'));
            $this->assertEquals(['handle',[]],$router->dispatch('GET','/route2'));
        }

        public function testNotFoundRoute()
        {
            $this->expectException(RouteNotFoundException::class);
            $router = $this->getRouter();
            $router->route(['GET'],'route','handle');
            $router->dispatch('GET','/route/123');
        }

        public function testVariableRoute()
        {
            $router = $this->getRouter();
            $router->route(['GET'],'route/{variable}/{variable2}','handle');
            $this->assertEquals(['variable' => 'dynamic','variable2' => 'variables'],$router->dispatch('GET','/route/dynamic/variables')[1]);
        }

        public function testRegexRoute()
        {
            $router = $this->getRouter();
            $router->route(['GET'],'route/{id:/^[0-9]+$/}','handle');
            $this->assertEquals(['handle',['id' => 123]],$router->dispatch('GET','/route/123'));
            $this->expectException(RouteNotFoundException::class);
            $router->dispatch('GET','/route/abc');
        }


        public function testGroupRoutes()
        {
            $router = $this->getRouter();
            $router->group('/group', function($router){
                $router->route(['GET'],'/child','handle');
                $router->group('/child-group', function($router){
                    $router->route(['GET'],'/child','handle');
                });
                $router->route(['GET'],'/child2','handle');
            });

            $this->assertEquals(['handle',[]],$router->dispatch('GET','group/child'));
            $this->assertEquals(['handle',[]],$router->dispatch('GET','group/child-group/child'));
            $this->assertEquals(['handle',[]],$router->dispatch('GET','group/child2'));
        }

        public function testNotAllowedRoutes()
        {
            $this->expectException(MethodNotAllowedException::class);
            $router = $this->getRouter();
            $router->route(['GET'],'route','handle');
            $router->dispatch('POST','/route');
        }

        public function testUnsupportedMethodRoutes()
        {
            $this->expectException(UnsupportedHttpMethodException::class);
            $router = $this->getRouter();
            $router->route(['UNSUPPORTED'],'route','handle');
        }
}
