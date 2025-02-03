<?php
namespace MediaWiki\Extension\Shubara\Tags;

use MediaWiki\Parser\Parser;
use MediaWiki\Parser\PPFrame;
use MediaWiki\Html\Html;
use MediaWiki\Extension\Shubara\Utils;
use MediaWiki\SiteStats\SiteStats;
use MediaWiki\Output\OutputPage;
use OOUI;

/**
* Render the sitestats tag
*/
// TODO: also add icons. I didn't do them from the start because they refuse to change color
class Projectstats {
    public static function run($input, array $args, Parser $parser, PPFrame $frame) {
        $users = SiteStats::activeUsers();
        $edits = SiteStats::edits();
        // in Main, Help, and Category namespaces
        $pages = SiteStats::pagesInNs(0)
            + SiteStats::pagesInNs(12)
            + SiteStats::pagesInNs(14);

        $usersBox = self::makeBox(\wfMessage('shubara-projectstats-users')->parse(), $users);
        $editsBox = self::makeBox(\wfMessage('shubara-projectstats-edits')->parse(), $edits);
        $pagesBox = self::makeBox(\wfMessage('shubara-projectstats-pages')->parse(), $pages);
        return Html::rawElement('div', ['class' => 'ext-shubara-projectstats'], $usersBox . $editsBox . $pagesBox);
    }

    static function makeBox(string $label, int $amount): string {
        $amountHtml = Html::rawElement(
            'p',
            ['class' => 'ext-shubara-projectstats-amount'],
            $amount);
        $labelHtml = Html::rawElement('p', ['class' => 'ext-shubara-projectstats-label'], $label);

        return Html::rawElement('div', [], $labelHtml . $amountHtml);
    }
}
