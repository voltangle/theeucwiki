<?php
namespace MediaWiki\Extension\Shubara;

use MediaWiki\Hook\ParserFirstCallInitHook;
use MediaWiki\Hook\BeforePageDisplayHook;
use MediaWiki\Extension\Shubara\Tags\Navcards;
use MediaWiki\Extension\Shubara\Tags\Navcard;
use MediaWiki\Extension\Shubara\Tags\Ulnav;

class Hooks implements ParserFirstCallInitHook, BeforePageDisplayHook {
    public function onParserFirstCallInit( $parser ) {
        $parser->setHook( 'navcards', Navcards::run(...) );
        $parser->setHook( 'navcard', Navcard::run(...) );
        $parser->setHook( 'ulnav', Ulnav::run(...) );
		return true;
	}

    public function onBeforePageDisplay($out, $skin): void {
		$out->addModuleStyles('ext.shubara.styles');
	}
}
