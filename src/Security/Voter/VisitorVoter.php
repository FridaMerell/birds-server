<?php

namespace App\Security\Voter;

use App\Controller\Api\Species;
use App\Entity\Location;
use App\Entity\Taxon\Species as TaxonSpecies;
use App\Entity\Taxon\TaxClass;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class VisitorVoter extends Voter
{
    public const EDIT = 'POST_EDIT';
    public const VIEW = 'VIEW';

    protected function supports(string $attribute, mixed $subject): bool
    {
        if ($attribute !== self::VIEW) {
            return false;
        }

        $supportedClasses = [TaxonSpecies::class, TaxClass::class, Location::class];
        if (in_array(get_class($subject),  $supportedClasses)) {
            return true;
        }

        // paginator of supported classes
        if (is_object($subject) && method_exists($subject, 'getIterator')) {
            $iterator = $subject->getIterator();
            foreach ($iterator as $item) {
                if (in_array(get_class($item), $supportedClasses)) {
                    return true;
                }
                return false;
            }
        }

        return false;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        if ($attribute === self::VIEW) {
            return true;
        }
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }
        return true;
    }
}
