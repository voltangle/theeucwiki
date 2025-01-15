<?php
# Protect against web entry
if ( !defined( 'MEDIAWIKI' ) ) {
	exit;
}

// https://www.mediawiki.org/wiki/Manual:Short_URL
$wgScriptPath = "/w";

// The URL path to static resources (images, scripts, etc.)
$wgResourceBasePath = $wgScriptPath;

$wgLogos = [
	'icon' => "$wgScriptPath/icon.png",
	'1x' => "$wgScriptPath/logo-1x.png",
	'1.5x' => "$wgScriptPath/logo-1.5x.png",
	'2x' => "$wgScriptPath/logo-2x.png",
	'svg' => "$wgScriptPath/logo.svg",
];
// $wgFavicon = "$wgScriptPath/favicon.ico";
$wgFavicon = "$wgScriptPath/favicon.png";

$wgEmergencyContact = "";
$wgPasswordSender = "";

## Database settings
$wgDBtype = "mysql";
$wgDBserver = "db";
$wgDBname = "mediawiki";
$wgDBuser = "mwuser";
$wgDBpassword = "";

# MySQL specific settings
$wgDBprefix = "";
$wgDBssl = false;

# MySQL table options to use during installation or update
$wgDBTableOptions = "ENGINE=InnoDB, DEFAULT CHARSET=binary";

# Shared database table
# This has no effect unless $wgSharedDB is also set.
$wgSharedTables[] = "actor";
$wgUseImageMagick = true;
$wgImageMagickConvertCommand = "/usr/bin/convert";
$wgUseInstantCommons = true;

# Periodically send a pingback to https://www.mediawiki.org/ with basic data
# about this MediaWiki instance. The Wikimedia Foundation shares this data
# with MediaWiki developers to help guide future development efforts.
$wgPingback = true;
$wgLocaltimezone = "UTC";
$wgCacheDirectory = "/tmp";

$wgSecretKey = getenv('MW_SECRETKEY');
$wgUpgradeKey = getenv('MW_UPGRADEKEY');

# Changing this will log out all existing sessions.
$wgAuthenticationTokenVersion = "1";

## For attaching licensing metadata to pages, and displaying an
## appropriate copyright notice / icon. GNU Free Documentation
## License and Creative Commons licenses are supported so far.
$wgRightsPage = ""; # Set to the title of a wiki page that describes your license/copyright
$wgRightsUrl = "";
$wgRightsText = "";
$wgRightsIcon = "";

$wgDiff3 = "/usr/bin/diff3";

########################### Core Settings ##########################
$wgLanguageCode = 'en';
$wgSitename = 'euc.repair';
$wgMetaNamespace = "euc.repair";
$wgServer = getenv('MW_SITE_SERVER');
$wgEnableUploads = true;
# TODO: When going into production, KILL THIS WITH HAMMERS
$wgRawHtml = false;

## https://www.mediawiki.org/wiki/Manual:Short_URL
$wgArticlePath = '/wiki/$1';
## Also see mediawiki.conf

##### Improve performance
# https://www.mediawiki.org/wiki/Manual:$wgMainCacheType
$wgMainCacheType = CACHE_MEMCACHED;
$wgParserCacheType = CACHE_MEMCACHED; # optional
$wgMessageCacheType = CACHE_MEMCACHED; # optional
$wgMemCachedServers = explode( ',', getenv( 'MW_MEMCACHED_SERVERS' ) );
$wgSessionsInObjectCache = true; # optional
$wgSessionCacheType = CACHE_MEMCACHED; # optional

# Use Varnish accelerator
// FIX: this stinks
$tmpProxy = getenv( 'MW_PROXY_SERVERS' );
if ( $tmpProxy ) {
    # https://www.mediawiki.org/wiki/Manual:Varnish_caching
    $wgUseSquid = true;
    $wgSquidServers = explode( ',', $tmpProxy );
    $wgUsePrivateIPs = true;
    $wgHooks['IsTrustedProxy'][] = function( $ip, &$trusted ) {
        // Proxy can be set as a name of proxy container
        if ( !$trusted ) {
            global $wgSquidServers;
            foreach ( $wgSquidServers as $proxy ) {
                if ( !ip2long( $proxy ) ) { // It is name of proxy
                    if ( gethostbyname( $proxy ) === $ip ) {
                        $trusted = true;
                        return;
                    }
                }
            }
        }
    };
}
//Use $wgSquidServersNoPurge if you don't want MediaWiki to purge modified pages
//$wgSquidServersNoPurge = array('127.0.0.1');
// This email setup is to be used with a locally-hosted mailcow instance
####################### Email #########################
$wgEnableEmail = true;
$wgEnableUserEmail = true;

