<?php

declare(strict_types=1);

/*
 * (c) Christian Gripp <mail@core23.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Core23\Doctrine\Adapter\ORM;

use Core23\Doctrine\Manager\ORM\BaseQueryTrait;
use Sonata\Doctrine\Entity\BaseEntityManager;

abstract class AbstractEntityManager extends BaseEntityManager
{
    use EntityManagerTrait;
    use BaseQueryTrait;
}
