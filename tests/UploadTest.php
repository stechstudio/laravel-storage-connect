<?php
namespace STS\StorageConnect\Tests;

use Carbon\Carbon;
use STS\StorageConnect\Drivers\Dropbox\Adapter;
use STS\StorageConnect\Events\UploadSucceeded;
use STS\StorageConnect\Models\CloudStorage;
use Event;

class UploadTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->setupDropbox();
    }

    public function testUploadingSources()
    {
        $target = new TestFile([
            'source_path' => '/tmp/foobar.txt',
            'destination_path' => 'uploaded.txt'
        ]);

        /** @var CloudStorage $s */
        $s = factory(TestUser::class)->create()->dropbox;
        $s->fill(['connected' => 1, 'enabled' => 1]);
        app()->instance('sts.storage-connect.adapter.dropbox', new UploadTestAdapter([]));

        Event::fake();

        // Both source path and destination path are pulled from the model
        $s->upload($target, null, false);

        Event::assertDispatched(UploadSucceeded::class, function(UploadSucceeded $event) use($target) {
            return $event->target->is($target) && $event->sourcePath == "/tmp/foobar.txt" && $event->destinationPath == "uploaded.txt";
        });

        Event::fake();

        // Do it again with an explicit destination path
        $s->upload($target, "destination.txt", false);

        Event::assertDispatched(UploadSucceeded::class, function(UploadSucceeded $event) use($target) {
            return $event->target->is($target) && $event->sourcePath == "/tmp/foobar.txt" && $event->destinationPath == "destination.txt";
        });

        Event::fake();

        // Now with both string paths
        $s->upload('/tmp/source.txt', "newfile.txt", false);

        Event::assertDispatched(UploadSucceeded::class, function(UploadSucceeded $event) use($target) {
            return is_null($event->target) && $event->sourcePath == "/tmp/source.txt" && $event->destinationPath == "newfile.txt";
        });

        $this->assertEquals(Carbon::now()->toFormattedDateString(), $s->uploaded_at->toFormattedDateString());
    }
}

class UploadTestAdapter extends Adapter {
    public function upload($sourcePath, $destinationPath)
    {
        return true;
    }
}