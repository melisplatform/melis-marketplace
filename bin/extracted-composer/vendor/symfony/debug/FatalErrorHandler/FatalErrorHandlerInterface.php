<?php










namespace Symfony\Component\Debug\FatalErrorHandler;

use Symfony\Component\Debug\Exception\FatalErrorException;






interface FatalErrorHandlerInterface
{








public function handleError(array $error, FatalErrorException $exception);
}
