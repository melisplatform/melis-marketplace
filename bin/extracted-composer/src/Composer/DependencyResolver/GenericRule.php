<?php











namespace Composer\DependencyResolver;

use Composer\Package\PackageInterface;
use Composer\Package\Link;




class GenericRule extends Rule
{
protected $literals;







public function __construct(array $literals, $reason, $reasonData, $job = null)
{
parent::__construct($reason, $reasonData, $job);


 sort($literals);

$this->literals = $literals;
}

public function getLiterals()
{
return $this->literals;
}

public function getHash()
{
$data = unpack('ihash', md5(implode(',', $this->literals), true));

return $data['hash'];
}









public function equals(Rule $rule)
{
return $this->literals === $rule->getLiterals();
}

public function isAssertion()
{
return 1 === count($this->literals);
}






public function __toString()
{
$result = ($this->isDisabled()) ? 'disabled(' : '(';

foreach ($this->literals as $i => $literal) {
if ($i != 0) {
$result .= '|';
}
$result .= $literal;
}

$result .= ')';

return $result;
}
}
