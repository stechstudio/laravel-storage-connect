<?php

namespace STS\StorageConnect\Drivers\Google;

use Google_Client;
use Google_Http_MediaFileUpload;
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;
use Illuminate\Support\Str;
use STS\StorageConnect\Drivers\AbstractAdapter;
use STS\StorageConnect\Models\Quota;
use StorageConnect;
use STS\StorageConnect\UploadRequest;
use STS\StorageConnect\UploadResponse;

class Adapter extends AbstractAdapter
{
    /**
     * @var string
     */
    protected $driver = "google";

    /**
     * @var string
     */
    protected $providerClass = Provider::class;

    /**
     * @param $user
     *
     * @return array
     */
    protected function mapUserDetails($user)
    {
        return [
            'name'  => $user->name,
            'email' => $user->email,
        ];
    }

    /**
     * @return Quota
     */
    public function getQuota()
    {
        $about = $this->service()->about->get();

        return new Quota($about->getQuotaBytesTotal(), $about->getQuotaBytesUsed());
    }

    /**
     * @return Google_Service_Drive
     */
    protected function makeService()
    {
        $client = new Google_Client([
            'client_id'     => $this->config['client_id'],
            'client_secret' => $this->config['client_secret']
        ]);

        $client->setApplicationName(
            config('storage-connect.app_name', config('app.name'))
        );

        $client->setAccessToken($this->token);

        if ($client->isAccessTokenExpired()) {
            $this->updateToken($client->refreshToken($client->getRefreshToken()));
        }

        return new Google_Service_Drive($client);
    }

    /**
     * @param UploadRequest $request
     *
     * @return string
     *
     */
    public function upload( UploadRequest $request )
    {
        $file = $this->prepareFile($request->getDestinationPath());
        list($filesize, $mimeType) = $this->stat($request->getSourcePath());

        if ($filesize >= 5 * 1024 * 1024) {
            return $this->uploadChunked($request->getSourcePath(), $file, $filesize);
        }

        return $this->service()->files->create($file, [
            'data'       => file_get_contents($request->getSourcePath()),
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
        $folderId = $this->getFolderIdForPath(StorageConnect::appName() . "/" . dirname($destinationPath));

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
        if (Str::startsWith($sourcePath, "http")) {
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
            ? $this->prepareFolderTree($folders, $folder->id)
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
    
    public function checkUploadStatus( UploadResponse $response )
    {

    }

    public function pathExists($remotePath)
    {
        // stub
    }
}