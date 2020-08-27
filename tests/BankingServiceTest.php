<?php
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
    const API_TOKEN = '{Put Api Token}';
    const ACCESS_TOKEN = '{Put Access Token}';
    const CLIENT_ID = 'Put client id';
    const CLIENT_SECRET = 'Put client secret';
    const CONFIRM_CODE = 'Put confirm code';

    public function setUp(): void
    {
        parent::setUp();
        # set serverType to SandBox or Production
        BaseInfo::initServerType(BaseInfo::SANDBOX_SERVER);

        $baseInfo = new BaseInfo();
        $baseInfo->setTokenIssuer(self::TOKEN_ISSUER);
        $baseInfo->setToken(self::API_TOKEN);

        self::$bankingService = new BankingService($baseInfo, 'Put private key');
    }

    public function testGetShebaInfoAllParameters()
    {
        $params =
        [
            ## ============== Required Parameters  ====================
            "sheba"              => "Put Sheba",
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
            "sheba"              => "Put sheba",
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
                "cardNumber"        => "put card number",
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
                'cardNumber' => 'put card number',
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