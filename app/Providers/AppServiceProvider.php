<?php

namespace App\Providers;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Response::macro('success', function ($data, $message, $status_code = 200) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $data
            ], $status_code);
        });

        Response::macro('error', function ($error, $message, $status_code = 400) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'error' => $error
            ], $status_code);
        });
    }
}
