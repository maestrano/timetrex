<?php

class Maestrano_Api_InvalidRequestError extends Maestrano_Api_Error
{
  public function __construct($message, $param, $httpStatus=null,
      $httpBody=null, $jsonBody=null
  )
  {
    parent::__construct($message, $httpStatus, $httpBody, $jsonBody);
    $this->param = $param;
  }
}
