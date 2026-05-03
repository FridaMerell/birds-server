<?php

namespace App\Security\Voter;

use App\Entity\Card;
use App\Entity\EditableEntityInterface;
use App\Entity\Sighting;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class EditVoter extends Voter
{
    public const EDIT = 'POST_EDIT';
    public const VIEW = 'POST_VIEW';
    public const DELETE = 'POST_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        $managedEntity = $subject instanceof EditableEntityInterface;
        
        return in_array($attribute, [self::EDIT, self::DELETE, self::VIEW])
            && $managedEntity;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::EDIT:
            case self::DELETE:
                return (!$subject->getRestricted()) || in_array($user, $subject->getOwners());
                break;
        }

        return false;
    }
}
