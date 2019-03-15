<?php

namespace Kyos;

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml as YamlParser;

class ConfigParser
{
    public static $configCache = [];

    protected $lastParseException;

    protected $loadedConfig;

    protected $evaluateKeys;
    protected $evaluateProperty;
    protected $valuationIsRequired;

    /**
     * Get a parser for a file.
     * If the file is already loaded this will contain the already parsed result object.
     */
    public static function getParserForFile($path)
    {
        self::ensureFileIsReadable($path);
        $path = realpath($path); // realpath only works on existing paths! so we checked it first.

        // if this file was parsed already, directly return a ConfigParser instance with the loaded config set.
        if (isset(self::$configCache[$path])) {
            $config = self::$configCache[$path];
        } else {
            // not parsed. parse it, add to cache and return object.
            try {
                $yamlParser = new YamlParser;
                $config     = $yamlParser->parseFile($path);
            } catch (ParseException $e) {
                throw $e;
            }
            self::$configCache[$path] = $config;
        }

        $parser = new ConfigParser;
        $parser->setLoadedConfig($config);
        return $parser;
    }

    public function setLoadedConfig($config)
    {
        $this->loadedConfig = $config;
    }

    /**
     * Ensures the given filePath is readable.
     *
     * @return void
     * @throws \InvalidArgumentException
     */
    public static function ensureFileIsReadable($path)
    {
        if (!is_readable($path) || !is_file($path)) {
            throw new \InvalidArgumentException(self::exceptionMessage('fileNotReadable'));
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
            $configValue = (is_array($configValue) && array_key_exists($property, $configValue))
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
    private function ensureValuationKeysExist()
    {
        if (!$this->evaluateKeys) {
            throw new \InvalidArgumentException(self::exceptionMessage('noValuationKey'));
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
        $this->ensureValuationKeysExist();
        $this->valuationIsRequired = true;

        if (is_null($this->evaluateProperty)) {
            throw new \Exception(self::exceptionMessage('required', $this->evaluateKeys));
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
        $this->ensureValuationKeysExist();

        if ($this->valuationIsRequired && !is_string($this->evaluateProperty)) {
            throw new \UnexpectedValueException(self::exceptionMessage('string', $this->evaluateKeys));
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
        $this->ensureValuationKeysExist();

        if ($this->valuationIsRequired && !is_numeric($this->evaluateProperty)) {
            throw new \UnexpectedValueException(self::exceptionMessage('number', $this->evaluateKeys));
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
        $this->ensureValuationKeysExist();

        if ($this->valuationIsRequired && !is_bool($this->evaluateProperty)) {
            throw new \UnexpectedValueException(self::exceptionMessage('boolean', $this->evaluateKeys));
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
        $this->ensureValuationKeysExist();

        if ($this->valuationIsRequired && !in_array($this->evaluateProperty, $allowedValues)) {
            throw new \UnexpectedValueException(self::exceptionMessage('oneOf', $this->evaluateKeys));
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
    public static function exceptionMessage(string $type, array $evaluateKeys = []): string
    {
        $exceptionMessage = '';

        if ($evaluateKeys) {
            $key = implode('.', $evaluateKeys);
        }

        switch ($type) {
            case 'fileNotReadable':
                $exceptionMessage = "Unable to read the provided yaml file";
                break;
            case 'boolean':
            case 'string':
            case 'number':
                $exceptionMessage = "One or more environment variables failed assertions: {$key} is not a {$type}";
                break;
            case 'required':
                $exceptionMessage = "One or more environment variables failed assertions: {$key} is empty";
                break;
            case 'oneOf':
                $exceptionMessage = "One or more environment variables failed assertions: {$key} is not an allowed value";
                break;
            case 'noValuationKey':
                $exceptionMessage = 'No evaluation keys found. Cannot proceed with check.';
                break;
        }
        return $exceptionMessage;
    }
}
