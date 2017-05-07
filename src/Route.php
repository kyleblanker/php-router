<?php
namespace KyleBlanker\Routing;

class Route
{
    private $method;
    private $path;
    private $handle;
    private $parameters = [];

    public function __construct($method,$path,$handle)
    {
        $this->method = $method;
        $this->path = ltrim($path,'/');
        $this->handle = $handle;
    }

    /**
     * Checks if the uri matches the route
     *
     * @param array $uri_parts list of the uri parts
     * @return boolean
     */
    public function matches(array $uri_parts): bool
    {
        $route_parts = array_filter(preg_split('/\/(?![^\{]*\})/',$this->path));

        if(count($route_parts) !== count($uri_parts))
        {
            return false;
        }

        $parameters = [];

        foreach($route_parts as $index => $part)
        {
            //If matches then it's a variable route part
            if(preg_match('/\{.*?\}/',$part))
            {
                $var = str_replace(['{','}'],'',$part);
                preg_match('~{(.*?):~', $part, $output);

                if(isset($output[1])) {
                    $key = $output[1];
                    $var = str_replace($key . ':','',$var);
                }

                //Check if the variable part is a regular expression
                $regex = @preg_match($var,$uri_parts[$index]);

                //If preg_match returns false, then it isn't regex
                if($regex === false)
                {
                    $parameters[$var] = $uri_parts[$index];
                }
                else
                {
                    //if $regex is 1 then it found a match
                    if($regex === 1)
                    {
                        if(isset($key))
                        {
                            $var = $key;
                        }

                        $parameters[$var] = $uri_parts[$index];
                    }
                    else
                    {
                        return false;
                    }
                }
            }
            else
            {
                if($uri_parts[$index] !== $part)
                {
                    return false;
                }
            }
        }

        $this->parameters = $parameters;
        return true;
    }

    public function getHandle()
    {
        return $this->handle;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getPath(): string
    {
        return $this->path;
    }
}
