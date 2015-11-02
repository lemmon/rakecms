<?php

namespace Rake;

abstract class HttpException extends Exception implements HttpExceptionInterface
{


    function getHttpCode()
    {
        return $this->httpCode;
    }
}