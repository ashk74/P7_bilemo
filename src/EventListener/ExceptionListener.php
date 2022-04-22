<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ExceptionListener
{
    public function onKernelException(ExceptionEvent $event)
    {
        // Get the exception object from the received event
        $exception = $event->getThrowable();

        // Init a new response
        $response = new Response();
        $statusCode = 500;
        $message = $exception->getMessage();
        $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);

        // HttpExceptionInterface is a special type of exception that
        // holds status code and header details
        if ($exception instanceof HttpExceptionInterface) {
            $statusCode = $exception->getStatusCode();
            $message = $exception->getMessage();

            $response->setStatusCode($exception->getStatusCode());
        }

        // Create json content for the response
        $jsonContent = json_encode([
            'code' => $statusCode,
            'message' => $message
        ]);

        $response->setContent($jsonContent);
        $response->headers->replace(['Content-Type' => 'application/json']);

        // Sends the modified response object to the event
        $event->setResponse($response);
    }
}
