<?php

/*
 * This file is part of Swap.
 *
 * (c) Florian Voutzinos <florian@voutzinos.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swap\Provider;

use Swap\Exception\QuotationException;
use Swap\Util\StringUtil;

/**
 * Xignite provider.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
class Xignite extends AbstractSingleRequestProvider
{
    const URI = 'https://globalcurrencies.xignite.com/xGlobalCurrencies.json/GetRealTimeRates?Symbols=%s&_fields=Outcome,Message,Symbol,Date,Time,Bid&_Token=%s';

    private $token;

    /**
     * Creates a new provider.
     *
     * @param \Guzzle\Http\ClientInterface|\Swap\AdapterInterface $client The HTTP client
     * @param string                                              $token  The application token
     */
    public function __construct($client, $token)
    {
        parent::__construct($client);

        $this->token = $token;
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareRequestUri(array $pairs)
    {
        $symbols = array();
        foreach ($pairs as $pair) {
            $symbols[] = $pair->getBaseCurrency().$pair->getQuoteCurrency();
        }

        return sprintf(self::URI, implode(',', $symbols), $this->token);
    }

    /**
     * {@inheritdoc}
     */
    protected function processResponse($body, array $pairs)
    {
        // Prepare an array of pairs indexed by their "symbol"
        $hashPairs = array();
        foreach ($pairs as $pair) {
            $hashPairs[$pair->getBaseCurrency().$pair->getQuoteCurrency()] = $pair;
        }

        // Process the response content
        $json = StringUtil::jsonToArray($body);

        foreach ($json as $row) {
            if ('Success' === $row['Outcome']) {
                $pair = $hashPairs[$row['Symbol']];
                $dateString = $row['Date'].' '.$row['Time'];

                $pair->setRate((string) $row['Bid']);
                $pair->setDate(\DateTime::createFromFormat('m/d/Y H:i:s A', $dateString, new \DateTimeZone('UTC')));
            } else {
                throw new QuotationException($row['Message']);
            }
        }
    }
}
