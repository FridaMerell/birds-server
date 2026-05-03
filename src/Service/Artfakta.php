<?php /** @noinspection ALL */

namespace App\Service;

use phpDocumentor\Reflection\Types\This;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * API Integration for Artfakta
 * https://www.artdatabanken.se/sok-art-och-miljodata/oppna-data-och-apier/om-artdatabankens-apier/api-for-artinformation
 */
class Artfakta {
	const API_ENDPOINT = 'https://api.artdatabanken.se/information/v1/speciesdataservice/v1/speciesdata?taxa={taxa}';
	/** @var int $taxa */
	private int $taxa;
	private static $artfakta = null;

	function __construct(private readonly string $apisecret, private readonly HttpClientInterface $webclient){
	}

	function setTaxa(int $taxa): void{
		$this->taxa = $taxa;
	}

	private function getHeaders(): array{
		return ['Ocp-Apim-Subscription-Key' => $this->apisecret];
	}

	private function prepareEndpoint(): ?string{
		return str_replace('{taxa}', $this->taxa, self::API_ENDPOINT);
	}

	/**
	 * @throws TransportExceptionInterface
	 * @throws ServerExceptionInterface
	 * @throws RedirectionExceptionInterface
	 * @throws ClientExceptionInterface
	 */
	public function getData(): bool{
		if (empty($this->taxa))
			return false;

		$response = $this->webclient->request(
			'GET',
			$this->prepareEndpoint(),
			['headers' => $this->getHeaders()]
		);

		$content = json_decode($response->getContent());

		self::$artfakta = ($content[0])->speciesData ?? null;
		return (self::$artfakta != null);
	}

	function getArray(){
		return self::$artfakta;
	}

	public function getCurrentRedlist(): ?array{
		$info = self::$artfakta->redlistInfo ?? null;
		$current = null;
		if (is_array($info)) {
			foreach ($info as $period) {
				if ($period->period->current) {
					$current = $period;
					break;
				}
			}
		}

		if (!$current) return null;
		return [
			'status' => $current->category ?? '',
			'text' => $current->criterionText ?? ''
		];
	}

	public function getSpread(){
		return self::$artfakta->speciesFactText->spreadAndStatus ?? null;
	}

	public function getEcology(){
		return self::$artfakta->speciesFactText->ecology ?? null;
	}

	public function getThreat(){
		return self::$artfakta->speciesFactText->threat ?? null;
	}

	public function getconservationMeasures(){
		return self::$artfakta->speciesFactText->conservationMeasures ?? null;
	}
}
