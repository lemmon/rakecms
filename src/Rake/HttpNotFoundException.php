<?php

namespace Rake;

class HttpNotFoundException extends HttpException
{
    protected $message = 'Page not found.';
    protected $httpCode = 404;
}