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

namespace PSX\Schema\Parser\TypeSchema;

/**
 * BCLayer
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://phpsx.org
 */
class BCLayer
{
    public static function transformDefinition(\stdClass $data): \stdClass
    {
        if (isset($data->patternProperties) && !isset($data->properties) && !isset($data->additionalProperties)) {
            // in this case we have a schema with only pattern properties
            $vars = get_object_vars($data->patternProperties);
            if (count($vars) === 1) {
                $data->additionalProperties = reset($vars);
            } else {
                $data->additionalProperties = true;
            }
        }

        if (!isset($data->type)) {
            if (isset($data->additionalProperties) || isset($data->schema)) {
                $data->type = 'map';
            } else {
                $data->type = 'struct';

                if (!isset($data->properties)) {
                    $data->properties = new \stdClass();
                }
            }
        } else {
            if ($data->type === 'object' && isset($data->properties)) {
                $data->type = 'struct';
            } elseif ($data->type === 'object' && isset($data->additionalProperties)) {
                $data->type = 'map';
            }
        }

        return $data;
    }

    public static function transformProperty(\stdClass $data): \stdClass
    {
        if (isset($data->{'$ref'})) {
            $data->type = 'reference';
        } elseif (isset($data->{'$generic'})) {
            $data->type = 'generic';
        }

        if (!isset($data->type)) {
            if (isset($data->additionalProperties)) {
                $data->type = 'map';
            } elseif (isset($data->items)) {
                $data->type = 'array';
            } elseif (isset($data->pattern) || isset($data->minLength) || isset($data->maxLength)) {
                $data->type = 'string';
            } elseif (isset($data->minimum) || isset($data->maximum)) {
                $data->type = 'number';
            }
        } else {
            if ($data->type === 'object' && isset($data->additionalProperties)) {
                $data->type = 'map';
            } elseif ($data->type === 'int') {
                $data->type = 'integer';
            }
        }

        return $data;
    }
}
