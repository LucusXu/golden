<?php
namespace library\util;

use \GOD\FastDFS\FastDFS;
use GOD\Log;

class FastdfsStorage {
    /**
	 * @desc 把本地文件上传到fastDfs
     * @param $filePath
     * @return array
     */
	public static function uploadFastDfs($filePath) {
        $objFastdfs = new \GOD\FastDFS\FastDFS();
        $result = $objFastdfs->upload($filePath);

        if ($result['success']) {
            unlink($filePath);
            return array(
                'success' => true,
                'path' => $result['path'],
                'url' => $result['path'],
                'msg' => ''
            );
        } else {
			Log::warning("upload fastdfs fail," . $filePath);
			return false;
		}
    }
}
