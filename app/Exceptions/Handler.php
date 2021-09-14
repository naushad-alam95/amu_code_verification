<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Redirect;

class Handler extends ExceptionHandler
{
    
    protected $dontReport = [
        
    ];
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    public function render($request, Exception $e)
	{
	  if ($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) 
	  {
	    Redirect::away(env('FRONT_URL_404'));
	  } 
	 return parent::render($request, $e); 
    }
    
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        return redirect()->guest('login');
    }
}
