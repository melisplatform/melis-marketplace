<?php











namespace Composer\DependencyResolver;

use Composer\Package\PackageInterface;
use Composer\Package\Link;




class Rule2Literals extends Rule
{
protected $literal1;
protected $literal2;








public function __construct($literal1, $literal2, $reason, $reasonData, $job = null)
{
parent::__construct($reason, $reasonData, $job);

if ($literal1 < $literal2) {
$this->literal1 = $literal1;
$this->literal2 = $literal2;
} else {
$this->literal1 = $literal2;
$this->literal2 = $literal1;
}
}

public function getLiterals()
{
return array($this->literal1, $this->literal2);
}

public function getHash()
{
$data = unpack('ihash', md5($this->literal1.','.$this->literal2, true));

return $data['hash'];
}









public function equals(Rule $rule)
{
$literals = $rule->getLiterals();
if (2 != count($literals)) {
return false;
}

if ($this->literal1 !== $literals[0]) {
return false;
}

if ($this->literal2 !== $literals[1]) {
return false;
}

return true;
}

public function isAssertion()
{
return false;
}






public function __toString()
{
$result = ($this->isDisabled()) ? 'disabled(' : '(';

$result .= $this->literal1 . '|' . $this->literal2 . ')';

return $result;
}
}
