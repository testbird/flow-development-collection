<?php
namespace TYPO3\Flow\Tests\Functional\Persistence\Doctrine;

/*
 * This file is part of the TYPO3.Flow package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use TYPO3\Flow\Tests\Functional\Persistence\Fixtures\Post;
use TYPO3\Flow\Tests\Functional\Persistence\Fixtures\SuperEntity;
use TYPO3\Flow\Tests\Functional\Persistence\Fixtures\SubEntity;
use TYPO3\Flow\Tests\Functional\Persistence\Fixtures\SubSubEntity;

/**
 * Testcase for basic repository operations
 */
class RepositoryTest extends \TYPO3\Flow\Tests\FunctionalTestCase
{
    /**
     * @var boolean
     */
    protected static $testablePersistenceEnabled = true;

    /**
     * @var \TYPO3\Flow\Tests\Functional\Persistence\Fixtures\PostRepository;
     */
    protected $postRepository;

    /**
     * @var \TYPO3\Flow\Tests\Functional\Persistence\Fixtures\SuperEntityRepository;
     */
    protected $superEntityRepository;

    /**
     * @var \TYPO3\Flow\Tests\Functional\Persistence\Fixtures\SubSubEntityRepository;
     */
    protected $subSubEntityRepository;

    /**
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        if (!$this->persistenceManager instanceof \TYPO3\Flow\Persistence\Doctrine\PersistenceManager) {
            $this->markTestSkipped('Doctrine persistence is not enabled');
        }
    }

    /**
     * @test
     */
    public function modificationsOnRetrievedEntitiesAreNotPersistedAutomatically()
    {
        $this->postRepository = $this->objectManager->get(\TYPO3\Flow\Tests\Functional\Persistence\Fixtures\PostRepository::class);

        $post = new Post();
        $post->setTitle('Sample');
        $this->postRepository->add($post);

        $this->persistenceManager->persistAll();
        unset($post);

        $post = $this->postRepository->findOneByTitle('Sample');
        $post->setTitle('Modified Sample');

        $this->persistenceManager->persistAll();
        unset($post);

        $post = $this->postRepository->findOneByTitle('Modified Sample');
        $this->assertNull($post);

        // The following assertions won't work because findOneByTitle() will get the _modified_ post
        // because it is still in Doctrine's identity map:

        // $post = $this->postRepository->findOneByTitle('Sample');
        // $this->assertNotNull($post);
        // $this->assertEquals('Sample', $post->getTitle());
    }

    /**
     * @test
     */
    public function modificationsOnRetrievedEntitiesArePersistedIfUpdateHasBeenCalled()
    {
        $this->postRepository = $this->objectManager->get(\TYPO3\Flow\Tests\Functional\Persistence\Fixtures\PostRepository::class);

        $post = new Post();
        $post->setTitle('Sample');
        $this->postRepository->add($post);

        $this->persistenceManager->persistAll();

        $post = $this->postRepository->findOneByTitle('Sample');
        $post->setTitle('Modified Sample');
        $this->postRepository->update($post);

        $this->persistenceManager->persistAll();

        $post = $this->postRepository->findOneByTitle('Modified Sample');
        $this->assertNotNull($post);
        $this->assertEquals('Modified Sample', $post->getTitle());
    }

    /**
     * @test
     */
    public function instancesOfTheManagedTypeCanBeAddedAndRetrieved()
    {
        $this->superEntityRepository = $this->objectManager->get(\TYPO3\Flow\Tests\Functional\Persistence\Fixtures\SuperEntityRepository::class);

        $superEntity = new SuperEntity();
        $superEntity->setContent('this is the super entity');
        $this->superEntityRepository->add($superEntity);

        $this->persistenceManager->persistAll();

        $superEntity = $this->superEntityRepository->findOneByContent('this is the super entity');
        $this->assertEquals('this is the super entity', $superEntity->getContent());
    }

    /**
     * @test
     */
    public function subTypesOfTheManagedTypeCanBeAddedAndRetrieved()
    {
        $this->superEntityRepository = $this->objectManager->get(\TYPO3\Flow\Tests\Functional\Persistence\Fixtures\SuperEntityRepository::class);

        $subEntity = new SubEntity();
        $subEntity->setContent('this is the sub entity');
        $this->superEntityRepository->add($subEntity);

        $this->persistenceManager->persistAll();

        $subEntity = $this->superEntityRepository->findOneByContent('this is the sub entity');
        $this->assertEquals('this is the sub entity', $subEntity->getContent());
    }

    /**
     * @test
     */
    public function subTypesOfTheManagedTypeCanBeRemoved()
    {
        $this->superEntityRepository = $this->objectManager->get(\TYPO3\Flow\Tests\Functional\Persistence\Fixtures\SuperEntityRepository::class);

        $subEntity = new SubEntity();
        $subEntity->setContent('this is the sub entity');
        $this->superEntityRepository->add($subEntity);

        $this->persistenceManager->persistAll();

        $subEntity = $this->superEntityRepository->findOneByContent('this is the sub entity');
        $this->superEntityRepository->remove($subEntity);
        $this->persistenceManager->persistAll();

        $subEntity = $this->superEntityRepository->findOneByContent('this is the sub entity');
        $this->assertNull($subEntity);
    }

