<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TwigUtils extends AbstractExtension {
	public function getFunctions(): array|\Generator{
		yield new TwigFunction('yearspan', [$this, 'getYearSpan']);
	}

	function getYearSpan(): array{
		$baseYear = new \DateTime();
		$ret = [$baseYear->format('Y')];

		do {
			$baseYear->sub(new \DateInterval('P1Y'));
			$ret[] = $baseYear->format('Y');
		} while ($baseYear->format('Y') > 2008);

		return $ret;
	}
}