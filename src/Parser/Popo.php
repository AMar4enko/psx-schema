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

namespace PSX\Schema\Parser;

use Doctrine\Common\Annotations\Reader;
use InvalidArgumentException;
use PSX\Schema\Definitions;
use PSX\Schema\DefinitionsInterface;
use PSX\Schema\Parser\Popo\Annotation;
use PSX\Schema\Parser\Popo\ObjectReader;
use PSX\Schema\Parser\Popo\Resolver\Composite;
use PSX\Schema\ParserInterface;
use PSX\Schema\Property;
use PSX\Schema\PropertyInterface;
use PSX\Schema\PropertyType;
use PSX\Schema\Schema;
use PSX\Schema\Type\ArrayType;
use PSX\Schema\Type\BooleanType;
use PSX\Schema\Type\IntersectionType;
use PSX\Schema\Type\MapType;
use PSX\Schema\Type\NumberType;
use PSX\Schema\Type\ReferenceType;
use PSX\Schema\Type\ScalarType;
use PSX\Schema\Type\StringType;
use PSX\Schema\Type\StructType;
use PSX\Schema\Type\TypeAbstract;
use PSX\Schema\Type\UnionType;
use PSX\Schema\TypeInterface;
use ReflectionClass;

/**
 * Tries to import the data into a plain old php object
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class Popo implements ParserInterface
{
    /**
     * @var \Doctrine\Common\Annotations\Reader
     */
    protected $reader;

    /**
     * @var \PSX\Schema\Parser\Popo\Resolver\Composite
     */
    protected $resolver;

    /**
     * @param \Doctrine\Common\Annotations\Reader $reader
     */
    public function __construct(Reader $reader)
    {
        $this->reader   = $reader;
        $this->resolver = new Popo\Resolver\Composite(
            new Popo\Resolver\Native(),
            new Popo\Resolver\Documentor()
        );
    }

    public function parse($className)
    {
        if (!is_string($className)) {
            throw new InvalidArgumentException('Class name must be a string');
        }

        $definitions = new Definitions();
        $property    = $this->parseClass($className, $definitions);

        return new Schema($property, $definitions);
    }

    protected function parseClass(string $className, DefinitionsInterface $definitions)
    {
        $class = new ReflectionClass($className);

        if ($definitions->hasType($class->getShortName())) {
            return $definitions->getType($class->getShortName());
        }

        $type = $this->resolver->resolveClass($class);
        $annotations = $this->reader->getClassAnnotations($class);

        $definitions->addType($class->getShortName(), $type);

        if ($type instanceof TypeAbstract) {
            $this->parseCommonAnnotations($annotations, $type);
        }

        if ($type instanceof StructType) {
            $this->parseStructAnnotations($annotations, $type);
            $this->parseProperties($class, $type, $definitions);
        } else {
            throw new \RuntimeException('Could not determine class type');
        }

        $type->setAttribute(TypeAbstract::ATTR_CLASS, $class->getName());

        return $type;
    }

    private function parseProperties(ReflectionClass $class, StructType $property, DefinitionsInterface $definitions)
    {
        $properties = ObjectReader::getProperties($this->reader, $class);
        $mapping    = [];

        foreach ($properties as $key => $reflection) {
            if ($key != $reflection->getName()) {
                $mapping[$key] = $reflection->getName();
            }

            $type = $this->parseProperty($reflection);
            if ($type instanceof TypeInterface) {
                $property->addProperty($key, $type);

                $this->parseNested($type, $definitions);
            }
        }

        if (!empty($mapping)) {
            $property->setAttribute(TypeAbstract::ATTR_MAPPING, $mapping);
        }
    }

    private function parseProperty(\ReflectionProperty $reflection): ?TypeInterface
    {
        $type = $this->resolver->resolveProperty($reflection);
        $annotations = $this->reader->getPropertyAnnotations($reflection);

        if ($type instanceof TypeAbstract) {
            $this->parseCommonAnnotations($annotations, $type);
        }

        if ($type instanceof ScalarType) {
            $this->parseScalarAnnotations($annotations, $type);
        }

        if ($type instanceof MapType) {
            $this->parseMapAnnotations($annotations, $type);
        } elseif ($type instanceof ArrayType) {
            $this->parseArrayAnnotations($annotations, $type);
        } elseif ($type instanceof StringType) {
            $this->parseStringAnnotations($annotations, $type);
        } elseif ($type instanceof NumberType) {
            $this->parseNumberAnnotations($annotations, $type);
        }

        return $type;
    }

    private function parseCommonAnnotations(array $annotations, TypeAbstract $type)
    {
        foreach ($annotations as $annotation) {
            if ($annotation instanceof Annotation\Title) {
                $type->setTitle($annotation->getTitle());
            } elseif ($annotation instanceof Annotation\Description) {
                $type->setDescription($annotation->getDescription());
            } elseif ($annotation instanceof Annotation\Nullable) {
                $type->setNullable($annotation->isNullable());
            } elseif ($annotation instanceof Annotation\Deprecated) {
                $type->setDeprecated($annotation->isDeprecated());
            } elseif ($annotation instanceof Annotation\Readonly) {
                $type->setReadonly($annotation->isReadonly());
            }
        }
    }

    private function parseScalarAnnotations(array $annotations, ScalarType $type)
    {
        foreach ($annotations as $annotation) {
            if ($annotation instanceof Annotation\Format) {
                $type->setFormat($annotation->getFormat());
            } elseif ($annotation instanceof Annotation\Enum) {
                $type->setEnum($annotation->getEnum());
            }
        }
    }

    private function parseStructAnnotations(array $annotations, StructType $type)
    {
        foreach ($annotations as $annotation) {
            if ($annotation instanceof Annotation\Required) {
                $type->setRequired($annotation->getRequired());
            }
        }
    }

    private function parseMapAnnotations(array $annotations, MapType $type)
    {
        foreach ($annotations as $annotation) {
            if ($annotation instanceof Annotation\MinProperties) {
                $type->setMinProperties($annotation->getMinProperties());
            } elseif ($annotation instanceof Annotation\MaxProperties) {
                $type->setMaxProperties($annotation->getMaxProperties());
            }
        }
    }

    private function parseArrayAnnotations(array $annotations, ArrayType $type)
    {
        foreach ($annotations as $annotation) {
            if ($annotation instanceof Annotation\MinItems) {
                $type->setMinItems($annotation->getMinItems());
            } elseif ($annotation instanceof Annotation\MaxItems) {
                $type->setMaxItems($annotation->getMaxItems());
            } elseif ($annotation instanceof Annotation\UniqueItems) {
                $type->setUniqueItems($annotation->getUniqueItems());
            }
        }
    }

    private function parseStringAnnotations(array $annotations, StringType $type)
    {
        foreach ($annotations as $annotation) {
            if ($annotation instanceof Annotation\MinLength) {
                $type->setMinLength($annotation->getMinLength());
            } elseif ($annotation instanceof Annotation\MaxLength) {
                $type->setMaxLength($annotation->getMaxLength());
            } elseif ($annotation instanceof Annotation\Pattern) {
                $type->setPattern($annotation->getPattern());
            } elseif ($annotation instanceof Annotation\Format) {
                $type->setFormat($annotation->getFormat());
            }
        }
    }

    private function parseNumberAnnotations(array $annotations, NumberType $type)
    {
        foreach ($annotations as $annotation) {
            if ($annotation instanceof Annotation\Minimum) {
                $type->setMinimum($annotation->getMinimum());
            } elseif ($annotation instanceof Annotation\Maximum) {
                $type->setMaximum($annotation->getMaximum());
            } elseif ($annotation instanceof Annotation\ExclusiveMinimum) {
                $type->setExclusiveMinimum($annotation->getExclusiveMinimum());
            } elseif ($annotation instanceof Annotation\ExclusiveMaximum) {
                $type->setExclusiveMaximum($annotation->getExclusiveMaximum());
            } elseif ($annotation instanceof Annotation\MultipleOf) {
                $type->setMultipleOf($annotation->getMultipleOf());
            }
        }
    }

    private function parseNested(TypeInterface $type, DefinitionsInterface $definitions)
    {
        if ($type instanceof MapType) {
            $additionalProperties = $type->getAdditionalProperties();
            if ($additionalProperties instanceof ReferenceType) {
                $this->parseClass($additionalProperties->getRef(), $definitions);
            }
        } elseif ($type instanceof ArrayType) {
            $items = $type->getItems();
            if ($items instanceof ReferenceType) {
                $this->parseClass($items->getRef(), $definitions);
            }
        } elseif ($type instanceof UnionType) {
            $items = $type->getOneOf();
            foreach ($items as $item) {
                if ($item instanceof ReferenceType) {
                    $this->parseClass($item->getRef(), $definitions);
                }
            }
        } elseif ($type instanceof IntersectionType) {
            $items = $type->getAllOf();
            foreach ($items as $item) {
                if ($item instanceof ReferenceType) {
                    $this->parseClass($item->getRef(), $definitions);
                }
            }
        } elseif ($type instanceof ReferenceType) {
            $this->parseClass($type->getRef(), $definitions);
        }
    }
}
