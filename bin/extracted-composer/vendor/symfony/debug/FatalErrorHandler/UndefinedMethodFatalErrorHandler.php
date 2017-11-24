<?php










namespace Symfony\Component\Debug\FatalErrorHandler;

use Symfony\Component\Debug\Exception\FatalErrorException;
use Symfony\Component\Debug\Exception\UndefinedMethodException;






class UndefinedMethodFatalErrorHandler implements FatalErrorHandlerInterface
{



public function handleError(array $error, FatalErrorException $exception)
{
preg_match('/^Call to undefined method (.*)::(.*)\(\)$/', $error['message'], $matches);
if (!$matches) {
return;
}

$className = $matches[1];
$methodName = $matches[2];

$message = sprintf('Attempted to call an undefined method named "%s" of class "%s".', $methodName, $className);

if (!class_exists($className) || null === $methods = get_class_methods($className)) {

 return new UndefinedMethodException($message, $exception);
}

$candidates = array();
foreach ($methods as $definedMethodName) {
$lev = levenshtein($methodName, $definedMethodName);
if ($lev <= strlen($methodName) / 3 || false !== strpos($definedMethodName, $methodName)) {
$candidates[] = $definedMethodName;
}
}

if ($candidates) {
sort($candidates);
$last = array_pop($candidates).'"?';
if ($candidates) {
$candidates = 'e.g. "'.implode('", "', $candidates).'" or "'.$last;
} else {
$candidates = '"'.$last;
}

$message .= "\nDid you mean to call ".$candidates;
}

return new UndefinedMethodException($message, $exception);
}
}
