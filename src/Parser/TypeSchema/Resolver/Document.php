<?php
/*
 * PSX is an open source PHP framework to develop RESTful APIs.
 * For the current version and information visit <https://phpsx.org>
 *
 * Copyright 2010-2022 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace PSX\Schema\Parser\TypeSchema\Resolver;

use PSX\Http\Client\ClientInterface;
use PSX\Http\Client\PostRequest;
use PSX\Json\Parser;
use PSX\Schema\Exception\ParserException;
use PSX\Schema\Parser\TypeSchema\ResolverInterface;
use PSX\Uri\Uri;
use PSX\Uri\Url;

/**
 * Resolver which loads documents from the typehub.cloud platform
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class Document implements ResolverInterface
{
    private const API_URL = 'https://api.typehub.cloud/document/%s/%s/export';

    /**
     * @var ClientInterface
     */
    private $httpClient;

    public function __construct(ClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function resolve(Uri $uri, ?string $basePath = null): \stdClass
    {
        $document = ltrim($uri->getPath(), '/');
        $user = $uri->getHost();
        $version = $uri->getParameter('version');

        $url = $this->export($user, $document, $version);

        $request  = new PostRequest($url, ['Accept' => 'application/json', 'User-Agent' => Http::USER_AGENT]);
        $response = $this->httpClient->request($request);

        if ($response->getStatusCode() !== 200) {
            throw new ParserException('Could not export provided document: ' . $user . '/' . $document . ' received ' . $response->getStatusCode());
        }

        return Parser::decode((string) $response->getBody());
    }

    private function export(string $user, string $document, ?string $version): Url
    {
        $request  = new PostRequest($this->getApiUrl($user, $document, $version), ['Accept' => 'application/json', 'User-Agent' => Http::USER_AGENT]);
        $response = $this->httpClient->request($request);

        if ($response->getStatusCode() !== 200) {
            throw new ParserException('Could not export provided document: ' . $user . '/' . $document . ' received ' . $response->getStatusCode());
        }

        $data = Parser::decode((string) $response->getBody(), true);

        $exportLink = $data['href'] ?? null;
        if (empty($exportLink)) {
            throw new ParserException('Could not find a fitting export url');
        }

        return new Url($exportLink);
    }

    private function getApiUrl(string $user, string $document, ?string $version): Url
    {
        $url = new Url(sprintf(self::API_URL, $user, $document));
        $parameters = ['format' => 'typeschema'];
        if (!empty($version)) {
            $parameters['version'] = $version;
        }

        return $url->withParameters($parameters);
    }
}
