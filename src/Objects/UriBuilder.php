<?php

namespace Magpie\Objects;

/**
 * A URI builder
 */
class UriBuilder extends CommonUri
{
    /**
     * @var array<string, string> Queries
     */
    protected array $queries = [];


    /**
     * Append path node
     * @param string $node
     * @return $this
     */
    public function withAddedPathNode(string $node) : static
    {
        $nodes = explode('/', $node);

        if (!str_ends_with($this->path, '/')) $this->path .= '/';

        foreach ($nodes as $subNode) {
            $this->path .= $subNode . '/';
        }

        while (str_ends_with($this->path, '/')) $this->path = substr($this->path, 0, -1);

        return $this;
    }


    /**
     * All queries
     * @return iterable<string, string>
     */
    public function getQueries() : iterable
    {
        foreach ($this->queries as $key => $value) {
            yield $key => $value;
        }
    }


    /**
     * With multiple specific query
     * @param iterable<string, string> $keyValues
     * @return $this
     */
    public function withQueries(iterable $keyValues) : static
    {
        foreach ($keyValues as $key => $value) {
            $this->withQuery($key, $value);
        }
        return $this;
    }


    /**
     * With specific query
     * @param string $key
     * @param string|null $value
     * @return $this
     */
    public function withQuery(string $key, ?string $value) : static
    {
        $this->queries[$key] = ($value ?? '');
        return $this;
    }


    /**
     * Without specific query (remove query)
     * @param string $key
     * @return $this
     */
    public function withoutQuery(string $key) : static
    {
        unset($this->queries[$key]);
        return $this;
    }


    /**
     * @inheritDoc
     */
    public function getQueryString() : ?string
    {
        if (count($this->queries) <= 0) return null;

        $ret = '';
        foreach ($this->queries as $key => $value) {
            $ret .= '&' . urlencode($key);
            if (!is_empty_string($value)) {
                 $ret .= '=' . urlencode($value);
            }
        }

        return substr($ret, 1);
    }


    /**
     * @inheritDoc
     */
    protected function setQueryString(?string $query) : void
    {
        if (is_empty_string($query)) {
            $this->queries = [];
            return;
        }

        $sections = explode('&', $query);
        foreach ($sections as $section) {
            $equalPos = strpos($section, '=');
            if ($equalPos !== false) {
                $queryKey = substr($section, 0, $equalPos);
                $queryValue = substr($section, $equalPos + 1);
            } else {
                $queryKey = $section;
                $queryValue = '';
            }

            // Decode and store
            $this->queries[urldecode($queryKey)] = urldecode($queryValue);
        }
    }


    /**
     * With specific fragment
     * @param string|null $fragment
     * @return $this
     */
    public function withFragment(?string $fragment) : static
    {
        $this->fragment = $fragment;
        return $this;
    }
}