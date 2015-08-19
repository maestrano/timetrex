<?php

class Maestrano_Util_PresetObject
{
  /* Internal Config Map */
  public static $preset_cache = array();
  protected $_preset;

  /**
   * Create a preset proxy
   * @return a preset proxy
   */
  public static function with($preset) {
    $cname = get_called_class();
    if (is_null($preset)) {
      $preset = 'maestrano';
    }

    if (!array_key_exists($cname, self::$preset_cache) || is_null(self::$preset_cache[$cname])) {
      self::$preset_cache[$cname] = array();
    }

    if (!array_key_exists($preset, self::$preset_cache[$cname]) || is_null(self::$preset_cache[$cname][$preset])) {
      self::$preset_cache[$cname][$preset] = new Maestrano_Util_PresetProxy(get_called_class(),$preset);
    }

    return self::$preset_cache[$cname][$preset];
  }

  public static function __callStatic($name, $arguments)
  {
    if (method_exists(get_called_class(),$name . 'WithPreset')) {
      array_unshift($arguments,'maestrano');
      return call_user_func_array(get_called_class() . '::' . $name . 'WithPreset',$arguments);
    } else {
      throw new BadMethodCallException('Method ' . $name . ' does not exist');
    }
  }
}
