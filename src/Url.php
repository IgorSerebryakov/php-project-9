<?php

namespace App;

use Carbon\Carbon;
use Valitron\Validator;

class Url
{
    private mixed $params;
    private $id;

    private bool $isNew = false;
    private $errors;

    public function __construct(mixed $params)
    {
        $this->params = $params;
    }

    public function isValid(): bool
    {
        $validator = new Validator($this->params);
        $validator->rules([
            'required' => [
                ['name']
            ],
            'lengthMax' => [
                ['name', 255]
            ],
            'url' => [
                ['name']
            ]
        ]);

        if (!$validator->validate()) {
            $this->errors = $validator->errors();
        }

        return $validator->validate();
    }

    public function isNew(): bool
    {
        return $this->isNew === true;
    }

    public function setNew()
    {
        $this->isNew = true;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getErrors()
    {
        return $this->errors['name'];
    }

    public function getName()
    {
        return $this->params['name'];
    }

    public function getCreatedAt()
    {
        return Carbon::now();
    }
}
