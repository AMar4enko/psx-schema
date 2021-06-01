<?php
/*
 * PSX is a open source PHP framework to develop RESTful APIs.
 * For the current version and informations visit <http://phpsx.org>
 *
 * Copyright 2010-2020 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace PSX\Schema\Generator;

use PSX\Schema\Generator\Type\GeneratorInterface;
use PSX\Schema\Type\IntersectionType;
use PSX\Schema\Type\MapType;
use PSX\Schema\Type\ReferenceType;
use PSX\Schema\Type\StructType;
use PSX\Schema\Type\TypeAbstract;
use PSX\Schema\Type\UnionType;

/**
 * Kotlin
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class Kotlin extends CodeGeneratorAbstract
{
    /**
     * @inheritDoc
     */
    public function getFileName(string $file): string
    {
        return $file . '.kt';
    }

    /**
     * @inheritDoc
     */
    protected function newTypeGenerator(array $mapping): GeneratorInterface
    {
        return new Type\Kotlin($mapping);
    }

    /**
     * @inheritDoc
     */
    protected function writeStruct(string $name, array $properties, ?string $extends, ?array $generics, StructType $origin): string
    {
        $code = 'open class ' . $name;

        if (!empty($generics)) {
            $code.= '<' . implode(', ', $generics) . '>';
        }

        if (!empty($extends)) {
            $code.= ' : ' . $extends;
        }

        $code.= ' {' . "\n";

        foreach ($properties as $name => $property) {
            /** @var Code\Property $property */
            $code.= $this->indent . 'var ' . $name . ': ' . $property->getType() . '? = null' . "\n";
        }

        foreach ($properties as $name => $property) {
            /** @var Code\Property $property */
            $code.= $this->indent . 'open fun set' . ucfirst($name) . '(' . $name . ': ' . $property->getType() . '?) {' . "\n";
            $code.= $this->indent . $this->indent . 'this.' . $name . ' = ' . $name . ';' . "\n";
            $code.= $this->indent . '}' . "\n";

            $code.= $this->indent . 'open fun get' . ucfirst($name) . '(): ' . $property->getType() . '? {' . "\n";
            $code.= $this->indent . $this->indent . 'return this.' . $name . ';' . "\n";
            $code.= $this->indent . '}' . "\n";
        }

        $code.= '}' . "\n";

        return $code;
    }

    protected function writeMap(string $name, string $type, MapType $origin): string
    {
        $subType = $this->generator->getType($origin->getAdditionalProperties());

        $code = 'open class ' . $name . ' : HashMap<String, ' . $subType . '>() {' . "\n";
        $code.= '}' . "\n";

        return $code;
    }

    protected function writeReference(string $name, string $type, ReferenceType $origin): string
    {
        return 'typealias ' . $name . ' = ' . $type . "\n";
    }

    protected function writeHeader(TypeAbstract $origin): string
    {
        $code = '';

        if (!empty($this->namespace)) {
            $code.= 'package ' . $this->namespace . ';' . "\n";
        }

        $comment = $origin->getDescription();
        if (!empty($comment)) {
            $code.= '/**' . "\n";
            $code.= ' * ' . $comment . "\n";
            $code.= ' */' . "\n";
        }

        return $code;
    }
}