<?php

namespace QBNK\QBank\API\Model;

class PropertyRequest extends PropertyCriteria implements \JsonSerializable
{
    /** @var bool Whether this property should be included in the SearchResult. */
    protected $forfetching;

    /**
     * Constructs a PropertyRequest.
     *
     * @param array $parameters An array of parameters to initialize the {@link PropertyRequest} with.
     *                          - <b>forfetching</b> - Whether this property should be included in the SearchResult.
     */
    public function __construct($parameters = [])
    {
        parent::__construct($parameters);

        $this->forfetching = true;

        if (isset($parameters['forfetching'])) {
            $this->setForfetching($parameters['forfetching']);
        }
    }

    /**
     * Tells whether the PropertyRequest is forfetching.
     * @return bool	 */
    public function isForfetching()
    {
        return $this->forfetching;
    }

    /**
     * Sets the "forfetching" of the PropertyRequest.
     *
     * @param bool $forfetching
     *
     * @return PropertyRequest
     */
    public function setForfetching($forfetching)
    {
        $this->forfetching = $forfetching;

        return $this;
    }

    /**
     * Gets all data that should be available in a json representation.
     *
     * @return array an associative array of the available variables
     */
    public function jsonSerialize()
    {
        $json = parent::jsonSerialize();

        if (null !== $this->forfetching) {
            $json['forfetching'] = $this->forfetching;
        }

        return $json;
    }
}
