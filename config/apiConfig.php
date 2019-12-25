<?php
return  [
    // #1
    "getDebitCardInfo" => [
        "baseUri" => 'PLATFORM-ADDRESS',
        "subUri" => 'nzh/doServiceCall',
        "method" => 'POST'
    ],

    // #2
    "getShebaInfo" => [
        "baseUri" => 'PLATFORM-ADDRESS',
        "subUri" => 'nzh/doServiceCall',
        "method" => 'POST'
    ],

    #3  بازیابی اطلاعات سپرده از کارت
    "getDepositNumberByCardNumber" => [
        "baseUri" => 'PLATFORM-ADDRESS',
        "subUri" => 'nzh/doServiceCall',
        "method" => 'POST'
    ],

     #4 بازیابی اطلاعات شبا از کارت
    "getShebaByCardNumber" => [
        "baseUri" => 'PLATFORM-ADDRESS',
        "subUri" => 'nzh/doServiceCall',
        "method" => 'POST'
    ],

    #5 دریافت اطلاعات کارت های شتابی و غیر شتابی
    "getCardInformation" => [
        "baseUri" => 'PLATFORM-ADDRESS',
        "subUri" => 'nzh/doServiceCall',
        "method" => 'POST'
    ],

    #6 کارت به کارت
    "cardToCard" => [
        "baseUri" => 'PLATFORM-ADDRESS',
        "subUri" => 'nzh/doServiceCall',
        "method" => 'POST'
    ],

    #7 گزارش کارت به کارت ها
    "cardToCardList" => [
        "baseUri" => 'PLATFORM-ADDRESS',
        "subUri" => 'nzh/doServiceCall',
        "method" => 'POST'
    ],

    #8 استعلام چک های واگذار شده
    "getSubmissionCheque" => [
        "baseUri" => 'PLATFORM-ADDRESS',
        "subUri" => 'nzh/doServiceCall',
        "method" => 'POST'
    ],

    #9 تبدیل شماره سپرده به شبا
    "convertDepositNumberToSheba" => [
        "baseUri" => 'PLATFORM-ADDRESS',
        "subUri" => 'nzh/doServiceCall',
        "method" => 'POST'
    ],

    #10 تبدیل شبا به سپرده
    "convertShebaToDepositNumber" => [
        "baseUri" => 'PLATFORM-ADDRESS',
        "subUri" => 'nzh/doServiceCall',
        "method" => 'POST'
    ],

    #11 سرویس پایا بانک پاسارگاد
    "payaService" => [
        "baseUri" => 'PLATFORM-ADDRESS',
        "subUri" => 'nzh/doServiceCall',
        "method" => 'POST'
    ],

     #12 دریافت صورتحساب سپرده
    "getDepositInvoice" => [
        "baseUri" => 'PLATFORM-ADDRESS',
        "subUri" => 'nzh/doServiceCall',
        "method" => 'POST'
    ],

    #13 دریافت موجودی سپرده
    "getDepositBalance" => [
        "baseUri" => 'PLATFORM-ADDRESS',
        "subUri" => 'nzh/doServiceCall',
        "method" => 'POST'
    ],

    #14 انتقال وجه داخلی پایا
    "transferMoney" => [
        "baseUri" => 'PLATFORM-ADDRESS',
        "subUri" => 'nzh/doServiceCall',
        "method" => 'POST'
    ],

    #15 دریافت وضعیت انتقال وجه
    "getTransferState" => [
        "baseUri" => 'PLATFORM-ADDRESS',
        "subUri" => 'nzh/doServiceCall',
        "method" => 'POST'
    ],

    #16  پرداخت قبض از طریق سپرده
    "billPaymentByDeposit" => [
        "baseUri" => 'PLATFORM-ADDRESS',
        "subUri" => 'nzh/doServiceCall',
        "method" => 'POST'
    ],

    #17  دریافت اطلاعات و وضعیت شبا
    "getShebaInfoAndStatus" => [
        "baseUri" => 'PLATFORM-ADDRESS',
        "subUri" => 'nzh/doServiceCall',
        "method" => 'POST'
    ]
];
