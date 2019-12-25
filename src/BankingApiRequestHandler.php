<?php
namespace Pod\Banking\Service;

use Pod\Base\Service\ApiRequestHandler;
use Pod\Base\Service\Exception\PodException;

class BankingApiRequestHandler extends ApiRequestHandler
{
    /**
     * @param string $baseUri
     * @param string $method
     * @param string $relativeUri
     * @param array $option
     * @param bool $optionHasArray
     * @param bool $restFull
     *
     * @return mixed
     *
     * @throws RequestException
     * @throws PodException
     */
    public static function Request($baseUri, $method, $relativeUri, $option, $restFull = false, $optionHasArray = false) {
        $result = parent::Request($baseUri, $method, $relativeUri, $option, $restFull, $optionHasArray);
        try {
        $xmlData = $result['result']['result'];
        $xmlDoc = new \DOMDocument();
        $xmlDoc->loadXML($xmlData);

        $x = $xmlDoc->documentElement;
        $bankResult = json_decode($x->nodeValue, true);
            if (isset($bankResult["IsSuccess"]) && !$bankResult["IsSuccess"]) {
                throw new PodException($bankResult["Message"], $bankResult["MessageCode"],null, $bankResult);
            } else {
                $result['result']['result'] =  $bankResult;
            }
        } catch (PodException $e) {
            throw new PodException($e->getMessage(), $e->getCode());
        }

        return $result;
    }
}