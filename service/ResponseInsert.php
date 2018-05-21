<?php

namespace CaixaWebService\service;


class ResponseInsert extends ResponseBase
{
    function __construct(array $arr){
        $this->response = $arr;
    }

    public function __toString()
    {
        return 'Ninety nine green bottles';
    }
}