<?php
/**
 * Created by PhpStorm.
 * User: zhjx
 * Date: 2018/11/15
 * Time: 9:32
 */

namespace Framework\Traits;

use Framework\SwServer\ServerManager;

trait ComponentTrait
{

    private $_components = [];
    /**
     * @var array singleton objects indexed by their types
     */
    private $_singletons = [];
    /**
     * @var array object definitions indexed by their types
     */
    private $_definitions = [];
    /**
     * @var array constructor parameters indexed by object types
     */
    private $_params = [];
    /**
     * @var array cached ReflectionClass objects indexed by class/interface names
     */
    private $_reflections = [];
    /**
     * @var array cached dependencies indexed by class/interface names. Each class name
     * is associated with a list of constructor parameter types or default values.
     */
    private $_dependencies = [];

    public function get($class, $params = [], $config = [])
    {
        if (isset($this->_singletons[$class])) {
            // singleton
            return $this->_singletons[$class];
        } elseif (!isset($this->_definitions[$class])) {
            return $this->build($class, $params, $config);
        }

        $definition = $this->_definitions[$class];
        if (is_callable($definition, true)) {
            $params = $this->resolveDependencies($this->mergeParams($class, $params));
            $object = call_user_func($definition, $this, $params, $config);
        } elseif (is_array($definition)) {
            $concrete = $definition['class'];
            unset($definition['class']);

            $config = array_merge($definition, $config);
            $params = $this->mergeParams($class, $params);

            if ($concrete === $class) {
                $object = $this->build($class, $params, $config);
            } else {
                $object = $this->get($concrete, $params, $config);
            }
        } elseif (is_object($definition)) {
            return $this->_singletons[$class] = $definition;
        } else {
            throw new Exception('Unexpected object definition type: ' . gettype($definition));
        }

        if (array_key_exists($class, $this->_singletons)) {
            // singleton
            $this->_singletons[$class] = $object;
        }

        return $object;
    }

    /**
     * Creates an instance of the specified class.
     * This method will resolve dependencies of the specified class, instantiate them, and inject
     * them into the new instance of the specified class.
     * @param string $class the class name
     * @param array $params constructor parameters
     * @param array $config configurations to be applied to the new instance
     * @return object the newly created instance of the specified class
     * @throws NotInstantiableException If resolved to an abstract class or an interface (since 2.0.9)
     */
    protected function build($class, $params, $config)
    {
        /* @var $reflection ReflectionClass */
        list($reflection, $dependencies) = $this->getDependencies($class);

        foreach ($params as $index => $param) {
            $dependencies[$index] = $param;
        }

        $dependencies = $this->resolveDependencies($dependencies, $reflection);
        if (!$reflection->isInstantiable()) {
            throw new NotInstantiableException($reflection->name);
        }
        if (empty($config)) {
            return $reflection->newInstanceArgs($dependencies);
        }

        if (!empty($dependencies) && $reflection->implementsInterface('Framework\SwServer\Base\Objects')) {
            // set $config as the last parameter (existing one will be overwritten)
            $dependencies[count($dependencies) - 1] = $config;
            return $reflection->newInstanceArgs($dependencies);
        }


        $object = $reflection->newInstanceArgs($dependencies);

        foreach ($config as $name => $value) {
            $object->$name = $value;
        }

        return $object;
    }

    /**
     * Returns the dependencies of the specified class.
     * @param string $class class name, interface name or alias name
     * @return array the dependencies of the specified class.
     */
    protected function getDependencies($class)
    {
        if (isset($this->_reflections[$class])) {
            return [$this->_reflections[$class], $this->_dependencies[$class]];
        }

        $dependencies = [];
        $reflection = new \ReflectionClass($class);

        $constructor = $reflection->getConstructor();
        if ($constructor !== null) {
            foreach ($constructor->getParameters() as $param) {
                if (version_compare(PHP_VERSION, '5.6.0', '>=') && $param->isVariadic()) {
                    break;
                } elseif ($param->isDefaultValueAvailable()) {
                    $dependencies[] = $param->getDefaultValue();
                } else {
                    $c = $param->getClass();
                    $dependencies[] = Instance::of($c === null ? null : $c->getName());
                }
            }
        }

        $this->_reflections[$class] = $reflection;
        $this->_dependencies[$class] = $dependencies;

        return [$reflection, $dependencies];
    }

