<?php

namespace App\ApiBundle\Service;

use App\ApiBundle\Enum\CommonEnum;
use App\Entity\User;
use App\Entity\UserFriend;
use Doctrine\ORM\EntityManagerInterface;
use FOS\UserBundle\Model\UserManager;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Security\Csrf\TokenGenerator\UriSafeTokenGenerator;

/**
 * Class UserService
 * @package App\ApiBundle\Service
 */
class UserService
{
    const WISHLIST_NEW_PASSWORD = "Wishlist new password";
    const SECURITY_FROM_EMAIL_ADDRESS = "wishlistapp1@gmail.com";

    /**
     * @var UserManager
     */
    private $userManager;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var \Twig_Environment
     */
    private $templating;
    /**
     * @var MailerInterface
     */
    private $mailer;

    /**
     * UserService constructor.
     * @param UserManager $userManager
     * @param EntityManagerInterface $entityManager
     * @param \Twig_Environment $templating
     * @param MailerInterface $mailer
     */
    public function __construct(
        UserManager $userManager,
        EntityManagerInterface $entityManager,
        \Twig_Environment $templating,
        MailerInterface $mailer
    ) {
        $this->userManager = $userManager;
        $this->entityManager = $entityManager;
        $this->templating = $templating;
        $this->mailer = $mailer;
    }

    /**
     * @param array $data
     * @return \FOS\UserBundle\Model\UserInterface|mixed
     */
    public function createUser(array $data)
    {
        /** @var User $user */
        $user = $this->userManager->createUser();
        isset($data['first_name']) ? $user->setFirstName($data['first_name']) : null;
        isset($data['last_name']) ? $user->setLastName($data['last_name']) : null;
        $user->setEnabled(1);
        $user->setUsername($data['username']);
        $user->setEmail($data['email']);
        $user->setPlainPassword($data['password']);
        $user->setRoles([CommonEnum::ROLE_APP_USER]);

        $this->userManager->updateUser($user);
        return $this->makeUserDetail($user);
    }

    /**
     * @param User|null $user
     * @param null $requestAccepted
     * @return array|null
     */
    public function makeUserDetail(User $user = null, $requestAccepted = null): ?array
    {
        if (empty($user)) {
            return null;
        }

        $data = [
            'id' => $user->getId(),
            'first_name' => $user->getFirstName(),
            'last_name' => $user->getLastName(),
            'username' => $user->getUsername(),
            'email' => $user->getEmail()
        ];

        if ($requestAccepted !== null) {
            $data['request_accepted'] = $requestAccepted;
        }

        return $data;
    }

    /**
     * @param string $email
     * @return \FOS\UserBundle\Model\UserInterface|null
     */
    public function getUserByEmail(string $email): ?\FOS\UserBundle\Model\UserInterface
    {
        return $this->userManager->findUserByEmail($email);
    }

    /**
     * @param string $username
     * @return \FOS\UserBundle\Model\UserInterface|null
     */
    public function getUserByUsername(string $username): ?\FOS\UserBundle\Model\UserInterface
    {
        return $this->userManager->findUserByUsername($username);
    }

    /**
     * @param int $id
     * @return \FOS\UserBundle\Model\UserInterface|null
     */
    public function getUserById(int $id): ?\FOS\UserBundle\Model\UserInterface
    {
        return $this->userManager->findUserBy(['id' => $id]);
    }

    /**
     * @param User $user
     * @param User $targetUser
     * @return bool|string
     */
    public function createUserFriend(User $user, User $targetUser)
    {
        /** @var UserFriend $userFriendObj */
        $userFriendObj = $this->entityManager->getRepository('App:UserFriend')->findOneBy([
            'user' => $user,
            'friend' => $targetUser
        ]);

        if (empty($userFriendObj)) {
            $userFriendObj = $this->entityManager->getRepository('App:UserFriend')->findOneBy([
                'user' => $targetUser,
                'friend' => $user
            ]);
        }

        if (!empty($userFriendObj)) {
            return $userFriendObj->isRequestAccepted() ? "This user is already a friend." : "Friend request has already been sent.";
        }

        $userFriendObj = new UserFriend();
        $userFriendObj->setUser($user);
        $userFriendObj->setFriend($targetUser);
        $userFriendObj->setRequestAccepted(false);
        $this->entityManager->persist($userFriendObj);
        $this->entityManager->flush();

        $this->entityManager->getRepository('App:Notification')->create(
            CommonEnum::NOTIFICATION_TYPE_ADD_FRIEND,
            $targetUser,
            $user,
            null
        );

        return true;
    }

