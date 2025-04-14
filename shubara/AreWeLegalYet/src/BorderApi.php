<?php
namespace MediaWiki\Extension\Shubara\AreWeLegalYet;

use MediaWiki\Api\ApiBase;
use MediaWiki\Api\ApiResult;

class BorderApi extends ApiBase {
    protected function getAllowedParams() {
        return [
            'country' => [
                ApiBase::PARAM_TYPE => 'string',
                ApiBase::PARAM_REQUIRED => true
            ],
            'admin_region' => [
                ApiBase::PARAM_TYPE => 'string'
            ],
            'recurse_levels' => [
                ApiBase::PARAM_TYPE => 'integer',
                ApiBase::PARAM_MIN => 1
            ],
            "detail_level" => [
                ApiBase::PARAM_TYPE => 'integer',
                ApiBase::PARAM_MIN => 1,
                ApiBase::PARAM_MAX => 5,
                ApiBase::PARAM_DFLT => '1'
            ]
        ];
    }

    public function execute() {
        $params = $this->extractRequestParams();
        $result = $this->getResult();
        
        // FIXME: THIS CODE SMELLS LIKE ASS
        // This is a stub for the time being, until this code becomes the thing that it's
        // supposed to be (https://github.com/voltangle/theeucwiki/issues/11)
        $geojsonRaw = file_get_contents(getenv('MW_HOME')
            . '/borders/' . $params['detail_level'] . '.geojson', true);
        $geojson = json_decode($geojsonRaw);

        $geojsonGotResult = false;

        foreach ($geojson->features as $item) {
            if ($item->properties->name_en == $params['country']) {
                $geojsonGotResult = true;
                $result->addValue(null, "geojson", $item);
                break;
            }
        }

        // If not found, retry with original name
        if (!$geojsonGotResult) {
            foreach ($geojson->features as $item) {
                if ($item->properties->name == $params['country']) {
                    $result->addValue(null, "geojson", $item);
                    break;
                }
            }
        }
        
        return $result;
    }
}
