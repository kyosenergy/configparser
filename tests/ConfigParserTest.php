<?php

namespace Kyos;

use Kyos\ConfigParser;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Exception\ParseException;

class ConfigParserTest extends TestCase
{
    /** @test */
    public function nonExistingFileGivesException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $configFile = dirname(__DIR__) . '/tests/fixtures/missingconfig.yml';
        $parser = ConfigParser::getParserForFile($configFile);
    }

    /** @test */
    public function existingFileCanBeLoaded()
    {
        $configFile = dirname(__DIR__) . '/tests/fixtures/emptyconfig.yml';
        $parser = ConfigParser::getParserForFile($configFile);

        $this->assertInstanceOf('Kyos\ConfigParser', $parser);
    }

    /** @test */
    public function invlidFileThrowsException()
    {
        $this->expectException(\Symfony\Component\Yaml\Exception\ParseException::class);

        $configFile = dirname(__DIR__) . '/tests/fixtures/invalidconfig.yml';
        $parser = ConfigParser::getParserForFile($configFile);
    }

    /** @test */
    public function canParseAnExistingValueUsingSingleString()
    {
        $configFile = dirname(__DIR__) . '/tests/fixtures/config.yml';
        $parser = ConfigParser::getParserForFile($configFile);

        $value = $parser->get('valueX');

        $this->assertEquals('TestValue', $value);
    }

    /** @test */
    public function canParseAnExistingValueUsingDotNotation()
    {
        $configFile = dirname(__DIR__) . '/tests/fixtures/config.yml';
        $parser = ConfigParser::getParserForFile($configFile);

        $value = $parser->get('application.releaseStage');

        $this->assertEquals('Production', $value);
    }

    /** @test */
    public function canParseAnExistingValueUsingArrayNotation()
    {
        $configFile = dirname(__DIR__) . '/tests/fixtures/config.yml';
        $parser = ConfigParser::getParserForFile($configFile);

        $value = $parser->get(['application', 'releaseStage']);

        $this->assertEquals('Production', $value);
    }

    /** @test */
    public function arrayNotationAndDotNotationResultToSameConfig()
    {
        $configFile = dirname(__DIR__) . '/tests/fixtures/config.yml';
        $parser = ConfigParser::getParserForFile($configFile);

        $this->assertSame(
            $parser->get('application.releaseStage'),
            $parser->get(['application', 'releaseStage'])
        );
    }

    /** @test */
    public function returnsNullIfValueIsMissing()
    {
        $configFile = dirname(__DIR__) . '/tests/fixtures/config.yml';
        $parser = ConfigParser::getParserForFile($configFile);

        $value = $parser->get('application.missingProperty');

        $this->assertEquals(null, $value);
    }

    /** @test */
    public function returnsNullIfDeeperValueIsMissing()
    {
        $configFile = dirname(__DIR__) . '/tests/fixtures/config.yml';
        $parser = ConfigParser::getParserForFile($configFile);

        $value = $parser->get('application.missingProperty.missingProperty');

        $this->assertEquals(null, $value);
    }

    /** @test */
    public function returnsProvidedFallbackIfKeyIsMissing()
    {
        $configFile = dirname(__DIR__) . '/tests/fixtures/config.yml';
        $parser = ConfigParser::getParserForFile($configFile);

        $value = $parser->get('invlidPath', 'ArbitraryValue');

        $this->assertEquals('ArbitraryValue', $value);
    }

    /** @test */
    public function returnsProvidedFallbackIfDeeperValueIsMissing()
    {
        $configFile = dirname(__DIR__) . '/tests/fixtures/config.yml';
        $parser = ConfigParser::getParserForFile($configFile);

        $value = $parser->get('application.missingProperty.missingProperty', 'ArbitraryValue');

        $this->assertEquals('ArbitraryValue', $value);
    }

    /** @test */
    public function returnsProvidedFallbackIfValueIsMissing()
    {
        $configFile = dirname(__DIR__) . '/tests/fixtures/config.yml';
        $parser = ConfigParser::getParserForFile($configFile);

        $value = $parser->get('application.missingProperty', 'ArbitraryValue');

        $this->assertEquals('ArbitraryValue', $value);
    }

    /** @test */
    public function beforeValuationCheckEvaluateFunctionHasToBeExecuted()
    {
        $this->expectException(\InvalidArgumentException::class);

        $configFile = dirname(__DIR__) . '/tests/fixtures/config.yml';
        $parser = ConfigParser::getParserForFile($configFile);

        $parser->isString();
    }

    /** @test */
    public function evaluateRequiredField()
    {
        $configFile = dirname(__DIR__) . '/tests/fixtures/config.yml';
        $parser = ConfigParser::getParserForFile($configFile);

        $parser->evaluate('application.releaseStage')->isRequired();
        $this->assertEquals('Production', $parser->get('application.releaseStage'));
    }

    /** @test */
    public function evaluateMissingKeyAsRequiredFieldThrowsException()
    {
        $this->expectException(\Exception::class);

        $configFile = dirname(__DIR__) . '/tests/fixtures/config.yml';
        $parser = ConfigParser::getParserForFile($configFile);

        $parser->evaluate('application.missingKey')->isRequired();
    }

    /** @test */
    public function evaluateStringsAsString()
    {
        $configFile = dirname(__DIR__) . '/tests/fixtures/config.yml';
        $parser = ConfigParser::getParserForFile($configFile);

        $parser->evaluate('application.releaseStage')->isString();
        $this->assertEquals('Production', $parser->get('application.releaseStage'));
    }

    /** @test */
    public function evaluateStringAsNumberThrowsException()
    {
        $this->expectException(\UnexpectedValueException::class);

        $configFile = dirname(__DIR__) . '/tests/fixtures/config.yml';
        $parser = ConfigParser::getParserForFile($configFile);

        $parser->evaluate('application.releaseStage')->isNumeric();
    }

    /** @test */
    public function evaluateStringAsBooleanThrowsException()
    {
        $this->expectException(\UnexpectedValueException::class);

        $configFile = dirname(__DIR__) . '/tests/fixtures/config.yml';
        $parser = ConfigParser::getParserForFile($configFile);

        $parser->evaluate('application.releaseStage')->isBoolean();
    }

    /** @test */
    public function evaluateStringAsArrayThrowsException()
    {
        $this->expectException(\UnexpectedValueException::class);

        $configFile = dirname(__DIR__) . '/tests/fixtures/config.yml';
        $parser = ConfigParser::getParserForFile($configFile);

        $parser->evaluate('application.releaseStage')->isBoolean();
    }

    /** @test */
    public function evaluateNumbersAsNumeric()
    {
        $configFile = dirname(__DIR__) . '/tests/fixtures/config.yml';
        $parser = ConfigParser::getParserForFile($configFile);

        $parser->evaluate('application.version')->isNumeric();
        $this->assertEquals(6, $parser->get('application.version'));
    }

    /** @test */
    public function evaluateNumberAsStringThrowsException()
    {
        $this->expectException(\UnexpectedValueException::class);

        $configFile = dirname(__DIR__) . '/tests/fixtures/config.yml';
        $parser = ConfigParser::getParserForFile($configFile);

        $parser->evaluate('application.version')->isString();
    }

    /** @test */
    public function evaluateNumberAsBooleanThrowsException()
    {
        $this->expectException(\UnexpectedValueException::class);

        $configFile = dirname(__DIR__) . '/tests/fixtures/config.yml';
        $parser = ConfigParser::getParserForFile($configFile);

        $parser->evaluate('application.version')->isBoolean();
    }

    /** @test */
    public function evaluateNumberAsArrayThrowsException()
    {
        $this->expectException(\UnexpectedValueException::class);

        $configFile = dirname(__DIR__) . '/tests/fixtures/config.yml';
        $parser = ConfigParser::getParserForFile($configFile);

        $parser->evaluate('application.version')->isArray();
    }

    /** @test */
    public function evaluateBooleansAsBoolean()
    {
        $configFile = dirname(__DIR__) . '/tests/fixtures/config.yml';
        $parser = ConfigParser::getParserForFile($configFile);

        $parser->evaluate('application.debugMode')->isBoolean();
        $this->assertEquals(true, $parser->get('application.debugMode'));
    }

    /** @test */
    public function evaluateBooleanAsStringThrowsException()
    {
        $this->expectException(\UnexpectedValueException::class);

        $configFile = dirname(__DIR__) . '/tests/fixtures/config.yml';
        $parser = ConfigParser::getParserForFile($configFile);

        $parser->evaluate('application.debugMode')->isString();
    }

    /** @test */
    public function evaluateBooleanAsNumberThrowsException()
    {
        $this->expectException(\UnexpectedValueException::class);

        $configFile = dirname(__DIR__) . '/tests/fixtures/config.yml';
        $parser = ConfigParser::getParserForFile($configFile);

        $parser->evaluate('application.debugMode')->isNumeric();
    }

    /** @test */
    public function evaluateBooleanAsArrayThrowsException()
    {
        $this->expectException(\UnexpectedValueException::class);

        $configFile = dirname(__DIR__) . '/tests/fixtures/config.yml';
        $parser = ConfigParser::getParserForFile($configFile);

        $parser->evaluate('application.debugMode')->isArray();
    }

    /** @test */
    public function evaluateArraysAsArray()
    {
        $configFile = dirname(__DIR__) . '/tests/fixtures/config.yml';
        $parser = ConfigParser::getParserForFile($configFile);

        $parser->evaluate('application')->isArray();
        $this->assertCount(3, $parser->get('application'));
    }

    /** @test */
    public function evaluateArrayAsStringThrowsException()
    {
        $this->expectException(\UnexpectedValueException::class);

        $configFile = dirname(__DIR__) . '/tests/fixtures/config.yml';
        $parser = ConfigParser::getParserForFile($configFile);

        $parser->evaluate('application')->isString();
    }

    /** @test */
    public function evaluateArrayAsNumberThrowsException()
    {
        $this->expectException(\UnexpectedValueException::class);

        $configFile = dirname(__DIR__) . '/tests/fixtures/config.yml';
        $parser = ConfigParser::getParserForFile($configFile);

        $parser->evaluate('application')->isNumeric();
    }

    /** @test */
    public function evaluateArrayAsBooleanThrowsException()
    {
        $this->expectException(\UnexpectedValueException::class);

        $configFile = dirname(__DIR__) . '/tests/fixtures/config.yml';
        $parser = ConfigParser::getParserForFile($configFile);

        $parser->evaluate('application')->isBoolean();
    }

    /** @test */
    public function evaluateIsOneOf()
    {
        $configFile = dirname(__DIR__) . '/tests/fixtures/config.yml';
        $parser = ConfigParser::getParserForFile($configFile);

        $parser->evaluate('application.releaseStage')->isOneOf(['Production', 'Staging', 'Test']);
        $this->assertEquals('Production', $parser->get('application.releaseStage'));
    }

    /** @test */
    public function evaluateIsOneOfNoMatchThrowsException()
    {
        $this->expectException(\UnexpectedValueException::class);

        $configFile = dirname(__DIR__) . '/tests/fixtures/config.yml';
        $parser = ConfigParser::getParserForFile($configFile);

        $parser->evaluate('application.releaseStage')->isOneOf(['NotMatching1', 'NotMatching2', 'NotMatching3']);
    }
}