    /**
     * Resolves dependencies by replacing them with the actual object instances.
     * @param array $dependencies the dependencies
     * @param ReflectionClass $reflection the class reflection associated with the dependencies
     * @return array the resolved dependencies
     * @throws InvalidConfigException if a dependency cannot be resolved or if a dependency cannot be fulfilled.
     */
    protected function resolveDependencies($dependencies, $reflection = null)
    {
        foreach ($dependencies as $index => $dependency) {
            if ($dependency instanceof Instance) {
                if ($dependency->id !== null) {
                    $dependencies[$index] = $this->get($dependency->id);
                } elseif ($reflection !== null) {
                    $name = $reflection->getConstructor()->getParameters()[$index]->getName();
                    $class = $reflection->getName();
                    throw new InvalidConfigException("Missing required parameter \"$name\" when instantiating \"$class\".");
                }
            }
        }

        return $dependencies;
    }

    public function createObject($type, array $params = [])
    {
        if (is_string($type)) {
            return $this->get($type, $params);
        } elseif (is_array($type) && isset($type['class'])) {
            $class = $type['class'];
            unset($type['class']);
            return $this->get($class, $params, $type);
        } elseif (is_array($type)) {
            throw new Exception('Object configuration must be an array containing a "class" element.');
        }

        throw new Exception('Unsupported configuration type: ' . gettype($type));
    }


    public function registerObject(string $com_alias_name, $definition = [], array $params = [])
    {
        if ($com_alias_name && is_array($definition) && isset($definition['class'])) {
            $class = $definition['class'];
            unset($definition['class']);
            return $this->setSingleton($class, $definition, $params);
        }
        throw new Exception('No registerObject');
    }

    public function setSingleton($class, $definition = [], array $params = [])
    {
        $this->_definitions[$class] = $this->normalizeDefinition($class, $definition);
        $this->_params[$class] = $params;
        unset($this->_singletons[$class]);
        $object = $this->build($class, $params, $definition);
        $this->_singletons[$class] = $object;
    }

    public function setComponent($id, $definition)
    {
        unset($this->_components[$id]);
        if ($definition === null) {
            unset($this->_definitions[$id]);
            return;
        }

        if (is_object($definition) || is_callable($definition, true)) {
            // an object, a class name, or a PHP callable
            $this->_definitions[$id] = $definition;
        } elseif (is_array($definition)) {
            // a configuration array
            if (isset($definition['class'])) {
                $this->_definitions[$id] = $definition;
            } else {
                throw new Exception("The configuration for the \"$id\" component must contain a \"class\" element.");
            }
        } else {
            throw new Exception("Unexpected configuration type for the \"$id\" component: " . gettype($definition));
        }
        $this->registerObject($id, $definition);
    }

    public function setComponents($components)
    {
        foreach ($components as $id => $component) {
            $this->setComponent($id, $component);
        }
    }

    /**
     * coreComponents 定义核心组件
     * @return   array
     */
    public function coreComponents()
    {
        return [];
    }

    /**
     * creatObject 创建组件对象
     * @param    string $com_alias_name 组件别名
     * @param    array $defination 组件定义类
     * @return   array
     */

