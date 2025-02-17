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
        $functionArgs = array_slice(func_get_args(), 1);
        $args = Utils::extractOptions($functionArgs);
        $title = $args['title'] ?? 'No title';
        $heroImage = @$args['heroImg'];

        $flatArgs = [];
        foreach ($args as $key => $value) {
            $flat = "$key=$value";
            // if it's actually an argument and not wikitext
            // BUG: it can include normal wikitext that has a single equals sign
            if (preg_match('/[\w]+=[\w .]+/', $flat) == 1) {
                array_push($flatArgs, "$key=$value");
            }
        }
        $input = array_diff($functionArgs, $flatArgs);

        $summary = Html::rawElement('summary', [], "Overview: $title");
        $heroImgContent = '';
        if ($heroImage != null) {
            // 392 = 400 - (4 * 2), or infobox width - borders
            $heroImgContent = $parser->recursiveTagParseFully("[[File:$heroImage|392px]]");
            $heroImgContent = substr($heroImgContent, 3); // cut off the <p>
            $heroImgContent = substr($heroImgContent, 0, strlen($heroImgContent) - 4); // and the </p>
            $heroImgContent = Html::rawElement('div', ['class' => 'hero'], $heroImgContent);
        }
        $heading = array_shift($input);;
        $content .= Html::rawElement('header', [], $parser->recursiveTagParseFully($heading));

        foreach ($input as &$wikitext) {
            $content .= $parser->recursiveTagParseFully($wikitext);
        }

        return [
            Html::rawElement('details', [
                // noexcerpt is there so Popups doesn't extract it
                'class' => 'ext-shubara-infobox noexcerpt',
                'open' => ''
            ], $summary . $heroImgContent . Html::rawElement('div', ['class' => 'content'], $content)),
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