    /**
     * @test
     */
    public function subTypesOfTheManagedTypeCanBeUpdated()
    {
        $this->superEntityRepository = $this->objectManager->get(\TYPO3\Flow\Tests\Functional\Persistence\Fixtures\SuperEntityRepository::class);

        $subEntity = new SubEntity();
        $subEntity->setContent('this is the sub entity');
        $this->superEntityRepository->add($subEntity);

        $this->persistenceManager->persistAll();

        $subEntity = $this->superEntityRepository->findOneByContent('this is the sub entity');
        $subEntity->setContent('updated sub entity content');
        $this->superEntityRepository->update($subEntity);

        $this->persistenceManager->persistAll();

        $subEntity = $this->superEntityRepository->findOneByContent('updated sub entity content');
        $this->assertNotNull($subEntity);
        $this->assertEquals('updated sub entity content', $subEntity->getContent());
    }

    /**
     * @test
     */
    public function countAllCountsSubTypesOfTheManagedType()
    {
        $this->superEntityRepository = $this->objectManager->get(\TYPO3\Flow\Tests\Functional\Persistence\Fixtures\SuperEntityRepository::class);

        $superEntity = new SuperEntity();
        $superEntity->setContent('this is the super entity');
        $this->superEntityRepository->add($superEntity);

        $subEntity = new SubEntity();
        $subEntity->setContent('this is the sub entity');
        $this->superEntityRepository->add($subEntity);

        $this->persistenceManager->persistAll();

        $this->assertEquals(2, $this->superEntityRepository->countAll());
    }

    /**
     * @test
     */
    public function findAllReturnsSubTypesOfTheManagedType()
    {
        $this->superEntityRepository = $this->objectManager->get(\TYPO3\Flow\Tests\Functional\Persistence\Fixtures\SuperEntityRepository::class);

        $superEntity = new SuperEntity();
        $superEntity->setContent('this is the super entity');
        $this->superEntityRepository->add($superEntity);

        $subEntity = new SubEntity();
        $subEntity->setContent('this is the sub entity');
        $this->superEntityRepository->add($subEntity);

        $this->persistenceManager->persistAll();

        $this->assertEquals(2, $this->superEntityRepository->findAll()->count());
    }

    /**
     * @test
     */
    public function findByIdentifierReturnsSubTypesOfTheManagedType()
    {
        $this->superEntityRepository = $this->objectManager->get(\TYPO3\Flow\Tests\Functional\Persistence\Fixtures\SuperEntityRepository::class);

        $subEntity = new SubEntity();
        $subEntity->setContent('this is the sub entity');
        $this->superEntityRepository->add($subEntity);
        $identifier = $this->persistenceManager->getIdentifierByObject($subEntity);

        $this->persistenceManager->persistAll();

        $subEntity = $this->superEntityRepository->findByIdentifier($identifier);
        $this->assertEquals('this is the sub entity', $subEntity->getContent());
    }

    /**
     * @test
     * @expectedException \TYPO3\Flow\Persistence\Exception\IllegalObjectTypeException
     */
    public function addingASuperTypeToAMoreSpecificRepositoryThrowsAnException()
    {
        $this->subSubEntityRepository = $this->objectManager->get(\TYPO3\Flow\Tests\Functional\Persistence\Fixtures\SubSubEntityRepository::class);

        $subEntity = new SubEntity();
        $this->subSubEntityRepository->add($subEntity);
    }

    /**
     * @test
     */
    public function usingASpecificRepositoryForSubTypesWorks()
    {
        $this->superEntityRepository = $this->objectManager->get(\TYPO3\Flow\Tests\Functional\Persistence\Fixtures\SuperEntityRepository::class);
        $this->subSubEntityRepository = $this->objectManager->get(\TYPO3\Flow\Tests\Functional\Persistence\Fixtures\SubSubEntityRepository::class);

        $subSubEntity = new SubSubEntity();
        $subSubEntity->setContent('this is the sub sub entity');
        $this->superEntityRepository->add($subSubEntity);

        $this->persistenceManager->persistAll();

        $subSubEntity = $this->superEntityRepository->findAll()->getFirst();
        $this->assertEquals('this is the sub sub entity', $subSubEntity->getContent());

        $subSubEntity = $this->subSubEntityRepository->findAll()->getFirst();
        $this->assertEquals('this is the sub sub entity - touched by SubSubEntityRepository', $subSubEntity->getContent());
    }

    /**
     * @test
     */
    public function findAllReturnsQueryResult()
    {
        $this->postRepository = $this->objectManager->get(\TYPO3\Flow\Tests\Functional\Persistence\Fixtures\PostRepository::class);
        $this->assertInstanceOf(\TYPO3\Flow\Persistence\Doctrine\Repository::class, $this->postRepository, 'Repository under test should be a Doctrine Repository');

        $result = $this->postRepository->findAll();
        $this->assertInstanceOf(\TYPO3\Flow\Persistence\QueryResultInterface::class, $result, 'findAll should return a QueryResult object');
    }
}
