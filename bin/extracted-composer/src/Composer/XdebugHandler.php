<?php











namespace Composer;

use Composer\Util\IniHelper;
use Symfony\Component\Console\Output\OutputInterface;




class XdebugHandler
{
const ENV_ALLOW = 'COMPOSER_ALLOW_XDEBUG';
const ENV_VERSION = 'COMPOSER_XDEBUG_VERSION';
const RESTART_ID = 'internal';

private $output;
private $loaded;
private $envScanDir;
private $version;
private $tmpIni;




public function __construct(OutputInterface $output)
{
$this->output = $output;
$this->loaded = extension_loaded('xdebug');
$this->envScanDir = getenv('PHP_INI_SCAN_DIR');

if ($this->loaded) {
$ext = new \ReflectionExtension('xdebug');
$this->version = strval($ext->getVersion());
}
}














public function check()
{
$args = explode('|', strval(getenv(self::ENV_ALLOW)), 2);

if ($this->needsRestart($args[0])) {
if ($this->prepareRestart()) {
$command = $this->getCommand();
$this->restart($command);
}

return;
}


 if (self::RESTART_ID === $args[0]) {
putenv(self::ENV_ALLOW);

if (false !== $this->envScanDir) {

 if (isset($args[1])) {
putenv('PHP_INI_SCAN_DIR='.$args[1]);
} else {
putenv('PHP_INI_SCAN_DIR');
}
}


 if ($this->loaded) {
putenv(self::ENV_VERSION);
}
}
}






protected function restart($command)
{
passthru($command, $exitCode);

if (!empty($this->tmpIni)) {
@unlink($this->tmpIni);
}

exit($exitCode);
}








private function needsRestart($allow)
{
if (PHP_SAPI !== 'cli' || !defined('PHP_BINARY')) {
return false;
}

return empty($allow) && $this->loaded;
}











private function prepareRestart()
{
$this->tmpIni = '';
$iniPaths = IniHelper::getAll();
$additional = count($iniPaths) > 1;

if (empty($iniPaths[0])) {

 array_shift($iniPaths);
}

if ($this->writeTmpIni($iniPaths)) {
return $this->setEnvironment($additional, $iniPaths);
}

return false;
}










private function writeTmpIni(array $iniFiles)
{
if (!$this->tmpIni = tempnam(sys_get_temp_dir(), '')) {
return false;
}

$content = '';
$regex = '/^\s*(zend_extension\s*=.*xdebug.*)$/mi';

foreach ($iniFiles as $file) {
$data = preg_replace($regex, ';$1', file_get_contents($file));
$content .= $data.PHP_EOL;
}

$content .= 'allow_url_fopen='.ini_get('allow_url_fopen').PHP_EOL;
$content .= 'disable_functions="'.ini_get('disable_functions').'"'.PHP_EOL;
$content .= 'memory_limit='.ini_get('memory_limit').PHP_EOL;

if (defined('PHP_WINDOWS_VERSION_BUILD')) {

 $content .= 'opcache.enable_cli=0'.PHP_EOL;
}

return @file_put_contents($this->tmpIni, $content);
}






private function getCommand()
{
$phpArgs = array(PHP_BINARY, '-c', $this->tmpIni);
$params = array_merge($phpArgs, $this->getScriptArgs($_SERVER['argv']));

return implode(' ', array_map(array($this, 'escape'), $params));
}









private function setEnvironment($additional, array $iniPaths)
{

 if ($additional && !putenv('PHP_INI_SCAN_DIR=')) {
return false;
}


 if (!putenv(IniHelper::ENV_ORIGINAL.'='.implode(PATH_SEPARATOR, $iniPaths))) {
return false;
}


 if (!putenv(self::ENV_VERSION.'='.$this->version)) {
return false;
}


 $args = array(self::RESTART_ID);

if (false !== $this->envScanDir) {

 $args[] = $this->envScanDir;
}

return putenv(self::ENV_ALLOW.'='.implode('|', $args));
}











private function getScriptArgs(array $args)
{
if (in_array('--no-ansi', $args) || in_array('--ansi', $args)) {
return $args;
}

if ($this->output->isDecorated()) {
$offset = count($args) > 1 ? 2 : 1;
array_splice($args, $offset, 0, '--ansi');
}

return $args;
}












private function escape($arg, $meta = true)
{
if (!defined('PHP_WINDOWS_VERSION_BUILD')) {
return escapeshellarg($arg);
}

$quote = strpbrk($arg, " \t") !== false || $arg === '';
$arg = preg_replace('/(\\\\*)"/', '$1$1\\"', $arg, -1, $dquotes);

if ($meta) {
$meta = $dquotes || preg_match('/%[^%]+%/', $arg);

if (!$meta && !$quote) {
$quote = strpbrk($arg, '^&|<>()') !== false;
}
}

if ($quote) {
$arg = preg_replace('/(\\\\*)$/', '$1$1', $arg);
$arg = '"'.$arg.'"';
}

if ($meta) {
$arg = preg_replace('/(["^&|<>()%])/', '^$1', $arg);
}

return $arg;
}
}
