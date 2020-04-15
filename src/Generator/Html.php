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

/**
 * Html
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class Html extends MarkupAbstract
{
    /**
     * @inheritDoc
     */
    protected function newTypeGenerator(): GeneratorInterface
    {
        return new Type\Html();
    }

    /**
     * @inheritDoc
     */
    protected function writeStruct(string $name, array $properties, ?string $extends, ?string $comment, ?array $generics): string
    {
        $return = '<div id="' . htmlspecialchars($name) . '" class="psx-object">';
        $return.= '<h' . $this->heading . '>' . htmlspecialchars($name) . '</h' . $this->heading . '>';

        if (!empty($comment)) {
            $return.= '<div class="psx-object-description">' . htmlspecialchars($comment) . '</div>';
        }

        $prop = '<table class="table psx-object-properties">';
        $prop.= '<colgroup>';
        $prop.= '<col width="30%" />';
        $prop.= '<col width="70%" />';
        $prop.= '</colgroup>';
        $prop.= '<thead>';
        $prop.= '<tr>';
        $prop.= '<th>Field</th>';
        $prop.= '<th>Description</th>';
        $prop.= '</tr>';
        $prop.= '</thead>';
        $prop.= '<tbody>';

        $json = '<span class="psx-object-json-pun">{</span>' . "\n";

        foreach ($properties as $name => $property) {
            /** @var Code\Property $property */
            $constraints = $this->getConstraints($property->getOrigin());

            $prop.= '<tr>';
            $prop.= '<td><span class="psx-property-name ' . ($property->isRequired() ? 'psx-property-required' : 'psx-property-optional') . '">' . $name . '</span></td>';
            $prop.= '<td>';
            $prop.= '<span class="psx-property-type">' . $property->getType() . '</span><br />';
            $prop.= '<div class="psx-property-description">' . htmlspecialchars($property->getComment()) . '</div>';
            $prop.= !empty($constraints) ? $this->writeConstraints($constraints) : '';
            $prop.= '</td>';
            $prop.= '</tr>';

            $json.= '  ';
            $json.= '<span class="psx-object-json-key">"' . $name . '"</span>';
            $json.= '<span class="psx-object-json-pun">: </span>';
            $json.= '<span class="psx-property-type">' . $property->getType() . '</span>';
            $json.= '<span class="psx-object-json-pun">,</span>';
            $json.= "\n";
        }

        $json.= '<span class="psx-object-json-pun">}</span>';

        $prop.= '</tbody>';
        $prop.= '</table>';

        $return.= '<pre class="psx-object-json">' . $json . '</pre>';
        $return.= $prop;
        $return.= '</div>';

        return $return . "\n";
    }

    /**
     * @inheritDoc
     */
    protected function writeMap(string $name, string $type): string
    {
        $return = '<div id="' . htmlspecialchars($name) . '" class="psx-object">';
        $return.= '<h' . $this->heading . '>' . htmlspecialchars($name) . '</h' . $this->heading . '>';

        $prop = '<table class="table psx-object-properties">';
        $prop.= '<colgroup>';
        $prop.= '<col width="30%" />';
        $prop.= '<col width="70%" />';
        $prop.= '</colgroup>';
        $prop.= '<thead>';
        $prop.= '<tr>';
        $prop.= '<th>Field</th>';
        $prop.= '<th>Description</th>';
        $prop.= '</tr>';
        $prop.= '</thead>';
        $prop.= '<tbody>';

        $json = '<span class="psx-object-json-pun">{</span>' . "\n";

        $prop.= '<tr>';
        $prop.= '<td><span class="psx-property-name psx-property-optional">*</span></td>';
        $prop.= '<td>';
        $prop.= '<span class="psx-property-type">' . $type . '</span><br />';
        $prop.= '<div class="psx-property-description"></div>';
        $prop.= '</td>';
        $prop.= '</tr>';

        $json.= '  ';
        $json.= '<span class="psx-object-json-key">"*"</span>';
        $json.= '<span class="psx-object-json-pun">: </span>';
        $json.= '<span class="psx-property-type">' . $type . '</span>';
        $json.= '<span class="psx-object-json-pun">,</span>';
        $json.= "\n";

        $json.= '<span class="psx-object-json-pun">}</span>';

        $prop.= '</tbody>';
        $prop.= '</table>';

        $return.= '<pre class="psx-object-json">' . $json . '</pre>';
        $return.= $prop;
        $return.= '</div>';

        return $return . "\n";
    }

    /**
     * @param array $constraints
     * @return string
     */
    protected function writeConstraints(array $constraints)
    {
        $return = '<dl class="psx-property-constraint">';
        foreach ($constraints as $name => $constraint) {
            if (empty($constraint)) {
                continue;
            }

            $return.= '<dt>' . htmlspecialchars(ucfirst($name)) . '</dt>';
            $return.= '<dd>';

            $type = strtolower($name);
            if ($name == 'enum') {
                $return.= '<ul class="psx-constraint-enum">';
                foreach ($constraint as $prop) {
                    $return.= '<li><code>' . htmlspecialchars(json_encode($prop)) . '</code></li>';
                }
                $return.= '</ul>';
            } elseif ($name == 'const') {
                $return.= '<span class="psx-constraint-const">';
                $return.= '<code>' . htmlspecialchars(json_encode($constraint)) . '</code>';
                $return.= '</span>';
            } else {
                $return.= '<span class="psx-constraint-' . $type . '">' . htmlspecialchars($constraint) . '</span>';
            }

            $return.= '</dd>';
        }
        $return.= '</dl>';

        return $return;
    }
}
