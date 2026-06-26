<?php

namespace CarlBennett\Tools\Libraries\Factorio;

class Entity
{
    const DIRECTION_NORTH      = 0;
    const DIRECTION_NORTH_EAST = 1;
    const DIRECTION_EAST       = 2;
    const DIRECTION_SOUTH_EAST = 3;
    const DIRECTION_SOUTH      = 4;
    const DIRECTION_SOUTH_WEST = 5;
    const DIRECTION_WEST       = 6;
    const DIRECTION_NORTH_WEST = 7;

    protected array $data;

    public function __construct(array &$data)
    {
        $this->data = &$data;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getDirection(): int
    {
        return $this->data['direction'] ?? 0;
    }

    public function getName(): string
    {
        return $this->data['name'];
    }

    public function getPositionX(): mixed
    {
        return $this->data['position']['x'];
    }

    public function getPositionY(): mixed
    {
        return $this->data['position']['y'];
    }

    public function reflectHorizontal(): string
    {
        $this->setPositionX($this->getPositionX() * -1);

        switch ($this->getName())
        {
            case 'curved-rail':   return $this->reflectHorizontalCurvedRail();
            case 'storage-tank':  return $this->reflectStorageTank();
            default:              return $this->reflectHorizontalBasic();
        }
    }

    protected function reflectHorizontalBasic(): string
    {
        $direction = $this->getDirection();

        switch ($direction)
        {
            case self::DIRECTION_NORTH:      break;
            case self::DIRECTION_NORTH_EAST: $direction = self::DIRECTION_NORTH_WEST; break;
            case self::DIRECTION_EAST:       $direction = self::DIRECTION_WEST;       break;
            case self::DIRECTION_SOUTH_EAST: $direction = self::DIRECTION_SOUTH_WEST; break;
            case self::DIRECTION_SOUTH:      break;
            case self::DIRECTION_SOUTH_WEST: $direction = self::DIRECTION_SOUTH_EAST; break;
            case self::DIRECTION_WEST:       $direction = self::DIRECTION_EAST;       break;
            case self::DIRECTION_NORTH_WEST: $direction = self::DIRECTION_NORTH_EAST; break;
            default: return 'Unknown entity direction.';
        }

        $this->setDirection($direction);
        return '';
    }

    protected function reflectHorizontalCurvedRail(): string
    {
        $direction = $this->getDirection();

        switch ($direction)
        {
            case self::DIRECTION_NORTH:      $direction = self::DIRECTION_NORTH_EAST; break;
            case self::DIRECTION_NORTH_EAST: $direction = self::DIRECTION_NORTH;      break;
            case self::DIRECTION_EAST:       $direction = self::DIRECTION_NORTH_WEST; break;
            case self::DIRECTION_SOUTH_EAST: $direction = self::DIRECTION_WEST;       break;
            case self::DIRECTION_SOUTH:      $direction = self::DIRECTION_SOUTH_WEST; break;
            case self::DIRECTION_SOUTH_WEST: $direction = self::DIRECTION_SOUTH;      break;
            case self::DIRECTION_WEST:       $direction = self::DIRECTION_SOUTH_EAST; break;
            case self::DIRECTION_NORTH_WEST: $direction = self::DIRECTION_EAST;       break;
            default: return 'Unknown entity direction.';
        }

        $this->setDirection($direction);
        return '';
    }

    protected function reflectStorageTank(): string
    {
        $this->setDirection(2 - $this->getDirection());
        return '';
    }

    public function reflectVertical(): string
    {
        $this->setPositionY($this->getPositionY() * -1);

        switch ($this->getName())
        {
            case 'curved-rail':  return $this->reflectVerticalCurvedRail();
            case 'storage-tank': return $this->reflectStorageTank();
            default:             return $this->reflectVerticalBasic();
        }
    }

    protected function reflectVerticalBasic(): string
    {
        $direction = $this->getDirection();

        switch ($direction)
        {
            case self::DIRECTION_NORTH:      $direction = self::DIRECTION_SOUTH;      break;
            case self::DIRECTION_NORTH_EAST: $direction = self::DIRECTION_SOUTH_EAST; break;
            case self::DIRECTION_EAST:       break;
            case self::DIRECTION_SOUTH_EAST: $direction = self::DIRECTION_NORTH_EAST; break;
            case self::DIRECTION_SOUTH:      $direction = self::DIRECTION_NORTH;      break;
            case self::DIRECTION_SOUTH_WEST: $direction = self::DIRECTION_NORTH_WEST; break;
            case self::DIRECTION_WEST:       break;
            case self::DIRECTION_NORTH_WEST: $direction = self::DIRECTION_SOUTH_WEST; break;
            default: return 'Unknown entity direction.';
        }

        $this->setDirection($direction);
        return '';
    }

    protected function reflectVerticalCurvedRail(): string
    {
        $direction = $this->getDirection();

        switch ($direction)
        {
            case self::DIRECTION_NORTH:      $direction = self::DIRECTION_SOUTH_WEST; break;
            case self::DIRECTION_NORTH_EAST: $direction = self::DIRECTION_SOUTH;      break;
            case self::DIRECTION_EAST:       $direction = self::DIRECTION_SOUTH_EAST; break;
            case self::DIRECTION_SOUTH_EAST: $direction = self::DIRECTION_EAST;       break;
            case self::DIRECTION_SOUTH:      $direction = self::DIRECTION_NORTH_EAST; break;
            case self::DIRECTION_SOUTH_WEST: $direction = self::DIRECTION_NORTH;      break;
            case self::DIRECTION_WEST:       $direction = self::DIRECTION_NORTH_WEST; break;
            case self::DIRECTION_NORTH_WEST: $direction = self::DIRECTION_WEST;       break;
            default: return 'Unknown entity direction.';
        }

        $this->setDirection($direction);
        return '';
    }

    public function setDirection(int $value): void
    {
        if ($value === 0)
            unset($this->data['direction']);
        else
            $this->data['direction'] = $value;
    }

    public function setName(string $value): void
    {
        $this->data['name'] = $value;
    }

    public function setPositionX(mixed $value): void
    {
        $this->data['position']['x'] = $value;
    }

    public function setPositionY(mixed $value): void
    {
        $this->data['position']['y'] = $value;
    }
}
