<?php











namespace Composer\Package\Archiver;






interface ArchiverInterface
{










public function archive($sources, $target, $format, array $excludes = array(), $ignoreFilters = false);









public function supports($format, $sourceType);
}
