<?php
namespace MediaWiki\Extension\Shubara;

use MediaWiki\Parser\Parser;
use MediaWiki\Title\Title;
use MediaWiki\Html\Html;
use MediaWiki\Registration\ExtensionRegistry;
use MediaWiki\MediaWikiServices;

class Utils {
    public static function generateRandomString($length = 10): string {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
    
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
    
        return $randomString;
    }

    // made with help from the CSS extension
    public static function addHeadItem(Parser $parser, string $data, string $type): void {
        $title = Title::newFromText($data);
        $headItem = '<!-- Begin Extension:Shubara -->';
        // Encode data URI and append link tag
		$dataPrefix = "data:text/$type;charset=UTF-8;base64,";
		$url = $dataPrefix . base64_encode( $data );

        switch ($type) {
            case "javascript":
                // $headItem .= Html::linkedScript( $url );
                // Do it like this instead of Html::linkedScript so I can add the defer
                $headItem .= Html::element('script', ['src' => $url, 'defer' => 'defer']);
                break;
            case "css": $headItem .= Html::linkedStyle( $url ); break;
            default: throw new InvalidArgumentException("Unexpected type, expected javascript or css, got $type");
        }
        $headItem .= '<!-- End Extension:Shubara -->';
		$parser->getOutput()->addHeadItem( $headItem );
    }

    public static function runWithExtension(string $ext, callback $callback) {
        if (ExtensionRegistry::getInstance()->isLoaded($ext)) {
            callback();
        } else {
            throw new BadFunctionCallException("Extension $ext is not loaded");
        }
    }

    /*
     * Returns a direct URL to a file with a specified name. Optionally can resize it
     * to a thumbnail of specified size (see File::createThumb in MediaWiki docs) if the
     * file is an image.
     *
     * @param string $file Filename to look for
     * @param ?int $width Image thumbnail width.
     * @param ?int $height Image thumbnail height.
     *
     * @return ?string File path or null in case of error.
     */
    public static function getDirectFileURL(
            string $file,
            ?int $width = null,
            ?int $height = null
    ): ?string {
        $fileTitle = Title::newFromText($file, NS_FILE);
        if (!($fileTitle && $fileTitle->exists())) {
            return null;
        }

        $file = MediaWikiServices::getInstance()->getRepoGroup()->findFile($fileTitle);
        if ($file) {
            if (!$width) {
                return $file->getFullUrl();
            } else {
                return $file->createThumb($width, $height ?? -1);
            }
        }
        return null;
    }
}
