<?php

namespace Album\Model;

class Album
{
    public $json;

    public function __construct()
    {
        $this->json = 'no data found';
    }

    public function setJson($id) {
        $this->json = "hello i am " . $id;
    }
}