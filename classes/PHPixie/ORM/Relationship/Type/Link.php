<?php

namespace PHPixie\ORM\Relationship;

abstract class Link
{
    protected $relationship;
    protected $config;
    protected $type;

    public function __construct($relationship, $type, $config)
    {
        $this->relationship = $relationship;
        $this->type = $type;
        $this->config = $config;
    }

    public function type()
    {
        return $this->type;
    }

    public function config()
    {
        return $this->config;
    }

    abstract public function modelName();
    abstract public function propertyName();
    abstract public function relationship();
}