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
use Doctrine\ORM\Mapping\ClassMetadata;

abstract class AbstractListener implements EventSubscriber
{
    /**
     * @param ClassMetadata $metadata
     * @param string        $field
     * @param bool          $nullable
     */
    final protected function createDateTimeField(ClassMetadata $metadata, string $field, bool $nullable): void
    {
        if ($metadata->hasField($field)) {
            return;
        }

        $metadata->mapField([
            'type'      => 'datetime',
            'fieldName' => $field,
            'nullable'  => $nullable,
        ]);
    }
}
