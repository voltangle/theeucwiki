<?php
namespace MediaWiki\Extension\Shubara\ParserFunctions;

use MediaWiki\Parser\Parser;
use MediaWiki\Parser\PPFrame;
use MediaWiki\Html\Html;
use MediaWiki\Extension\Shubara\Utils;

/**
* Render the infobox parser function 
*/
class Infobox {
    public static function main(Parser $parser) {
        $content = '';
        $functionArgs = func_get_args();
        $args = Utils::extractOptions(array_slice($functionArgs, 1));
        $title = $args['title'] ?? 'No title';
        $input = array_slice($functionArgs, count($args));

        $summary = Html::rawElement('summary', [], "Overview: $title");
        foreach ($input as &$wikitext) {
            $content .= $parser->recursiveTagParseFully($wikitext);
        }

        return [
            Html::rawElement('details', [
                'class' => 'ext-shubara-infobox',
                'open' => ''
            ], $summary . Html::rawElement('div', [], $content)),
            'isHTML' => true
        ];
    }

    public static function list(Parser $parser) {
        $chunks = array_chunk(array_slice(func_get_args(), 1), 2);
        $argKeys = array_column($chunks, 0);
        $argValues = array_column($chunks, 1);
        $args = array_combine($argKeys, $argValues);
        $content = '';

        foreach ($args as $key => $value) {
            $content .= Html::rawElement('div', [

            ],
            Html::rawElement('div', [], $key) . Html::rawElement('div', [], $value));
        }

        return Html::rawElement('div', ['class' => 'ext-shubara-infobox-list'], $content);
    }
}
