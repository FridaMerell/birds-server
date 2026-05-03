<?php

namespace App\Entity;

use App\Repository\AccessTokenRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AccessTokenRepository::class)]
class AccessToken {
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column]
	private ?int $id = null;
	#[ORM\ManyToOne(inversedBy: 'accessTokens')]
	#[ORM\JoinColumn(nullable: false)]
	private ?User $user = null;
	#[ORM\Column(length: 255)]
	private ?string $token = null;

	/**
	 * @throws \Exception
	 */
	function __construct(){
		$this->token = bin2hex(random_bytes(32));
	}

	public function getId(): ?int{
		return $this->id;
	}

	public function getUser(): ?User{
		return $this->user;
	}

	public function setUser(?User $user): self{
		$this->user = $user;

		return $this;
	}

	public function getToken(): ?string{
		return $this->token;
	}

	public function setToken(string $token): self{
		$this->token = $token;

		return $this;
	}
}
