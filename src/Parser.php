<?php

namespace App;

use DiDom\Document;
use Psr\Http\Message\ResponseInterface;

use function optional;

class Parser
{
    private Document $document;
    private ResponseInterface $response;

    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
        $html = $response->getBody()->__toString();
        $this->document = new Document();
        $this->document->loadHtml($html);
    }

    public function getHtmlParams()
    {
        $h1 = $this->getTag('h1');
        $title = $this->getTag('title');
        $metaName = $this->getTag('meta[name=description]');

        $h1 = ($h1 != null) ? $h1->innerHtml() : null;
        $title = ($h1 != null) ? $title->innerHtml() : null;
        $description = ($metaName != null) ? $metaName->attr('content') : null;

        return new Check(
            ['h1' => $h1,
             'title' => $title,
             'description' => $description
            ]
        );
    }

    private function getTag(string $tagSearch)
    {
        return optional($this->document)->first($tagSearch);
    }
}
