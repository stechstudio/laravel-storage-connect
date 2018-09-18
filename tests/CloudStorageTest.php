<?php
namespace STS\StorageConnect\Tests;

use STS\StorageConnect\Models\CloudStorage;

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

        $s->enable();

        $this->assertTrue($s->isEnabled());

        $s->disable('full');

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
}