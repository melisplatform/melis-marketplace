<?php

namespace JsonSchema;

use JsonSchema\Constraints\BaseConstraint;
use JsonSchema\Entity\JsonPointer;
use JsonSchema\Exception\UnresolvableJsonPointerException;
use JsonSchema\Iterator\ObjectIterator;
use JsonSchema\Uri\UriResolver;
use JsonSchema\Uri\UriRetriever;

class SchemaStorage implements SchemaStorageInterface
{
const INTERNAL_PROVIDED_SCHEMA_URI = 'internal://provided-schema';

protected $uriRetriever;
protected $uriResolver;
protected $schemas = array();

public function __construct(
UriRetrieverInterface $uriRetriever = null,
UriResolverInterface $uriResolver = null
) {
$this->uriRetriever = $uriRetriever ?: new UriRetriever();
$this->uriResolver = $uriResolver ?: new UriResolver();
}




public function getUriRetriever()
{
return $this->uriRetriever;
}




public function getUriResolver()
{
return $this->uriResolver;
}




public function addSchema($id, $schema = null)
{
if (is_null($schema) && $id !== self::INTERNAL_PROVIDED_SCHEMA_URI) {

 
 
 $schema = $this->uriRetriever->retrieve($id);
}


 if (is_array($schema)) {
$schema = BaseConstraint::arrayToObjectRecursive($schema);
}


 
 if (is_object($schema) && property_exists($schema, 'id')) {
if ($schema->id == 'http://json-schema.org/draft-04/schema#') {
$schema->properties->id->format = 'uri-reference';
} elseif ($schema->id == 'http://json-schema.org/draft-03/schema#') {
$schema->properties->id->format = 'uri-reference';
$schema->properties->{'$ref'}->format = 'uri-reference';
}
}

$objectIterator = new ObjectIterator($schema);
foreach ($objectIterator as $toResolveSchema) {
if (property_exists($toResolveSchema, '$ref') && is_string($toResolveSchema->{'$ref'})) {
$jsonPointer = new JsonPointer($this->uriResolver->resolve($toResolveSchema->{'$ref'}, $id));
$toResolveSchema->{'$ref'} = (string) $jsonPointer;
}
}
$this->schemas[$id] = $schema;
}




public function getSchema($id)
{
if (!array_key_exists($id, $this->schemas)) {
$this->addSchema($id);
}

return $this->schemas[$id];
}




public function resolveRef($ref)
{
$jsonPointer = new JsonPointer($ref);


 $fileName = $jsonPointer->getFilename();
if (!strlen($fileName)) {
throw new UnresolvableJsonPointerException(sprintf(
"Could not resolve fragment '%s': no file is defined",
$jsonPointer->getPropertyPathAsString()
));
}


 $refSchema = $this->getSchema($fileName);
foreach ($jsonPointer->getPropertyPaths() as $path) {
if (is_object($refSchema) && property_exists($refSchema, $path)) {
$refSchema = $this->resolveRefSchema($refSchema->{$path});
} elseif (is_array($refSchema) && array_key_exists($path, $refSchema)) {
$refSchema = $this->resolveRefSchema($refSchema[$path]);
} else {
throw new UnresolvableJsonPointerException(sprintf(
'File: %s is found, but could not resolve fragment: %s',
$jsonPointer->getFilename(),
$jsonPointer->getPropertyPathAsString()
));
}
}

return $refSchema;
}




public function resolveRefSchema($refSchema)
{
if (is_object($refSchema) && property_exists($refSchema, '$ref') && is_string($refSchema->{'$ref'})) {
$newSchema = $this->resolveRef($refSchema->{'$ref'});
$refSchema = (object) (get_object_vars($refSchema) + get_object_vars($newSchema));
unset($refSchema->{'$ref'});
}

return $refSchema;
}
}
