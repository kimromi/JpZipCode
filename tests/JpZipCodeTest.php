<?php
class JpZipCodeTest extends \PHPUnit_Framework_TestCase
{
    function testSearch()
    {
        $this->assertEquals(JpZipCode::search('8100001'), array(
            'code'          => '40133',
            'old_zip_code'  => '810',
            'zip_code'      => '8100001',
            'pref_code'     => '40',
            'pref_kanji'    => '福岡県',
            'pref_roman'    => 'Fukuoka-Ken',
            'pref_kana'     => 'ﾌｸｵｶｹﾝ',
            'city_kanji'    => '福岡市中央区',
            'city_roman'    => 'Chuo-Ku Fukuoka-Shi',
            'city_kana'     => 'ﾌｸｵｶｼﾁｭｳｵｳｸ',
            'town_kanji'    => '天神',
            'town_roman'    => 'Tenjin',
            'town_kana'     => 'ﾃﾝｼﾞﾝ',
            'address_kanji' => '福岡県福岡市中央区天神',
            'address_roman' => 'Tenjin Chuo-Ku Fukuoka-Shi Fukuoka-Ken',
            'address_kana'  => 'ﾌｸｵｶｹﾝﾌｸｵｶｼﾁｭｳｵｳｸﾃﾝｼﾞﾝ',
        ));

        foreach([
          null,
          123456,
          '123456',
          '123-456',
          '12345678',
          '123-45678',
          'abcdefg',
          '0000000',
          '8190124'
        ] as $errorZip) {
            $this->assertNull(JpZipCode::search($errorZip));
        }
    }

}
