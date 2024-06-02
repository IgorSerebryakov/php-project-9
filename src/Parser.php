<?php

namespace App;

use DiDom\Document;
use Illuminate\Support;

class Parser
{
    private Document $document;
    
    public function __construct(Url $url)
    {
        $this->document = new Document($url->getName(), true);
    }
    
    public function getH1()
    {
        return optional($this->document->first('h1')->innerHtml());
    }
    
    public function getTitle()
    {
        return optional($this->document->first('title')->innerHtml());
    }
    
    public function getDescription()
    {
        return optional($this->document->first('meta[name=description]'))->attr('content');
    }
}