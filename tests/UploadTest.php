<?php
namespace STS\StorageConnect\Tests;

use Carbon\Carbon;
use STS\StorageConnect\Drivers\Dropbox\Adapter;
use STS\StorageConnect\Events\UploadFailed;
use STS\StorageConnect\Events\UploadRetrying;
use STS\StorageConnect\Events\UploadSucceeded;
use STS\StorageConnect\Exceptions\UploadException;
use STS\StorageConnect\Jobs\UploadFile;
use STS\StorageConnect\Models\CloudStorage;
use Event;
use Queue;

class UploadTest extends TestCase
{
    /**
     * @var CloudStorage
     */
    protected $storage;

    public function setUp()
    {
        parent::setUp();

        $this->setupDropbox();
        $this->storage = factory(TestUser::class)->create()->dropbox;
        $this->storage->fill(['connected' => 1, 'enabled' => 1])->save();

        app()->instance('sts.storage-connect.adapter.dropbox', new UploadTestAdapter([]));
    }

    public function testUploadingSources()
    {
        $target = new TestFile([
            'source_path' => '/tmp/foobar.txt',
            'destination_path' => 'uploaded.txt'
        ]);

        $this->storage->adapter()->result(true);

        Event::fake();

        // Both source path and destination path are pulled from the model
        $this->storage->upload($target, null, false);

        Event::assertDispatched(UploadSucceeded::class, function(UploadSucceeded $event) use($target) {
            return $event->target->is($target) && $event->sourcePath == "/tmp/foobar.txt" && $event->destinationPath == "uploaded.txt";
        });

        Event::fake();

        // Do it again with an explicit destination path
        $this->storage->upload($target, "destination.txt", false);

        Event::assertDispatched(UploadSucceeded::class, function(UploadSucceeded $event) use($target) {
            return $event->target->is($target) && $event->sourcePath == "/tmp/foobar.txt" && $event->destinationPath == "destination.txt";
        });

        Event::fake();

        // Now with both string paths
        $this->storage->upload('/tmp/source.txt', "newfile.txt", false);

        Event::assertDispatched(UploadSucceeded::class, function(UploadSucceeded $event) use($target) {
            return is_null($event->target) && $event->sourcePath == "/tmp/source.txt" && $event->destinationPath == "newfile.txt";
        });

        $this->assertEquals(Carbon::now()->toFormattedDateString(), $this->storage->uploaded_at->toFormattedDateString());
    }

    public function testFailedUpload()
    {
        $this->storage->adapter()->result(function() {
            throw new UploadException('foobar');
        });

        Event::fake();
        $this->assertFalse($this->storage->upload("source", "dest", false));
        Event::assertDispatched(UploadFailed::class);
    }

    public function testRetryingUpload()
    {
        $this->storage->adapter()->result(function() {
            throw (new UploadException('foobar'))->retry('please');
        });

        Event::fake();
        $this->storage->upload("source", "dest", false, new UploadTestQueueJob());
        Event::assertDispatched(UploadRetrying::class);

        Queue::fake();
        $this->storage->upload("source", "dest");
        Queue::assertPushed(UploadFile::class);
    }
}

class UploadTestAdapter extends Adapter {
    protected $result;

    public function result($result)
    {
        $this->result = $result;
    }
    public function upload($sourcePath, $destinationPath)
    {
        return value($this->result);
    }
}

class UploadTestQueueJob extends UploadFile {
    public function __construct() {}
}