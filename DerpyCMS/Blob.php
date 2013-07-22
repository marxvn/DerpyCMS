<?php
/**
 * DerpyCMS - A derped CMS built on Slim
 */

namespace DerpyCMS;


use Slim\Slim;

class Blob
{
    static function getParts($page_id)
    {
        $db = DerpyCMS::getPDOInstance();
        $query = $db->prepare(
            'SELECT id, `blob`, `file`, type FROM '.DERPY_DB_PREFIX.'blobs WHERE page_id = :page_id ;'
        );
        $query->bindValue(':page_id', $page_id, \PDO::PARAM_INT);
        $query->execute();
        $blobs = $query->fetchAll(\PDO::FETCH_OBJ);
        $parts = array();
        foreach ($blobs as $blob) {
            $part = 'parts.'.$blob->type;
            if ($blob->file == 0) {
                // Blob is stored in DB
                $parts[$part] = $blob->blob;
            } else {
                // Blob is stored in filesystem
                $parts[$part] = Blob::getFileBlob($blob->blob);

            }
        }
        return $parts;
    }

    /**
     * Fetches blobs stored as files
     * Based on Shimmie's warehouse_path()
     *
     * @param string $blob Hash of the blob
     * @return mixed
     */
    static public function getFileBlob($blob)
    {
        $app = DerpyCMS::getInstance();
        $base = $app->config('blob.path');
        $ab = substr($blob, 0, 2);
        $cd = substr($blob, 2, 2);
        return file_get_contents($base.'/'.$ab.'/'.$cd.'/'.$blob);
    }
}