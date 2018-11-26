<?php

namespace Kyos;

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml as YamlParser;

class ConfigParser
{
    protected $yamlParser;
    protected $lastParseException;

    protected $loadedConfig;

    protected $evaluateKeys;
    protected $evaluateProperty;
    protected $valuationIsRequired;

    /**
     * Initializes the YamlParser property and parses a file.
     *
     * @param string $source
     * @throws \Exception
     */
    public function __construct($source = '.userConfig.yml')
    {
        $this->yamlParser = new YamlParser;
        $this->parseConfig($source);
    }

    /**
     * Parse a string of YAML and return the.
     *
     * @param string $path
     * @throws \Symfony\Component\Yaml\Exception\ParseException
     * @throws \Exception
     */
    private function parseConfig($path)
    {
        $this->ensureFileIsReadable($path);

        try {
            $this->loadedConfig = $this->yamlParser->parseFile($path);
        } catch (ParseException $e) {
            $this->lastParseException = $e;
            throw $e;
        }
    }

    /**
     * Ensures the given filePath is readable.
     *
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function ensureFileIsReadable($path)
    {
        if (!is_readable($path) || !is_file($path)) {
            throw new \InvalidArgumentException($this->exceptionMessage('fileNotReadable'));
        }
    }

    /**
     * Parse a key by supporting 3 notations:
     * a. single string for top level keys: 'key'
     * b. associative keys in array: ['key1', 'key2']
     * 3. associative keys in dot notation: ('key1.key2')
     *
     * @param string|array $key
     * @return array
     */
    private function getSearchKey($key): array
    {
        if (!is_array($key)) {
            $key = explode('.', $key);
        }
        return $key;
    }

    /**
     * Parse a yaml value using a key.
     * Accepts a fallback value, if value is empty or doesn't exist.
     *
     * @param string|array $key
     * @param mixed $fallback
     *
     * @return mixed|null
     */
    public function get($key, $fallback = null)
    {
        $configValue = $this->loadedConfig;

        foreach ($this->getSearchKey($key) as $property) {
            $configValue = array_key_exists($property, $configValue)
                ? $configValue[$property]
                : null;
        }

        return !is_null($configValue)
            ? $configValue
            : (!is_null($fallback) ? $fallback : null);
    }

    /**
     * Starts the valuation for the provided key.
     *
     * @param string|array $key
     *
     * @return self
     */
    public function evaluate($key): self
    {
        $this->evaluateKeys = $this->getSearchKey($key);
        $this->evaluateProperty = $this->get($key);

        return $this;
    }

    /**
     * Ensures valuation has started.
     *
     * @throws \InvalidArgumentException
     */
    private function ensureValidationKeysExist()
    {
        if (!$this->evaluateKeys) {
            throw new \InvalidArgumentException($this->exceptionMessage('noValuationKey'));
        }

        $this->valuationIsRequired = true;
    }

    /**
     * Ensures the evaluateProperty is not null (exists)
     *
     * @return self
     * @throws \Exception
     */
    public function isRequired(): self
    {
        $this->ensureValidationKeysExist();
        $this->valuationIsRequired = true;

        if (is_null($this->evaluateProperty)) {
            throw new \Exception($this->exceptionMessage('required'));
        }

        return $this;
    }

    /**
     * Ensures the evaluateProperty is string
     *
     * @return self
     * @throws \UnexpectedValueException
     */
    public function isString(): self
    {
        $this->ensureValidationKeysExist();

        if ($this->valuationIsRequired && !is_string($this->evaluateProperty)) {
            throw new \UnexpectedValueException($this->exceptionMessage('string'));
        }

        return $this;
    }

    /**
     * Ensures the evaluateProperty is numeric
     *
     * @return self
     * @throws \UnexpectedValueException
     */
    public function isNumeric(): self
    {
        $this->ensureValidationKeysExist();

        if ($this->valuationIsRequired && !is_numeric($this->evaluateProperty)) {
            throw new \UnexpectedValueException($this->exceptionMessage('number'));
        }

        return $this;
    }

    /**
     * Ensures the evaluateProperty is boolean
     *
     * @return self
     * @throws \UnexpectedValueException
     */
    public function isBoolean(): self
    {
        $this->ensureValidationKeysExist();

        if ($this->valuationIsRequired && !is_bool($this->evaluateProperty)) {
            throw new \UnexpectedValueException($this->exceptionMessage('boolean'));
        }

        return $this;
    }

    /**
     * Ensures the evaluateProperty is one of the provided allowed values.
     *
     * @param array $allowedValues
     *
     * @return self
     * @throws \UnexpectedValueException
     */
    public function isOneOf(array $allowedValues): self
    {
        $this->ensureValidationKeysExist();

        if ($this->valuationIsRequired && !in_array($this->evaluateProperty, $allowedValues)) {
            throw new \UnexpectedValueException($this->exceptionMessage('oneOf'));
        }

        return $this;
    }

    /**
     * Provides the proper exception message based in the provided type.
     *
     * @param string $type
     *
     * @return string
     */
    private function exceptionMessage(string $type): string
    {
        $exceptionMessage = '';
        switch ($type) {
            case 'fileNotReadable':
                $exceptionMessage = "Unable to read the provided yaml file";
                break;
            case 'boolean':
            case 'string':
            case 'number':
                $k = $this->expandConfigKey();
                $exceptionMessage = "One or more environment variables failed assertions: {$k} is not a {$type}";
                break;
            case 'required':
                $k = $this->expandConfigKey();
                $exceptionMessage = "One or more environment variables failed assertions: {$k} is empty";
                break;
            case 'oneOf':
                $k = $this->expandConfigKey();
                $exceptionMessage = "One or more environment variables failed assertions: {$k} is not an allowed value";
                break;
            case 'noValuationKey':
                $exceptionMessage = 'No evaluation keys found. Cannot proceed with check.';
                break;
        }
        return $exceptionMessage;
    }

    /**
     * Returns the do notation of the requested valuation key
     *
     * @return string
     */
    private function expandConfigKey(): string
    {
        return implode('.', $this->evaluateKeys);
    }
}
