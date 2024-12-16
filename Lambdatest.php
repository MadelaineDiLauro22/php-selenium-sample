<?php

require_once('vendor/autoload.php');

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\Interactions\WebDriverActions;
use PHPUnit\Framework\Assert;

# Credenciales de LambdaTest
$LT_USERNAME = getenv("madeludmila");
$LT_ACCESS_KEY = getenv("aW00OrcRJPxjLBUICMghKjbogTsqPMpmisGPkL5jHYidp2iUxs");

# Configuración de LambdaTest
$host = "http://" . $LT_USERNAME . ":" . $LT_ACCESS_KEY . "@hub.lambdatest.com/wd/hub";

$capabilities = array(
    "browserName" => "chrome",
    "browserVersion" => "latest",
    "LT:Options" => array(
        "username" => $LT_USERNAME,
        "accessKey" => $LT_ACCESS_KEY,
        "platformName" => "Windows 10",
        "project" => "Desafio Onboarding",
        "w3c" => true,
        "plugin" => "php-php"
    )
);

try {
    $driver = RemoteWebDriver::create($host, $capabilities);

    # Paso 1: Navegar al sitio y completar el onboarding
    $driver->get("https://codelosophy.com/demo-bfore/");
    $actions = new WebDriverActions($driver);

    # Simular clics para avanzar en el onboarding
    $driver->findElement(WebDriverBy::cssSelector(".start-onboarding-btn"))->click();
    $driver->findElement(WebDriverBy::cssSelector(".industry-select"))->click();
    $driver->findElement(WebDriverBy::xpath("//option[text()='Manufacturing']"))->click();

    $driver->findElement(WebDriverBy::cssSelector(".company-size-select"))->click();
    $driver->findElement(WebDriverBy::xpath("//option[text()='200-500']"))->click();

    $driver->findElement(WebDriverBy::cssSelector(".team-size-select"))->click();
    $driver->findElement(WebDriverBy::xpath("//option[text()='1-10']"))->click();

    $driver->findElement(WebDriverBy::cssSelector(".next-btn"))->click();

    # Validar "Cost saved by attacks preempted"
    $costElement = $driver->findElement(WebDriverBy::cssSelector(".cost-saved"));
    Assert::assertNotNull($costElement, "No se encontró la información de ahorro.");

    # Cancelar el onboarding
    $driver->findElement(WebDriverBy::cssSelector(".cancel-onboarding-btn"))->click();

    # Paso 2: Navegar a la sección Threats y contar amenazas
    $driver->findElement(WebDriverBy::linkText("Threats"))->click();
    $threatElements = $driver->findElements(WebDriverBy::cssSelector(".result-card"));
    $threatCount = count($threatElements);

    Assert::assertEquals(4, $threatCount, "No se encontraron exactamente 4 amenazas.");

    # Paso 3: Cargar archivo .tsv
    $driver->get("https://codelosophy.com/demo-bfore/load-data");
    $uploadElement = $driver->findElement(WebDriverBy::cssSelector("#file-upload"));
    $uploadElement->sendKeys("/mnt/data/tdbank example - bfore-threats.tsv");

    $driver->findElement(WebDriverBy::cssSelector(".upload-btn"))->click();
    $successMessage = $driver->findElement(WebDriverBy::cssSelector(".upload-success"));
    Assert::assertNotNull($successMessage, "La carga del archivo no fue exitosa.");

    # Paso 4: Validación final
    $driver->get("https://codelosophy.com/demo-bfore/");
    $driver->findElement(WebDriverBy::cssSelector(".start-onboarding-btn"))->click();

    $driver->findElement(WebDriverBy::linkText("Threats"))->click();
    $firstThreat = $driver->findElement(WebDriverBy::cssSelector(".result-card:first-child"))->getText();

    # Validar con un valor fijo del archivo TSV
    $expectedValue = "tdbankr.com";  # Cambiar según el contenido del archivo
    Assert::assertEquals($expectedValue, $firstThreat, "El primer elemento no coincide con el valor esperado.");

} catch (Exception $e) {
    print "Test falló con el error: " . $e->getMessage();
} finally {
    # Finalizar la sesión
    if (isset($driver)) {
        $driver->quit();
    }
}
