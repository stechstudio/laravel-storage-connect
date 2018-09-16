<?php

namespace STS\StorageConnect\Providers;

use Google_Client;
use Google_Http_MediaFileUpload;
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;
use SocialiteProviders\Google\Provider;
use SocialiteProviders\Manager\OAuth2\User;
use STS\StorageConnect\Connections\GoogleConnection;
use STS\StorageConnect\Traits\ProvidesOAuth;
use Log;

/**
 * Class GoogleProvider
 * @package STS\StorageConnect\Providers
 */
class GoogleProvider extends Provider implements ProviderContract
{
    use ProvidesOAuth;

    /**
     * @var array
     */
    protected $scopes = [
        'profile', 'email', 'openid',
        'https://www.googleapis.com/auth/drive.file'
    ];

    /**
     * @var array
     */
    protected $parameters = [
        'access_type' => 'offline',
        'prompt'      => 'consent'
    ];

    /**
     * @var Google_Service_Drive
     */
    protected $service;

    /**
     * @param $sourcePath
     * @param $destinationPath
     *
     * @return string
     */
    public function upload( $sourcePath, $destinationPath )
    {
        $file = $this->prepareFile($destinationPath);
        list($filesize, $mimeType) = $this->stats($sourcePath);

        if ($filesize >= 5 * 1024 * 1024) {
            return $this->uploadChunked($sourcePath, $file, $filesize);
        }

        return $this->service()->files->create($file, [
            'data'       => file_get_contents($sourcePath),
            'mimeType'   => $mimeType,
            'uploadType' => 'media',
        ])->id;
    }

    /**
     * @param $destinationPath
     *
     * @return Google_Service_Drive_DriveFile
     */
    protected function prepareFile( $destinationPath )
    {
        $folderId = $this->getFolderIdForPath($this->manager->appName() . "/" . dirname($destinationPath));

        return new Google_Service_Drive_DriveFile([
            'name'    => basename($destinationPath),
            'parents' => [$folderId],
        ]);
    }

    /**
     * @param $sourcePath
     *
     * @return array
     */
    protected function stat( $sourcePath )
    {
        if (starts_with($sourcePath, "http")) {
            $headers = array_change_key_case(get_headers($sourcePath, 1));
            return [$headers['content-length'], $headers['content-type']];
        }

        return [filesize($sourcePath), mime_content_type($sourcePath)];
    }

    /**
     * @param $path
     *
     * @return mixed
     */
    protected function getFolderIdForPath( $path )
    {
        return $this->prepareFolderTree(
            array_filter(explode("/", $path))
        );
    }

    /**
     * @param                                $sourcePath
     * @param Google_Service_Drive_DriveFile $file
     * @param                                $filesize
     *
     * @return string
     */
    protected function uploadChunked( $sourcePath, Google_Service_Drive_DriveFile $file, $filesize )
    {
        $chunkSize = 2 * 1024 * 1024;

        $this->service()->getClient()->setDefer(true);
        $request = $this->service()->files->create($file);

        $upload = new Google_Http_MediaFileUpload(
            $this->service()->getClient(),
            $request,
            $file->getMimeType(),
            null,
            true,
            $chunkSize
        );
        $upload->setFileSize($filesize);

        $file = false;
        $handle = fopen($sourcePath, "rb");

        while (!$file && !feof($handle)) {
            $chunk = fread($handle, $chunkSize);
            $file = $upload->nextChunk($chunk);
        }

        fclose($handle);
        $this->service()->getClient()->setDefer(false);

        return $file->id;
    }

    /**
     * @param array  $folders
     * @param string $parentFolderId
     *
     * @return mixed
     */
    protected function prepareFolderTree( array $folders, $parentFolderId = 'root' )
    {
        $foldername = array_shift($folders);

        if (!$folder = $this->folderExists($foldername, $parentFolderId)) {
            $fileMetadata = new Google_Service_Drive_DriveFile([
                'name'     => $foldername,
                'parents'  => [$parentFolderId],
                'mimeType' => 'application/vnd.google-apps.folder'
            ]);

            $folder = $this->service()->files->create($fileMetadata, [
                'fields' => 'id'
            ]);
        }

        return count($folders)
            ? $this->prepareFolders($folders, $folder->id)
            : $folder->id;
    }

    /**
     * @param        $name
     * @param string $parentFolderId
     *
     * @return mixed
     */
    protected function folderExists( $name, $parentFolderId = 'root' )
    {
        $name = str_replace("'", "", $name);

        return collect($this->service()->files->listFiles([
            'q'      => "mimeType = 'application/vnd.google-apps.folder' and name = '$name' and trashed = false  and '$parentFolderId' in parents",
            'spaces' => 'drive',
            'fields' => 'files(id, name)',
        ]))->first();
    }
}