$wgEnotifUserTalk = false;
$wgEnotifWatchlist = false;
$wgEnotifRevealEditorAddress = true;

$wgPasswordSender = 'info@euc.repair';
$wgNoReplyAddress = 'noreply@euc.repair';

if (!str_contains($wgServer, 'localhost')) { // if running in prod
    $wgEmailAuthentication = true;
    $wgEmailConfirmToEdit = true;
    $wgSMTP = [
        'host' => 'in-v3.mailjet.com',
        'localhost' => 'euc.repair',
        'port' => 587,
        'auth' => true,
        'username' => getenv('MAILJET_APIKEY'),
        'password' => getenv('MAILJET_SECRETKEY'),
    ];
}

####################### Uploads #########################
# Set this value if needed
# $wgUploadSizeWarning
$wgMaxUploadSize = 209715200; # 200 mebibytes

####################### Extensions #########################
wfLoadExtension('AbuseFilter');
wfLoadExtension('Babel');
wfLoadExtension('CategoryTree');
wfLoadExtension('CheckUser');
wfLoadExtension('Cite');
wfLoadExtension('CiteThisPage');
wfLoadExtension('CirrusSearch');
wfLoadExtension('cldr');
wfLoadExtension('CleanChanges');
wfLoadExtension('CodeEditor'); 
wfLoadExtension('ConfirmEdit');
if (getenv('USE_TURNSTILE') == 'yes') {
    wfLoadExtension('ConfirmEdit/Turnstile');
}
wfLoadExtension('CSS');
wfLoadExtension('DiscussionTools');
wfLoadExtension('DisplayTitle');
# FIXME: Reenable
# wfLoadExtension( 'Drafts' );
wfLoadExtension('Echo');
wfLoadExtension('Elastica');
wfLoadExtension('ElectronPdfService');
# wfLoadExtension('Gadgets');
wfLoadExtension('ImageMap');
wfLoadExtension('InputBox');
wfLoadExtension('Interwiki');
wfLoadExtension('Linter');
wfLoadExtension('LoginNotify');
wfLoadExtension('Math');
wfLoadExtension('MultimediaViewer');
wfLoadExtension('Nuke');
wfLoadExtension('OATHAuth');
wfLoadExtension('PageImages');
wfLoadExtension('ParserFunctions');
wfLoadExtension('PdfHandler');
wfLoadExtension('Poem');
wfLoadExtension('Popups');
wfLoadExtension('RelatedArticles');
wfLoadExtension('ReplaceText');
wfLoadExtension('RSS');
wfLoadExtension('SecureLinkFixer');
wfLoadExtension('Scribunto');
wfLoadExtension('ShortDescription');
wfLoadExtension('Shubara');
wfLoadExtension('SpamBlacklist');
wfLoadExtension('SyntaxHighlight_GeSHi');
# wfLoadExtension( 'TemplateStyles' );
wfLoadExtension('TextExtracts');
wfLoadExtension('Thanks');
wfLoadExtension('TitleBlacklist');
wfLoadExtension('UniversalLanguageSelector');
wfLoadExtension('VisualEditor');
wfLoadExtension('WikiEditor');

### Math ###
$wgMathInternalRestbaseURL = getenv('MW_REST_RESTBASE_URL');

### DisplayTitle ###
$wgAllowDisplayTitle = true;
$wgRestrictDisplayTitle = false;
### SpamBlacklist ###
$wgSpamBlacklistFiles = array(
   "https://meta.wikimedia.org/w/index.php?title=Spam_blacklist&action=raw&sb_ver=1",
   "https://en.wikipedia.org/w/index.php?title=MediaWiki:Spam-blacklist&action=raw&sb_ver=1"
);
### TitleBlacklist ###
$wgTitleBlacklistSources = array(
    array(
         'type' => 'localpage',
         'src'  => 'MediaWiki:Titleblacklist',
    ),
    array(
         'type' => 'url',
         'src'  => 'https://meta.wikimedia.org/w/index.php?title=Title_blacklist&action=raw',
    ),
);

### ConfirmEdit ###
$wgTurnstileSiteKey = getenv('TURNSTILE_SITEKEY');
$wgTurnstileSecretKey = getenv('TURNSTILE_SECRETKEY');

### SyntaxHighlight_GeSHi ###
$wgPygmentizePath = '/usr/bin/pygmentize';

### CheckUser ###
$wgGroupPermissions['sysop']['checkuser'] = true;
$wgGroupPermissions['sysop']['checkuser-log'] = true;

### CleanChanges
#$wgDefaultUserOptions['usenewrc'] = 1;

