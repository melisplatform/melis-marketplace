<?php










namespace Symfony\Component\HttpKernel\Exception;










class FatalErrorException extends \ErrorException
{
}

namespace Symfony\Component\Debug\Exception;

use Symfony\Component\HttpKernel\Exception\FatalErrorException as LegacyFatalErrorException;






class FatalErrorException extends LegacyFatalErrorException
{
public function __construct($message, $code, $severity, $filename, $lineno, $traceOffset = null, $traceArgs = true, array $trace = null)
{
parent::__construct($message, $code, $severity, $filename, $lineno);

if (null !== $trace) {
if (!$traceArgs) {
foreach ($trace as &$frame) {
unset($frame['args'], $frame['this'], $frame);
}
}

$this->setTrace($trace);
} elseif (null !== $traceOffset) {
if (function_exists('xdebug_get_function_stack')) {
$trace = xdebug_get_function_stack();
if (0 < $traceOffset) {
array_splice($trace, -$traceOffset);
}

foreach ($trace as &$frame) {
if (!isset($frame['type'])) {

 if (isset($frame['class'])) {
$frame['type'] = '::';
}
} elseif ('dynamic' === $frame['type']) {
$frame['type'] = '->';
} elseif ('static' === $frame['type']) {
$frame['type'] = '::';
}


 if (!$traceArgs) {
unset($frame['params'], $frame['args']);
} elseif (isset($frame['params']) && !isset($frame['args'])) {
$frame['args'] = $frame['params'];
unset($frame['params']);
}
}

unset($frame);
$trace = array_reverse($trace);
} elseif (function_exists('symfony_debug_backtrace')) {
$trace = symfony_debug_backtrace();
if (0 < $traceOffset) {
array_splice($trace, 0, $traceOffset);
}
} else {
$trace = array();
}

$this->setTrace($trace);
}
}

protected function setTrace($trace)
{
$traceReflector = new \ReflectionProperty('Exception', 'trace');
$traceReflector->setAccessible(true);
$traceReflector->setValue($this, $trace);
}
}
