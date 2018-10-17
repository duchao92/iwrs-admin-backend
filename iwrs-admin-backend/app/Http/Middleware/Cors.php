<?php

namespace App\Http\Middleware;

use Closure;
use Response;

class Cors
{
    /**
     * 允许访问的域
     *
     * @var array
     */
    private $domains = array(
        'http://6408e0b0.ngrok.io',
        'http://localhost:9528'
    );

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (isset($request->server()['HTTP_ORIGIN'])) {
            $origin = $request->server()['HTTP_ORIGIN'];
            if (in_array($origin, $this->domains)) {
                header('Access-Control-Allow-Credentials: true');
                header('Access-Control-Allow-Origin: '.$origin);
                header('Access-Control-Allow-Methods: GET,PUT,DELETE,POST');
                header('Access-Control-Allow-Headers: Origin, Content-Type, Cookie, Accept, multipart/form-data, application/json, Authorization');
            }
        }

        return $next($request);
    }
}
