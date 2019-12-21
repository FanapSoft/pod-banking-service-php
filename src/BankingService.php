<?php
/**
 * Created by PhpStorm.
 * User :  keshtgar
 * Date :  6/28/19
 * Time : 10:29 AM
 *
 * $baseInfo BaseInfo
 */
namespace Pod\Banking\Service;

use Pod\Base\Service\BaseService;
use Pod\Base\Service\BaseInfo;
use Pod\Base\Service\ApiRequestHandler;

class BankingService extends BaseService
{
    private $header;
    private static $jsonSchema;
    private static $bankingServiceApi;
    private static $serviceCallProductId;
    private static $baseUri;
    private $privateKey;

    public function __construct($baseInfo, $privateKey)
    {
        BaseInfo::initServerType(BaseInfo::PRODUCTION_SERVER);
        parent::__construct();
        self::$jsonSchema = json_decode(file_get_contents(__DIR__ . '/../config/validationSchema.json'), true);
        $this->header = [
            '_token_issuer_'    =>  $baseInfo->getTokenIssuer(),
            '_token_'           => $baseInfo->getToken(),
        ];
        $this->privateKey = $privateKey;
        self::$bankingServiceApi = require __DIR__ . '/../config/apiConfig.php';
        self::$serviceCallProductId = require __DIR__ . '/../config/serviceCallProductId.php';
        self::$baseUri = self::$config[self::$serverType];
        self::$serviceCallProductId = self::$serviceCallProductId[self::$serverType];
    }

    public function getShebaInfo($params) {
        $apiName = 'getShebaInfo';
        $header = $this->header;
        array_walk_recursive($params, 'self::prepareData');

        $method = self::$bankingServiceApi[$apiName]['method'];
        $paramKey = $method == 'GET' ? 'query' : 'form_params';
        $relativeUri = self::$bankingServiceApi[$apiName]['subUri'];

        // if apiToken is set replace it
        if (isset($params['token'])) {
            $header["_token_"] = $params['token'];
        }
        unset($params['token']);

        $option = [
            'headers' => $header,
            $paramKey => $params, // set query param for validation
        ];

        self::validateOption($option, self::$jsonSchema[$apiName], $paramKey);

        # set service call product Id
        $option[$paramKey]['scProductId'] = self::$serviceCallProductId[$apiName];

        return ApiRequestHandler::Request(
            self::$config[self::$serverType][self::$bankingServiceApi[$apiName]['baseUri']],
            $method,
            $relativeUri,
            $option
        );
    }

    public function getDebitCardInfo($params) {
        $apiName = 'getDebitCardInfo';
        $header = $this->header;
        array_walk_recursive($params, 'self::prepareData');

        $method = self::$bankingServiceApi[$apiName]['method'];
        $paramKey = $method == 'GET' ? 'query' : 'form_params';

        $relativeUri = self::$bankingServiceApi[$apiName]['subUri'];

        // if apiToken is set replace it
        if (isset($params['token'])) {
            $header["_token_"] = $params['token'];
        }
        unset($params['token']);

        $option = [
            'headers' => $header,
            $paramKey => $params, // set query param for validation
        ];

        self::validateOption($option, self::$jsonSchema[$apiName], $paramKey);

        # set service call product Id
        $option[$paramKey]['scProductId'] = self::$serviceCallProductId[$apiName];

        return ApiRequestHandler::Request(
            self::$config[self::$serverType][self::$bankingServiceApi[$apiName]['baseUri']],
            $method,
            $relativeUri,
            $option
        );
    }

