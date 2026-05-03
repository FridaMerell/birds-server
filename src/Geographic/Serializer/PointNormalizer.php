<?php

namespace App\Geographic\Serializer;

use App\Geographic\ValueObject\Point;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Normalizer\DenormalizableInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PointNormalizer implements
    NormalizerInterface,
    DenormalizerInterface
{
    public function __construct(
        #[Autowire(service: 'serializer.normalizer.object')]
        private readonly NormalizerInterface $normalizer,
    ) {}

    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        $normalizedData = $this->normalizer->normalize($data, $format, $context);
        $normalizedData['latitude'] = $data->getLatitude();
        $normalizedData['longitude'] = $data->getLongitude();
        return $normalizedData;
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Point;
    }


    public function getSupportedTypes(?string $format): array
    {
        return [
            Point::class => true,
        ];
    }

    function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return $type === Point::class;
    }

    function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): Point
    {
        return new Point(
            (float)$data['latitude'],
            (float)$data['longitude']
        );
    }
}
