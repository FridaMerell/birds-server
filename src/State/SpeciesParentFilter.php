<?php
// src/State/DynamicGroupProvider.php
namespace App\State;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Parameter;
use ApiPlatform\State\ParameterNotFound;
use ApiPlatform\State\ParameterProviderInterface;
use App\Entity\Taxon\Family;
use App\Entity\Taxon\Genus;
use App\Entity\Taxon\Order;
use App\Entity\Taxon\TaxClass;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

final class SpeciesParentFilter implements FilterInterface
{

  public function apply(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
  {
    $parameter = $context['parameter'] ?? null;
    $value = $parameter?->getValue();

    if ($value instanceof ParameterNotFound) {
      return;
    }

    $alias = $queryBuilder->getRootAliases()[0];
    $parameterName = $queryNameGenerator->generateParameterName('taxonomy');

    // Access the parameter's property or use the parameter key as fallback
    $property = $parameter->getProperty() ?? $parameter->getKey() ?? 'name';

    // join into the parent tables as needed, working backwards from Species to TaxClass


    $queryBuilder
      ->leftJoin(sprintf('%s.taxClass', $alias), 'directTaxClass')
      ->leftJoin(sprintf('%s.genus', $alias), 'genus')
      ->leftJoin('genus.family', 'family')
      ->leftJoin('family.taxOrder', 'taxOrder')
      ->leftJoin('taxOrder.class', 'inheritedTaxClass')
      ->andWhere($queryBuilder->expr()->orX(
        $queryBuilder->expr()->eq('directTaxClass.taxonomyId', ':' . $parameterName),
        $queryBuilder->expr()->eq('genus.taxonomyId', ':' . $parameterName),
        $queryBuilder->expr()->eq('family.taxonomyId', ':' . $parameterName),
        $queryBuilder->expr()->eq('taxOrder.taxonomyId', ':' . $parameterName),
        $queryBuilder->expr()->eq('inheritedTaxClass.taxonomyId', ':' . $parameterName)
      ));

    $queryBuilder
      ->setParameter($parameterName, $value);
  }

  function getDescription(string $resourceClass): array
  {
    return [
      'taxonomy' => [
        'property' => 'taxonomy',
        'type' => 'integer',
        'required' => false,
        'description' => 'Filter species by parent taxonomy ID (TaxClass, Order, Family, Genus). Supports regular expressions.',
      ],
    ];
  }
}
