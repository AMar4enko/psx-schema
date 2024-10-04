<?php
/*
 * PSX is an open source PHP framework to develop RESTful APIs.
 * For the current version and information visit <https://phpsx.org>
 *
 * Copyright 2010-2023 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace PSX\Schema;

use PSX\Schema\Exception\InvalidSchemaException;
use PSX\Schema\Type\AnyPropertyType;
use PSX\Schema\Type\BooleanPropertyType;
use PSX\Schema\Type\GenericPropertyType;
use PSX\Schema\Type\IntersectionType;
use PSX\Schema\Type\NumberPropertyType;
use PSX\Schema\Type\ReferencePropertyType;
use PSX\Schema\Type\StringPropertyType;
use PSX\Schema\Type\StructDefinitionType;
use PSX\Schema\Type\UnionType;

/**
 * TypeAssert
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://phpsx.org
 */
class TypeAssert
{
    /**
     * @param TypeInterface $type
     * @see https://typeschema.org/specification#Properties
     * @throws InvalidSchemaException
     */
    public static function assertProperty(TypeInterface $type): void
    {
        if ($type instanceof StructDefinitionType) {
            throw new InvalidSchemaException('Property must not contain a nested struct, got ' . get_class($type));
        }
    }

    /**
     * @param TypeInterface $type
     * @see https://typeschema.org/specification#ArrayType
     * @throws InvalidSchemaException
     */
    public static function assertItem(TypeInterface $type): void
    {
        if (!($type instanceof BooleanPropertyType
            || $type instanceof NumberPropertyType
            || $type instanceof StringPropertyType
            || $type instanceof IntersectionType
            || $type instanceof UnionType
            || $type instanceof ReferencePropertyType
            || $type instanceof GenericPropertyType
            || $type instanceof AnyPropertyType)) {
            throw new InvalidSchemaException('Item must be of type boolean, number, string, intersection, union, reference, generic or any type, got ' . get_class($type));
        }
    }

    /**
     * @param array $items
     * @see https://typeschema.org/specification#IntersectionType
     * @throws InvalidSchemaException
     */
    public static function assertIntersection(array $items): void
    {
        foreach ($items as $index => $item) {
            if (!($item instanceof ReferencePropertyType)) {
                throw new InvalidSchemaException('All of item must be of type reference, at index ' . $index . ' we got ' . get_class($item));
            }
        }
    }

    /**
     * @param array $items
     * @see https://typeschema.org/specification#UnionType
     * @throws InvalidSchemaException
     */
    public static function assertUnion(array $items): void
    {
        foreach ($items as $index => $item) {
            if (!($item instanceof NumberPropertyType
                || $item instanceof StringPropertyType
                || $item instanceof BooleanPropertyType
                || $item instanceof ReferencePropertyType)) {
                throw new InvalidSchemaException('One of item must be of type string, number, boolean or reference, at index ' . $index . ' we got ' . get_class($item));
            }
        }
    }
}
