<?php

namespace Kunsal\Laramodula\Traits;

trait ReplaceStubTrait
{
    protected function replaceClass($stub, $name)
    {
        return str_replace('{{class}}', $name, $stub);
    }

    protected function replaceNamespace($stub, $name)
    {
        return str_replace('{{namespace}}', $name, $stub);
    }

    protected function replaceName($stub, $name)
    {
        return str_replace('{{name}}', $name, $stub);
    }

    protected function replaceLowerName($stub, $name)
    {
        return str_replace('{{lowername}}', $name, $stub);
    }

    protected function replacePlural($stub, $name)
    {
        return str_replace('{{plural}}', $name, $stub);
    }

    protected function replaceType($stub, $type)
    {
        return str_replace('{{type}}', ucfirst($type), $stub);
    }

    protected function replaceLowerPlural($stub, $lowerplural)
    {
        return str_replace('{{lowerplural}}', $lowerplural, $stub);
    }
}
