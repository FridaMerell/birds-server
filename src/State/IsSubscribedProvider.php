<?php

namespace App\State;

use ApiPlatform\Doctrine\Orm\Paginator;
use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\SubscriptionResource;
use App\Entity\Taxon\Species;
use App\Entity\Taxon\TaxClass;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Security\Core\User\UserInterface;

// Always decorate the default provider to avoid fetching logic
final readonly class IsSubscribedProvider implements ProviderInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.item_provider')]
        private ProviderInterface $defaultProvider,
        #[Autowire(service: 'api_platform.doctrine.orm.state.collection_provider')]
        private ProviderInterface $collectionProvider,
        private Security $security,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        /**
         * @var  Paginator|object
         */
        $result = null;
        if ($operation instanceof CollectionOperationInterface) {

            $result = $this->collectionProvider->provide($operation, $uriVariables, $context);
        } else {

            $result = $this->defaultProvider->provide($operation, $uriVariables, $context);
        }

        // 1. Get the current user
        $user = $this->security->getUser();

        // get the original resource provided by the default provider

        // 2. If the resource is a Species, set the isSubscribed property
        if ($result instanceof Species) {
            $this->setSubscriptionStatus($result, $user);
        } elseif ($result instanceof TaxClass) {
            foreach ($result->getUnsortedSpecies() as $species) {
                $this->setSubscriptionStatus($species, $user);
            }
        } else {
            if ($result instanceof Paginator) {
                $iterator = $result->getIterator();
                foreach ($iterator as $item) {
                    if ($item instanceof Species) {
                        $this->setSubscriptionStatus($item, $user);
                    }
                }
            } elseif (is_array($result)) {
                foreach ($result as $item) {
                    if ($item instanceof Species) {
                        $this->setSubscriptionStatus($item, $user);
                    }
                }
            }
        }
        return $result;
    }


    private function setSubscriptionStatus(Species $resource, ?UserInterface $user): void
    {
        $isSubscribed = false;
        /**
         * @var User
         */
        $user = $this->security->getUser();
        if ($user) {
            $subscribers = $resource->getSubscribers();
            $arr = $subscribers->map(fn($subscriber) => $subscriber->getId())->toArray();

            $isSubscribed = in_array($user->getId(), $arr);
        }

        $resource->setIsSubscribed($isSubscribed);
    }
}
