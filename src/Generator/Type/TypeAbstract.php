<?php
/*
 * PSX is a open source PHP framework to develop RESTful APIs.
 * For the current version and informations visit <http://phpsx.org>
 *
 * Copyright 2010-2019 Christoph Kappestein <christoph.kappestein@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace PSX\Schema\Generator\Type;

use PSX\Schema\Generator\GeneratorTrait;
use PSX\Schema\PropertyInterface;
use PSX\Schema\PropertyType;
use PSX\Schema\Type\ArrayType;
use PSX\Schema\Type\BooleanType;
use PSX\Schema\Type\IntegerType;
use PSX\Schema\Type\IntersectionType;
use PSX\Schema\Type\MapType;
use PSX\Schema\Type\NumberType;
use PSX\Schema\Type\StringType;
use PSX\Schema\Type\StructType;
use PSX\Schema\Type\UnionType;

/**
 * TypeAbstract
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
abstract class TypeAbstract implements TypeInterface
{
    use GeneratorTrait;

    /**
     * @inheritDoc
     */
    public function getType(PropertyInterface $property): string
    {
        if ($property instanceof StringType) {
            return $this->getStringType($property);
        } elseif ($property instanceof IntegerType) {
            return $this->getIntegerType($property);
        } elseif ($property instanceof NumberType) {
            return $this->getNumber();
        } elseif ($property instanceof BooleanType) {
            return $this->getBoolean();
        } elseif ($property instanceof ArrayType) {
            return $this->getArrayType($property);
        } elseif ($property instanceof StructType) {
            return $this->getStruct($this->getIdentifierForProperty($property));
        } elseif ($property instanceof MapType) {
            $additional = $property->getAdditionalProperties();
            if ($additional === true) {
                return $this->getMap($this->getIdentifierForProperty($property), $this->getAny());
            } elseif ($additional instanceof PropertyInterface) {
                return $this->getMap($this->getIdentifierForProperty($property), $this->getType($additional));
            }
        } elseif ($property instanceof UnionType) {
            return $this->getUnion($this->getCombinationType($property->getOneOf()));
        } elseif ($property instanceof IntersectionType) {
            return $this->getIntersection($this->getCombinationType($property->getAllOf()));
        }

        return $this->getAny();
    }

    /**
     * @inheritDoc
     */
    public function getDocType(PropertyInterface $property): string
    {
        return $this->getType($property);
    }

    /**
     * @return string
     */
    protected function getDate(): string
    {
        return $this->getString();
    }

    /**
     * @return string
     */
    protected function getDateTime(): string
    {
        return $this->getString();
    }

    /**
     * @return string
     */
    protected function getTime(): string
    {
        return $this->getString();
    }

    /**
     * @return string
     */
    protected function getDuration(): string
    {
        return $this->getString();
    }

    /**
     * @return string
     */
    protected function getUri(): string
    {
        return $this->getString();
    }

    /**
     * @return string
     */
    protected function getBinary(): string
    {
        return $this->getString();
    }

    /**
     * @return string
     */
    abstract protected function getString(): string;

    /**
     * @return string
     */
    protected function getInteger32(): string
    {
        return $this->getInteger();
    }

    /**
     * @return string
     */
    protected function getInteger64(): string
    {
        return $this->getInteger();
    }

    /**
     * @return string
     */
    abstract protected function getInteger(): string;

    /**
     * @return string
     */
    abstract protected function getNumber(): string;

    /**
     * @return string
     */
    abstract protected function getBoolean(): string;

    /**
     * @param string $type
     * @return string
     */
    abstract protected function getArray(string $type): string;

    /**
     * @param string $type
     * @return string
     */
    abstract protected function getStruct(string $type): string;

    /**
     * @param string $type
     * @param string $child
     * @return string
     */
    abstract protected function getMap(string $type, string $child): string;

    /**
     * @param array $types
     * @return string
     */
    abstract protected function getUnion(array $types): string;

    /**
     * @param array $types
     * @return string
     */
    abstract protected function getIntersection(array $types): string;

    /**
     * @param string $type
     * @return string
     */
    abstract protected function getGroup(string $type): string;

    /**
     * @return string
     */
    abstract protected function getAny(): string;

    /**
     * @param PropertyInterface $property
     * @return string
     */
    private function getStringType(PropertyInterface $property): string
    {
        $format = $property->getFormat();
        if ($format === PropertyType::FORMAT_DATE) {
            return $this->getDate();
        } elseif ($format === PropertyType::FORMAT_DATETIME) {
            return  $this->getDateTime();
        } elseif ($format === PropertyType::FORMAT_TIME) {
            return  $this->getTime();
        } elseif ($format === PropertyType::FORMAT_DURATION) {
            return  $this->getDuration();
        } elseif ($format === PropertyType::FORMAT_URI) {
            return  $this->getUri();
        } elseif ($format === PropertyType::FORMAT_BINARY) {
            return  $this->getBinary();
        } else {
            return $this->getString();
        }
    }

    /**
     * @param PropertyInterface $property
     * @return string
     */
    private function getIntegerType(PropertyInterface $property): string
    {
        $format = $property->getFormat();
        if ($format === PropertyType::FORMAT_INT32) {
            return $this->getInteger32();
        } elseif ($format === PropertyType::FORMAT_INT64) {
            return $this->getInteger64();
        } else {
            return $this->getInteger();
        }
    }

    /**
     * @param PropertyInterface $property
     * @return string
     */
    private function getArrayType(PropertyInterface $property): string
    {
        $items = $property->getItems();
        if ($items instanceof PropertyInterface) {
            return $this->getArray($this->getType($items));
        } else {
            return $this->getArray($this->getAny());
        }
    }

    /**
     * @param array $properties
     * @return array
     */
    private function getCombinationType(array $properties): array
    {
        $types = [];
        foreach ($properties as $property) {
            $type = $this->getType($property);
            if ($property instanceof UnionType || $property instanceof IntersectionType) {
                $types[] = $this->getGroup($type);
            } else {
                $types[] = $type;
            }
        }

        return $types;
    }
}
