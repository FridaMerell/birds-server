<?php

namespace App\Geographic\ValueObject;

class Point {
	function __construct(
		private float $latitude,
		private float $longitude
	){
	}

	function __toString(){
		return 'POINT(' . $this->latitude . ' ' . $this->longitude . ')';
	}

	public function getLatitude(): float{
		return $this->latitude;
	}

	public function getLongitude(): float{
		return $this->longitude;
	}
}