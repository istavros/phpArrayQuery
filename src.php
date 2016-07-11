<?php

interface iCollection {
  public function select($callback);
  public function where($callback);
  public function distinct($callback);
  public function first($item_count = 1);
  public function last($item_count);
  public function toArray();
}

class Collection extends ArrayIterator implements iCollection {
  public function where($callback) {
    return new WhereIterator($this, $callback);
  }

  public function distinct($callback) {
    return new DistinctIterator($this, $callback);
  }

  public function select($callback) {
    return new SelectIterator($this, $callback);
  }

  public function first($item_count = 1) {
    return new FirstIterator($this, $item_count);
  }

  public function last($item_count = 1) {
    return new LastIterator($this, $item_count);
  }

  public static function range($start, $end, $step = 1) {
    $range = range($start, $end, $step);

    return new Collection($range);
  }

  public static function from($array) {

    if ($array instanceof Generator) {
      return new GeneratorItterator($array);
    }

    return new Collection($array);
  }

  public function toArray() {
    return $this->getArrayCopy();
  }
}

class GeneratorItterator implements Iterator, iCollection {
  protected $generator;

  public function __construct(Generator $gen) {
    $this->generator = $gen;
  }

  public function select($callback) {
    return new SelectIterator($this, $callback);
  }

  public function where($callback) {
    return new WhereIterator($this, $callback);
  }

  public function distinct($callback) {
    return new DistinctIterator($this, $callback);
  }

  public function first($item_count = 1) {
    return new FirstIterator($this, $item_count);
  }

  public function last($item_count = 1) {
    return new LastIterator($this, $item_count);
  }

  public function toArray() {
    $tmp = array();
    foreach ($this as $key => $value) {
      $tmp[$key] = $value;
    }
    return $tmp;
  }

  public function current () {
    return $this->generator->current();
  }

  public function key () {
    return $this->generator->key();
  }

  public function next () {
    $this->generator->next();
  }

  public function rewind () {
    $this->generator->rewind();
  }

  public function valid () {
    return $this->generator->valid();
  }

}

class SelectIterator extends IteratorIterator implements iCollection{
  private $callback;

  public function __construct(Iterator $collection, $callback) {
    parent::__construct($collection);
    $this->callback = $callback;
  }

  public function current() {
    return call_user_func($this->callback, parent::current());
  }

  public function select($callback) {
    return new SelectIterator($this, $callback);
  }

  public function where($callback) {
    return new WhereIterator($this, $callback);
  }

  public function distinct($callback) {
    return new DistinctIterator($this, $callback);
  }

  public function first($item_count = 1) {
    return new FirstIterator($this, $item_count);
  }

  public function last($item_count = 1) {
    return new LastIterator($this, $item_count);
  }

  public function toArray() {
    $tmp = array();
    foreach ($this as $key => $value) {
      $tmp[$key] = $value;
    }
    return $tmp;
  }
}

class FirstIterator extends IteratorIterator implements iCollection {
  protected $index = 0;
  private $item_count = 1;

  public function __construct(Iterator $collection, $item_count = 1) {
    parent::__construct($collection);
    $this->item_count = $item_count;
  }

  public function select($callback) {
    return new SelectIterator($this, $callback);
  }

  public function where($callback) {
    return new WhereIterator($this, $callback);
  }

  public function distinct($callback) {
    return new DistinctIterator($this, $callback);
  }

  public function first($item_count = 1) {
    return new FirstIterator($this, $item_count);
  }

  public function last($item_count = 1) {
    return new LastIterator($this, $item_count);
  }

  public function rewind() {
    parent::rewind();
    $index = 0;
  }

  public function next() {
    if ($this->valid()){
      $this->index++;
      parent::next();
    }
  }

  public function valid() {
    return ($this->index < $this->item_count) && parent::valid();
  }

  public function toArray() {
    $tmp = array();
    foreach ($this as $key => $value) {
      $tmp[$key] = $value;
    }
    return $tmp;
  }
}

class LastIterator extends IteratorIterator implements iCollection {
  protected $index = 0;
  private $item_count = 1;

  public function __construct(Iterator $collection, $item_count = 1) {
    parent::__construct($collection);
    $this->index = $this->count() - $item_count;
    if ($this->index < 0) {
      $this->index = 0;
      $this->item_count = $this->count();
    } else {
      $this->item_count = $item_count;
    }
  }

  private function revert_position() {
    $this->index = 0;
    $index = $this->count() - $this->item_count;
    while($this->index < $index && $this->valid()) {
      $this->next();
    }
  }

  public function select($callback) {
    return new SelectIterator($this, $callback);
  }

  public function where($callback) {
    return new WhereIterator($this, $callback);
  }

  public function distinct($callback) {
    return new DistinctIterator($this, $callback);
  }

  public function first($item_count = 1) {
    return new FirstIterator($this, $item_count);
  }

  public function last($item_count = 1) {
    return new LastIterator($this, $item_count);
  }

  public function rewind() {
    parent::rewind();
    $this->revert_position();
  }

  public function next() {
    if ($this->valid()){
      $this->index++;
      parent::next();
    }
  }

  public function toArray() {
    $tmp = array();
    foreach ($this as $key => $value) {
      $tmp[$key] = $value;
    }
    return $tmp;
  }
}

class WhereIterator extends CallbackFilterIterator implements iCollection {
  public function __construct(Iterator $collection, $filter) {
    parent::__construct($collection, $filter);
  }

  public function select($callback) {
    return new SelectIterator($this, $callback);
  }

  public function where($callback) {
    return new WhereIterator($this, $callback);
  }

  public function distinct($callback) {
    return new DistinctIterator($this, $callback);
  }

  public function first($item_count = 1) {
    return new FirstIterator($this, $item_count);
  }

  public function last($item_count = 1) {
    return new LastIterator($this, $item_count);
  }

  public function toArray() {
    $tmp = array();
    foreach ($this as $key => $value) {
      $tmp[$key] = $value;
    }
    return $tmp;
  }
}

class DistinctIterator extends FilterIterator implements iCollection {
  protected $uniques = array();
  protected $callback = NULL;

  public function __construct(Iterator $collection, $callback) {
    parent::__construct($collection);
    $this->callback = $callback;
  }

  public function select($callback) {
    return new SelectIterator($this, $callback);
  }

  public function where($callback) {
    return new WhereIterator($this, $callback);
  }

  public function distinct($callback) {
    return new DistinctIterator($this, $callback);
  }

  public function first($item_count = 1) {
    return new FirstIterator($this, $item_count);
  }

  public function last($item_count = 1) {
    return new LastIterator($this, $item_count);
  }

  public function toArray() {
    $tmp = array();
    foreach ($this as $key => $value) {
      $tmp[$key] = $value;
    }
    return $tmp;
  }

  public function accept()
  {
    $value = md5(serialize($this->current()));
    if(!in_array($value, $this->uniques))
    {
      $this->uniques[] = $value;
      return TRUE;
    }

    return FALSE;
  }

  public function current() {
    if ($this->callback) {
      return call_user_func($this->callback, parent::current());
    }
    return parent::current();
  }
}
