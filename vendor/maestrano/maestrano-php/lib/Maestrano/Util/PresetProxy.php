<?php

class Maestrano_Util_PresetProxy
{

  public function __construct($class_name, $preset = 'maestrano')
  {
    $this->class_name = $class_name;
    $this->preset = $preset;
  }

  public function __call($name, $arguments)
  {
    array_unshift($arguments,$this->preset);
    return call_user_func_array($this->class_name . '::' . $name . 'WithPreset',$arguments);
  }
}
