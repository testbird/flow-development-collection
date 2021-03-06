<?php
namespace TYPO3\Flow\Tests\Functional\Persistence\Fixtures;

/*
 * This file is part of the TYPO3.Flow package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Doctrine\ORM\Mapping as ORM;
use TYPO3\Flow\Annotations as Flow;

/**
 * A simple entity for persistence tests of OneToOne relations.
 *
 * @Flow\Entity
 * @ORM\Table(name="persistence_onetooneentity")
 */
class OneToOneEntity
{
    /**
     * Self-referencing
     * @var OneToOneEntity
     * @ORM\OneToOne
     */
    protected $selfReferencing;

    /**
     * Bidirectional relation owning side
     * @var OneToOneEntity2
     * @ORM\OneToOne(inversedBy="bidirectionalRelation")
     */
    protected $bidirectionalRelation;

    /**
     * Unidirectional relation
     * @var OneToOneEntity2
     * @ORM\OneToOne
     */
    protected $unidirectionalRelation;
}
