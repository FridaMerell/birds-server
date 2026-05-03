<?php

namespace App\Security;

use App\Repository\AccessTokenRepository;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

class AccessTokenHandler implements AccessTokenHandlerInterface {
	function __construct(private AccessTokenRepository $repository){
	}

	/**
	 * @inheritDoc
	 */
	public function getUserBadgeFrom(#[\SensitiveParameter] string $accessToken): UserBadge{
		$accessToken = $this->repository->findOneBy(['token' => $accessToken]);
		if (null === $accessToken) {
			throw new BadCredentialsException('Invalid credentials.');
		}

		// and return a UserBadge object containing the user identifier from the found token
		return new UserBadge($accessToken->getUser()->getUserIdentifier());
	}
}