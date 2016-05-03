<?php
class FilerTest extends \PHPUnit_Framework_TestCase
{
    function getInstance($type)
    {
        return new JpZipCode\Filer($type);
    }

    function testDownloadUrl()
    {
        foreach([
            ['jp',    'http://www.post.japanpost.jp/zipcode/dl/kogaki/zip/ken_all.zip'],
            ['roman', 'http://www.post.japanpost.jp/zipcode/dl/roman/ken_all_rome.zip']
        ] as list($type, $url)) {
            $this->assertEquals($this->getInstance($type)->downloadUrl(), $url);
        }
    }

    function testBasename()
    {
        foreach([
            ['jp',    'ken_all.zip'],
            ['roman', 'ken_all_rome.zip']
        ] as list($type, $basename)) {
            $this->assertEquals($this->getInstance($type)->basename(), $basename);
        }
    }
}
