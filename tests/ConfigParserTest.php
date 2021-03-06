<?php

namespace Kyos;

use Kyos\ConfigParser;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Exception\ParseException;

class ConfigParserTest extends TestCase
{
    /**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function nonExistingFileGivesException()
    {
        $configFile = dirname(__DIR__) . '/tests/fixtures/missingconfig.yml';
        $parser = ConfigParser::getParserForFile($configFile);
    }

    /**
     * @test
     */
    public function existingFileCanBeLoaded()
    {
        $configFile = dirname(__DIR__) . '/tests/fixtures/emptyconfig.yml';
        $parser = ConfigParser::getParserForFile($configFile);

        $this->assertInstanceOf('Kyos\ConfigParser', $parser);
    }

    /**
     * @test
     *
     * @expectedException Symfony\Component\Yaml\Exception\ParseException
     */
    public function invlidFileThrowsException()
    {
        $configFile = dirname(__DIR__) . '/tests/fixtures/invalidconfig.yml';
        $parser = ConfigParser::getParserForFile($configFile);
    }

    /**
     * @test
     */
    public function canParseAnExistingValueUsingSingleString()
    {
        $configFile = dirname(__DIR__) . '/tests/fixtures/config.yml';
        $parser = ConfigParser::getParserForFile($configFile);

        $value = $parser->get('valueX');

        $this->assertEquals('TestValue', $value);
    }

    /**
     * @test
     */
    public function canParseAnExistingValueUsingDotNotation()
    {
        $configFile = dirname(__DIR__) . '/tests/fixtures/config.yml';
        $parser = ConfigParser::getParserForFile($configFile);

        $value = $parser->get('application.releaseStage');

        $this->assertEquals('Production', $value);
    }

    /**
     * @test
     */
    public function canParseAnExistingValueUsingArrayNotation()
    {
        $configFile = dirname(__DIR__) . '/tests/fixtures/config.yml';
        $parser = ConfigParser::getParserForFile($configFile);

        $value = $parser->get(['application', 'releaseStage']);

        $this->assertEquals('Production', $value);
    }

    /**
     * @test
     */
    public function arrayNotationAndDotNotationResultToSameConfig()
    {
        $configFile = dirname(__DIR__) . '/tests/fixtures/config.yml';
        $parser = ConfigParser::getParserForFile($configFile);

        $this->assertSame(
            $parser->get('application.releaseStage'),
            $parser->get(['application', 'releaseStage'])
        );
    }

    /**
     * @test
     */
    public function returnsNullIfValueIsMissing()
    {
        $configFile = dirname(__DIR__) . '/tests/fixtures/config.yml';
        $parser = ConfigParser::getParserForFile($configFile);

        $value = $parser->get('application.missingProperty');

        $this->assertEquals(null, $value);
    }

    /**
     * @test
     */
    public function returnsNullIfDeeperValueIsMissing()
    {
        $configFile = dirname(__DIR__) . '/tests/fixtures/config.yml';
        $parser = ConfigParser::getParserForFile($configFile);

        $value = $parser->get('application.missingProperty.missingProperty');

        $this->assertEquals(null, $value);
    }

    /**
     * @test
     */
    public function returnsProvidedFallbackIfKeyIsMissing()
    {
        $configFile = dirname(__DIR__) . '/tests/fixtures/config.yml';
        $parser = ConfigParser::getParserForFile($configFile);

        $value = $parser->get('invlidPath', 'ArbitraryValue');

        $this->assertEquals('ArbitraryValue', $value);
    }

    /**
     * @test
     */
    public function returnsProvidedFallbackIfDeeperValueIsMissing()
    {
        $configFile = dirname(__DIR__) . '/tests/fixtures/config.yml';
        $parser = ConfigParser::getParserForFile($configFile);

        $value = $parser->get('application.missingProperty.missingProperty', 'ArbitraryValue');

        $this->assertEquals('ArbitraryValue', $value);
    }

