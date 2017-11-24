<?php



namespace Composer\Autoload;

class ComposerStaticInitComposerPhar1505141966
{
public static $files = array (
'0e6d7bf4a5811bfa5cf40c5ccd6fae6a' => __DIR__ . '/..' . '/symfony/polyfill-mbstring/bootstrap.php',
);

public static $prefixLengthsPsr4 = array (
'S' => 
array (
'Symfony\\Polyfill\\Mbstring\\' => 26,
'Symfony\\Component\\Process\\' => 26,
'Symfony\\Component\\Finder\\' => 25,
'Symfony\\Component\\Filesystem\\' => 29,
'Symfony\\Component\\Debug\\' => 24,
'Symfony\\Component\\Console\\' => 26,
'Seld\\PharUtils\\' => 15,
'Seld\\JsonLint\\' => 14,
'Seld\\CliPrompt\\' => 15,
),
'P' => 
array (
'Psr\\Log\\' => 8,
),
'J' => 
array (
'JsonSchema\\' => 11,
),
'C' => 
array (
'Composer\\Spdx\\' => 14,
'Composer\\Semver\\' => 16,
'Composer\\CaBundle\\' => 18,
'Composer\\' => 9,
),
);

public static $prefixDirsPsr4 = array (
'Symfony\\Polyfill\\Mbstring\\' => 
array (
0 => __DIR__ . '/..' . '/symfony/polyfill-mbstring',
),
'Symfony\\Component\\Process\\' => 
array (
0 => __DIR__ . '/..' . '/symfony/process',
),
'Symfony\\Component\\Finder\\' => 
array (
0 => __DIR__ . '/..' . '/symfony/finder',
),
'Symfony\\Component\\Filesystem\\' => 
array (
0 => __DIR__ . '/..' . '/symfony/filesystem',
),
'Symfony\\Component\\Debug\\' => 
array (
0 => __DIR__ . '/..' . '/symfony/debug',
),
'Symfony\\Component\\Console\\' => 
array (
0 => __DIR__ . '/..' . '/symfony/console',
),
'Seld\\PharUtils\\' => 
array (
0 => __DIR__ . '/..' . '/seld/phar-utils/src',
),
'Seld\\JsonLint\\' => 
array (
0 => __DIR__ . '/..' . '/seld/jsonlint/src/Seld/JsonLint',
),
'Seld\\CliPrompt\\' => 
array (
0 => __DIR__ . '/..' . '/seld/cli-prompt/src',
),
'Psr\\Log\\' => 
array (
0 => __DIR__ . '/..' . '/psr/log/Psr/Log',
),
'JsonSchema\\' => 
array (
0 => __DIR__ . '/..' . '/justinrainbow/json-schema/src/JsonSchema',
),
'Composer\\Spdx\\' => 
array (
0 => __DIR__ . '/..' . '/composer/spdx-licenses/src',
),
'Composer\\Semver\\' => 
array (
0 => __DIR__ . '/..' . '/composer/semver/src',
),
'Composer\\CaBundle\\' => 
array (
0 => __DIR__ . '/..' . '/composer/ca-bundle/src',
),
'Composer\\' => 
array (
0 => __DIR__ . '/../..' . '/src/Composer',
),
);

public static function getInitializer(ClassLoader $loader)
{
return \Closure::bind(function () use ($loader) {
$loader->prefixLengthsPsr4 = ComposerStaticInitComposerPhar1505141966::$prefixLengthsPsr4;
$loader->prefixDirsPsr4 = ComposerStaticInitComposerPhar1505141966::$prefixDirsPsr4;

}, null, ClassLoader::class);
}
}
