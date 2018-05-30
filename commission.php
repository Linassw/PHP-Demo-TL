<?php
require_once __DIR__ . '/lib/vendor/autoload.php';

use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\MessageSelector;
use Symfony\Component\Translation\Loader\XliffFileLoader;

use League\Csv\Reader as CsvReader;

use Demo\Console;
use Demo\Book;

$locale = 'lt_LT';
//$locale = 'en_EN';

$translator = new Translator($locale, new MessageSelector());
$translator->addLoader('xliff', new XliffFileLoader());
$translator->addResource('xliff', 'i18n/messages.lt.xlf', 'lt_LT');
$translator->addResource('xliff', 'i18n/messages.en.xlf', 'en_EN');
$translator->setFallbackLocales(['en']);

$args = getopt('f:');

if ( empty($args) || empty($args['f']) ) {
    Console::writeLine($translator->trans('missingArgument'));
    return;
}

$csvFilePath = $args['f'];

if ( !file_exists($csvFilePath) || is_dir($csvFilePath) ) {
    Console::writeLine($translator->trans('fileNotFound', ['%path%' => $csvFilePath]));
    return;
}

$storage = new StorageSqlLite('../storage/demo.db');


$map = [
     'date' => 0,
     'userId' => 1,
     'userType' => 2,
     'operationType' => 3,
     'amount' => 4,
     'currency' => 5,
 ];

$csv = CsvReader::createFromPath($csvFilePath);

/**
 * @assert (2016-01-05,1,natural,cash_in,200.00,EUR) == 0.06
 *
 * @param string $dateString
 * @param integer $userId
 * @param string $userType
 * @param string $operationType
 * @param float $operationAmount
 * @param string $operationCurrency
 * @return boolean
 */


$csv->each(function($row) use ($translator, $storage) {
    try {
        $dateString = $row[0];
        $userId = $row[1];
        $userType = $row[2];
        $operationType = $row[3];
        $operationAmount = $row[4];
        $operationCurrency = $row[5];

        $commissionAmount = Book::getCommission($dateString, $userId, $userType, $operationType, $operationAmount, $operationCurrency, $storage);
        Console::WriteLine($commissionAmount);
        return true;
    }
    catch ( Demo\Exceptions\CurrencyNotSupportedException $ce ) {
        Console::writeLine($translator->trans('currencyNotSupported', ['%currency%' => $ce->getCurrency()]));
    } catch ( Exception $e ) {
        Console::writeLine($e->getMessage());
    }
});
