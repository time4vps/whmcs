<?php

namespace Time4VPS\Base;

class Debug
{
    private $benchmark_start = null;

    public function __construct()
    {
        $this->benchmark_start = microtime(true);
    }

    public function benchmark()
    {
        return microtime(true) - $this->benchmark_start;
    }

    public function log()
    {
        //TODO: To be implemented
    }

}