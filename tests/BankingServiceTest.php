<?php
/**
 * Created by PhpStorm.
 * User: keshtgar
 * Date: 11/11/19
 * Time: 9:49 AM
 */
use PHPUnit\Framework\TestCase;
use Pod\Banking\Service\BankingService;
use Pod\Base\Service\BaseInfo;
use Pod\Base\Service\Exception\ValidationException;
use Pod\Base\Service\Exception\PodException;

final class BankingServiceTest extends TestCase
{
//    public static $apiToken;
    public static $bankingService;
    const TOKEN_ISSUER = 1;
    const API_TOKEN = '4d3d6b85e2e844b0ade83cc2ec5b4c85';
    const ACCESS_TOKEN = '7e1044745ba543ce97231dafa200859f';
    const CLIENT_ID = '6257411i38cb46e0ae26be4629583b22';
    const CLIENT_SECRET = 'd33b5e71';
    const CONFIRM_CODE = '2007431';

    public function setUp(): void
    {
        parent::setUp();
        # set serverType to SandBox or Production
        BaseInfo::initServerType(BaseInfo::SANDBOX_SERVER);

        $baseInfo = new BaseInfo();
        $baseInfo->setTokenIssuer(self::TOKEN_ISSUER);
        $baseInfo->setToken(self::API_TOKEN);

        self::$bankingService = new BankingService($baseInfo);
    }

    public function testGetShebaInfoAllParameters()
    {
        $params =
        [
            ## ============== Required Parameters  ====================
            "sheba"              => "IR550560960180002284298001",
            ## ============== Optional Parameters  ====================
            'scVoucherHash'      => ['{Put Service Call Voucher Hashes}'],
            'scApiKey'           => '{Put service call Api Key}',
        ];

        try {
            $result = self::$bankingService->getShebaInfo($params);
            $this->assertFalse($result['hasError']);

        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testGetShebaInfoRequiredParameters()
    {
        $params =
        [
            ## ============== Required Parameters  ====================
            "sheba"              => "IR550560960180002284298001",
        ];
        try {
            $result = self::$bankingService->getShebaInfo($params);
            $this->assertFalse($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testGetShebaInfoValidationError()
    {
        $paramsWithoutRequired = [];
        $paramsWrongValue = [
            'sheba'              => 'wrongSheba',
        ];
        try {
            self::$bankingService->getShebaInfo($paramsWithoutRequired);
        } catch (ValidationException $e) {

            $validation = $e->getErrorsAsArray();
            $this->assertNotEmpty($validation);

            $result = $e->getResult();

            $this->assertArrayHasKey('sheba', $validation);
            $this->assertEquals('The property sheba is required', $validation['sheba'][0]);

            $this->assertEquals(887, $result['code']);
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
        try {
            self::$bankingService->getShebaInfo($paramsWrongValue);
        } catch (ValidationException $e) {

            $validation = $e->getErrorsAsArray();
            $this->assertNotEmpty($validation);

            $result = $e->getResult();

            $this->assertArrayHasKey('sheba', $validation);
            $this->assertEquals('Does not match the regex pattern ^(|IR)(\d)(?!\1{23}$)\d{23}$', $validation['sheba'][1]);

            $this->assertEquals(887, $result['code']);
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }
    
    public function testGetDebitCardInfoAllParameters()
    {
        $params =
            [
                ## ============== Required Parameters  ====================
                "cardNumber"        => "6104337865445059",
                ## ============== Optional Parameters  ====================
                'scVoucherHash'     => ['{Put Service Call Voucher Hashes}'],
                'scApiKey'          => '{Put service call Api Key}',
            ];

        try {
            $result = self::$bankingService->getDebitCardInfo($params);
            $this->assertFalse($result['hasError']);

        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testGetDebitCardInfoRequiredParameters()
    {
        $params =
            [
                ## ============== Required Parameters  ====================
                'cardNumber' => '6104337865445059',
            ];

        try {
            $result = self::$bankingService->getDebitCardInfo($params);
            $this->assertFalse($result['hasError']);
        } catch (ValidationException $e) {
            $this->fail('ValidationException: ' . $e->getErrorsAsString());
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }

    public function testGetDebitCardInfoValidationError()
    {
        $paramsWithoutRequired = [];
        $paramsWrongValue = [
            'cardNumber'              => '1234',
        ];
        try {
            self::$bankingService->getDebitCardInfo($paramsWithoutRequired);
        } catch (ValidationException $e) {

            $validation = $e->getErrorsAsArray();
            $this->assertNotEmpty($validation);

            $result = $e->getResult();

            $this->assertArrayHasKey('cardNumber', $validation);
            $this->assertEquals('The property cardNumber is required', $validation['cardNumber'][0]);

            $this->assertEquals(887, $result['code']);
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
        try {
            self::$bankingService->getDebitCardInfo($paramsWrongValue);
        } catch (ValidationException $e) {

            $validation = $e->getErrorsAsArray();
            $this->assertNotEmpty($validation);

            $result = $e->getResult();

            $this->assertArrayHasKey('cardNumber', $validation);
            $this->assertEquals('Does not match the regex pattern ^[1-9][0-9]{15}$', $validation['cardNumber'][1]);

            $this->assertEquals(887, $result['code']);
        } catch (PodException $e) {
            $error = $e->getResult();
            $this->fail('PodException: ' . $error['message']);
        }
    }
}