<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[AsController]
class ActionController extends AbstractController
{
  public function __construct(private Security $security) {}

  #[Route('/species/{scientificName}/subscribe', name: 'species_subscribe', methods: ['POST'])]
  #[IsGranted('ROLE_USER')]
  function toggleSubscriber(string $scientificName, EntityManagerInterface $entityManager)
  {
    $user = $this->security->getUser();
    if (!$user) {
      throw $this->createAccessDeniedException('You must be logged in to subscribe to a species.');
    }

    $speciesRepository = $entityManager->getRepository('App\Entity\Taxon\Species');
    $species = $speciesRepository->findOneBy(['scientificName' => $scientificName]);

    if (!$species) {
      throw $this->createNotFoundException('Species not found.');
    }

    if ($species->getSubscribers()->contains($user)) {
      $species->removeSubscriber($user);
    } else {
      $species->addSubscriber($user);
    }

    $entityManager->persist($species);
    $entityManager->flush();

    return $this->json([
      'isSubscribed' => $species->getSubscribers()->contains($user),
    ]);
  }

  #[Route('/subscribed-species', name: 'user_subscribed_species', methods: ['GET'])]
  function getSubscribedSpecies(EntityManagerInterface $entityManager)
  {
    /**
     * @var User $user
     */
    $user = $this->security->getUser();
    if (!$user) {
      throw $this->createAccessDeniedException('You must be logged in to view subscribed species.');
    }

    $ret = $user->getSubscribedSpecies();
    return $this->json(array_map(fn($species) => $species->getTaxonomyId(), $ret->toArray()));
  }
}
