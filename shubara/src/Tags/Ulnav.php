<?php
namespace MediaWiki\Extension\Shubara\Tags;

use MediaWiki\Parser\Parser;
use MediaWiki\Parser\PPFrame;

/**
* Render the ulnav tag
*/
class Ulnav {
    /**
     * @param string $input What is supplied between the HTML tags. This gets evaluated
     * so it get spit out as normal wikitext
     * @param array $args HTML tag attribute params. Valid params:
     * - title: title of the card. Displayed at the top
     * - mode: how list items are displayed. 'col' by default. Valid values: col, chiplist
     * - flex: sets the flex CSS value
     * @param Parser $parser MediaWiki Parser object
     * @param PPFrame $frame MediaWiki PPFrame object
     */
    public static function run($input, array $args, Parser $parser, PPFrame $frame) {
        $output = '';
        
        $title = $args['title'] ?? null;
        $mode = $args['mode'] ?? 'col';
    
        $output .= '<div class="ext-shubara-ulnav';
        switch ($mode) {
            case 'col': $output .= ' ext-shubara-ulnav-col'; break;
            case 'chiplist': $output .= ' ext-shubara-ulnav-chiplist'; break;
        }
        if ($title == null) { $output .= ' ext-shubara-notitle'; }
        $output .= '"';
        if (isset($args['flex']) && is_numeric(@$args['flex'])) {
            $flex = @$args['flex'];
            $output .= " style=\"flex: $flex;\"";
        }
        $output .= '>';
    
        $parsedTitle = $parser->recursiveTagParse($title, $frame);
        $output .= "<h2>$parsedTitle</h2>";
        $output .= $parser->recursiveTagParse($input, $frame);
        $output .= '</div>';
    
        return $output;
    }
}
