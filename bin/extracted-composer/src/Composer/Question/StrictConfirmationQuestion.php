<?php











namespace Composer\Question;

use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Question\Question;








class StrictConfirmationQuestion extends Question
{
private $trueAnswerRegex;
private $falseAnswerRegex;









public function __construct($question, $default = true, $trueAnswerRegex = '/^y(?:es)?$/i', $falseAnswerRegex = '/^no?$/i')
{
parent::__construct($question, (bool) $default);

$this->trueAnswerRegex = $trueAnswerRegex;
$this->falseAnswerRegex = $falseAnswerRegex;
$this->setNormalizer($this->getDefaultNormalizer());
$this->setValidator($this->getDefaultValidator());
}






private function getDefaultNormalizer()
{
$default = $this->getDefault();
$trueRegex = $this->trueAnswerRegex;
$falseRegex = $this->falseAnswerRegex;

return function ($answer) use ($default, $trueRegex, $falseRegex) {
if (is_bool($answer)) {
return $answer;
}
if (empty($answer) && !empty($default)) {
return $default;
}

if (preg_match($trueRegex, $answer)) {
return true;
}

if (preg_match($falseRegex, $answer)) {
return false;
}

return null;
};
}






private function getDefaultValidator()
{
return function ($answer) {
if (!is_bool($answer)) {
throw new InvalidArgumentException('Please answer yes, y, no, or n.');
}

return $answer;
};
}
}
