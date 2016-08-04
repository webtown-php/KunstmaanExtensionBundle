<?php
/**
 * Created by PhpStorm.
 * User: whitezo
 * Date: 2016. 08. 03.
 * Time: 17:00
 */

namespace Webtown\KunstmaanExtensionBundle\User;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Kunstmaan\AdminBundle\Entity\User;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;

class UserEditService
{
    /**
     * @var Registry
     */
    private $registry;
    /**
     * @var UserPasswordEncoder
     */
    private $encoder;

    /**
     * @param Registry $registry
     */
    public function __construct(Registry $registry, UserPasswordEncoder $encoder)
    {
        $this->registry = $registry;
        $this->encoder = $encoder;
    }

    /**
     * @return UserPasswordEncoder
     */
    public function getEncoder()
    {
        return $this->encoder;
    }

    /**
     * @return Registry
     */
    public function getRegistry()
    {
        return $this->registry;
    }

    /**
     * @param $username
     * @param $email
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getUsernameEmailQuery($username = null, $email = null)
    {
        $qb = $this->getRepository()->createQueryBuilder('u');

        if ($username) {
            $qb->where('u.username = :username');
            $qb->setParameter('username', $username);
        }
        if ($email) {
            $qb->andWhere('u.email = :email');
            $qb->setParameter('email', $email);
        }

        return $qb;
    }

    /**
     * Find user choices
     *
     * @param string $username
     * @param string $email
     * @param bool   $or
     * @param int    $limit    Limit the number of results
     *
     * @return array
     */
    public function getChoices($username = null, $email = null, $or = false, $limit = null)
    {
        $qb = $this->getRepository()->createQueryBuilder('u');
        $qb->orderBy('u.username');
        $method = $or ? 'orWhere' : 'andWhere';
        if ($username) {
            $qb->$method('u.username LIKE :username');
            $qb->setParameter('username', '%' . $username . '%');
        }
        if ($email) {
            $qb->$method('u.email LIKE :email');
            $qb->setParameter('email', '%' . $email . '%');
        }
        if ($limit) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Update user details
     *
     * @param User   $user
     * @param string $username
     * @param string $email
     * @param string $password
     */
    public function updateUser(User $user, $username, $email, $password)
    {
        if ($username) {
            $user->setUsername($username);
        }
        if ($email) {
            $user->setEmail($email);
        }
        if ($password) {
            $password = $this->getEncoder()->encodePassword($user, $password);
            $user->setPassword($password);
        }
        $em = $this->getRegistry()->getManager();
        $em->persist($user);
        $em->flush();
    }

    /**
     * @return \Doctrine\Common\Persistence\ObjectRepository|\Kunstmaan\AdminBundle\Repository\UserRepository
     */
    protected function getRepository()
    {
        return $this->getRegistry()->getRepository('KunstmaanAdminBundle:User');
    }
}
