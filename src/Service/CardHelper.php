<?php

namespace App\Service;

use App\Entity\Card;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class CardHelper {
	function __construct(private EntityManagerInterface $entityManager, private NewCard $newCard){
	}

	function synchronizeCard(Card $card, User $user){
		$startDate = $card->getStart();
		$endDate = $card->getEnds();

		$sightings = $user->getSightings();
		foreach ($sightings as $sighting) {
			if ($sighting->getDateTime() >= $startDate && $sighting->getDateTime() <= $endDate)
				$card->addSighting($sighting);
		}

		$this->entityManager->persist($card);
		$this->entityManager->flush();
	}

	function updateCardSpecies(Card $card){
		$tmpCard = new Card();
		$this->newCard->setTemplate('300.csv');
		$this->newCard->addSpecies($tmpCard);

		$diff = array_diff($tmpCard->getSpecies()->toArray(), $card->getSpecies()->toArray());

		foreach ($diff as $item) {
			$card->addSpecies($item);
		}

		$this->entityManager->persist($card);
		$this->entityManager->flush();
	}
}