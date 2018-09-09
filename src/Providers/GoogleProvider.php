<?php
namespace STS\StorageConnect\Providers;

use Google_Client;
use Google_Http_MediaFileUpload;
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;
use SocialiteProviders\Google\Provider;
use SocialiteProviders\Manager\OAuth2\User;
use STS\StorageConnect\Connections\AbstractConnection;
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
     * @param $sourcePath
     * @param $destinationPath
     *
     * @return string
     */
    public function upload( $sourcePath, $destinationPath )
    {
        $folderId = $this->prepareFolders(explode("/", ltrim(dirname($destinationPath), "/")));
        $filename = basename($destinationPath);
        $filesize = filesize($sourcePath);

        $file = new Google_Service_Drive_DriveFile([
            'name'     => $filename,
            'parents'  => [$folderId],
            'mimeType' => mime_content_type($sourcePath)
        ]);

        if ($filesize >= 5 * 1024 * 1024) {
            return $this->uploadChunked($sourcePath, $file, $filesize, $folderId);
        }

        return $this->service()->files->create(
            $file,
            [
                'data' => file_get_contents($sourcePath),
                'mimeType' => mime_content_type($sourcePath),
                'uploadType' => 'media',
            ]
        )->id;
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
     * @param array       $folders
     * @param string $parentFolderId
     *
     * @return mixed
     */
    protected function prepareFolders( array $folders, $parentFolderId = 'root' )
    {
        $foldername = array_shift($folders);

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
     * @param string $parentFolderId
     *
     * @return mixed
     */
    protected function folderExists($name, $parentFolderId = 'root')
    {
        $name = str_replace("'", "", $name);

        return collect($this->service()->files->listFiles([
            'q' => "mimeType = 'application/vnd.google-apps.folder' and name = '$name' and trashed = false  and '$parentFolderId' in parents",
            'spaces' => 'drive',
            'fields' => 'files(id, name)',
        ]))->first();
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