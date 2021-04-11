<?php


namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;
use Throwable;

class ErrorController
{
    /**
     * @param Throwable $exception
     * @param DebugLoggerInterface|null $logger
     * @return JsonResponse
     */
    public function show(Throwable $exception, DebugLoggerInterface $logger = null): Response
    {
        $response = new JsonResponse();
        if($exception instanceof NotFoundHttpException)
        {
            $response->setStatusCode(404);
        }
        $response->setStatusCode(400);
        $response->setData([
            'errorMessage' => $exception->getMessage()
        ]);
        $response->setEncodingOptions($response->getEncodingOptions() | JSON_PRETTY_PRINT);

        return $response;
    }
}
