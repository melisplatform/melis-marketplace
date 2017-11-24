<?php











namespace Composer\Util;

use Composer\Factory;
use Composer\IO\IOInterface;
use Composer\Config;
use Composer\Downloader\TransportException;




class Bitbucket
{
private $io;
private $config;
private $process;
private $remoteFilesystem;
private $token = array();
private $time;

const OAUTH2_ACCESS_TOKEN_URL = 'https://bitbucket.org/site/oauth2/access_token';










public function __construct(IOInterface $io, Config $config, ProcessExecutor $process = null, RemoteFilesystem $remoteFilesystem = null, $time = null)
{
$this->io = $io;
$this->config = $config;
$this->process = $process ?: new ProcessExecutor;
$this->remoteFilesystem = $remoteFilesystem ?: Factory::createRemoteFilesystem($this->io, $config);
$this->time = $time;
}




public function getToken()
{
if (!isset($this->token['access_token'])) {
return '';
}

return $this->token['access_token'];
}







public function authorizeOAuth($originUrl)
{
if ($originUrl !== 'bitbucket.org') {
return false;
}


 if (0 === $this->process->execute('git config bitbucket.accesstoken', $output)) {
$this->io->setAuthentication($originUrl, 'x-token-auth', trim($output));

return true;
}

return false;
}





private function requestAccessToken($originUrl)
{
try {
$json = $this->remoteFilesystem->getContents($originUrl, self::OAUTH2_ACCESS_TOKEN_URL, false, array(
'retry-auth-failure' => false,
'http' => array(
'method' => 'POST',
'content' => 'grant_type=client_credentials',
),
));

$this->token = json_decode($json, true);
} catch (TransportException $e) {
if ($e->getCode() === 400) {
$this->io->writeError('<error>Invalid OAuth consumer provided.</error>');
$this->io->writeError('This can have two reasons:');
$this->io->writeError('1. You are authenticating with a bitbucket username/password combination');
$this->io->writeError('2. You are using an OAuth consumer, but didn\'t configure a (dummy) callback url');

return false;
} elseif (in_array($e->getCode(), array(403, 401))) {
$this->io->writeError('<error>Invalid OAuth consumer provided.</error>');
$this->io->writeError('You can also add it manually later by using "composer config --global --auth bitbucket-oauth.bitbucket.org <consumer-key> <consumer-secret>"');

return false;
}

throw $e;
}

return true;
}










public function authorizeOAuthInteractively($originUrl, $message = null)
{
if ($message) {
$this->io->writeError($message);
}

$url = 'https://confluence.atlassian.com/bitbucket/oauth-on-bitbucket-cloud-238027431.html';
$this->io->writeError(sprintf('Follow the instructions on %s', $url));
$this->io->writeError(sprintf('to create a consumer. It will be stored in "%s" for future use by Composer.', $this->config->getAuthConfigSource()->getName()));
$this->io->writeError('Ensure you enter a "Callback URL" (http://example.com is fine) or it will not be possible to create an Access Token (this callback url will not be used by composer)');

$consumerKey = trim($this->io->askAndHideAnswer('Consumer Key (hidden): '));

if (!$consumerKey) {
$this->io->writeError('<warning>No consumer key given, aborting.</warning>');
$this->io->writeError('You can also add it manually later by using "composer config --global --auth bitbucket-oauth.bitbucket.org <consumer-key> <consumer-secret>"');

return false;
}

$consumerSecret = trim($this->io->askAndHideAnswer('Consumer Secret (hidden): '));

if (!$consumerSecret) {
$this->io->writeError('<warning>No consumer secret given, aborting.</warning>');
$this->io->writeError('You can also add it manually later by using "composer config --global --auth bitbucket-oauth.bitbucket.org <consumer-key> <consumer-secret>"');

return false;
}

$this->io->setAuthentication($originUrl, $consumerKey, $consumerSecret);

if (!$this->requestAccessToken($originUrl)) {
return false;
}


 $this->storeInAuthConfig($originUrl, $consumerKey, $consumerSecret);


 $this->config->getAuthConfigSource()->removeConfigSetting('http-basic.' . $originUrl);

$this->io->writeError('<info>Consumer stored successfully.</info>');

return true;
}









public function requestToken($originUrl, $consumerKey, $consumerSecret)
{
if (!empty($this->token) || $this->getTokenFromConfig($originUrl)) {
return $this->token['access_token'];
}

$this->io->setAuthentication($originUrl, $consumerKey, $consumerSecret);
if (!$this->requestAccessToken($originUrl)) {
return '';
}

$this->storeInAuthConfig($originUrl, $consumerKey, $consumerSecret);

return $this->token['access_token'];
}







private function storeInAuthConfig($originUrl, $consumerKey, $consumerSecret)
{
$this->config->getConfigSource()->removeConfigSetting('bitbucket-oauth.'.$originUrl);

$time = null === $this->time ? time() : $this->time;
$consumer = array(
"consumer-key" => $consumerKey,
"consumer-secret" => $consumerSecret,
"access-token" => $this->token['access_token'],
"access-token-expiration" => $time + $this->token['expires_in'],
);

$this->config->getAuthConfigSource()->addConfigSetting('bitbucket-oauth.'.$originUrl, $consumer);
}





private function getTokenFromConfig($originUrl)
{
$authConfig = $this->config->get('bitbucket-oauth');

if (
!isset($authConfig[$originUrl]['access-token'])
|| !isset($authConfig[$originUrl]['access-token-expiration'])
|| time() > $authConfig[$originUrl]['access-token-expiration']
) {
return false;
}

$this->token = array(
'access_token' => $authConfig[$originUrl]['access-token'],
);

return true;
}
}
