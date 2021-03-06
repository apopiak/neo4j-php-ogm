<?php

namespace GraphAware\Neo4j\OGM\Persister;

use GraphAware\Common\Cypher\Statement;
use GraphAware\Neo4j\OGM\Annotations\Relationship;
use GraphAware\Neo4j\OGM\Metadata\RelationshipMetadata;

class RelationshipPersister
{
    public function getRelationshipQuery($entityIdA, RelationshipMetadata $relationship, $entityIdB)
    {
        if ('' === trim($relationship->getType())) {
            throw new \RuntimeException(sprintf('Cannot create empty relationship type', $relationship->getPropertyName()));
        }
        $relString = '';
        switch ($relationship->getDirection()) {
            case 'OUTGOING':
                $relString = '-[r:%s]->';
                break;
            case 'INCOMING':
                $relString = '<-[r:%s]-';
                break;
            case 'BOTH':
                $relString = '-[r:%s]-';
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Direction "%s" is not valid', $relationship->getDirection()));
        }

        $relStringPart = sprintf($relString, $relationship->getType());

        $query = 'MATCH (a), (b) WHERE id(a) = {ida} AND id(b) = {idb}
        MERGE (a)'.$relStringPart.'(b)
        RETURN id(r)';

        return Statement::create($query, ['ida' => $entityIdA, 'idb' => $entityIdB]);
    }

    public function getDeleteRelationshipQuery($entityIdA, $entityIdB, RelationshipMetadata $relationship)
    {
        $relString = '';
        switch ($relationship->getDirection()) {
            case 'OUTGOING':
                $relString = '-[r:%s]->';
                break;
            case 'INCOMING':
                $relString = '<-[r:%s]-';
                break;
            case 'BOTH':
                $relString = '-[r:%s]-';
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Direction "%s" is not valid', $relationship->getDirection()));
        }

        $relStringPart = sprintf($relString, $relationship->getType());

        $query = 'MATCH (a), (b) WHERE id(a) = {ida} AND id(b) = {idb}
        MATCH (a)'.$relStringPart.'(b)
        DELETE r';

        return Statement::create($query, ['ida' => $entityIdA, 'idb' => $entityIdB]);
    }
}
