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
        $output->setPageTitle('Are We Legal Yet?');

		$wikitext = "= Depends on where you live, but mostly legal*. =\n";
        $wikitext .= 'In countries like Germany, Switzerland, Netherlands etc EUCs are
banned and riding them is heavily punished by fines, and in some cases even license
suspensions. But most of central/eastern Europe and the Americas have EUCs somewhat legal.
Some countries have made laws for EUCs, some still have them as a sort of gray area.';
        $wikitext .= "__NOTOC__\n__NOEDITSECTION__";
		$output->addWikiTextAsInterface( $wikitext );
        $output->addHTML('<div id="map"></div>');
	}
}
