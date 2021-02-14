<?php

namespace WPStaging\Framework\DI;

class Container extends \WPStaging\Vendor\tad_DI52_Container
{
    /**
     * @var string The PSR-4 namespace prefix we use to isolate third-party dependencies.
     */
    protected $prefix = 'WPStaging\\Vendor\\';

    /**
     * @deprecated Currently, all usages of _get in the codebase
     *              are Service Locators, not Dependency Injection.
     *              They need to be refactored in the future.
     *
     * @param $offset
     *
     * @return mixed|null
     */
    public function _get($offset)
    {
        try {
            return $this->offsetGet($offset);
        } catch (\Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log($e->getMessage());
            }

            return null;
        }
    }

    /**
     * You can use this to store an array of data in the container, without having to worry
     * if the array was already initialized or not.
     *
     * @param $arrayName string The name of the array. If it doesn't exist yet, it will be created.
     * @param $value mixed The value to add to the array.
     *
     * @return bool True if the value was added to the array. False if value already existed in the array.
     */
    public function pushToArray($arrayName, $value)
    {
        try {
            $arrayValues = (array)$this->offsetGet($arrayName);

            if (in_array($value, $arrayValues)) {
                // Do nothing, as the item already exists in this array.
                return false;
            }
        } catch (\Exception $e) {
            // If nothing is set in the container yet, create an empty one.
            $this->setVar($arrayName, []);
            $arrayValues = [];
        }

        // Add this value to the array.
        $arrayValues[] = $value;

        $this->setVar($arrayName, $arrayValues);

        return true;
    }

    /**
     * You can use this to get an array of data in the container, without having to worry
     * if the array was already initialized or not.
     *
     * @param $arrayName string The name of the array. If it doesn't exist yet, an empty array will be returned.
     *
     * @return array The array of data requested, or an empty array if it's not set.
     */
    public function getFromArray($arrayName)
    {
        try {
            return (array)$this->offsetGet($arrayName);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Overloads bind definition binding the prefix as well so that the DI container works locally.
     *
     * @param string     $classOrInterface
     * @param null       $implementation
     * @param array|null $afterBuildMethods
     */
    public function bind($classOrInterface, $implementation = null, array $afterBuildMethods = null)
    {
        if (defined('WPSTG_DEV') && WPSTG_DEV) {
            parent::bind(str_replace($this->prefix, '', $classOrInterface), $implementation, $afterBuildMethods);
        }
        parent::bind($classOrInterface, $implementation, $afterBuildMethods);
    }

    /**
     * Overloads singleton definition binding the prefix as well so that the DI container works locally.
     *
     * @param string     $classOrInterface
     * @param null       $implementation
     * @param array|null $afterBuildMethods
     */
    public function singleton($classOrInterface, $implementation = null, array $afterBuildMethods = null)
    {
        if (defined('WPSTG_DEV') && WPSTG_DEV) {
            parent::singleton(str_replace($this->prefix, '', $classOrInterface), $implementation, $afterBuildMethods);
        }
        parent::singleton($classOrInterface, $implementation, $afterBuildMethods);
    }
}
