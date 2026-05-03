<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Repository\UserRepository;
use Symfony\Bundle\SecurityBundle\Security;

class CurrentUserProvider implements ProviderInterface
{

    function __construct(
        private Security $security,
        private UserRepository $userRepository
    )
    {
    
    }


    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {

        $currentUser = $this->security->getUser();
        

        return $currentUser ?? $this->userRepository->findOneBy(['email' => 'frida@merell.se']);
       
    }
}
