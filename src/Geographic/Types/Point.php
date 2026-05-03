<?php

namespace App\Geographic\Types;

use App\Geographic\ValueObject\Point as PointValueType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class Point extends Type
{
	const POINT = 'point';

	/**
	 * @inheritDoc
	 */
	public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
	{
		return 'POINT';
	}

	/**
	 * @inheritDoc
	 */
	public function getName()
	{
		return self::POINT;
	}

	public function convertToPHPValue($value, AbstractPlatform $platform): PointValueType
	{
		[$longitude, $latitude] = sscanf($value, 'POINT(%f %f)');
		if ($latitude === null || $longitude === null) {
			return new PointValueType(0, 0);
		}
		return new PointValueType($latitude, $longitude);
	}

	public function convertToDatabaseValue($value, AbstractPlatform $platform)
	{
		if ($value instanceof PointValueType) {
			$value = sprintf('POINT(%F %F)', $value->getLongitude(), $value->getLatitude());
		}

		return $value;
	}

	public function canRequireSQLConversion()
	{
		return true;
	}

	public function convertToPHPValueSQL($sqlExpr, $platform)
	{
		return sprintf('ST_AsText(%s)', $sqlExpr);
	}

	public function convertToDatabaseValueSQL($sqlExpr, AbstractPlatform $platform)
	{
		return sprintf('ST_PointFromText(%s)', $sqlExpr);
	}
}
