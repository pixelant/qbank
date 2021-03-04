<?php

namespace QBNK\QBank\API\Model;

class Command implements \JsonSerializable
{
    /** @var array */
    protected $parameters;

    /** @var string The name of the command */
    protected $class;

    /**
     * Constructs a Command.
     *
     * @param array $parameters An array of parameters to initialize the {@link Command} with.
     *                          - <b>class</b> - The name of the command
     */
    public function __construct($parameters = [])
    {
        $this->parameters = [];
        foreach ($parameters as $name => $value) {
            if (!is_callable([$this, 'set' . ucfirst($name)])) {
                $this->parameters[$name] = $value;
            }
        }

        if (isset($parameters['class'])) {
            $this->setClass($parameters['class']);
        }
    }

    /**
     * Gets the class of the Command.
     * @return string	 */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Sets the "class" of the Command.
     *
     * @param string $class
     *
     * @return Command
     */
    public function setClass($class)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * Gets a command parameter.
     *
     * @param $name string The name of the parameter
     * @param $default mixed The default value if it is not defined
     *
     * @return mixed
     */
    public function getParameter($name, $default = null)
    {
        if (isset($this->parameters[$name])) {
            return $this->parameters[$name];
        }

        return $default;
    }

    /**
     * Gets all the command parameters.
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Gets all data that should be available in a json representation.
     *
     * @return array an associative array of the available variables
     */
    public function jsonSerialize()
    {
        $json = [];

        if (null !== $this->class) {
            $json['class'] = $this->class;
        }

        foreach ($this->parameters as $name => $value) {
            $json[$name] = $value;
        }

        return $json;
    }
}
