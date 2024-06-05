<?php

namespace App;

use Carbon\Carbon;

class Check
{
    private int $urlId;
    private string $h1;
    private string $statusCode;
    private string $title;
    private string $description;

    public function __construct(array $params)
    {
        $this->h1 = $params['h1'];
        $this->title = $params['title'];
        $this->description = $params['description'];
    }

    public function setStatusCode(string $statusCode)
    {
        $this->statusCode = $statusCode;
    }

    public function setUrlId(int $id)
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
