<?php

/**
 * This file is part of Swap.
 *
 * (c) Florian Voutzinos <florian@voutzinos.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swap\Tests\Model;

use Swap\Model\CurrencyPair;

class CurrencyPairTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @dataProvider validStringProvider
     */
    public function it_creates_a_pair_from_a_valid_string($string, $baseCurrency, $quoteCurrency)
    {
        $pair = CurrencyPair::createFromString($string);

        $this->assertEquals($baseCurrency, $pair->getBaseCurrency());
        $this->assertEquals($quoteCurrency, $pair->getQuoteCurrency());
    }

    public function validStringProvider()
    {
        return array(
            array('EUR/USD', 'EUR', 'USD'),
            array('GBP/GBP', 'GBP', 'GBP'),
        );
    }

    /**
     * @test
     * @dataProvider invalidStringProvider
     * @expectedException \InvalidArgumentException
     */
    public function it_throws_an_exception_when_creating_from_an_invalid_string($string)
    {
        CurrencyPair::createFromString($string);
    }

    public function invalidStringProvider()
    {
        return array(
            array('EUR'), array('EUR/'), array('EU/US'), array('EUR/US'), array('US/EUR')
        );
    }

    /**
     * @test
     */
    public function it_can_be_converted_to_a_string()
    {
        $pair = new CurrencyPair('EUR', 'USD');
        $this->assertSame('', (string) $pair);

        $pair->setRate('1.5000');
        $this->assertEquals('1.5000', (string) $pair);
    }
}
