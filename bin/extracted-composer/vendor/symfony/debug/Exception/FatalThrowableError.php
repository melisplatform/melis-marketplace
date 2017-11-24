<?php










namespace Symfony\Component\Debug\Exception;






class FatalThrowableError extends FatalErrorException
{
public function __construct(\Throwable $e)
{
if ($e instanceof \ParseError) {
$message = 'Parse error: '.$e->getMessage();
$severity = E_PARSE;
} elseif ($e instanceof \TypeError) {
$message = 'Type error: '.$e->getMessage();
$severity = E_RECOVERABLE_ERROR;
} else {
$message = $e->getMessage();
$severity = E_ERROR;
}

\ErrorException::__construct(
$message,
$e->getCode(),
$severity,
$e->getFile(),
$e->getLine()
);

$this->setTrace($e->getTrace());
}
}