    public function getShebaInfoAndStatus($params) {
        $apiName = 'getShebaInfoAndStatus';
        $defaultTimeZone = date_default_timezone_get();
        date_default_timezone_set('Asia/Tehran');

        $optionHasArray = false;
        $header = $this->header;
        $privateKey = $this->privateKey;

        if(isset($params['token'])) {
            $header["_token_"] = $params['token'];
            unset($params['token']);
        }

        array_walk_recursive($params, 'self::prepareData');

        $method = self::$bankingServiceApi[$apiName]['method'];
        $paramKey = $method == 'GET' ? 'query' : 'form_params';

        $relativeUri = self::$bankingServiceApi[$apiName]['subUri'];
        $params['Timestamp'] = date('Y/m/d H:i:s:z');
        date_default_timezone_set($defaultTimeZone);

        $privateKey = $params['privateKey'] = isset($params['privateKey']) ? $params['privateKey'] : $privateKey;
        $option = [
            'headers' => $header,
            $paramKey => $params, // set query param for validation
        ];

        self::validateOption($option, self::$jsonSchema[$apiName], $paramKey);
        unset($params['privateKey']);

        $dataForSign = [
            "UserName" => $params["UserName"],
            "Sheba" => $params["Sheba"],
            "Timestamp" => $params["Timestamp"],
        ];

        // create signature
        $privateKeyId = openssl_pkey_get_private($privateKey);
        $data = json_encode($dataForSign, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if (openssl_sign($data,$signature , $privateKeyId, OPENSSL_ALGO_SHA1)) {
            $option[$paramKey]['signature'] = base64_encode($signature);
        }
        // free the key from memory
        openssl_free_key($privateKeyId);

        # set service call product Id
        $option[$paramKey]['scProductId'] = self::$serviceCallProductId[$apiName];

        if (isset($params['scVoucherHash'])) {
            $option['withoutBracketParams'] =  $option[$paramKey];
            unset($option[$paramKey]);
            $optionHasArray = true;
            $method = 'GET';
        }

        return BankingApiRequestHandler::Request(
            self::$config[self::$serverType][self::$bankingServiceApi[$apiName]['baseUri']],
            $method,
            $relativeUri,
            $option,
            false,
            $optionHasArray
        );
    }

    public function getDepositNumberByCardNumber($params) {
        $defaultTimeZone = date_default_timezone_get();
        date_default_timezone_set('Asia/Tehran');

        $apiName = 'getDepositNumberByCardNumber';
        $optionHasArray = false;
        $header = $this->header;
        $privateKey = $this->privateKey;

        if(isset($params['token'])) {
            $header["_token_"] = $params['token'];
            unset($params['token']);
        }

        array_walk_recursive($params, 'self::prepareData');

        $method = self::$bankingServiceApi[$apiName]['method'];
        $paramKey = $method == 'GET' ? 'query' : 'form_params';

        $relativeUri = self::$bankingServiceApi[$apiName]['subUri'];
        $params['Timestamp'] = date('Y/m/d H:i:s:z');
        date_default_timezone_set($defaultTimeZone);

        $privateKey = $params['privateKey'] = isset($params['privateKey']) ? $params['privateKey'] : $privateKey;
        $option = [
            'headers' => $header,
            $paramKey => $params, // set query param for validation
        ];

        self::validateOption($option, self::$jsonSchema[$apiName], $paramKey);
        unset($params['privateKey']);


        $dataForSign = [
            "UserName" => $params["UserName"],
            "CardNumber" => $params["CardNumber"],
            "Timestamp" => $params["Timestamp"],
        ];

        // create signature
        $privateKeyId = openssl_pkey_get_private($privateKey);
        $data = json_encode($dataForSign, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if (openssl_sign($data,$signature , $privateKeyId, OPENSSL_ALGO_SHA1)) {
            $option[$paramKey]['signature'] = base64_encode($signature);
        }
        // free the key from memory
        openssl_free_key($privateKeyId);

        # set service call product Id
        $option[$paramKey]['scProductId'] = self::$serviceCallProductId[$apiName];

        if (isset($params['scVoucherHash'])) {
            $option['withoutBracketParams'] =  $option[$paramKey];
            unset($option[$paramKey]);
            $optionHasArray = true;
            $method = 'GET';
        }

        return BankingApiRequestHandler::Request(
            self::$config[self::$serverType][self::$bankingServiceApi[$apiName]['baseUri']],
            $method,
            $relativeUri,
            $option,
            false,
            $optionHasArray
        );
    }

    public function getShebaByCardNumber($params) {
        $defaultTimeZone = date_default_timezone_get();
        date_default_timezone_set('Asia/Tehran');
        $apiName = 'getShebaByCardNumber';
        $method = self::$bankingServiceApi[$apiName]['method'];
        $relativeUri = self::$bankingServiceApi[$apiName]['subUri'];
        $paramKey = $method == 'GET' ? 'query' : 'form_params';
        $optionHasArray = false;
        $header = $this->header;
        $privateKey = $this->privateKey;

        if(isset($params['token'])) {
            $header["_token_"] = $params['token'];
            unset($params['token']);
        }
        array_walk_recursive($params, 'self::prepareData');

        $params['Timestamp'] = date('Y/m/d H:i:s:z');
        date_default_timezone_set($defaultTimeZone);

        $privateKey = $params['privateKey'] = isset($params['privateKey']) ? $params['privateKey'] : $privateKey;
        $option = [
            'headers' => $header,
            $paramKey => $params, // set query param for validation
        ];

        self::validateOption($option, self::$jsonSchema[$apiName], $paramKey);
        unset($params['privateKey']);

        $dataForSign = [
            "UserName" => $params["UserName"],
            "CardNumber" => $params["CardNumber"],
            "Timestamp" => $params["Timestamp"],
        ];

        // create signature
        $privateKeyId = openssl_pkey_get_private($privateKey);
        $data = json_encode($dataForSign, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if (openssl_sign($data,$signature , $privateKeyId, OPENSSL_ALGO_SHA1)) {
            $option[$paramKey]['signature'] = base64_encode($signature);
        }
        // free the key from memory
        openssl_free_key($privateKeyId);

        # set service call parameters
        $option[$paramKey]['scProductId'] = self::$serviceCallProductId[$apiName];
        if (isset($params['scVoucherHash'])) {
            $option['withoutBracketParams'] =  $option[$paramKey];
            unset($option[$paramKey]);
            $optionHasArray = true;
            $method = 'GET';
        }

        return BankingApiRequestHandler::Request(
            self::$config[self::$serverType][self::$bankingServiceApi[$apiName]['baseUri']],
            $method,
            $relativeUri,
            $option,
            false,
            $optionHasArray
        );
    }

    public function getCardInformation($params) {
        $defaultTimeZone = date_default_timezone_get();
        date_default_timezone_set('Asia/Tehran');

        $apiName = 'getCardInformation';
        $method = self::$bankingServiceApi[$apiName]['method'];
        $relativeUri = self::$bankingServiceApi[$apiName]['subUri'];
        $paramKey = $method == 'GET' ? 'query' : 'form_params';
        $optionHasArray = false;
        $header = $this->header;
        $privateKey = $this->privateKey;

        if(isset($params['token'])) {
            $header["_token_"] = $params['token'];
            unset($params['token']);
        }
        array_walk_recursive($params, 'self::prepareData');

        $params['Timestamp'] = date('Y/m/d H:i:s:z');
        date_default_timezone_set($defaultTimeZone);

        $privateKey = $params['privateKey'] = isset($params['privateKey']) ? $params['privateKey'] : $privateKey;
        $option = [
            'headers' => $header,
            $paramKey => $params, // set query param for validation
        ];

        self::validateOption($option, self::$jsonSchema[$apiName], $paramKey);
        unset($params['privateKey']);

        // prepare data for sign
        $dataForSign = [
            "UserName" => $params["UserName"],
            "SrcCardNumber" => $params["SrcCardNumber"],
            "DestCardNumber" => $params["DestCardNumber"],
            "Email" => isset($params["Email"]) ? $params["Email"] : '', # Optional Params
            "Timestamp" => $params["Timestamp"],
        ];

        // prepare parameter to send
        $option[$paramKey] = $dataForSign;

        // create signature
        $privateKeyId = openssl_pkey_get_private($privateKey);
        $data = json_encode($dataForSign, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if (openssl_sign($data,$signature , $privateKeyId, OPENSSL_ALGO_SHA1)) {
            $option[$paramKey]['signature'] = base64_encode($signature);
        }
        // free the key from memory
        openssl_free_key($privateKeyId);

        # set service call parameters
        $option[$paramKey]['scProductId'] = self::$serviceCallProductId[$apiName];

        if (isset($params['scApiKey'])) {
            $option[$paramKey]['scApiKey'] = $params['scApiKey'];
        }

        if (isset($params['scVoucherHash'])) {
            $option[$paramKey]['scVoucherHash'] = $params['scVoucherHash'];
            $option['withoutBracketParams'] =  $option[$paramKey];
            unset($option[$paramKey]);
            $optionHasArray = true;
            $method = 'GET';
        }

        return BankingApiRequestHandler::Request(
            self::$config[self::$serverType][self::$bankingServiceApi[$apiName]['baseUri']],
            $method,
            $relativeUri,
            $option,
            false,
            $optionHasArray
        );
    }

    public function cardToCard($params) {
        $apiName = 'cardToCard';
        $defaultTimeZone = date_default_timezone_get();
        date_default_timezone_set('Asia/Tehran');
        $method = self::$bankingServiceApi[$apiName]['method'];
        $relativeUri = self::$bankingServiceApi[$apiName]['subUri'];
        $paramKey = $method == 'GET' ? 'query' : 'form_params';
        $optionHasArray = false;
        $header = $this->header;
        $privateKey = $this->privateKey;

        if(isset($params['token'])) {
            $header["_token_"] = $params['token'];
            unset($params['token']);
        }
        array_walk_recursive($params, 'self::prepareData');

        $params['Timestamp'] = date('Y/m/d H:i:s:z');
        date_default_timezone_set($defaultTimeZone);

        $privateKey = $params['privateKey'] = isset($params['privateKey']) ? $params['privateKey'] : $privateKey;
        $option = [
            'headers' => $header,
            $paramKey => $params, // set query param for validation
        ];

        self::validateOption($option, self::$jsonSchema[$apiName], $paramKey);
        unset($params['privateKey']);

        // prepare data for sign
        $dataForSign = [
            "UserName" => $params["UserName"],
            "SrcCardNumber" => $params["SrcCardNumber"],
            "DestCardNumber" => $params["DestCardNumber"],
            "Password" => $params["Password"],
            "Cvv2" => $params["Cvv2"],
            "ExpireMonth" => $params["ExpireMonth"],
            "ExpireYear" => $params["ExpireYear"],
            "Amount" => (string)$params["Amount"],
            "Email" => isset($params["Email"]) ? $params["Email"] : '',
            "AuthorizationCode" => isset($params["AuthorizationCode"]) ? $params["AuthorizationCode"] : '0',
            "WithReferenceNumber" => isset($params["WithReferenceNumber"]) ? (string) $params["WithReferenceNumber"] : 'false',
            "CardName" => isset($params["CardName"]) ? $params["CardName"] : '',
            "SrcComment" => isset($params["SrcComment"]) ? $params["SrcComment"] : '',
            "DestComment" => isset($params["DestComment"]) ? $params["DestComment"] : '',
            "OriginalAddress" => isset($params["OriginalAddress"]) ? $params["OriginalAddress"] : '',
            "JsonData" => isset($params["ExtraData"]) ? json_encode($params["ExtraData"], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '',
            "Timestamp" => $params["Timestamp"],
        ];

        // prepare parameter to send
        $option[$paramKey] = $dataForSign;

        // create signature
        $privateKeyId = openssl_pkey_get_private($privateKey);
        $data = json_encode($dataForSign, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
//        var_dump($data);die;
        if (openssl_sign($data,$signature , $privateKeyId, OPENSSL_ALGO_SHA1)) {
            $option[$paramKey]['signature'] = base64_encode($signature);
        }
        // free the key from memory
        openssl_free_key($privateKeyId);

        # set service call parameters
        $option[$paramKey]['scProductId'] = self::$serviceCallProductId[$apiName];
        if (isset($params['scApiKey'])) {
            $option[$paramKey]['scApiKey'] = $params['scApiKey'];
        }
        if (isset($params['scVoucherHash'])) {
            $option[$paramKey]['scVoucherHash'] = $params['scVoucherHash'];
            $option['withoutBracketParams'] =  $option[$paramKey];
            unset($option[$paramKey]);
            $optionHasArray = true;
            $method = 'GET';
        }
        return BankingApiRequestHandler::Request(
            self::$config[self::$serverType][self::$bankingServiceApi[$apiName]['baseUri']],
            $method,
            $relativeUri,
            $option,
            false,
            $optionHasArray
        );
    }

    public function cardToCardList($params) {
        $apiName = 'cardToCardList';
        $defaultTimeZone = date_default_timezone_get();
        date_default_timezone_set('Asia/Tehran');
        $method = self::$bankingServiceApi[$apiName]['method'];
        $relativeUri = self::$bankingServiceApi[$apiName]['subUri'];
        $paramKey = $method == 'GET' ? 'query' : 'form_params';
        $optionHasArray = false;
        $header = $this->header;
        $privateKey = $this->privateKey;

        if(isset($params['token'])) {
            $header["_token_"] = $params['token'];
            unset($params['token']);
        }
        array_walk_recursive($params, 'self::prepareData');

        $params['Timestamp'] = date('Y/m/d H:i:s:z');
        date_default_timezone_set($defaultTimeZone);

        $privateKey = $params['privateKey'] = isset($params['privateKey']) ? $params['privateKey'] : $privateKey;
        $option = [
            'headers' => $header,
            $paramKey => $params, // set query param for validation
        ];

        self::validateOption($option, self::$jsonSchema[$apiName], $paramKey);
        unset($params['privateKey']);

        // prepare data for sign
        $dataForSign = [
            "UserName" => $params["UserName"],
            "SourceCardNumber" => $params["SourceCardNumber"],
            "SourceDepositNumber" => isset($params["SourceDepositNumber"]) ? $params["SourceDepositNumber"] : '',
            "DestinationCardNumber" => isset($params["DestinationCardNumber"]) ? $params["DestinationCardNumber"] : '',
            "MinAmount" => (string)$params["MinAmount"],
            "MaxAmount" => (string)$params["MaxAmount"],
            "StartDate" => $params["StartDate"],
            "EndDate" => $params["EndDate"],
            "RefrenceNumber" => isset($params["ReferenceNumber"]) ? $params["ReferenceNumber"] : '',
            "SequenceNumber" => isset($params["SequenceNumber"]) ? $params["SequenceNumber"] : '',
            "SourceNote" => isset($params["SourceNote"]) ? $params["SourceNote"] : '',
            "DestinationNote" => isset($params["DestinationNote"]) ? $params["DestinationNote"] : '',
            "Timestamp" => $params["Timestamp"],
        ];

        // prepare parameter to send
        $option[$paramKey] = $dataForSign;

        // create signature
        $privateKeyId = openssl_pkey_get_private($privateKey);
        $data = json_encode($dataForSign, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if (openssl_sign($data,$signature , $privateKeyId, OPENSSL_ALGO_SHA1)) {
            $option[$paramKey]['signature'] = base64_encode($signature);
        }
        // free the key from memory
        openssl_free_key($privateKeyId);

        # set service call parameters
        $option[$paramKey]['scProductId'] = self::$serviceCallProductId[$apiName];
        if (isset($params['scApiKey'])) {
            $option[$paramKey]['scApiKey'] = $params['scApiKey'];
        }
        if (isset($params['scVoucherHash'])) {
            $option[$paramKey]['scVoucherHash'] = $params['scVoucherHash'];
            $option['withoutBracketParams'] =  $option[$paramKey];
            unset($option[$paramKey]);
            $optionHasArray = true;
            $method = 'GET';
        }

        return BankingApiRequestHandler::Request(
            self::$config[self::$serverType][self::$bankingServiceApi[$apiName]['baseUri']],
            $method,
            $relativeUri,
            $option,
            false,
            $optionHasArray
        );
    }

    public function getSubmissionCheque($params) {
        $defaultTimeZone = date_default_timezone_get();
        date_default_timezone_set('Asia/Tehran');
        $apiName = 'getSubmissionCheque';
        $method = self::$bankingServiceApi[$apiName]['method'];
        $relativeUri = self::$bankingServiceApi[$apiName]['subUri'];
        $paramKey = $method == 'GET' ? 'query' : 'form_params';
        $optionHasArray = false;
        $header = $this->header;
        $privateKey = $this->privateKey;

        if(isset($params['token'])) {
            $header["_token_"] = $params['token'];
            unset($params['token']);
        }
        array_walk_recursive($params, 'self::prepareData');

        $params['Timestamp'] = date('Y/m/d H:i:s:z');
        date_default_timezone_set($defaultTimeZone);

        $privateKey = $params['privateKey'] = isset($params['privateKey']) ? $params['privateKey'] : $privateKey;
        $option = [
            'headers' => $header,
            $paramKey => $params, // set query param for validation
        ];

        self::validateOption($option, self::$jsonSchema[$apiName], $paramKey);
        unset($params['privateKey']);

        // prepare data for sign
        $dataForSign = [
            "UserName" => $params["UserName"],
            "Deposit" => $params["Deposit"],
            "ChequeNumber" => isset($params["ChequeNumber"]) ? $params["ChequeNumber"] : '',
            "MinAmount" => (string) $params["MinAmount"],
            "MaxAmount" => (string) $params["MaxAmount"],
            "StartDate" => $params["StartDate"],
            "EndDate" => $params["EndDate"],
            "BankCode" => (string) $params["BankCode"],
            "ChequeStatus" => (string) $params["ChequeStatus"],
            "StartSubmisionDate" => $params["StartSubmisionDate"],
            "EndSubmissionDate" => $params["EndSubmissionDate"],
            "RowCount" => (string) $params["RowCount"],
            "Timestamp" => $params["Timestamp"],
        ];
        // prepare parameter to send
        $option[$paramKey] = $dataForSign;

        // create signature
        $privateKeyId = openssl_pkey_get_private($privateKey);
        $data = json_encode($dataForSign, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if (openssl_sign($data,$signature , $privateKeyId, OPENSSL_ALGO_SHA1)) {
            $option[$paramKey]['signature'] = base64_encode($signature);
        }
        // free the key from memory
        openssl_free_key($privateKeyId);

        # set service call parameters
        $option[$paramKey]['scProductId'] = self::$serviceCallProductId[$apiName];

        if (isset($params['scApiKey'])) {
            $option[$paramKey]['scApiKey'] = $params['scApiKey'];
        }

        if (isset($params['scVoucherHash'])) {
            $option[$paramKey]['scVoucherHash'] = $params['scVoucherHash'];
            $option['withoutBracketParams'] =  $option[$paramKey];
            unset($option[$paramKey]);
            $optionHasArray = true;
            $method = 'GET';
        }

        return BankingApiRequestHandler::Request(
            self::$config[self::$serverType][self::$bankingServiceApi[$apiName]['baseUri']],
            $method,
            $relativeUri,
            $option,
            false,
            $optionHasArray
        );
    }

    public function convertDepositNumberToSheba($params) {
        $defaultTimeZone = date_default_timezone_get();
        date_default_timezone_set('Asia/Tehran');

        $apiName = 'convertDepositNumberToSheba';
        $method = self::$bankingServiceApi[$apiName]['method'];
        $relativeUri = self::$bankingServiceApi[$apiName]['subUri'];
        $paramKey = $method == 'GET' ? 'query' : 'form_params';
        $optionHasArray = false;
        $header = $this->header;
        $privateKey = $this->privateKey;

        if(isset($params['token'])) {
            $header["_token_"] = $params['token'];
            unset($params['token']);
        }
        array_walk_recursive($params, 'self::prepareData');

        $params['Timestamp'] = date('Y/m/d H:i:s:z');
        date_default_timezone_set($defaultTimeZone);

        $privateKey = $params['privateKey'] = isset($params['privateKey']) ? $params['privateKey'] : $privateKey;
        $option = [
            'headers' => $header,
            $paramKey => $params, // set query param for validation
        ];

        self::validateOption($option, self::$jsonSchema[$apiName], $paramKey);
        unset($params['privateKey']);

        // prepare data for sign
        $dataForSign = [
            "UserName" => $params["UserName"],
            "DepositNumber" => $params["DepositNumber"],
            "Timestamp" => $params["Timestamp"],
        ];

        // create signature
        $privateKeyId = openssl_pkey_get_private($privateKey);
        $data = json_encode($dataForSign, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if (openssl_sign($data,$signature , $privateKeyId, OPENSSL_ALGO_SHA1)) {
            $option[$paramKey]['signature'] = base64_encode($signature);
        }
        // free the key from memory
        openssl_free_key($privateKeyId);

        # set service call parameters
        $option[$paramKey]['scProductId'] = self::$serviceCallProductId[$apiName];
        if (isset($params['scVoucherHash'])) {
            $option['withoutBracketParams'] =  $option[$paramKey];
            unset($option[$paramKey]);
            $optionHasArray = true;
            $method = 'GET';
        }

        return BankingApiRequestHandler::Request(
            self::$config[self::$serverType][self::$bankingServiceApi[$apiName]['baseUri']],
            $method,
            $relativeUri,
            $option,
            false,
            $optionHasArray
        );
    }

    public function convertShebaToDepositNumber($params) {
        $apiName = 'convertShebaToDepositNumber';
        $defaultTimeZone = date_default_timezone_get();
        date_default_timezone_set('Asia/Tehran');
        $method = self::$bankingServiceApi[$apiName]['method'];
        $relativeUri = self::$bankingServiceApi[$apiName]['subUri'];
        $paramKey = $method == 'GET' ? 'query' : 'form_params';
        $optionHasArray = false;
        $header = $this->header;
        $privateKey = $this->privateKey;

        if(isset($params['token'])) {
            $header["_token_"] = $params['token'];
            unset($params['token']);
        }
        array_walk_recursive($params, 'self::prepareData');

        $params['Timestamp'] = date('Y/m/d H:i:s:z');
        date_default_timezone_set($defaultTimeZone);

        $privateKey = $params['privateKey'] = isset($params['privateKey']) ? $params['privateKey'] : $privateKey;
        $option = [
            'headers' => $header,
            $paramKey => $params, // set query param for validation
        ];

        self::validateOption($option, self::$jsonSchema[$apiName], $paramKey);
        unset($params['privateKey']);

        // prepare data for sign
        $dataForSign = [
            "UserName" => $params["UserName"],
            "Sheba" => $params["Sheba"],
            "Timestamp" => $params["Timestamp"],
        ];
        // create signature
        $privateKeyId = openssl_pkey_get_private($privateKey);
        $data = json_encode($dataForSign, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if (openssl_sign($data,$signature , $privateKeyId, OPENSSL_ALGO_SHA1)) {
            $option[$paramKey]['signature'] = base64_encode($signature);
        }
        // free the key from memory
        openssl_free_key($privateKeyId);

        # set service call parameters
        $option[$paramKey]['scProductId'] = self::$serviceCallProductId[$apiName];
        if (isset($params['scVoucherHash'])) {
            $option['withoutBracketParams'] =  $option[$paramKey];
            unset($option[$paramKey]);
            $optionHasArray = true;
            $method = 'GET';
        }

        return BankingApiRequestHandler::Request(
            self::$config[self::$serverType][self::$bankingServiceApi[$apiName]['baseUri']],
            $method,
            $relativeUri,
            $option,
            false,
            $optionHasArray
        );
    }

    public function payaService($params) {
        $apiName = 'payaService';
        $defaultTimeZone = date_default_timezone_get();
        date_default_timezone_set('Asia/Tehran');
        $method = self::$bankingServiceApi[$apiName]['method'];
        $relativeUri = self::$bankingServiceApi[$apiName]['subUri'];
        $paramKey = $method == 'GET' ? 'query' : 'form_params';
        $optionHasArray = false;
        $header = $this->header;
        $privateKey = $this->privateKey;

        if(isset($params['token'])) {
            $header["_token_"] = $params['token'];
            unset($params['token']);
        }
        array_walk_recursive($params, 'self::prepareData');

        $params['Timestamp'] = date('Y/m/d H:i:s:z');
        date_default_timezone_set($defaultTimeZone);

        $privateKey = $params['privateKey'] = isset($params['privateKey']) ? $params['privateKey'] : $privateKey;
        $option = [
            'headers' => $header,
            $paramKey => $params, // set query param for validation
        ];

        self::validateOption($option, self::$jsonSchema[$apiName], $paramKey);
        unset($params['privateKey']);

//        $params['BatchPayaItemInfos'] = json_encode($params['BatchPayaItemInfos']);

        // prepare data for sign
        $dataForSign = [
            "UserName" => $params["UserName"],
            "SourceDepositNumber" => $params["SourceDepositNumber"],
            "FileUniqueIdentifier" => $params["FileUniqueIdentifier"],
            "TransferMoneyBillNumber" => isset($params["TransferMoneyBillNumber"]) ? $params["TransferMoneyBillNumber"] : '',
            "BatchPayaItemInfos" => $params['BatchPayaItemInfos'],
            "Timestamp" => $params["Timestamp"],
        ];
        // prepare parameter to send
        $option[$paramKey] = $dataForSign;
        $option[$paramKey]['BatchPayaItemInfos'] =  json_encode($option[$paramKey]['BatchPayaItemInfos']);

        // create signature
        $privateKeyId = openssl_pkey_get_private($privateKey);
        $data = json_encode($dataForSign, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if (openssl_sign($data,$signature , $privateKeyId, OPENSSL_ALGO_SHA1)) {
            $option[$paramKey]['signature'] = base64_encode($signature);
        }
        // free the key from memory
        openssl_free_key($privateKeyId);

        # set service call parameters
        $option[$paramKey]['scProductId'] = self::$serviceCallProductId[$apiName];
        if (isset($params['scApiKey'])) {
            $option[$paramKey]['scApiKey'] = $params['scApiKey'];
        }
        if (isset($params['scVoucherHash'])) {
            $option[$paramKey]['scVoucherHash'] = $params['scVoucherHash'];
            $option['withoutBracketParams'] =  $option[$paramKey];
            unset($option[$paramKey]);
            $optionHasArray = true;
            $method = 'GET';
        }
        return BankingApiRequestHandler::Request(
            self::$config[self::$serverType][self::$bankingServiceApi[$apiName]['baseUri']],
            $method,
            $relativeUri,
            $option,
            false,
            $optionHasArray
        );
    }

    public function getDepositInvoice($params) {
        $apiName = 'getDepositInvoice';
        $defaultTimeZone = date_default_timezone_get();
        date_default_timezone_set('Asia/Tehran');
        $method = self::$bankingServiceApi[$apiName]['method'];
        $relativeUri = self::$bankingServiceApi[$apiName]['subUri'];
        $paramKey = $method == 'GET' ? 'query' : 'form_params';
        $optionHasArray = false;
        $header = $this->header;
        $privateKey = $this->privateKey;

        if(isset($params['token'])) {
            $header["_token_"] = $params['token'];
            unset($params['token']);
        }
        array_walk_recursive($params, 'self::prepareData');

        $params['Timestamp'] = date('Y/m/d H:i:s:z');
        date_default_timezone_set($defaultTimeZone);

        $privateKey = $params['privateKey'] = isset($params['privateKey']) ? $params['privateKey'] : $privateKey;
        $option = [
            'headers' => $header,
            $paramKey => $params, // set query param for validation
        ];

        self::validateOption($option, self::$jsonSchema[$apiName], $paramKey);
        unset($params['privateKey']);

        // prepare data for sign
        $dataForSign = [
            "UserName" => $params["UserName"],
            "DepositNumber" => isset($params["DepositNumber"])? $params["DepositNumber"] : '',
            "Sheba" => isset($params["Sheba"])? $params["Sheba"] : '',
            "StartDate" => date('Y/m/d 00:00:00:000', strtotime($params["StartDate"])),
            "EndDate" => date('Y/m/d 23:59:59:999', strtotime($params["EndDate"])),
            "FirstIndex" => isset($params["FirstIndex"]) ? (string)$params["FirstIndex"] : '',
            "Count" => isset($params["FirstIndex"]) ? (string)$params["FirstIndex"] : '',
            "Timestamp" => $params["Timestamp"],
        ];
        // prepare parameter to send
        $option[$paramKey] = $dataForSign;

        // create signature
        $privateKeyId = openssl_pkey_get_private($privateKey);
        $data = json_encode($dataForSign, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if (openssl_sign($data,$signature , $privateKeyId, OPENSSL_ALGO_SHA1)) {
            $option[$paramKey]['signature'] = base64_encode($signature);
        }
        // free the key from memory
        openssl_free_key($privateKeyId);

        # set service call parameters
        $option[$paramKey]['scProductId'] = self::$serviceCallProductId[$apiName];
        if (isset($params['scApiKey'])) {
            $option[$paramKey]['scApiKey'] = $params['scApiKey'];
        }
        if (isset($params['scVoucherHash'])) {
            $option[$paramKey]['scVoucherHash'] = $params['scVoucherHash'];
            $option['withoutBracketParams'] =  $option[$paramKey];
            unset($option[$paramKey]);
            $optionHasArray = true;
            $method = 'GET';
        }
        return BankingApiRequestHandler::Request(
            self::$config[self::$serverType][self::$bankingServiceApi[$apiName]['baseUri']],
            $method,
            $relativeUri,
            $option,
            false,
            $optionHasArray
        );
    }

    public function getDepositBalance($params) {
        $apiName = 'getDepositBalance';
        $defaultTimeZone = date_default_timezone_get();
        date_default_timezone_set('Asia/Tehran');
        $method = self::$bankingServiceApi[$apiName]['method'];
        $relativeUri = self::$bankingServiceApi[$apiName]['subUri'];
        $paramKey = $method == 'GET' ? 'query' : 'form_params';
        $optionHasArray = false;
        $header = $this->header;
        $privateKey = $this->privateKey;

        if(isset($params['token'])) {
            $header["_token_"] = $params['token'];
            unset($params['token']);
        }
        array_walk_recursive($params, 'self::prepareData');

        $params['Timestamp'] = date('Y/m/d H:i:s:z');
        date_default_timezone_set($defaultTimeZone);

        $privateKey = $params['privateKey'] = isset($params['privateKey']) ? $params['privateKey'] : $privateKey;
        $option = [
            'headers' => $header,
            $paramKey => $params, // set query param for validation
        ];

        self::validateOption($option, self::$jsonSchema[$apiName], $paramKey);
        unset($params['privateKey']);

        // prepare data for sign
        $dataForSign = [
            "UserName" => $params["UserName"],
            "DepositNumber" => isset($params["DepositNumber"])? $params["DepositNumber"] : '',
            "Sheba" => isset($params["Sheba"])? $params["Sheba"] : '',
            "Timestamp" => $params["Timestamp"],
        ];
        // prepare parameter to send
        $option[$paramKey] = $dataForSign;

        // create signature
        $privateKeyId = openssl_pkey_get_private($privateKey);
        $data = json_encode($dataForSign, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if (openssl_sign($data,$signature , $privateKeyId, OPENSSL_ALGO_SHA1)) {
            $option[$paramKey]['signature'] = base64_encode($signature);
        }
        // free the key from memory
        openssl_free_key($privateKeyId);

        # set service call parameters
        $option[$paramKey]['scProductId'] = self::$serviceCallProductId[$apiName];
        if (isset($params['scApiKey'])) {
            $option[$paramKey]['scApiKey'] = $params['scApiKey'];
        }
        if (isset($params['scVoucherHash'])) {
            $option[$paramKey]['scVoucherHash'] = $params['scVoucherHash'];
            $option['withoutBracketParams'] =  $option[$paramKey];
            unset($option[$paramKey]);
            $optionHasArray = true;
            $method = 'GET';
        }
        return BankingApiRequestHandler::Request(
            self::$config[self::$serverType][self::$bankingServiceApi[$apiName]['baseUri']],
            $method,
            $relativeUri,
            $option,
            false,
            $optionHasArray
        );
    }

    public function transferMoney($params) {
        $apiName = 'transferMoney';
        $defaultTimeZone = date_default_timezone_get();
        date_default_timezone_set('Asia/Tehran');
        $method = self::$bankingServiceApi[$apiName]['method'];
        $relativeUri = self::$bankingServiceApi[$apiName]['subUri'];
        $paramKey = $method == 'GET' ? 'query' : 'form_params';
        $optionHasArray = false;
        $header = $this->header;
        $privateKey = $this->privateKey;

        if(isset($params['token'])) {
            $header["_token_"] = $params['token'];
            unset($params['token']);
        }
        array_walk_recursive($params, 'self::prepareData');

        $params['Timestamp'] = date('Y/m/d H:i:s:z');
        date_default_timezone_set($defaultTimeZone);

        $privateKey = $params['privateKey'] = isset($params['privateKey']) ? $params['privateKey'] : $privateKey;
        $option = [
            'headers' => $header,
            $paramKey => $params, // set query param for validation
        ];

        self::validateOption($option, self::$jsonSchema[$apiName], $paramKey);
        unset($params['privateKey']);

        // prepare data for sign
        $dataForSign = [
            "UserName" => $params["UserName"],
            "SourceDepositNumber" => isset($params["SourceDepositNumber"]) ? $params["SourceDepositNumber"] : '',
            "SourceSheba" => isset($params["SourceSheba"]) ? $params["SourceSheba"] : '',
            "DestDepositNumber" => isset($params["DestDepositNumber"]) ? $params["DestDepositNumber"] : '',
            "DestSheba" => isset($params["DestSheba"]) ? $params["DestSheba"] : '',
            "DestFirstName" => isset($params["DestFirstName"]) ? $params["DestFirstName"] : '',
            "DestLastName" =>  isset($params["DestLastName"]) ? $params["DestLastName"] : '',
            "Amount" => (string)$params["Amount"],
            "SourceComment" => isset($params["SourceComment"]) ? $params["SourceComment"] : '',
            "DestComment" => isset($params["DestComment"]) ? $params["DestComment"] : '',
            "PaymentId" => $params["PaymentId"],
            "Timestamp" => $params["Timestamp"],
        ];

        // prepare parameter to send
        $option[$paramKey] = $dataForSign;

        // create signature
        $privateKeyId = openssl_pkey_get_private($privateKey);
        $data = json_encode($dataForSign, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if (openssl_sign($data,$signature , $privateKeyId, OPENSSL_ALGO_SHA1)) {
            $option[$paramKey]['signature'] = base64_encode($signature);
        }
        // free the key from memory
        openssl_free_key($privateKeyId);

        # set service call parameters
        $option[$paramKey]['scProductId'] = self::$serviceCallProductId[$apiName];
        if (isset($params['scApiKey'])) {
            $option[$paramKey]['scApiKey'] = $params['scApiKey'];
        }
        if (isset($params['scVoucherHash'])) {
            $option[$paramKey]['scVoucherHash'] = $params['scVoucherHash'];
            $option['withoutBracketParams'] =  $option[$paramKey];
            unset($option[$paramKey]);
            $optionHasArray = true;
            $method = 'GET';
        }

        return BankingApiRequestHandler::Request(
            self::$config[self::$serverType][self::$bankingServiceApi[$apiName]['baseUri']],
            $method,
            $relativeUri,
            $option,
            false,
            $optionHasArray
        );
    }

    public function getTransferState($params) {
        $apiName = 'getTransferState';
        $defaultTimeZone = date_default_timezone_get();
        date_default_timezone_set('Asia/Tehran');
        $method = self::$bankingServiceApi[$apiName]['method'];
        $relativeUri = self::$bankingServiceApi[$apiName]['subUri'];
        $paramKey = $method == 'GET' ? 'query' : 'form_params';
        $optionHasArray = false;
        $header = $this->header;
        $privateKey = $this->privateKey;

        if(isset($params['token'])) {
            $header["_token_"] = $params['token'];
            unset($params['token']);
        }
        array_walk_recursive($params, 'self::prepareData');

        $params['Timestamp'] = date('Y/m/d H:i:s:z');
        date_default_timezone_set($defaultTimeZone);

        $privateKey = $params['privateKey'] = isset($params['privateKey']) ? $params['privateKey'] : $privateKey;
        $option = [
            'headers' => $header,
            $paramKey => $params, // set query param for validation
        ];

        self::validateOption($option, self::$jsonSchema[$apiName], $paramKey);
        unset($params['privateKey']);

        // prepare data for sign
        $dataForSign = [
            "UserName" => $params["UserName"],
            "Date" => $params["Date"],
            "PaymentId" => $params["PaymentId"],
            "Timestamp" => $params["Timestamp"],
        ];

        // prepare parameter to send
        $option[$paramKey] = $dataForSign;

        // create signature
        $privateKeyId = openssl_pkey_get_private($privateKey);
        $data = json_encode($dataForSign, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if (openssl_sign($data,$signature , $privateKeyId, OPENSSL_ALGO_SHA1)) {
            $option[$paramKey]['signature'] = base64_encode($signature);
        }
        // free the key from memory
        openssl_free_key($privateKeyId);

        # set service call parameters
        $option[$paramKey]['scProductId'] = self::$serviceCallProductId[$apiName];
        if (isset($params['scApiKey'])) {
            $option[$paramKey]['scApiKey'] = $params['scApiKey'];
        }
        if (isset($params['scVoucherHash'])) {
            $option[$paramKey]['scVoucherHash'] = $params['scVoucherHash'];
            $option['withoutBracketParams'] =  $option[$paramKey];
            unset($option[$paramKey]);
            $optionHasArray = true;
            $method = 'GET';
        }

        return BankingApiRequestHandler::Request(
            self::$config[self::$serverType][self::$bankingServiceApi[$apiName]['baseUri']],
            $method,
            $relativeUri,
            $option,
            false,
            $optionHasArray
        );
    }

    public function billPaymentByDeposit($params) {
        $apiName = 'billPaymentByDeposit';
        $defaultTimeZone = date_default_timezone_get();
        date_default_timezone_set('Asia/Tehran');
        $method = self::$bankingServiceApi[$apiName]['method'];
        $relativeUri = self::$bankingServiceApi[$apiName]['subUri'];
        $paramKey = $method == 'GET' ? 'query' : 'form_params';
        $optionHasArray = false;
        $header = $this->header;
        $privateKey = $this->privateKey;

        if(isset($params['token'])) {
            $header["_token_"] = $params['token'];
            unset($params['token']);
        }
        array_walk_recursive($params, 'self::prepareData');

        $params['Timestamp'] = date('Y/m/d H:i:s:z');
        date_default_timezone_set($defaultTimeZone);

        $privateKey = $params['privateKey'] = isset($params['privateKey']) ? $params['privateKey'] : $privateKey;
        $option = [
            'headers' => $header,
            $paramKey => $params, // set query param for validation
        ];

        self::validateOption($option, self::$jsonSchema[$apiName], $paramKey);
        unset($params['privateKey']);

        // prepare data for sign
        $dataForSign = [
            "UserName" => $params["UserName"],
            "PaymentId" => $params["PaymentId"],
            "DepositNumber" => $params["DepositNumber"],
            "BillNumber" => $params["BillNumber"],
            "Timestamp" => $params["Timestamp"],
        ];

        // prepare parameter to send
        $option[$paramKey] = $dataForSign;

        // create signature
        $privateKeyId = openssl_pkey_get_private($privateKey);
        $data = json_encode($dataForSign, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if (openssl_sign($data,$signature , $privateKeyId, OPENSSL_ALGO_SHA1)) {
            $option[$paramKey]['signature'] = base64_encode($signature);
        }
        // free the key from memory
        openssl_free_key($privateKeyId);

        # set service call parameters
        $option[$paramKey]['scProductId'] = self::$serviceCallProductId[$apiName];
        if (isset($params['scApiKey'])) {
            $option[$paramKey]['scApiKey'] = $params['scApiKey'];
        }
        if (isset($params['scVoucherHash'])) {
            $option[$paramKey]['scVoucherHash'] = $params['scVoucherHash'];
            $option['withoutBracketParams'] =  $option[$paramKey];
            unset($option[$paramKey]);
            $optionHasArray = true;
            $method = 'GET';
        }

        return BankingApiRequestHandler::Request(
            self::$config[self::$serverType][self::$bankingServiceApi[$apiName]['baseUri']],
            $method,
            $relativeUri,
            $option,
            false,
            $optionHasArray
        );
    }
}