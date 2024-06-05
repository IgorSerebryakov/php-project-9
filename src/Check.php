<?php

namespace App;

use Carbon\Carbon;

class Check
{
    private $urlId;
    private $h1;
    private $statusCode;
    private $title;
    private $description;

    public function __construct($params)
    {
        $this->h1 = $params['h1'];
        $this->title = $params['title'];
        $this->description = $params['description'];
    }

    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
    }

    public function setUrlId($id)
    {
        $this->urlId = $id;
    }

    public function getUrlId()
    {
        return $this->urlId;
    }

    public function getH1()
    {
        return $this->h1;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getCreatedAt()
    {
        return Carbon::now();
    }
}