### WikiEditor ###
# Enables use of WikiEditor by default but still allows users to disable it in preferences
$wgDefaultUserOptions['usebetatoolbar'] = 1;
# Enables link and table wizards by default but still allows users to disable them in preferences
$wgDefaultUserOptions['usebetatoolbar-cgd'] = 1;
# Displays the Preview and Changes tabs
$wgDefaultUserOptions['wikieditor-preview'] = 1;
# Displays the Publish and Cancel buttons on the top right side
$wgDefaultUserOptions['wikieditor-publish'] = 1;

### VisualEditor ###
$tmpRestDomain = getenv( 'MW_REST_DOMAIN' );
$tmpRestParsoidUrl = getenv( 'MW_REST_PARSOID_URL' );

// Enable by default for everybody
$wgDefaultUserOptions['visualeditor-enable'] = 1;

// TODO: check this out
// Optional: Set VisualEditor as the default for anonymous users
// otherwise they will have to switch to VE
// $wgDefaultUserOptions['visualeditor-editor'] = "visualeditor";

// Don't allow users to disable it
$wgHiddenPrefs[] = 'visualeditor-enable';

// OPTIONAL: Enable VisualEditor's experimental code features
#$wgDefaultUserOptions['visualeditor-enable-experimental'] = 1;

$wgVirtualRestConfig['modules']['parsoid'] = [
        // URL to the Parsoid instance
        'url' => $tmpRestParsoidUrl,
        // Parsoid "domain", see below (optional)
        'domain' => $tmpRestDomain,
        // Parsoid "prefix", see below (optional)
        'prefix' => $tmpRestDomain,
];

$tmpRestRestbaseUrl = getenv( 'MW_REST_RESTBASE_URL' );
if ( $tmpRestRestbaseUrl ) {
    $wgVirtualRestConfig['modules']['restbase'] = [
    'url' => $tmpRestRestbaseUrl,
    'domain' => $tmpRestDomain,
    'parsoidCompat' => false
    ];

    $tmpRestProxyPath = getenv( 'MW_REST_RESTBASE_PROXY_PATH' );
    if ( $tmpProxy && $tmpRestProxyPath ) {
        $wgVisualEditorFullRestbaseURL = $wgServer . $tmpRestProxyPath;
    } else {
        $wgVisualEditorFullRestbaseURL = $wgServer . ':' . getenv( 'MW_REST_RESTBASE_PORT' ) . "/$tmpRestDomain/";
    }
    $wgVisualEditorRestbaseURL = $wgVisualEditorFullRestbaseURL . 'v1/page/html/';
}

########################### Search ############################
# https://www.mediawiki.org/wiki/Extension:CirrusSearch
$wgSearchType = 'CirrusSearch';
$wgDebugLogGroups['CirrusSearch'] = "$IP/cache/CirrusSearch.log";
$wgCirrusSearchConnectionAttempts = 3;
$wgCirrusSearchUseCompletionSuggester = 'build';
$wgCirrusSearchServers =  explode(',', getenv('MW_CIRRUS_SEARCH_SERVERS'));

### RelatedArticles ###
$wgRelatedArticlesFooterAllowedSkins = ['vector-2022', 'vector', 'citizen'];
$wgRelatedArticlesUseCirrusSearch = true;
$wgRelatedArticlesDescriptionSource = 'pagedescription';

### Scribunto ###
$wgScribuntoDefaultEngine = 'luastandalone';

######################### UI ######################### 

wfLoadSkin('Vector');
wfLoadSkin('MinervaNeue');
wfLoadSkin('Timeless');
wfLoadSkin('MonoBook');
wfLoadSkin('CologneBlue');
wfLoadSkin('Citizen');

$wgDefaultSkin = 'citizen';

$wgHooks['SkinAddFooterLinks'][] = function($skin, $key, &$footerLinks) {
    if ($key === 'places') {
        $footerLinks['github'] = Html::rawElement('a',
            [
                'href' => 'https://github.com/voltangle/euc.repair',
                'rel' => 'noreferrer noopener'
            ],
        'GitHub');
        $footerLinks['ko-fi'] = Html::rawElement('a',
            [
                'href' => 'https://ko-fi.com/eucrepair',
                'rel' => 'noreferrer noopener'
            ],
        'Ko-fi');
        $footerLinks['patreon'] = Html::rawElement('a',
            [
                'href' => 'https://www.patreon.com/c/eucrepair',
                'rel' => 'noreferrer noopener'
            ],
        'Patreon');
    }
};

######################### Permissions ######################### 

$wgGroupPermissions['*']['edit'] = false; // Disable anonymous editing
$wgGroupPermissions['*']['createaccount'] = true;
$wgGroupPermissions['sysop']['interwiki'] = true;

$wgNamespacesWithSubpages[NS_MAIN] = true;

######################### Debug ######################### 
#
if (str_contains($wgServer, 'localhost')) { // if running locally
    $wgShowDebug = true;
    $wgShowExceptionDetails = true;
}
