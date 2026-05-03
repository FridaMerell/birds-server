<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Sighting;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;

#[AsDecorator('api_platform.doctrine.orm.state.persist_processor')]
class AssignUserToObject implements ProcessorInterface
{
    function __construct(private ProcessorInterface $innerProcessor,private Security $security) {}
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if ($data instanceof Sighting || $data instanceof \App\Entity\Card) {
            $user = $this->security->getUser();
            
            $data->setUser($user);
        }
        return $this->innerProcessor->process($data, $operation, $uriVariables, $context);
    }
}
