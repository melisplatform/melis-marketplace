<?php











namespace Composer\Package\Version;

use Composer\Semver\VersionParser as SemverVersionParser;

class VersionParser extends SemverVersionParser
{
private static $constraints = array();




public function parseConstraints($constraints)
{
if (!isset(self::$constraints[$constraints])) {
self::$constraints[$constraints] = parent::parseConstraints($constraints);
}

return self::$constraints[$constraints];
}











public function parseNameVersionPairs(array $pairs)
{
$pairs = array_values($pairs);
$result = array();

for ($i = 0, $count = count($pairs); $i < $count; $i++) {
$pair = preg_replace('{^([^=: ]+)[=: ](.*)$}', '$1 $2', trim($pairs[$i]));
if (false === strpos($pair, ' ') && isset($pairs[$i + 1]) && false === strpos($pairs[$i + 1], '/')) {
$pair .= ' '.$pairs[$i + 1];
$i++;
}

if (strpos($pair, ' ')) {
list($name, $version) = explode(" ", $pair, 2);
$result[] = array('name' => $name, 'version' => $version);
} else {
$result[] = array('name' => $pair);
}
}

return $result;
}
}
