<?php

namespace App\Service;

use App\Entity\Taxon\Species;
use App\Repository\LocationRepository;
use DateTime;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * https://github.com/biodiversitydata-se/SOS/blob/master/Docs/SearchFilter.md
 */
class Observations {
	const OBSERVATION_ENDPOINT = 'https://api.artdatabanken.se/species-observation-system/v1/Observations/Search';

	function __construct(private readonly string $apiSecret, private HttpClientInterface $client, private readonly LocationRepository $locationRepository){
	}

	private function getHeaders(): array{
		return ['Ocp-Apim-Subscription-Key' => $this->apiSecret];
	}

	function getObservations(){
		$locations = $this->locationRepository->findAll();
		$geometry = [];
		foreach ($locations as $location) {
			$geometry[] = [
				'type' => 'point',
				'coordinates' => [$location->getPoint()->getLatitude(), $location->getPoint()->getLongitude()],
				"maxDistanceFromPoint" => 3500
			];
		}

		$now = new DateTime();
		$start = new DateTime('-2 days');
		$requestData = [
			"date" => [
				"endDate" => $now->format('Y-m-d'),
				"startDate" => $start->format('Y-m-d'),
				"dateFilterType" => "OnlyStartDate"
			],
			'geographics' => [
				'geometries' => $geometry
			],
			'taxon'=>[
				"ids"=> [4000104],
        "includeUnderlyingTaxa"=> true
			]
		];


		$request = $this->client->request('POST', self::OBSERVATION_ENDPOINT, [
			'headers'	=>	$this->getHeaders(),
			'body'		=>	$requestData,
		]);

		return $request->toArray();
	}
}
