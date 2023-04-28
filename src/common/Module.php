<?php

namespace tpext\common;

class Module extends Extension
{
    protected $modules = [];
    
    /**
     * Undocumented function
     *
     * @return array
     */
    public function getModules()
    {
        return $this->modules;
    }

    public function extInit($info = [])
    {
        
    }
}
