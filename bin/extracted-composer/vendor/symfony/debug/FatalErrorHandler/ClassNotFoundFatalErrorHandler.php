<?php










namespace Symfony\Component\Debug\FatalErrorHandler;

use Symfony\Component\Debug\Exception\ClassNotFoundException;
use Symfony\Component\Debug\Exception\FatalErrorException;
use Symfony\Component\Debug\DebugClassLoader;
use Composer\Autoload\ClassLoader as ComposerClassLoader;
use Symfony\Component\ClassLoader\ClassLoader as SymfonyClassLoader;
use Symfony\Component\ClassLoader\UniversalClassLoader as SymfonyUniversalClassLoader;






class ClassNotFoundFatalErrorHandler implements FatalErrorHandlerInterface
{



public function handleError(array $error, FatalErrorException $exception)
{
$messageLen = strlen($error['message']);
$notFoundSuffix = '\' not found';
$notFoundSuffixLen = strlen($notFoundSuffix);
if ($notFoundSuffixLen > $messageLen) {
return;
}

if (0 !== substr_compare($error['message'], $notFoundSuffix, -$notFoundSuffixLen)) {
return;
}

foreach (array('class', 'interface', 'trait') as $typeName) {
$prefix = ucfirst($typeName).' \'';
$prefixLen = strlen($prefix);
if (0 !== strpos($error['message'], $prefix)) {
continue;
}

$fullyQualifiedClassName = substr($error['message'], $prefixLen, -$notFoundSuffixLen);
if (false !== $namespaceSeparatorIndex = strrpos($fullyQualifiedClassName, '\\')) {
$className = substr($fullyQualifiedClassName, $namespaceSeparatorIndex + 1);
$namespacePrefix = substr($fullyQualifiedClassName, 0, $namespaceSeparatorIndex);
$message = sprintf('Attempted to load %s "%s" from namespace "%s".', $typeName, $className, $namespacePrefix);
$tail = ' for another namespace?';
} else {
$className = $fullyQualifiedClassName;
$message = sprintf('Attempted to load %s "%s" from the global namespace.', $typeName, $className);
$tail = '?';
}

if ($candidates = $this->getClassCandidates($className)) {
$tail = array_pop($candidates).'"?';
if ($candidates) {
$tail = ' for e.g. "'.implode('", "', $candidates).'" or "'.$tail;
} else {
$tail = ' for "'.$tail;
}
}
$message .= "\nDid you forget a \"use\" statement".$tail;

return new ClassNotFoundException($message, $exception);
}
}











private function getClassCandidates($class)
{
if (!is_array($functions = spl_autoload_functions())) {
return array();
}


 $classes = array();

foreach ($functions as $function) {
if (!is_array($function)) {
continue;
}

 if ($function[0] instanceof DebugClassLoader) {
$function = $function[0]->getClassLoader();


 if (is_object($function)) {
$function = array($function);
}

if (!is_array($function)) {
continue;
}
}

if ($function[0] instanceof ComposerClassLoader || $function[0] instanceof SymfonyClassLoader || $function[0] instanceof SymfonyUniversalClassLoader) {
foreach ($function[0]->getPrefixes() as $prefix => $paths) {
foreach ($paths as $path) {
$classes = array_merge($classes, $this->findClassInPath($path, $class, $prefix));
}
}
}
if ($function[0] instanceof ComposerClassLoader) {
foreach ($function[0]->getPrefixesPsr4() as $prefix => $paths) {
foreach ($paths as $path) {
$classes = array_merge($classes, $this->findClassInPath($path, $class, $prefix));
}
}
}
}

return array_unique($classes);
}








private function findClassInPath($path, $class, $prefix)
{
if (!$path = realpath($path.'/'.strtr($prefix, '\\_', '//')) ?: realpath($path.'/'.dirname(strtr($prefix, '\\_', '//'))) ?: realpath($path)) {
return array();
}

$classes = array();
$filename = $class.'.php';
foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::LEAVES_ONLY) as $file) {
if ($filename == $file->getFileName() && $class = $this->convertFileToClass($path, $file->getPathName(), $prefix)) {
$classes[] = $class;
}
}

return $classes;
}








private function convertFileToClass($path, $file, $prefix)
{
$candidates = array(

 $namespacedClass = str_replace(array($path.DIRECTORY_SEPARATOR, '.php', '/'), array('', '', '\\'), $file),

 $prefix.$namespacedClass,

 $prefix.'\\'.$namespacedClass,

 str_replace('\\', '_', $namespacedClass),

 str_replace('\\', '_', $prefix.$namespacedClass),

 str_replace('\\', '_', $prefix.'\\'.$namespacedClass),
);

if ($prefix) {
$candidates = array_filter($candidates, function ($candidate) use ($prefix) { return 0 === strpos($candidate, $prefix); });
}


 
 
 foreach ($candidates as $candidate) {
if ($this->classExists($candidate)) {
return $candidate;
}
}

require_once $file;

foreach ($candidates as $candidate) {
if ($this->classExists($candidate)) {
return $candidate;
}
}
}






private function classExists($class)
{
return class_exists($class, false) || interface_exists($class, false) || (function_exists('trait_exists') && trait_exists($class, false));
}
}
