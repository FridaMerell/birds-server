<?php

namespace App\Controller;

use App\Entity\Card;
use App\Entity\Taxon\Species;
use App\Repository\CardRepository;
use App\Repository\Taxon\FamilyRepository;
use App\Repository\Taxon\SpeciesRepository;
use App\Repository\Taxon\TaxClassRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CardController extends AbstractController
{
  public function __construct(
    private CardRepository $cardRepository,
    private SpeciesRepository $speciesRepository,
    private FamilyRepository $familyRepository,
    private TaxClassRepository $taxClassRepository,
    private UserRepository $userRepository,
  ) {}

  #[Route('/view/card/{id}', name: 'get_card')]
  function getFormattedCard(Card $card): JsonResponse
  {
    $data = $this->familyRepository->findSpeciesFromCard($card);
    return $this->json([$card, $data], context: ['groups' => ['card:read']]);
  }

  #[Route('/card/from-filters', name: 'get_card_from_filters', methods: ['POST'])]
  #[IsGranted('ROLE_USER')]
  function getCardFromFilters(Request $request): JsonResponse
  {
    
    $filters = json_decode($request->getContent(), true);
    
    $card = new Card();


    if (!isset($filters['name']) || empty($filters['name'])) {
      throw $this->createNotFoundException('Name filter is required');
    }

    $card->setName($filters['name']);

    if(isset($filters['start']) && is_string($filters['start'])) {
      $card->setStart(new \DateTime($filters['start']));
    }
    if(isset($filters['end']) && is_string($filters['end'])) {
      $card->setEnds(new \DateTime($filters['end']));
    }

    if(isset($filters['subscribers']) && is_array($filters['subscribers'])) {
      foreach($filters['subscribers'] as $subscriberId) {
        $user = $this->userRepository->find((int)$subscriberId);
        if($user) {
          $card->addSubscriber($user);
        }
      }
    }else{
      $card->addSubscriber($this->getUser());
    }



    $parentTaxon = $this->taxClassRepository->find((int)$filters['taxonomy']);

    if (!$parentTaxon) {
      throw $this->createNotFoundException('Taxon not found');
    }

    $card->setTaxonomy($parentTaxon);

    $query = $this->speciesRepository->createQueryBuilder('s')
      ->leftJoin('s.genus', 'g')
      ->leftJoin('g.family', 'f')
      ->leftJoin('f.taxOrder', 'o')
      ->leftJoin('s.taxClass', 'tc');
    $query->where(
      $query->expr()->orX(
        'tc = :parentTaxon',
        'o.class = :parentTaxon',
      )
    )
      ->setParameter('parentTaxon', $parentTaxon)
    ;


    if ($filters['swedishProminence'] && is_array($filters['swedishProminence']) && count($filters['swedishProminence']) > 0) {
     

      $options = [];
      foreach ($filters['swedishProminence'] as $prominence) {
        switch ($prominence) {
          case 'common':
            $options[] = 'Bofast och reproducerande';
            break;
          case 'regular':
            $options[] = 'Regelbunden förekomst, ej reproducerande';
            break;
          case 'temporary':
            $options[] = 'Ej bofast men tillfälligt reproducerande';
            break;
          case 'occasional':
            $options[] = 'Tillfällig förekomst (alt. kvarstående)';
            break;
          case 'former':
            $options[] = 'Ej längre bofast, nu endast tillfälligt förekommande';
            break;
          case 'uncertain':
            $options[] = 'Osäkert om påträffad';
            break;
          case 'notfound':
            $options[] = 'Ej påträffad';
            break;
          case 'empty':
            $options[] = '';
            break;
        }
      }
      $query->andWhere('s.swedishProminence IN (:prominences)')
        ->setParameter('prominences', $options);
    }

    $speciesList = $query->getQuery()->getResult();

    
    if (count($speciesList) > 0) {
      foreach ($speciesList as $species) {
        /** @var Species $species */
        $card->addSpecies($species);
      }

    }

    $this->cardRepository->save($card, true);


    return $this->json($card,201, context: ['groups' => ['card:created']]);
  }

  #[IsGranted('ROLE_USER')]
  #[Route('/cards/remove-species/{id}', name: 'remove_species_from_card', methods: ['PATCH'])]
  function removeSpeciesFromCard(Card $card, Request $request): JsonResponse
  { 
    $data = json_decode($request->getContent(), true);
    

    foreach ($data['taxonomyIds'] as $taxonomyId) {
      $species = $this->speciesRepository->findOneBy(['taxonomyId' => (int)$taxonomyId]);
      if ($species && $card->getSpecies()->contains($species)) {
        $card->removeSpecies($species);
      }
    }
    $this->cardRepository->save($card, true);
    return $this->json($card, context: ['groups' => ['card:read']]);
  }
}
