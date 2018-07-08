<?php
namespace App\Service;

use App\Entity\User;
use Doctrine\Common\Persistence\ObjectManager;

class UserService
{
    private $em;

    public function __construct(ObjectManager $em)
    {
        $this->em = $em;
    }

    public function saveToDB($userData)
    {
        $user = $this->em->getRepository(User::class)->findOneBy([
            'fbId' => $userData['id']
        ]);

        if(!isset($user)){
            $user = new User();
        }

        $user->setFbId($userData['id']);
        $user->setEmail($userData['email']);
        $user->setFirstName($userData['first_name']);
        $user->setLastName($userData['last_name']);
        $user->setGender(isset($userData['gender']) ? $userData['gender'] : null);
        $user->setCity(isset($userData['location']) ? $userData['location']['name'] : null);
        $user->setPicture(isset($userData['picture']) ? $userData['picture']['url'] : null);
        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }
}