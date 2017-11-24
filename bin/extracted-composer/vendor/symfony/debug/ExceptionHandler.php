<?php










namespace Symfony\Component\Debug;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\Debug\Exception\OutOfMemoryException;













class ExceptionHandler
{
private $debug;
private $charset;
private $handler;
private $caughtBuffer;
private $caughtLength;
private $fileLinkFormat;

public function __construct($debug = true, $charset = null, $fileLinkFormat = null)
{
if (false !== strpos($charset, '%')) {
@trigger_error('Providing $fileLinkFormat as second argument to '.__METHOD__.' is deprecated since version 2.8 and will be unsupported in 3.0. Please provide it as third argument, after $charset.', E_USER_DEPRECATED);


 $pivot = $fileLinkFormat;
$fileLinkFormat = $charset;
$charset = $pivot;
}
$this->debug = $debug;
$this->charset = $charset ?: ini_get('default_charset') ?: 'UTF-8';
$this->fileLinkFormat = $fileLinkFormat ?: ini_get('xdebug.file_link_format') ?: get_cfg_var('xdebug.file_link_format');
}










public static function register($debug = true, $charset = null, $fileLinkFormat = null)
{
$handler = new static($debug, $charset, $fileLinkFormat);

$prev = set_exception_handler(array($handler, 'handle'));
if (is_array($prev) && $prev[0] instanceof ErrorHandler) {
restore_exception_handler();
$prev[0]->setExceptionHandler(array($handler, 'handle'));
}

return $handler;
}








public function setHandler($handler)
{
if (null !== $handler && !is_callable($handler)) {
throw new \LogicException('The exception handler must be a valid PHP callable.');
}
$old = $this->handler;
$this->handler = $handler;

return $old;
}








public function setFileLinkFormat($format)
{
$old = $this->fileLinkFormat;
$this->fileLinkFormat = $format;

return $old;
}









public function handle(\Exception $exception)
{
if (null === $this->handler || $exception instanceof OutOfMemoryException) {
$this->failSafeHandle($exception);

return;
}

$caughtLength = $this->caughtLength = 0;

ob_start(array($this, 'catchOutput'));
$this->failSafeHandle($exception);
while (null === $this->caughtBuffer && ob_end_flush()) {

 }
if (isset($this->caughtBuffer[0])) {
ob_start(array($this, 'cleanOutput'));
echo $this->caughtBuffer;
$caughtLength = ob_get_length();
}
$this->caughtBuffer = null;

try {
call_user_func($this->handler, $exception);
$this->caughtLength = $caughtLength;
} catch (\Exception $e) {
if (!$caughtLength) {

 throw $exception;
}
}
}










private function failSafeHandle(\Exception $exception)
{
if (class_exists('Symfony\Component\HttpFoundation\Response', false)
&& __CLASS__ !== get_class($this)
&& ($reflector = new \ReflectionMethod($this, 'createResponse'))
&& __CLASS__ !== $reflector->class
) {
$response = $this->createResponse($exception);
$response->sendHeaders();
$response->sendContent();
@trigger_error(sprintf("The %s::createResponse method is deprecated since 2.8 and won't be called anymore when handling an exception in 3.0.", $reflector->class), E_USER_DEPRECATED);

return;
}

$this->sendPhpResponse($exception);
}









public function sendPhpResponse($exception)
{
if (!$exception instanceof FlattenException) {
$exception = FlattenException::create($exception);
}

if (!headers_sent()) {
header(sprintf('HTTP/1.0 %s', $exception->getStatusCode()));
foreach ($exception->getHeaders() as $name => $value) {
header($name.': '.$value, false);
}
header('Content-Type: text/html; charset='.$this->charset);
}

echo $this->decorate($this->getContent($exception), $this->getStylesheet($exception));
}










public function createResponse($exception)
{
@trigger_error('The '.__METHOD__.' method is deprecated since version 2.8 and will be removed in 3.0.', E_USER_DEPRECATED);

if (!$exception instanceof FlattenException) {
$exception = FlattenException::create($exception);
}

return Response::create($this->getHtml($exception), $exception->getStatusCode(), $exception->getHeaders())->setCharset($this->charset);
}








public function getHtml($exception)
{
if (!$exception instanceof FlattenException) {
$exception = FlattenException::create($exception);
}

return $this->decorate($this->getContent($exception), $this->getStylesheet($exception));
}








public function getContent(FlattenException $exception)
{
switch ($exception->getStatusCode()) {
case 404:
$title = 'Sorry, the page you are looking for could not be found.';
break;
default:
$title = 'Whoops, looks like something went wrong.';
}

$content = '';
if ($this->debug) {
try {
$count = count($exception->getAllPrevious());
$total = $count + 1;
foreach ($exception->toArray() as $position => $e) {
$ind = $count - $position + 1;
$class = $this->formatClass($e['class']);
$message = nl2br($this->escapeHtml($e['message']));
$content .= sprintf(<<<'EOF'
                        <h2 class="block_exception clear_fix">
                            <span class="exception_counter">%d/%d</span>
                            <span class="exception_title">%s%s:</span>
                            <span class="exception_message">%s</span>
                        </h2>
                        <div class="block">
                            <ol class="traces list_exception">

EOF
, $ind, $total, $class, $this->formatPath($e['trace'][0]['file'], $e['trace'][0]['line']), $message);
foreach ($e['trace'] as $trace) {
$content .= '       <li>';
if ($trace['function']) {
$content .= sprintf('at %s%s%s(%s)', $this->formatClass($trace['class']), $trace['type'], $trace['function'], $this->formatArgs($trace['args']));
}
if (isset($trace['file']) && isset($trace['line'])) {
$content .= $this->formatPath($trace['file'], $trace['line']);
}
$content .= "</li>\n";
}

$content .= "    </ol>\n</div>\n";
}
} catch (\Exception $e) {

 if ($this->debug) {
$title = sprintf('Exception thrown when handling an exception (%s: %s)', get_class($e), $this->escapeHtml($e->getMessage()));
} else {
$title = 'Whoops, looks like something went wrong.';
}
}
}

return <<<EOF
            <div id="sf-resetcontent" class="sf-reset">
                <h1>$title</h1>
                $content
            </div>
EOF;
}








public function getStylesheet(FlattenException $exception)
{
return <<<'EOF'
            .sf-reset { font: 11px Verdana, Arial, sans-serif; color: #333 }
            .sf-reset .clear { clear:both; height:0; font-size:0; line-height:0; }
            .sf-reset .clear_fix:after { display:block; height:0; clear:both; visibility:hidden; }
            .sf-reset .clear_fix { display:inline-block; }
            .sf-reset * html .clear_fix { height:1%; }
            .sf-reset .clear_fix { display:block; }
            .sf-reset, .sf-reset .block { margin: auto }
            .sf-reset abbr { border-bottom: 1px dotted #000; cursor: help; }
            .sf-reset p { font-size:14px; line-height:20px; color:#868686; padding-bottom:20px }
            .sf-reset strong { font-weight:bold; }
            .sf-reset a { color:#6c6159; cursor: default; }
            .sf-reset a img { border:none; }
            .sf-reset a:hover { text-decoration:underline; }
            .sf-reset em { font-style:italic; }
            .sf-reset h1, .sf-reset h2 { font: 20px Georgia, "Times New Roman", Times, serif }
            .sf-reset .exception_counter { background-color: #fff; color: #333; padding: 6px; float: left; margin-right: 10px; float: left; display: block; }
            .sf-reset .exception_title { margin-left: 3em; margin-bottom: 0.7em; display: block; }
            .sf-reset .exception_message { margin-left: 3em; display: block; }
            .sf-reset .traces li { font-size:12px; padding: 2px 4px; list-style-type:decimal; margin-left:20px; }
            .sf-reset .block { background-color:#FFFFFF; padding:10px 28px; margin-bottom:20px;
                -webkit-border-bottom-right-radius: 16px;
                -webkit-border-bottom-left-radius: 16px;
                -moz-border-radius-bottomright: 16px;
                -moz-border-radius-bottomleft: 16px;
                border-bottom-right-radius: 16px;
                border-bottom-left-radius: 16px;
                border-bottom:1px solid #ccc;
                border-right:1px solid #ccc;
                border-left:1px solid #ccc;
                word-wrap: break-word;
            }
            .sf-reset .block_exception { background-color:#ddd; color: #333; padding:20px;
                -webkit-border-top-left-radius: 16px;
                -webkit-border-top-right-radius: 16px;
                -moz-border-radius-topleft: 16px;
                -moz-border-radius-topright: 16px;
                border-top-left-radius: 16px;
                border-top-right-radius: 16px;
                border-top:1px solid #ccc;
                border-right:1px solid #ccc;
                border-left:1px solid #ccc;
                overflow: hidden;
                word-wrap: break-word;
            }
            .sf-reset a { background:none; color:#868686; text-decoration:none; }
            .sf-reset a:hover { background:none; color:#313131; text-decoration:underline; }
            .sf-reset ol { padding: 10px 0; }
            .sf-reset h1 { background-color:#FFFFFF; padding: 15px 28px; margin-bottom: 20px;
                -webkit-border-radius: 10px;
                -moz-border-radius: 10px;
                border-radius: 10px;
                border: 1px solid #ccc;
            }
EOF;
}

private function decorate($content, $css)
{
return <<<EOF
<!DOCTYPE html>
<html>
    <head>
        <meta charset="{$this->charset}" />
        <meta name="robots" content="noindex,nofollow" />
        <style>
            /* Copyright (c) 2010, Yahoo! Inc. All rights reserved. Code licensed under the BSD License: http://developer.yahoo.com/yui/license.html */
            html{color:#000;background:#FFF;}body,div,dl,dt,dd,ul,ol,li,h1,h2,h3,h4,h5,h6,pre,code,form,fieldset,legend,input,textarea,p,blockquote,th,td{margin:0;padding:0;}table{border-collapse:collapse;border-spacing:0;}fieldset,img{border:0;}address,caption,cite,code,dfn,em,strong,th,var{font-style:normal;font-weight:normal;}li{list-style:none;}caption,th{text-align:left;}h1,h2,h3,h4,h5,h6{font-size:100%;font-weight:normal;}q:before,q:after{content:'';}abbr,acronym{border:0;font-variant:normal;}sup{vertical-align:text-top;}sub{vertical-align:text-bottom;}input,textarea,select{font-family:inherit;font-size:inherit;font-weight:inherit;}input,textarea,select{*font-size:100%;}legend{color:#000;}

            html { background: #eee; padding: 10px }
            img { border: 0; }
            #sf-resetcontent { width:970px; margin:0 auto; }
            $css
        </style>
    </head>
    <body>
        $content
    </body>
</html>
EOF;
}

private function formatClass($class)
{
$parts = explode('\\', $class);

return sprintf('<abbr title="%s">%s</abbr>', $class, array_pop($parts));
}

private function formatPath($path, $line)
{
$path = $this->escapeHtml($path);
$file = preg_match('#[^/\\\\]*$#', $path, $file) ? $file[0] : $path;

if ($linkFormat = $this->fileLinkFormat) {
$link = strtr($this->escapeHtml($linkFormat), array('%f' => $path, '%l' => (int) $line));

return sprintf(' in <a href="%s" title="Go to source">%s line %d</a>', $link, $file, $line);
}

return sprintf(' in <a title="%s line %3$d" ondblclick="var f=this.innerHTML;this.innerHTML=this.title;this.title=f;">%s line %d</a>', $path, $file, $line);
}








private function formatArgs(array $args)
{
$result = array();
foreach ($args as $key => $item) {
if ('object' === $item[0]) {
$formattedValue = sprintf('<em>object</em>(%s)', $this->formatClass($item[1]));
} elseif ('array' === $item[0]) {
$formattedValue = sprintf('<em>array</em>(%s)', is_array($item[1]) ? $this->formatArgs($item[1]) : $item[1]);
} elseif ('string' === $item[0]) {
$formattedValue = sprintf("'%s'", $this->escapeHtml($item[1]));
} elseif ('null' === $item[0]) {
$formattedValue = '<em>null</em>';
} elseif ('boolean' === $item[0]) {
$formattedValue = '<em>'.strtolower(var_export($item[1], true)).'</em>';
} elseif ('resource' === $item[0]) {
$formattedValue = '<em>resource</em>';
} else {
$formattedValue = str_replace("\n", '', var_export($this->escapeHtml((string) $item[1]), true));
}

$result[] = is_int($key) ? $formattedValue : sprintf("'%s' => %s", $this->escapeHtml($key), $formattedValue);
}

return implode(', ', $result);
}






protected static function utf8Htmlize($str)
{
@trigger_error('The '.__METHOD__.' method is deprecated since version 2.7 and will be removed in 3.0.', E_USER_DEPRECATED);

return htmlspecialchars($str, ENT_QUOTES | (\PHP_VERSION_ID >= 50400 ? ENT_SUBSTITUTE : 0), 'UTF-8');
}




private function escapeHtml($str)
{
return htmlspecialchars($str, ENT_QUOTES | (\PHP_VERSION_ID >= 50400 ? ENT_SUBSTITUTE : 0), $this->charset);
}




public function catchOutput($buffer)
{
$this->caughtBuffer = $buffer;

return '';
}




public function cleanOutput($buffer)
{
if ($this->caughtLength) {

 $cleanBuffer = substr_replace($buffer, '', 0, $this->caughtLength);
if (isset($cleanBuffer[0])) {
$buffer = $cleanBuffer;
}
}

return $buffer;
}
}
