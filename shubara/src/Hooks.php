<?php
namespace MediaWiki\Extension\Shubara;

use MediaWiki\Hook\ParserFirstCallInitHook;
use MediaWiki\Hook\BeforePageDisplayHook;
use MediaWiki\Parser\Parser\SFH_NO_HASH;
use MediaWiki\Extension\Shubara\Tags\Navcards;
use MediaWiki\Extension\Shubara\Tags\Navcard;
use MediaWiki\Extension\Shubara\Tags\Ulnav;
use MediaWiki\Extension\Shubara\Tags\Imagechip;
use MediaWiki\Extension\Shubara\Tags\Projectstats;
use MediaWiki\Extension\Shubara\Tags\NewsList;
use MediaWiki\Extension\Shubara\ParserFunctions\Infobox;
use MediaWiki\Extension\Shubara\ParserFunctions\Messagebox;

class Hooks implements ParserFirstCallInitHook, BeforePageDisplayHook {
    public function onParserFirstCallInit($parser) {
        $parser->setHook('navcards', Navcards::run(...));
        $parser->setHook('navcard', Navcard::run(...));
        $parser->setHook('ulnav', Ulnav::run(...));
        $parser->setHook('imagechip', Imagechip::run(...));
        $parser->setHook('projectstats', Projectstats::run(...));
        $parser->setHook('newslist', NewsList::run(...));
        $parser->setFunctionHook('infobox', Infobox::main(...), SFH_NO_HASH);
        $parser->setFunctionHook('infobox-list', Infobox::list(...), SFH_NO_HASH);
        $parser->setFunctionHook('messagebox', Messagebox::main(...), SFH_NO_HASH);
		return true;
	}

    public function onBeforePageDisplay($out, $skin): void {
		$out->addModuleStyles('ext.shubara.styles');
	}
}
