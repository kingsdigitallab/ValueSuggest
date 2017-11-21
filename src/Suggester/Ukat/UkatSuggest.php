<?php
namespace ValueSuggest\Suggester\Ukat;

use ValueSuggest\Suggester\SuggesterInterface;
use Zend\Http\Client;

class UkatSuggest implements SuggesterInterface
{
    const ENDPOINT = 'http://localhost:3030/ukat/';

    /**
     * @var Client
     */
    protected $_client;

    public function __construct(Client $client)
    {
        $this->_client = $client;
    }

    /**
     * Retrieve suggestions from the UKAT Vocabularies SPARQL endpoint.
     *
     * @param string $query
     * @return array
     */
    public function getSuggestions($query)
    {
        $sparqlQuery = sprintf(
            'PREFIX dc: <http://purl.org/dc/elements/1.1/>
            PREFIX skos: <http://www.w3.org/2004/02/skos/core#>
            PREFIX foaf: <http://xmlns.com/foaf/0.1/>
            SELECT ?subject ?title ?topic ?info WHERE {
              {
                ?subject dc:title ?value .
                ?subject dc:title ?title .
              } UNION {
                ?subject skos:prefLabel ?value .
                ?subject skos:prefLabel ?title .
              } UNION {
                ?subject skos:altLabel ?value .
                ?subject skos:altLabel ?title .
                ?subject skos:prefLabel ?topic .
              }
              OPTIONAL { ?subject  skos:scopeNote ?info }
              FILTER regex(?value, "^%s", "i")
            }
            ORDER BY ASC(lcase(str(?topic))) ASC(lcase(str(?title)))
            LIMIT 100',
            addslashes($query)
        );
        $client = $this->_client->setUri(self::ENDPOINT)->setParameterGet([
            'query' => $sparqlQuery,
        ]);

        // Must include Accept header or endpoint returns 400 Bad Request with
        // message: "The request sent by the client was syntactically incorrect."
        $client->getRequest()->getHeaders()->addHeaderLine('Accept', 'application/sparql-results+json');
        $response = $client->send();
        if (!$response->isSuccess()) {
            return [];
        }

        $suggestions = [];
        $results = json_decode($response->getBody(), true);
        foreach ($results['results']['bindings'] as $result) {
            $topic = '';
            if (isset($result['topic']['value'])) {
                $topic = ' (preferred term: ' . $result['topic']['value'] . ')';
            }

            $info = '';
            if (isset($result['info']['value'])) {
                $info = $result['info']['value'];
            }
            $suggestions[] = [
                'value' => $result['title']['value'] . $topic,
                'data' => [
                    'uri' => $result['subject']['value'],
                    'info' => $info,
                ],
            ];
        }

        return $suggestions;
    }
}
