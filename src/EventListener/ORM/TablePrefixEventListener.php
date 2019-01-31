<?php

declare(strict_types=1);

/*
 * (c) Christian Gripp <mail@core23.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Core23\Doctrine\EventListener\ORM;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Id\SequenceGenerator;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

final class TablePrefixEventListener implements EventSubscriber
{
    /**
     * @var string|null
     */
    private $prefix;

    /**
     * @param string|null $prefix
     */
    public function __construct(?string $prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents(): array
    {
        return [
            Events::loadClassMetadata,
        ];
    }

    /**
     * @param LoadClassMetadataEventArgs $args
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $args): void
    {
        if (null === $this->prefix) {
            return;
        }

        $classMetadata = $args->getClassMetadata();
        $entityManager = $args->getEntityManager();

        $this->addTablePrefix($classMetadata);
        $this->addSequencePrefix($classMetadata, $entityManager);
    }

    /**
     * @param ClassMetadata $classMetadata
     */
    private function addTablePrefix(ClassMetadata $classMetadata): void
    {
        if ($classMetadata->isInheritanceTypeSingleTable() && !$classMetadata->isRootEntity()) {
            return;
        }

        $tableName = $classMetadata->getTableName();

        if (!$this->prefixExists($tableName)) {
            $classMetadata->setPrimaryTable([
                'name' => $this->prefix.$tableName,
            ]);
        }

        foreach ($classMetadata->getAssociationMappings() as $fieldName => $mapping) {
            if (ClassMetadataInfo::MANY_TO_MANY !== $mapping['type']) {
                continue;
            }

            if (isset($classMetadata->associationMappings[$fieldName]['joinTable']['name'])) {
                $mappedTableName  = $classMetadata->associationMappings[$fieldName]['joinTable']['name'];

                if (!$this->prefixExists($mappedTableName)) {
                    $classMetadata->associationMappings[$fieldName]['joinTable']['name'] = $this->prefix.$mappedTableName;
                }
            }
        }
    }

    /**
     * @param ClassMetadata $classMetadata
     * @param EntityManager $em
     */
    private function addSequencePrefix(ClassMetadata $classMetadata, EntityManager $em): void
    {
        if ($classMetadata->isInheritanceTypeSingleTable() && !$classMetadata->isRootEntity()) {
            return;
        }

        if (!$classMetadata->isIdGeneratorSequence()) {
            return;
        }

        $newDefinition  = $classMetadata->sequenceGeneratorDefinition;

        $sequenceName = $newDefinition['sequenceName'];
        if (!$this->prefixExists($sequenceName)) {
            $newDefinition['sequenceName'] = $this->prefix.$sequenceName;
        }

        $classMetadata->setSequenceGeneratorDefinition($newDefinition);

        if (isset($classMetadata->idGenerator)) {
            $sequenceGenerator = new SequenceGenerator(
                $em->getConfiguration()->getQuoteStrategy()->getSequenceName(
                    $newDefinition,
                    $classMetadata,
                    $em->getConnection()->getDatabasePlatform()
                ),
                $newDefinition['allocationSize']
            );
            $classMetadata->setIdGenerator($sequenceGenerator);
        }
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    private function prefixExists(string $name): bool
    {
        return 0 === strpos($name, $this->prefix);
    }
}
