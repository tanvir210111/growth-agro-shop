<?php

namespace App\Exceptions;

use Exception;
use Throwable;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\PostTooLargeException;


class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        // Detailed Error Logging
        if ($this->shouldReport($exception)) {
            \Log::error('=============== EXCEPTION CAUGHT ===============');
            \Log::error('Exception Type: ' . get_class($exception));
            \Log::error('Message: ' . $exception->getMessage());
            \Log::error('File: ' . $exception->getFile());
            \Log::error('Line: ' . $exception->getLine());
            \Log::error('URL: ' . request()->fullUrl());
            \Log::error('Method: ' . request()->method());
            \Log::error('IP: ' . request()->ip());
            \Log::error('User ID: ' . (auth()->check() ? auth()->id() : 'Guest'));
            \Log::error('Stack Trace: ' . $exception->getTraceAsString());
            \Log::error('===============================================');
        }
        
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        // Handle file upload size exceeded (POST too large)
        if ($exception instanceof PostTooLargeException) {
            return redirect()->back()
                ->withInput($request->except('update_file'))
                ->with('error', 'ফাইল সাইজ খুব বড়! সার্ভারের upload_max_filesize ও post_max_size ১ GB (১০২৪ MB) হতে হবে। cPanel MultiPHP INI Editor থেকে এগুলো বাড়িয়ে দিন।');
        }
        
        // Log render errors too
        if (app()->environment('local')) {
            \Log::error('Rendering Exception: ' . $exception->getMessage());
        }
        
        return parent::render($request, $exception);
    }

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        if ($request->is('admin') || $request->is('admin/*')) {
            return redirect()->guest('/admin/login');
        }else{
            return redirect()->guest(route('login'));

        }
    }

}