    /**
     * @param User $user
     * @param User $targetUser
     * @return bool|string
     */
    public function removeUserFriend(User $user, User $targetUser)
    {
        /** @var UserFriend $userFriendObj */
        $userFriendObj = $this->entityManager->getRepository('App:UserFriend')->findOneBy([
            'user' => $user,
            'friend' => $targetUser
        ]);

        if (empty($userFriendObj)) {
            $userFriendObj = $this->entityManager->getRepository('App:UserFriend')->findOneBy([
                'user' => $targetUser,
                'friend' => $user
            ]);
        }

        if (empty($userFriendObj)) {
            return "This user is not a friend.";
        }

        $this->entityManager->remove($userFriendObj);
//        $this->entityManager->getRepository('App:Notification')->create(
//            CommonEnum::NOTIFICATION_TYPE_REMOVE_FRIEND,
//            $targetUser,
//            $user,
//            null
//        );

        return true;
    }

    /**
     * @param User $user
     * @param User $targetUser
     * @return bool|string
     */
    public function acceptRequest(User $user, User $targetUser)
    {
        /** @var UserFriend $userFriendObj */
        $userFriendObj = $this->entityManager->getRepository('App:UserFriend')->findOneBy([
            'user' => $user,
            'friend' => $targetUser
        ]);

        if (empty($userFriendObj)) {
            $userFriendObj = $this->entityManager->getRepository('App:UserFriend')->findOneBy([
                'user' => $targetUser,
                'friend' => $user
            ]);
        }

        if (empty($userFriendObj)) {
            return "User don't have pending request.";
        }

        if ($userFriendObj->isRequestAccepted()) {
            return "Request has already been accepted.";
        }

        $userFriendObj->setRequestAccepted(true);
        $this->entityManager->flush();

        return true;
    }

    /**
     * @param User $user
     * @return array
     */
    public function getUserFriendList(User $user): array
    {
        $returnData = [];
        $userFriends = $this->entityManager->getRepository('App:UserFriend')->createQueryBuilder('uf')
            ->where('uf.user = :user')
            ->orWhere('uf.friend = :user')
            ->setParameter('user', $user)
            ->getQuery()->getResult();

        /** @var UserFriend $userFriend */
        foreach ($userFriends as $userFriend) {
            $friendObj = $userFriend->getUser() == $user ? $userFriend->getFriend() : $userFriend->getUser();
            $returnData[] = $this->makeUserDetail($friendObj, $userFriend->isRequestAccepted());
        }

        return $returnData;
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function getAllUsers(array $data): array
    {
        $perPage = empty($data['per_page']) ? CommonEnum::PER_PAGE_MAX : (int) $data['per_page'];
        $page = empty($data['page']) ? 1 : (int) $data['page'];
        if ($page <= 0) {
            $page = 1;
        }

        $qb = $this->entityManager->getRepository('App:User')->createQueryBuilder('u')
            ->orderBy('u.firstName', 'ASC');

        if (!empty($data['page'])) {
            $qb->setFirstResult(($page-1) * $perPage)->setMaxResults($perPage);
        }

        $users = $qb->getQuery()->getResult();

        $data = [];
        /** @var User $user */
        foreach ($users as $user) {
            $data[] = $this->makeUserDetail($user);
        }

        return $data;
    }

    /**
     * @param User $user
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function sendNewPasswordEmail(User $user)
    {
        $tokenGenerator = new UriSafeTokenGenerator(35);
        $newPassword=  $tokenGenerator->generateToken();

        $user->setPlainPassword($newPassword);
        $this->userManager->updateUser($user);


        $subject = self::WISHLIST_NEW_PASSWORD;
        $fromAddress = self::SECURITY_FROM_EMAIL_ADDRESS;
        $toAddress = $user->getEmail();
        $body = $this->templating->render(
            'emails/reset_password.html.twig',
            ['password' => $newPassword]
        );

        $this->sendEmail($subject, $fromAddress, $toAddress, $body);
    }

    /**
     * @param string $subject
     * @param string $fromAddress
     * @param string $toAddress
     * @param $body
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    public function sendEmail(string $subject, string $fromAddress, string $toAddress, $body)
    {
        $email = (new Email())
            ->from($fromAddress)
            ->to($toAddress)
            ->subject($subject)
            ->html($body);

        $this->mailer->send($email);
    }

}
