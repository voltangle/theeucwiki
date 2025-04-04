<?php
namespace MediaWiki\Extension\Shubara\AreWeLegalYet;

use MediaWiki\SpecialPage\SpecialPage;

class Page extends SpecialPage {
    public function __construct() {
		parent::__construct( 'AreWeLegalYet' );
	}

    public function execute( $par ) {
		$request = $this->getRequest();
		$output = $this->getOutput();
		$this->setHeaders();
        $output->addModules('ext.shubara.arewelegalyet');

		$wikitext = 'Hello world!';
		$output->addWikiTextAsInterface( $wikitext );
        $output->addHTML('<div id="map"></div>');
        $output->setPageTitle('Are We Legal Yet?');
	}
}
