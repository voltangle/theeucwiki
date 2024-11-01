<?php
namespace MediaWiki\Extension\Shubara;

use MediaWiki\Extension\Shubara\Hooks\NavCards;
use MediaWiki\Logger\LoggerFactory;

$logger = LoggerFactory::getInstance( 'Shubara' );

class Hooks implements ParserFirstCallInitHook, RawPageViewBeforeOutputHook {
    public function onParserFirstCallInit( $parser ) {
        $parser->setHook( 'navcard', 'NavCards::renderTagNavCard' );
        $parser->setHook( 'navcards', 'NavCards::renderTagNavCards' );
        $logger->error('Set hooks successfully');
		return true;
	}
}
?>
