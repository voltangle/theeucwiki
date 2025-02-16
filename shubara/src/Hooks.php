<?php
namespace MediaWiki\Extension\Shubara;

use MediaWiki\Hook\ParserFirstCallInitHook;
use MediaWiki\Hook\BeforePageDisplayHook;
use MediaWiki\Parser\Parser\SFH_OBJECT_ARGS;
use MediaWiki\Extension\Shubara\Tags\Navcards;
use MediaWiki\Extension\Shubara\Tags\Navcard;
use MediaWiki\Extension\Shubara\Tags\Ulnav;
use MediaWiki\Extension\Shubara\Tags\Imagechip;
use MediaWiki\Extension\Shubara\Tags\Projectstats;
use MediaWiki\Extension\Shubara\Tags\NewsList;
use MediaWiki\Extension\Shubara\ParserFunctions\Infobox;

class Hooks implements ParserFirstCallInitHook, BeforePageDisplayHook {
    public function onParserFirstCallInit($parser) {
        $parser->setHook('navcards', Navcards::run(...));
        $parser->setHook('navcard', Navcard::run(...));
        $parser->setHook('ulnav', Ulnav::run(...));
        $parser->setHook('imagechip', Imagechip::run(...));
        $parser->setHook('projectstats', Projectstats::run(...));
        $parser->setHook('newslist', NewsList::run(...));
        $parser->setFunctionHook('infobox', Infobox::main(...));
        $parser->setFunctionHook('infobox-list', Infobox::list(...));
		return true;
	}

    public function onBeforePageDisplay($out, $skin): void {
		$out->addModuleStyles('ext.shubara.styles');
	}
}
