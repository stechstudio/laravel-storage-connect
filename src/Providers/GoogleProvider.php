<?php

namespace STS\StorageConnect\Providers;

use Google_Client;
use Google_Http_MediaFileUpload;
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;
use SocialiteProviders\Google\Provider;
use SocialiteProviders\Manager\OAuth2\User;
use STS\StorageConnect\Connections\GoogleConnection;
use STS\StorageConnect\Traits\EnhancedProvider;
use Log;

/**
 * Class GoogleProvider
 * @package STS\StorageConnect\Providers
 */
class GoogleProvider extends Provider implements ProviderContract
{
    use EnhancedProvider;

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
     * @var string
     */
    protected $connectionClass = GoogleConnection::class;

    /**
     * @param User $user
     *
     * @return array
     */
    protected function mapUserToConnectionConfig( User $user )
    {
        return [
            'name'   => $user->name,
            'email'  => $user->email,
            'token'  => $user->accessTokenResponseBody
        ];
    }

    /**
     * @param $sourcePath
     * @param $destinationPath
     *
     * @return string
     */
    public function upload( $sourcePath, $destinationPath )
    {
        $file = $this->prepareFile($sourcePath, $destinationPath);
        $filesize = filesize($sourcePath);

        if ($filesize >= 5 * 1024 * 1024) {
            return $this->uploadChunked($sourcePath, $file, $filesize);
        }

        return $this->service()->files->create($file, [
            'data'       => file_get_contents($sourcePath),
            'mimeType'   => $file->getMimeType(),
            'uploadType' => 'media',
        ])->id;
    }

    /**
     * @param $sourcePath
     * @param $destinationPath
     *
     * @return Google_Service_Drive_DriveFile
     */
    protected function prepareFile( $sourcePath, $destinationPath )
    {
        $folderId = $this->getFolderIdForPath($this->manager->appName() . "/" . dirname($destinationPath));

        return new Google_Service_Drive_DriveFile([
            'name'     => basename($destinationPath),
            'parents'  => [$folderId],
            'mimeType' => mime_content_type($sourcePath)
        ]);
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

    /**
     * @return float
     */
    public function percentFull()
    {
        $about = $this->service()->about->get();

        return round(($about->getQuotaBytesUsed() / $about->getQuotaBytesTotal()) * 100, 1);
    }

    /**
     * @return Google_Service_Drive
     */
    protected function makeService()
    {
        $client = new Google_Client([
            'client_id'     => $this->fullConfig['client_id'],
            'client_secret' => $this->fullConfig['client_secret']
        ]);

        $client->setApplicationName(
            $this->app['config']->get('storage-connect.app_name', $this->app['config']->get('app.name'))
        );

        $client->setAccessToken($this->connection->token);

        if ($client->isAccessTokenExpired()) {
            $this->connection->token = $client->refreshToken($client->getRefreshToken());
            $this->connection->save();
        }

        return new Google_Service_Drive($client);
    }
}