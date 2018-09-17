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

    public function testOwnerRelationship()
    {
        $u = factory(TestUser::class)->create();

        $this->assertTrue($u->dropbox->owner->is($u));
    }
}