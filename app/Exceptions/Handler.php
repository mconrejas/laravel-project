<?php

namespace Buzzex\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

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
     * @param  \Exception $exception
     *
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Exception $exception
     *
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        $code = 404;
        
        if ($request->ajax()) {
            if ($exception instanceof AuthenticationException) {
                $code = 403;
            }

            if ($exception instanceof InvalidPairException) {
                $code = 422;
            }

            if ($exception instanceof NotEnoughFundsException) {
                $code = 422;
            }

            return response()->json([
                'error'   => $code,
                'message' => $exception->getMessage(),
                'errors'  => method_exists(
                    $exception,
                    'errors'
                ) && !empty($exception->errors()) ? $exception->errors() : [],
            ], $code);
        }

        if (strpos($request->getUri(), 'api') !== false) {
            if ($exception instanceof AuthenticationException) {
                $code = 403;
            }

            $errorData = [
                'error'   => $code,
                'message' => $exception->getMessage(),
            ];

            if (method_exists($exception, 'errors') && !empty($exception->errors())) {
                $errorData['errors'] = $exception->errors();
            }

            return response()->json($errorData, $code);
        }


        return parent::render($request, $exception);
    }
}
