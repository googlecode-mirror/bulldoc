<?php
/**********************************************************************************
* Copyright 2002-2006 H-type. http://www.h-type.com, mailto: smirnov@h-type.com
*
* Released under the MIT license (http://www.opensource.org/licenses/mit-license.html)
***********************************************************************************
*
* $Id: dataset.lib.php 186 2007-05-07 15:24:32Z hamster $
***********************************************************************************/

class colesoDataset implements ArrayAccess,Countable,Iterator
{
  protected $_count=0;
  protected $_index=0;
  protected $_data=array();
//---------------------------------------------------------------------------
  public function __construct($array=null)
  {
    if ($array) $this->assignArray($array);
  }
//---------------------------------------------------------------------------
  public function assignArray($array)
  {
    $this->_data = array();
    $this->addArray($array);
  }
//---------------------------------------------------------------------------
  public function addArray($array)
  {
    foreach ($array as $key => $value) {
      if (is_array($value)) {
          $this->_data[$key] = new colesoDataset($value);
      } else {
          $this->_data[$key] = $value;
      }
    }
    $this->_count = count($this->_data);
  }
//---------------------------------------------------------------------------
  public function getOne()
  {
    reset($this->_data);
    list($k,$v) = each($this->_data);
    return $v;
  }
//---------------------------------------------------------------------------
  public function getOneKey()
  {
    reset($this->_data);
    list($k,$v) = each($this->_data);
    return $k;
  }
//---------------------------------------------------------------------------
  public function get($name, $default = null)
  {
    $result = $default;
    if (array_key_exists($name, $this->_data)) {
        $result = $this->_data[$name];
    }
    return $result;
  }
//---------------------------------------------------------------------------
  public function getSet($name)
  {
    if (array_key_exists($name, $this->_data)) {
      $result = $this->_data[$name];
      if (!($result instanceof colesoDataset)) throw Exception('Requested field is a scalar value');
    } else $result=new colesoDataset();
    return $result;
  }
//---------------------------------------------------------------------------
  public function set($name, $value)
  {
    if (is_array($value)) {
      $this->_data[$name] = new colesoDataset($value, true);
    } else {
      $this->_data[$name] = $value;
    }
    $this->_count = count($this->_data);
  }
//---------------------------------------------------------------------------
  public function __get($name)
  {
    return $this->get($name);
  }
//---------------------------------------------------------------------------
  public function __set($name, $value)
  {
    $this->set($name, $value);
  }
//---------------------------------------------------------------------------
  public function toArray()
  {
    $array = array();
    foreach ($this->_data as $key => $value) {
      if ($value instanceof colesoDataset) {
        $array[$key] = $value->toArray();
      } else {
        $array[$key] = $value;
      }
    }
    return $array;
  }
//---------------------------------------------------------------------------
  public function __isset($name)
  {
    return isset($this->_data[$name]);
  }
//---------------------------------------------------------------------------
  public function __unset($name)
  {
    unset($this->_data[$name]);
  }
//---------------------------------------------------------------------------
  public function count()
  {
      return $this->_count;
  }
//---------------------------------------------------------------------------
  public function insertBefore($key,$value)
  {
    if (is_array($value)) $value=colesoDataset($value);
    $this->_data=array($key=>$value)+$this->_data;
  }
//---------------------------------------------------------------------------
  public function merge(colesoDataset $merge)
  {
    foreach($merge as $key => $item) {
      if(array_key_exists($key, $this->_data)) {
        if($item instanceof colesoDataset && $this->$key instanceof colesoDataset) {
            $this->$key = $this->$key->merge($item);
        } else {
            $this->$key = $item;
        }
      } else {
        $this->$key = $item;
      }
    }
    return $this;
  }
//---------------------------------------------------------------------------
  public function offsetExists($offset)
  {
    return isset($this->_data[$offset]);
  }
//---------------------------------------------------------------------------
  public function offsetGet($offset)
  {
    return isset($this->_data[$offset])? $this->_data[$offset]:'';
  }
//---------------------------------------------------------------------------
  public function offsetSet($offset,$value)
  {
    $this->_data[$offset]=$value;
  }
//---------------------------------------------------------------------------
  public function offsetUnset($offset)
  {
    unset ($this->_data[$offset]);
  }
//---------------------------------------------------------------------------
  public function current()
  {
    return current($this->_data);
  }
//---------------------------------------------------------------------------
  public function key()
  {
    return key($this->_data);
  }
//---------------------------------------------------------------------------
  public function next()
  {
    next($this->_data);
  }
//---------------------------------------------------------------------------
  public function rewind()
  {
    reset($this->_data);
  }
//---------------------------------------------------------------------------
  public function valid()
  {
    return current($this->_data)!==false;
  }  
}
?>