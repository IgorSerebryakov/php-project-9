<?php

namespace App;

use DiDom\Document;

use function optional;

class Parser
{
    private Document $document;

    public function __construct(Url $url)
    {
        $this->document = new Document($url->getName(), true);
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

    private function getTag($tagSearch)
    {
        return optional($this->document)->first($tagSearch);
    }
}
