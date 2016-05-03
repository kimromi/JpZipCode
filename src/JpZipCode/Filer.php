<?php
namespace JpZipCode;

class Filer
{
    function Filer($type)
    {
        $this->__construct($type);
    }

    function __construct($type)
    {
        $this->type = $type;
        $this->rootPath = __DIR__ . '/../../';
    }

    function downloadUrl() {
        switch ($this->type) {
            case 'jp':
                return 'http://www.post.japanpost.jp/zipcode/dl/kogaki/zip/ken_all.zip';
                break;
            case 'roman':
                return 'http://www.post.japanpost.jp/zipcode/dl/roman/ken_all_rome.zip';
                break;
        }
    }

    function basename() {
        return basename($this->downloadUrl());
    }

    function downloadFile() {
        $file = file_get_contents($this->downloadUrl());
        file_put_contents($this->rootPath . $this->basename(), $file);
    }

    function unzipFile() {
        $zip = new \ZipArchive;
        $res = $zip->open($this->rootPath . $this->basename());
        if ($res === TRUE) {
            $zip->extractTo($this->rootPath);
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $this->csvFiles[] = $zip->getNameIndex($i);
            }

            $zip->close();

            foreach ($this->csvFiles as $fileName) {
                $file = mb_convert_encoding(file_get_contents($this->rootPath . $fileName), 'UTF-8', 'cp932');
                file_put_contents($this->rootPath . $fileName, $file);
            }
            return $this->csvFiles;
        }
        return false;
    }

    function updateJson() {
        foreach ($this->csvFiles as $csv) {
            $file = new \SplFileObject($this->rootPath . $csv);
            $file->setFlags(\SplFileObject::READ_CSV);
            foreach ($file as $line) {
                if (is_null($line[0])) {
                    continue;
                }
                $this->writeJson($this->toHash($line));
            }
        }
    }

    function writeJson($data) {
        $jsonFilePath = $this->rootPath . 'data/zip_code/' . substr($data['zip_code'], 0, 4) . '.json';
        $jsonData = (file_exists($jsonFilePath)) ? json_decode(file_get_contents($jsonFilePath), true) : [];
        if (array_key_exists($data['zip_code'], $jsonData)) {
            $jsonData[$data['zip_code']] = array_merge($data, $jsonData[$data['zip_code']]);
        } else {
            $jsonData[$data['zip_code']] = $data;
        }
        file_put_contents($jsonFilePath, json_encode($jsonData));
    }

    function toHash($data) {
        switch($this->type) {
            case 'jp':
                return [
                    'code'         => $data[0],
                    'old_zip_code' => $data[1],
                    'zip_code'     => $data[2],
                    'pref_kana'    => $data[3],
                    'city_kana'    => $data[4],
                    'town_kana'    => $data[5],
                    'pref_kanji'   => $data[6],
                    'city_kanji'   => $data[7],
                    'town_kanji'   => $data[8]
                ];
            case 'roman':
                return [
                    'zip_code'   => $data[0],
                    'pref_kanji' => $data[1],
                    'city_kanji' => $data[2],
                    'town_kanji' => $data[3],
                    'pref_roman' => $data[4],
                    'city_roman' => $data[5],
                    'town_roman' => $data[6]
                ];
        }
    }

    function clean() {
        foreach(glob($this->rootPath . '{*.zip,*.CSV}', GLOB_BRACE) as $file) {
            unlink($file);
        }
    }
}
