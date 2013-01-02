<?php

require_once(dirname(__FILE__) . '/../wikiScraper.php');

$ws = new wikiScraper(true);

$searches = array('Rock (Feral) Pigeon','Rock Pigeon','Rofewefw');

foreach($searches as $search) {
 
    echo "\n scraping $search\n";
    $ws->loadFindAndScrapeFromQuery($search);
    var_dump($ws->getScrapedContent());
}

