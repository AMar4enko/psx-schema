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

namespace PSX\Schema\Generator;

use PSX\Schema\Generator\Type\GeneratorInterface;
use PSX\Schema\TypeInterface;

/**
 * CSharp
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class CSharp extends CodeGeneratorAbstract
{
    /**
     * @inheritDoc
     */
    protected function newTypeGenerator(): GeneratorInterface
    {
        return new Type\CSharp();
    }

    /**
     * @inheritDoc
     */
    protected function writeStruct(string $name, array $properties, ?string $extends, ?array $generics, TypeInterface $origin): string
    {
        $code = $this->writeHeader($origin->getDescription());
        $code.= 'public class ' . $name;

        if (!empty($generics)) {
            $code.= '<' . implode(', ', $generics) . '>';
        }

        if (!empty($extends)) {
            $code.= ' extends ' . $extends;
        }

        $code.= "\n";
        $code.= '{' . "\n";

        foreach ($properties as $name => $property) {
            /** @var Code\Property $property */
            $code.= $this->indent . 'public ' . $property->getType() . ' ' . ucfirst($name) . ' { get; set; }' . "\n";
        }

        $code.= '}' . "\n";
        $code.= $this->writerFooter();

        return $code;
    }

    private function writeHeader(?string $comment)
    {
        $code = '';

        if (!empty($this->namespace)) {
            $code.= 'namespace ' . $this->namespace . "\n";
            $code.= '{' . "\n";
        }

        if (!empty($comment)) {
            $code.= '/// <summary>' . "\n";
            $code.= '/// ' . $comment . "\n";
            $code.= '/// </summary>' . "\n";
        }

        return $code;
    }

    private function writerFooter()
    {
        if (!empty($this->namespace)) {
            return '}' . "\n";
        } else {
            return '';
        }
    }
}
