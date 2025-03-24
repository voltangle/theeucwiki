<?php
namespace MediaWiki\Extension\Shubara\ParserFunctions;

use MediaWiki\Parser\Parser;
use MediaWiki\Parser\PPFrame;
use MediaWiki\Html\Html;
use MediaWiki\Extension\Shubara\Utils;

/**
* Render the messagebox parser function 
*/
// TODO: Rewrite this as a tag extension
class Messagebox {
    public static function main(Parser $parser) {
        $content = '';
        $input = array_slice(func_get_args(), 1);
        
        return [
            Html::rawElement('div', [
                // noexcerpt is there so Popups doesn't extract it
                'class' => 'ext-shubara-messagebox noexcerpt',
            ], $parser->recursiveTagParseFully(implode('', $input))),
            'isHTML' => true
        ];
    }
}