    /**
     * @test
     */
    public function returnsProvidedFallbackIfValueIsMissing()
    {
        $configFile = dirname(__DIR__) . '/tests/fixtures/config.yml';
        $parser = ConfigParser::getParserForFile($configFile);

        $value = $parser->get('application.missingProperty', 'ArbitraryValue');

        $this->assertEquals('ArbitraryValue', $value);
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function beforeValuationCheckEvaluateFunctionHasToBeExecuted()
    {
        $configFile = dirname(__DIR__) . '/tests/fixtures/config.yml';
        $parser = ConfigParser::getParserForFile($configFile);

        $parser->isString();
    }

    /**
     * @test
     */
    public function evaluateRequiredField()
    {
        $configFile = dirname(__DIR__) . '/tests/fixtures/config.yml';
        $parser = ConfigParser::getParserForFile($configFile);

        $parser->evaluate('application.releaseStage')->isRequired();
        $this->assertEquals('Production', $parser->get('application.releaseStage'));
    }

    /**
     * @test
     * @expectedException Exception
     */
    public function evaluateMissingKeyAsRequiredFieldThrowsException()
    {
        $configFile = dirname(__DIR__) . '/tests/fixtures/config.yml';
        $parser = ConfigParser::getParserForFile($configFile);

        $parser->evaluate('application.missingKey')->isRequired();
    }

    /**
     * @test
     */
    public function evaluateStringsAsString()
    {
        $configFile = dirname(__DIR__) . '/tests/fixtures/config.yml';
        $parser = ConfigParser::getParserForFile($configFile);

        $parser->evaluate('application.releaseStage')->isString();
        $this->assertEquals('Production', $parser->get('application.releaseStage'));
    }

    /**
     * @test
     * @expectedException UnexpectedValueException
     */
    public function evaluateStringAsNumberThrowsException()
    {
        $configFile = dirname(__DIR__) . '/tests/fixtures/config.yml';
        $parser = ConfigParser::getParserForFile($configFile);

        $parser->evaluate('application.releaseStage')->isNumeric();
    }

    /**
     * @test
     * @expectedException UnexpectedValueException
     */
    public function evaluateStringAsBooleanThrowsException()
    {
        $configFile = dirname(__DIR__) . '/tests/fixtures/config.yml';
        $parser = ConfigParser::getParserForFile($configFile);

        $parser->evaluate('application.releaseStage')->isBoolean();
    }

    /**
     * @test
     * @expectedException UnexpectedValueException
     */
    public function evaluateStringAsArrayThrowsException()
    {
        $configFile = dirname(__DIR__) . '/tests/fixtures/config.yml';
        $parser = ConfigParser::getParserForFile($configFile);

        $parser->evaluate('application.releaseStage')->isBoolean();
    }

    /**
     * @test
     */
    public function evaluateNumbersAsNumeric()
    {
        $configFile = dirname(__DIR__) . '/tests/fixtures/config.yml';
        $parser = ConfigParser::getParserForFile($configFile);

        $parser->evaluate('application.version')->isNumeric();
        $this->assertEquals(6, $parser->get('application.version'));
    }

    /**
     * @test
     * @expectedException UnexpectedValueException
     */
    public function evaluateNumberAsStringThrowsException()
    {
        $configFile = dirname(__DIR__) . '/tests/fixtures/config.yml';
        $parser = ConfigParser::getParserForFile($configFile);

        $parser->evaluate('application.version')->isString();
    }

    /**
     * @test
     * @expectedException UnexpectedValueException
     */
    public function evaluateNumberAsBooleanThrowsException()
    {
        $configFile = dirname(__DIR__) . '/tests/fixtures/config.yml';
        $parser = ConfigParser::getParserForFile($configFile);

        $parser->evaluate('application.version')->isBoolean();
    }

    /**
     * @test
     * @expectedException UnexpectedValueException
     */
    public function evaluateNumberAsArrayThrowsException()
    {
        $configFile = dirname(__DIR__) . '/tests/fixtures/config.yml';
        $parser = ConfigParser::getParserForFile($configFile);

        $parser->evaluate('application.version')->isArray();
    }

    /**
     * @test
     */
    public function evaluateBooleansAsBoolean()
    {
        $configFile = dirname(__DIR__) . '/tests/fixtures/config.yml';
        $parser = ConfigParser::getParserForFile($configFile);

        $parser->evaluate('application.debugMode')->isBoolean();
        $this->assertEquals(true, $parser->get('application.debugMode'));
    }

    /**
     * @test
     * @expectedException UnexpectedValueException
     */
    public function evaluateBooleanAsStringThrowsException()
    {
        $configFile = dirname(__DIR__) . '/tests/fixtures/config.yml';
        $parser = ConfigParser::getParserForFile($configFile);

        $parser->evaluate('application.debugMode')->isString();
    }

    /**
     * @test
     * @expectedException UnexpectedValueException
     */
    public function evaluateBooleanAsNumberThrowsException()
    {
        $configFile = dirname(__DIR__) . '/tests/fixtures/config.yml';
        $parser = ConfigParser::getParserForFile($configFile);

        $parser->evaluate('application.debugMode')->isNumeric();
    }

    /**
     * @test
     * @expectedException UnexpectedValueException
     */
    public function evaluateBooleanAsArrayThrowsException()
    {
        $configFile = dirname(__DIR__) . '/tests/fixtures/config.yml';
        $parser = ConfigParser::getParserForFile($configFile);

        $parser->evaluate('application.debugMode')->isArray();
    }

    /**
     * @test
     */
    public function evaluateArraysAsArray()
    {
        $configFile = dirname(__DIR__) . '/tests/fixtures/config.yml';
        $parser = ConfigParser::getParserForFile($configFile);

        $parser->evaluate('application')->isArray();
        $this->assertCount(3, $parser->get('application'));
    }

    /**
     * @test
     * @expectedException UnexpectedValueException
     */
    public function evaluateArrayAsStringThrowsException()
    {
        $configFile = dirname(__DIR__) . '/tests/fixtures/config.yml';
        $parser = ConfigParser::getParserForFile($configFile);

        $parser->evaluate('application')->isString();
    }

    /**
     * @test
     * @expectedException UnexpectedValueException
     */
    public function evaluateArrayAsNumberThrowsException()
    {
        $configFile = dirname(__DIR__) . '/tests/fixtures/config.yml';
        $parser = ConfigParser::getParserForFile($configFile);

        $parser->evaluate('application')->isNumeric();
    }

    /**
     * @test
     * @expectedException UnexpectedValueException
     */
    public function evaluateArrayAsBooleanThrowsException()
    {
        $configFile = dirname(__DIR__) . '/tests/fixtures/config.yml';
        $parser = ConfigParser::getParserForFile($configFile);

        $parser->evaluate('application')->isBoolean();
    }

    /**
     * @test
     */
    public function evaluateIsOneOf()
    {
        $configFile = dirname(__DIR__) . '/tests/fixtures/config.yml';
        $parser = ConfigParser::getParserForFile($configFile);

        $parser->evaluate('application.releaseStage')->isOneOf(['Production', 'Staging', 'Test']);
        $this->assertEquals('Production', $parser->get('application.releaseStage'));
    }

    /**
     * @test
     * @expectedException UnexpectedValueException
     */
    public function evaluateIsOneOfNoMatchThrowsException()
    {
        $configFile = dirname(__DIR__) . '/tests/fixtures/config.yml';
        $parser = ConfigParser::getParserForFile($configFile);

        $parser->evaluate('application.releaseStage')->isOneOf(['NotMatching1', 'NotMatching2', 'NotMatching3']);
    }
}
