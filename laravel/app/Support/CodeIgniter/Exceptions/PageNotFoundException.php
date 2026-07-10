<?php

namespace CodeIgniter\Exceptions;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class PageNotFoundException extends NotFoundHttpException
{
    public static function forPageNotFound(string $message = 'Page not found'): self
    {
        return new self($message);
    }
}
