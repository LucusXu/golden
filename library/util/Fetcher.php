<?php
/*
 * @desc 下载模块
 */
namespace library\util;
use GOD\Log;

class Fetcher {
    public static function get($url, $header = null, $cookie = null) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

    function fetchFile($url, $fileName) {
		try {
        	$ch = curl_init();
			curl_setopt($ch, CURLOPT_POST, 0);
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$fileContent = curl_exec($ch);
			if (strlen($fileContent) < 15 || '{"ret":-6001}' == $fileContent) {
				Log::warning("fetch file fail, maybe ip has been anti");
				return false;
			}
			curl_close($ch);
			$objFile = fopen($fileName, 'w');
			fwrite($objFile, $fileContent);
			fclose($objFile);
		} catch (\Exception $e) {
			Log::warning("fetch file fail.");
			return false;
		}
		return true;
	}
}
