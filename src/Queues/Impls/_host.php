<?php

use Magpie\Codecs\Parsers\StringParser;
use Magpie\Configurations\Env;
use Magpie\Consoles\Concepts\Consolable;
use Magpie\General\Sugars\Excepts;
use Magpie\HttpServer\ServerCollection;
use Magpie\Locales\I18n;
use Magpie\Queues\BaseQueueRunnable;
use Magpie\System\Kernel\ExceptionHandler;
use Magpie\System\Kernel\Kernel;

define('MAGPIE_INTERNAL_CONTEXT', 'console');

/**
 * Configuration function
 * @param string $key
 * @return mixed|null
 */
$getHostConfig = function(string $key) : mixed {
    $finalKey = 'MAGPIE_HOST_ENV_' . $key;
    if (!array_key_exists($finalKey, $_SERVER)) return null;
    return $_SERVER[$finalKey];
};

/**
 * Show error message
 * @param string $message
 * @return void
 */
$showMessage = function(string $message) : void {
    $message = str_replace("\r", '', $message);
    $messages = explode("\n", $message);

    foreach ($messages as $message) {
        echo "[!!CRITICAL!!] $message\n";
    }
};

// Vendor autoload
$vendorAutoloadPath = $getHostConfig('VENDOR_AUTOLOAD');
if (empty($vendorAutoloadPath)) {
    $showMessage('Vendor autoload path not found');
    exit(1);
}
require $vendorAutoloadPath;

// Get root path
$rootPath = $getHostConfig('ROOT');
if (empty($rootPath)) {
    $showMessage('Root path not found');
    exit(1);
}

// Create application configuration
$appConfigPath = $getHostConfig('APPCONFIG');
if (empty($appConfigPath)) {
    $showMessage('AppConfig path not found');
    exit(1);
}
$config = require_once $appConfigPath;

// Use specific environment (if so)
$useEnv = $getHostConfig('USE_ENV');
if ($useEnv !== null) {
    Env::usingEnv($useEnv);
}

// Bootstrapping
$kernel = Kernel::boot($rootPath, $config);

// Simulate the console run part
$kernel->registerProvider(Consolable::class, $kernel->getConfig()->createDefaultConsolable());

Excepts::noThrow(function () {
    $serverVars = ServerCollection::capture();
    $lang = $serverVars->optional('LANG', StringParser::createTrimEmptyAsNull());
    if ($lang !== null) I18n::setCurrentLocale($lang);
});

// Recreate target
$targetFile = $getHostConfig('TARGET');
if (empty($targetFile)) {
    $showMessage('Target not specified');
    exit(1);
}
if (!file_exists($targetFile) && !is_file($targetFile)) {
    $showMessage('Target file not found');
    exit(1);
}

$targetData = @file_get_contents($targetFile);
$target = unserialize($targetData);
@unlink($targetFile);

if (!$target instanceof BaseQueueRunnable) {
    $showMessage('Target not of expected type \'BaseQueueRunnable\'');
    exit(1);
}

try {
    $target->run();
} catch (Exception $ex) {
    ExceptionHandler::handle($ex);
}

exit(0);
