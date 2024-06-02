<?php

namespace App;

use Carbon\Carbon;

class Check extends Parser
{
    private $urlId;
    private $h1;
    private $statusCode;
    private $title;
    private $description;
    
    public function __construct(Url $url)
    {
        parent::__construct($url);
        
        $this->urlId = $url->getId();
        $this->h1 = $this->getH1();
        $this->title = $this->getTitle();
        $this->description = $this->getDescription();
    }
    
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
    }

    public function getCreatedAt()
    {
        return Carbon::now();
    }
}