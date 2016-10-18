<?php
namespace JpZipCode;

require_once __DIR__ . '/JpZipCode/Filer.php';
use Symfony\Component\Yaml\Parser;
define("DEFAULT_PREF_CODES_FILE_PATH", __DIR__ . '/../data/pref_code.yaml');

class JpZipCode {

    private static $prefCodesConfigFilePath = DEFAULT_PREF_CODES_FILE_PATH;
    private static $prefCodesConfigArray = [];

    public static function search($zipCode)
    {
        $zipCode = str_replace('-', '', ((string)$zipCode));
        if (!preg_match('/^\d{7}$/', $zipCode)) {
            return null;
        }

        $top4 = substr($zipCode, 0, 4);
        $jsonFile = __DIR__ . "/../data/zip_code/{$top4}.json";
        if (file_exists($jsonFile)) {
            $data = json_decode(file_get_contents($jsonFile), true);
            return array_key_exists($zipCode, $data) ? self::convert($data[$zipCode]) : null;
        }
        return null;
    }

    private static function convert($data) {
        $converted = [];
        $converted['pref_code'] = (string)array_flip(self::prefCodes())[$data['pref_kanji']];

        foreach ($data as $key => $value) {
            $converted[$key] = trim($value);
            $converted[$key] = (in_array($converted[$key], ['ｲｶﾆｹｲｻｲｶﾞﾅｲﾊﾞｱｲ', 'IKANIKEISAIGANAIBAAI', '以下に掲載がない場合'])) ? '' : $converted[$key];
            $converted[$key] = preg_replace('/\(.*\)/', '', $converted[$key]);
            $converted[$key] = preg_replace('/\（.*\）/', '', $converted[$key]);

            if (preg_match('/_roman$/', $key)) {
                $romans = [];
                foreach (explode(' ', $converted[$key]) as $roman) {
                    $romans[] = ucfirst(strtolower($roman));
                }
                $converted[$key] = join(' ', $romans);

                foreach (['Ken', 'To', 'Fu', 'Shi', 'Gun', 'Ku', 'Machi'] as $suffix) {
                    $converted[$key] = preg_replace("/ {$suffix}/", "-{$suffix}", $converted[$key]);
                }

                $converted[$key] = join(' ', array_reverse(explode(' ', $converted[$key])));
            }
        }

        $converted['address_kanji'] = trim($converted['pref_kanji'].$converted['city_kanji'].$converted['town_kanji']);
        $converted['address_kana']  = trim($converted['pref_kana'].$converted['city_kana'].$converted['town_kana']);
        $converted['address_roman'] = trim($converted['town_roman'].' '.$converted['city_roman'].' '.$converted['pref_roman']);

        if (self::$prefCodesConfigFilePath != DEFAULT_PREF_CODES_FILE_PATH) {
            $jisPrefConfig =  self::readYaml(DEFAULT_PREF_CODES_FILE_PATH);
            $converted['jis_pref_code'] = (string)array_flip($jisPrefConfig)[$data['pref_kanji']];
        }

        return $converted;
    }

    private static function prefCodes() {
        $file = is_readable(self::$prefCodesConfigFilePath) ? self::$prefCodesConfigFilePath : DEFAULT_PREF_CODES_FILE_PATH;
        return self::readYaml($file);
    }

    public static function setPrefCodesConfigFilePath($path) {
        self::$prefCodesConfigFilePath = $path;
    }

    public static function update()
    {
        foreach(['jp', 'roman'] as $type) {
            $filer = new JpZipCode\Filer($type);
            $filer->downloadFile();
            $filer->unzipFile();
            $filer->updateJson();
            $filer->clean();
        }
    }

    private static function readYaml($path) {
        $yaml = new Parser();
        return $yaml->parse(file_get_contents($path));
    }
}
