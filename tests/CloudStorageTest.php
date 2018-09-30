<?php
namespace STS\StorageConnect\Tests;

use Carbon\Carbon;
use STS\StorageConnect\Drivers\AbstractAdapter;
use STS\StorageConnect\Events\CloudStorageDisabled;
use STS\StorageConnect\Events\CloudStorageEnabled;
use STS\StorageConnect\Models\CloudStorage;
use STS\StorageConnect\Models\Quota;
use Event;

class CloudStorageTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        // Your code here
    }

    public function testStateAccessors()
    {
        $s = new CloudStorage([
            'connected' => 1,
            'enabled' => 0,
            'full' => 0,
            'reason' => 'invalid'
        ]);

        $this->assertTrue($s->isConnected());
        $this->assertFalse($s->isEnabled());
        $this->assertTrue($s->isDisabled());
        $this->assertTrue($s->isTokenInvalid());

        // This is an invalid state, nevertheless want to ensure that 'connected' overrides 'enabled' in case of tampering
        $s->fill([
            'connected' => 0,
            'enabled' => 1,
            'full' => 1,
        ]);

        $this->assertFalse($s->isConnected());
        $this->assertFalse($s->isEnabled());
        $this->assertTrue($s->isFull());
    }

    public function testUser()
    {
        $s = new CloudStorage([
            'name' => 'Somebody',
            'email' => 'someone@somewhere.com'
        ]);

        $this->assertEquals('Somebody', $s->getUserName());
        $this->assertEquals('someone@somewhere.com', $s->getUserEmail());
    }

    public function testOwnerRelationship()
    {
        $u = factory(TestUser::class)->create();

        $this->assertTrue($u->dropbox->owner->is($u));

        $this->assertEquals("TestUser:" . $u->id, $u->dropbox->owner_description);
    }

    public function testEnablingDisabling()
    {
        /** @var CloudStorage $s */
        $s = factory(TestUser::class)->create()->dropbox;
        $s->connected = 1;

        $this->assertFalse($s->isEnabled());

        Event::fake();
        $s->enable();
        Event::assertDispatched(CloudStorageEnabled::class);

        $this->assertTrue($s->isEnabled());

        Event::fake();
        $s->disable('full');
        Event::assertDispatched(CloudStorageDisabled::class);

        $this->assertFalse($s->isEnabled());
        $this->assertTrue($s->isFull());

        $s->enable();

        $this->assertTrue($s->isEnabled());
        $this->assertFalse($s->isFull());

        $s->disable('invalid');

        $this->assertFalse($s->isEnabled());
        $this->assertTrue($s->isTokenInvalid());
        $this->assertTrue($s->isConnected());
    }

    public function testSleepWakeup()
    {
        $this->setupDropbox();

        /** @var CloudStorage $s */
        $s = factory(TestUser::class)->create()->dropbox;

        $this->assertInstanceOf(AbstractAdapter::class, $s->adapter());

        $s = unserialize(serialize($s));

        $this->assertInstanceOf(AbstractAdapter::class, $s->adapter());
    }

    public function testNeedToCheckQuota()
    {
        /** @var CloudStorage $s */
        $s = factory(TestUser::class)->create()->dropbox;

        $this->assertNull($s->space_checked_at);
        $this->assertTrue($s->shouldCheckSpace());

        $s->space_checked_at = Carbon::now()->subHour(2);
        $this->assertFalse($s->shouldCheckSpace());

        $s->full = 1;
        $this->assertTrue($s->shouldCheckSpace());

        $s->full = 0;
        $s->space_checked_at = Carbon::now()->subDay(2);
        $this->assertTrue($s->shouldCheckSpace());
    }

    public function testUpdateQuota()
    {
        $this->setupDropbox();

        /** @var CloudStorage $s */
        $s = factory(TestUser::class)->create()->dropbox;

        $s->updateQuota(new Quota(1000, 999));
        $this->assertTrue($s->isFull());

        $s->updateQuota(new Quota(1000, 100));
        $this->assertFalse($s->isFull());
    }
}