<?php

namespace Harchibald;

class Configuration 
{
  const SEPARATOR = '/';
  
  protected $parameters = array();
  protected $prefix = array();
  protected $boxes = array();
  
  public function __construct(array $parameters = array())
  {
    $this->merge($parameters);
  }
  
  public function box($box)
  {
    $this->boxes[] = $box;
    
    return $this;
  }
  
  public function endBox($all = false)
  {
    if ($all)
    {
      $this->boxes = array();
    }
    elseif (count($this->boxes) > 0)
    {
      array_pop($this->boxes);  
    }
    
    return $this;
  }
  
  public function inBoxMode()
  {
    return count($this->boxes) > 0;
  }
  
  public function getPrefix()
  {
    if (count($this->boxes) == 1 && $this->boxes[0] == '/')
    {
      return array();
    }
    
    return array_merge($this->prefix, $this->boxes);
  }
  
  public function checkAndThrowForBoxMode()
  {
    if ($this->inBoxMode())
    {
      throw new Exception('You are in Box mode, you can\'t modify the prefix');  
    }
  }
  
  public function prefix($prefix)
  {
    $this->checkAndThrowForBoxMode();
    
    $this->prefix = $this->explodePath($prefix);
    
    return $this;
  }
  
  public function resetPrefix()
  {
    $this->checkAndThrowForBoxMode();
    
    $this->setPrefix(null);
    
    return $this;
  }
  
  public function addToPrefix($path)
  {
    $this->checkAndThrowForBoxMode();
    
    $this->prefix = array_merge($this->prefix, $this->explodePath($path));
    
    return $this;
  }
  
  public function removeFromPrefix($path)
  {
    $this->checkAndThrowForBoxMode();
    
    $exploded_path = $this->explodePath($path);
    $first = reset($exploded_path);
    $count_paths = count($exploded_path);
    
    $nb_removed = 0;
    foreach (array_keys($this->prefix, $first) as $key) 
    {
      $key -= $nb_removed * $count_paths; 
      
      $slice = array_slice($this->prefix, $key, $count_paths);
      
      if (count($slice) != $count_paths)
      {
        continue;
      }
      
      $founded_piece = 0;
      foreach ($exploded_path as $piece_key => $piece) 
      {
        if ($slice[$piece_key] == $piece)
        {
          $founded_piece++;
        } 
      }

      if ($founded_piece == $count_paths)
      {
        array_splice($this->prefix, $key, $count_paths);
        $nb_removed++;
      }
    }
    
    return $this;
  }
  
  protected function explodePath($path)
  {
    return explode(self::SEPARATOR, $path);
  }
  
  protected function getCompleteExplodedPath($path)
  {
    $path = $this->getPathWithPrefix($path);
    
    return $this->explodePath($path);
  }
  
  protected function getPathWithPrefix($path)
  {
    $prefix = $this->getPrefix();
    if (!empty($prefix))
    {
      $prefix_path = implode(self::SEPARATOR, $this->getPrefix());
      
      if ($path)
      {
        $path = $prefix_path.self::SEPARATOR.$path;
      }
      else 
      {
        return $prefix_path;  
      }
    }
    
    return $path;
  }
  
  public function has($path)
  {
    $path = $this->getCompleteExplodedPath($path);
    
    return $this->recursiveHas($path, $this->parameters);
  }
  
  protected function recursiveHas(array $path, array $parameters)
  {
    $name = array_shift($path);
    
    if (count($path) == 0)
    {
      if ($name)
      {
        return isset($parameters[$name]);
      }
      else 
      {
        return isset($parameters);
      }
    }
    else
    {
      if (!isset($parameters[$name]))
      {
        return false;
      }
      
      return $this->recursiveHas($path, $parameters[$name]);
    }
  }

  public function set($path, $value)
  {
    $path = $this->getCompleteExplodedPath($path);
    
    $this->parameters = $this->recursiveSet($path, $value, $this->parameters);
    
    return $this;
  }
  
  protected function recursiveSet(array $path, $value, $parameters)
  {
    $name = array_shift($path);
        
    if (count($path) == 0)
    {
      if ($name)
      {
        $parameters[$name] = $value;  
      }
      else 
      {
        $parameters = $value;  
      }
    }
    else 
    {
      if (!isset($parameters[$name]))
      {
        $parameters[$name] = array();
      }
      
      $parameters[$name] = array_merge($parameters[$name], $this->recursiveSet($path, $value, $parameters[$name]));
    }
    
    return $parameters;
  }
  
  public function get($path, $default = null)
  {
    $path = $this->getCompleteExplodedPath($path);

    return $this->recursiveGet($path, $this->parameters, $default);
  }
  
  protected function recursiveGet(array $path, $parameters, $default = null)
  {
    $name = array_shift($path);
    
    if (count($path) == 0)
    {
      if ($name)
      {
        return isset($parameters[$name])?$parameters[$name]:$default;  
      }
      else 
      {
        return $parameters;  
      }
    }
    else 
    {
      if (!isset($parameters[$name]))
      {
        return $default;
      }
      
      return $this->recursiveGet($path, $parameters[$name], $default);
    }
  }
  
  public function merge(array $parameters, $path = null)
  {
    $old_value = $this->get($path, array());

    if (!is_array($old_value))
    {
      throw new \Exception('Can\'t merge a non array value');
    }
    
    $this->set($path, $this->recursiveMerge($old_value, $parameters));
    
    return $this; 
  }
  
  protected function recursiveMerge($array1, $array2)
  {
    if (!is_array($array1) && !is_array($array2))
    {
      return $array2;
    }
    elseif (!is_array($array1) && is_array($array2)) 
    {
      array_unshift($array2, $array1);
      
      return $array2;
    }
    elseif(is_array($array1) && !is_array($array2))
    {
      $array1[] = $array2;
      
      return $array1;
    }
    else 
    {
      foreach ($array2 as $key => $value) 
      {
        if (isset($array1[$key]))
        {
          $array1[$key] = $this->recursiveMerge($array1[$key], $array2[$key]);
        }
        else 
        {
          $array1[$key] = $array2[$key];
        }
      }
      
      return $array1; 
    }
  }
  
  public function all()
  {
    return $this->get(null);
  }
  
  public function remove($path)
  {
    $path = $this->getCompleteExplodedPath($path);
    $parameters = $this->recursiveRemove($path, $this->parameters);
    
    $this->set(null, $parameters);
    
    return $this;
  }
  
  protected function recursiveRemove($path, $parameters)
  {
    $name = array_shift($path);
        
    if (count($path) == 0)
    {
      if ($name)
      {
        unset($parameters[$name]);  
      }
      else 
      {
        $parameters = array();
      }
    }
    else 
    {
      if (!isset($parameters[$name]))
      {
        return $parameters;
      }
      
      $parameters[$name] = array_merge($parameters[$name], $this->recursiveRemove($path, $value, $parameters[$name]));
    }
    
    return $parameters;
  }
  
  public function clear($path)
  {
    $this->set($path, array());
    
    return $this;
  }
}
