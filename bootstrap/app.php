<?php


require __DIR__ . '/../app/Exceptions/handleJsonException.php';


use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\RouteNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\RoleMiddleware;



return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
         then: function () {
            Route::middleware('api')
                ->prefix('api/admin')
                ->group(base_path('routes/Api/Admin.php'));

                Route::middleware('api')
                ->prefix('api/general')
                ->group(base_path('routes/Api/Customer.php'));
        },


        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(SetLocale::class);
        $middleware->alias([
            'role' => RoleMiddleware::class
        ]);


    })
    ->withExceptions(function (Exceptions $exceptions): void {
        


          $exceptions->render(function (Throwable $exception, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return handleJsonException($exception);
            }
            return null;
        });



    })->create();