    public function creatObject(string $com_alias_name = null, array $defination = [])
    {
        // 动态创建公用组件
        if (!isset($this->_components[$com_alias_name])) {
            if (isset($defination['class'])) {
                $class = $defination['class'];
                if (!isset($this->_singletons[$class])) {
                    $params = [];
                    if (isset($defination['constructor'])) {
                        $params = $defination['constructor'];
                        unset($defination['constructor']);
                    }
                    $this->registerObject($com_alias_name, $defination, $params);
                    $this->_components[$com_alias_name] = $class;
                    return $this->_singletons[$class];
                } else {
                    return $this->_singletons[$class];
                }
            } else {
                throw new \Exception("component:" . $com_alias_name . 'must be set class', 1);
            }
        } else {
            return $this->_singletons[$this->_components[$com_alias_name]];
        }
        return;
    }


    /**
     * __set
     * @param    string $name
     * @param    object $value
     * @return   object
     */
    public function __set($name, $value)
    {
        if (isset($this->_components[$name])) {
            return $this->_singletons[$this->_components[$name]];
        } else {
            if (is_array($value)) {
                return $this->creatObject($name, $value);
            }
            return false;
        }
    }

    /**
     * __get
     * @param    string $name
     * @return   object | boolean
     */
    public function __get($name)
    {
        if (!isset($this->$name)) {
            if (isset($this->_components[$name])) {
                if (is_object($this->_singletons[$this->_components[$name]])) {
                    return $this->_singletons[$this->_components[$name]];
                } else {
                    $this->clearComponent($name);
                    return false;
                }
            } else if (in_array($name, array_keys(ServerManager::$config['components']))) {
                return $this->creatObject($name, ServerManager::$config['components'][$name]);
            }
            return false;
        }
    }

    /**
     * clearComponent
     * @param    string|array $component_alias_name
     * @return   boolean
     */
    public function clearComponent($com_alias_name = null)
    {
        if (!is_null($com_alias_name) && is_string($com_alias_name)) {
            $com_alias_name = (array)$com_alias_name;
        } else if (is_array($com_alias_name)) {
            $com_alias_name = array_unique($com_alias_name);
        } else {
            return false;
        }
        foreach ($com_alias_name as $alias_name) {
            unset($this->_singletons[$this->_components[$alias_name]]);
            unset($this->_components[$alias_name]);
        }
        return true;
    }

    /**
     * __isset
     * @param    string $name
     * @return   boolean
     */
    public function __isset($name)
    {
        return isset($this->$name);
    }

    /**
     * __unset
     * @param   string $name
     */
    public function __unset($name)
    {
        unset($this->$name);
    }

    public function initComponents()
    {
        // 配置文件初始化创建公用对象
        $coreComponents = $this->coreComponents();
        $components = array_merge($coreComponents, ServerManager::$config['components']);
        foreach ($components as $com_name => $component) {
            // 存在直接跳过
            if (isset($this->_components[$com_name])) {
                continue;
            }
            if (isset($component['class']) && $component['class'] != '') {
                $params = [];
                if (isset($component['constructor'])) {
                    $params = $component['constructor'];
                    unset($component['constructor']);
                }
                $defination = $component;
                $this->registerObject($com_name, $defination, $params);
                $this->_components[$com_name] = $component['class'];
            }
        }
         return $this->_singletons;
    }

    /**
     * Normalizes the class definition.
     * @param string $class class name
     * @param string|array|callable $definition the class definition
     * @return array the normalized class definition
     * @throws InvalidConfigException if the definition is invalid.
     */
    protected function normalizeDefinition($class, $definition)
    {
        if (empty($definition)) {
            return ['class' => $class];
        } elseif (is_string($definition)) {
            return ['class' => $definition];
        } elseif (is_callable($definition, true) || is_object($definition)) {
            return $definition;
        } elseif (is_array($definition)) {
            if (!isset($definition['class'])) {
                if (strpos($class, '\\') !== false) {
                    $definition['class'] = $class;
                } else {
                    throw new InvalidConfigException('A class definition requires a "class" member.');
                }
            }

            return $definition;
        }

        throw new InvalidConfigException("Unsupported definition type for \"$class\": " . gettype($definition));
    }

    public function getComponents(){
        return $this->_components;
    }

    public function getSingletons(){
        return $this->_singletons;
    }


}