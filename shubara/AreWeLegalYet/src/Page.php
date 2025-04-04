<?php
namespace MediaWiki\Extension\Shubara\AreWeLegalYet;

use MediaWiki\SpecialPage\SpecialPage;

class Page extends SpecialPage {
    public function __construct() {
		parent::__construct( 'Shubara/AreWeLegalYet' );
	}

    public function execute( $par ) {
		$request = $this->getRequest();
		$output = $this->getOutput();
		$this->setHeaders();

		# Get request data from, e.g.
		$param = $request->getText( 'param' );

		# Do stuff
		# ...
		$wikitext = 'Hello world!';
		$output->addWikiTextAsInterface( $wikitext );
	}
}
