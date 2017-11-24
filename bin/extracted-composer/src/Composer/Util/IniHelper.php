<?php











namespace Composer\Util;








class IniHelper
{
const ENV_ORIGINAL = 'COMPOSER_ORIGINAL_INIS';









public static function getAll()
{
if ($env = strval(getenv(self::ENV_ORIGINAL))) {
return explode(PATH_SEPARATOR, $env);
}

$paths = array(strval(php_ini_loaded_file()));

if ($scanned = php_ini_scanned_files()) {
$paths = array_merge($paths, array_map('trim', explode(',', $scanned)));
}

return $paths;
}






public static function getMessage()
{
$paths = self::getAll();

if (empty($paths[0])) {
return 'A php.ini file does not exist. You will have to create one.';
}

return 'The php.ini used by your command-line PHP is: '.$paths[0];
}
}
