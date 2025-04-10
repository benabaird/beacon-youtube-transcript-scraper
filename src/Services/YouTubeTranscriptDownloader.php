<?php

declare(strict_types=1);

namespace App\Services;

use Dom\HTMLDocument;
use Facebook\WebDriver\Exception\ElementClickInterceptedException;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\TimeoutException;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

final readonly class YouTubeTranscriptDownloader
{

    private RemoteWebDriver $driver;


    public function __construct()
    {
        $this->driver = RemoteWebDriver::create(
            "http://{$_ENV['DRUPAL_TEST_WEBDRIVER_HOSTNAME']}:{$_ENV['DRUPAL_TEST_WEBDRIVER_PORT']}",
            DesiredCapabilities::chrome(),
    );
    }

    public function __destruct()
    {
        $this->driver->quit();
    }

    public function retrieveTranscript(string $id): array
    {
        try {
            $this->driver
                 ->get("https://www.youtube.com/watch?v=$id")
                 ->wait(5)
                 ->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#description-inline-expander')))
                 ->click();
            $this->driver
                 ->wait(5)
                 ->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#button-container [aria-label="Show transcript"]')))
                 ->click();
            $segments = $this->driver
                 ->wait(5)
                 ->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::cssSelector('#segments-container')))
                 ->getDomProperty('innerHTML');
        }
        catch (NoSuchElementException|ElementClickInterceptedException|TimeoutException) {
            return [];
        }

        $dom = HTMLDocument::createFromString("<!DOCTYPE html><html><head></head><body>$segments</body></html>}");

        $transcripts = [];
        foreach ($dom->querySelectorAll('.segment') as $segment) {
            $timestamp = trim($segment->querySelector('.segment-timestamp')->textContent);
            $transcript = trim($segment->querySelector('.segment-text')->textContent);
            $transcripts[$timestamp] = $transcript;
        }
        return $transcripts;
    }

}
