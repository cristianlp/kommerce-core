<?php
namespace inklabs\kommerce\EntityRepository;

use inklabs\kommerce\Entity as Entity;
use inklabs\kommerce\tests\Helper as Helper;

class UserTest extends Helper\DoctrineTestCase
{
    /**
     * @return User
     */
    private function getRepository()
    {
        return $this->entityManager->getRepository('kommerce:User');
    }

    public function setUp()
    {
        $userRole = new Entity\UserRole;
        $userRole->setName('Administrator');
        $userRole->setDescription('Admin account. Access to everything');

        $userToken = new Entity\UserToken;
        $userToken->setUserAgent('SampleBot/1.1');
        $userToken->settoken('xxxx');
        $userToken->setexpires(new \DateTime);
        $userToken->setType(Entity\UserToken::TYPE_FACEBOOK);

        $userLogin1 = new Entity\UserLogin;
        $userLogin1->setUsername('johndoe');
        $userLogin1->setIp4('8.8.8.8');
        $userLogin1->setResult(Entity\UserLogin::RESULT_SUCCESS);

        $userLogin2 = clone $userLogin1;
        $userLogin3 = clone $userLogin1;
        $userLogin4 = clone $userLogin1;

        $user = new Entity\User;
        $user->setFirstName('John');
        $user->setLastName('Doe');
        $user->setEmail('john@example.com');
        $user->setUsername('johndoe');
        $user->setPassword('xxx');
        $user->addRole($userRole);
        $user->addToken($userToken);
        $user->addLogin($userLogin1);
        $user->addLogin($userLogin2);
        $user->addLogin($userLogin3);
        $user->addLogin($userLogin4);

        $this->entityManager->persist($user);
        $this->entityManager->flush();
        $this->entityManager->clear();
    }

    public function testFind()
    {
        /* @var Entity\User $user */
        $user = $this->getRepository()
            ->find(1);

        $this->assertSame(1, $user->getId());
        $this->assertSame(1, $user->getRoles()[0]->getId());
        $this->assertSame(1, $user->getTokens()[0]->getId());

        /* @var Entity\UserLogin[] $userLogins; */
        $userLogins = $user->getLogins()->slice(0, 3);

        $this->assertSame(4, $userLogins[0]->getId());
    }

    public function testFindByUsernameOrEmailUsingUsername()
    {
        /* @var Entity\User $user */
        $user = $this->getRepository()
            ->findOneByUsernameOrEmail('johndoe');

        $this->assertSame(1, $user->getId());
    }

    public function testFindByUsernameOrEmailUsingEmail()
    {
        /* @var Entity\User $user */
        $user = $this->getRepository()
            ->findOneByUsernameOrEmail('john@example.com');

        $this->assertSame(1, $user->getId());
    }
}
