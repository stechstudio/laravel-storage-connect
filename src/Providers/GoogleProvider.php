<?php

namespace STS\StorageConnect\Providers;


use Google_Client;
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;
use SocialiteProviders\Google\Provider;
use SocialiteProviders\Manager\OAuth2\User;
use STS\StorageConnect\Connections\AbstractConnection;
use STS\StorageConnect\Connections\GoogleConnection;
use STS\StorageConnect\Traits\EnhancedProvider;

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
     * @param User $user
     *
     * @return AbstractConnection
     */
    protected function mapUserToConnection( User $user )
    {
        return (new GoogleConnection($this))->load([
            'status' => 'active',
            'name'   => $user->name,
            'email'  => $user->email,
            'token'  => $user->accessTokenResponseBody
        ]);
    }

    /**
     * @param $localPath
     * @param $remotePath
     */
    public function upload( $localPath, $remotePath )
    {
        $folderId = $this->prepareFolders(explode("/", ltrim(dirname($remotePath), "/")));
        $filename = basename($remotePath);
        $filesize = filesize($localPath);

        if ($filesize >= 5 * 1024 * 1024) {
            return $this->uploadChunked($localPath, $filename, $folderId);
        }

        $file = new Google_Service_Drive_DriveFile([
            'name'     => $filename,
            'parents'  => [$folderId],
        ]);

        $result = $this->service()->files->create(
            $file,
            [
                'data' => file_get_contents($localPath),
                'mimeType' => mime_content_type($localPath),
                'uploadType' => 'media',
            ]
        );

        return $result->id;
    }

    protected function uploadChunked( $localPath, $file, $folderId )
    {
        //TODO
    }

    /**
     * @param array $folders
     * @param null  $parentFolderId
     *
     * @return mixed
     */
    protected function prepareFolders( array $folders, $parentFolderId = null )
    {
        $foldername = array_shift($folders);

        // See if this folder already exists
        if(!$folder = $this->folderExists($foldername, $parentFolderId)) {
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
     * @param      $name
     * @param null $parentFolderId
     *
     * @return mixed
     */
    protected function folderExists($name, $parentFolderId = null)
    {
        $name = str_replace("'", "", $name);
        $query = "mimeType = 'application/vnd.google-apps.folder' and name = '$name' and trashed = false";

        if($parentFolderId != null) {
            $query .= " and '$parentFolderId' in parents";
        }

        $response = $this->service()->files->listFiles([
            'q' => $query,
            'spaces' => 'drive',
            'fields' => 'files(id, name)',
        ]);

        return array_shift($response->files);
    }

    /**
     * @return Google_Service_Drive
     */
    public function service()
    {
        if (!$this->service) {
            $this->service = $this->makeService();
        }

        return $this->service;
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