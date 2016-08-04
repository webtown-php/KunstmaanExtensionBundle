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

/**
 * UserEditCommand db services
 *
 * @author Zoltan Feher <whitezo@webtown.hu>
 */
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
     * @param Registry            $registry
     * @param UserPasswordEncoder $encoder
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
     * Find user choices
     *
     * @param string $username
     * @param string $email
     * @param bool   $or    combine search params with OR
     * @param int    $limit Limit the number of results
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
     * Get selector choices as combined username+email
     *
     * @param User[] $choices
     *
     * @return string[]
     */
    public function getChoicesAsEmailUsername(array &$choices)
    {
        $ret = [];
        foreach ($choices as $item) {
            $ret[] = sprintf('%s (%s)', $item->getEmail(), $item->getUsername());
        }

        return $ret;
    }

    /**
     * User choices is separate usernames/email for autocomplete
     *
     * @param User[] $choices
     *
     * @return string[]
     */
    public function getChoicesAsSeparateEmailUsername(array &$choices)
    {
        $ret = [];
        foreach ($choices as $item) {
            $ret[] = $item->getEmail();
            $ret[] = $item->getUsername();
        }

        return $ret;
    }

    /**
     * Update user details
     *
     * @param User        $user
     * @param UserUpdater $up
     */
    public function updateUser(User $user, UserUpdater $up)
    {
        $up->updateUser($user, $this->getEncoder());
        $em = $this->getRegistry()->getManager();
        $em->persist($user);
        $em->flush();
    }

    /**
     * @return \Kunstmaan\AdminBundle\Repository\UserRepository
     */
    protected function getRepository()
    {
        return $this->getRegistry()->getRepository('KunstmaanAdminBundle:User');
    }
}
