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

        // HttpExceptionInterface is a special type of exception that
        // holds status code and header details
        if ($exception instanceof HttpExceptionInterface) {
            $jsonMessage = json_encode([
                'code' => $exception->getStatusCode(),
                'message' => $exception->getMessage()
            ]);

            $response->setContent($jsonMessage);
            $response->setStatusCode($exception->getStatusCode());
            $response->headers->replace(['Content-Type' => 'application/json']);
        } else {
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Sends the modified response object to the event
        $event->setResponse($response);
    }
}
