<?php

namespace QBNK\QBank\API\Model;

class SavedSearch implements \JsonSerializable
{
    /** @var string */
    protected $search;

    /**
     * Constructs a SavedSearch.
     *
     * @param array $parameters An array of parameters to initialize the {@link SavedSearch} with.
     *                          - <b>search</b> -
     */
    public function __construct($parameters = [])
    {
        if (isset($parameters['search'])) {
            $this->setSearch($parameters['search']);
        }
    }

    /**
     * Gets the search of the SavedSearch.
     * @return string	 */
    public function getSearch()
    {
        return $this->search;
    }

    /**
     * Sets the "search" of the SavedSearch.
     *
     * @param string $search
     *
     * @return SavedSearch
     */
    public function setSearch($search)
    {
        $this->search = $search;

        return $this;
    }

    /**
     * Gets all data that should be available in a json representation.
     *
     * @return array an associative array of the available variables
     */
    public function jsonSerialize()
    {
        $json = [];

        if (null !== $this->search) {
            $json['search'] = $this->search;
        }

        return $json;
    }
}
