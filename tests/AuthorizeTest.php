<?php
namespace STS\StorageConnect\Tests;

use Illuminate\Http\RedirectResponse;
use StorageConnect;

class AuthorizeTest extends TestCase
{
    public function testAuthorizeProducesRedirect()
    {
        $this->assertInstanceOf(RedirectResponse::class, $this->getResponse());
    }

    public function testCustomState()
    {
        StorageConnect::includeState(['foo' => 'bar']);

        $this->assertEquals('bar', array_get(
            json_decode(base64_decode($this->getQuery()['state']), true),
            'foo')
        );
    }

    public function testDefaultCallbackDomain()
    {
        $this->assertEquals('localhost', $this->getParseRedirectUri()['host']);
    }

    public function testCustomGlobalCallbackDomain()
    {
        config(['storage-connect.callback_domain' => 'www.google.com']);

        $this->assertEquals('www.google.com', $this->getParseRedirectUri()['host']);
    }

    public function testCustomLocalCallbackDomain()
    {
        // The callback specified in the dropbox config overrides our global callback
        config([
            'storage-connect.callback_domain' => 'www.google.com',
            'services.dropbox.callback_domain' => 'www.mysite.com'
        ]);

        $this->assertEquals('www.mysite.com', $this->getParseRedirectUri()['host']);
    }

    public function testFinalRedirectSet()
    {
        $this->getResponse("/final-redirect");

        $this->assertEquals("/final-redirect", session("storage-connect.redirect"));
    }

    protected function getResponse($final = null)
    {
        $this->setupDropbox();
        return factory(TestUser::class)->create()->dropbox->authorize($final);
    }

    protected function getParsedUrl()
    {
        return parse_url($this->getResponse()->getTargetUrl());
    }

    protected function getQuery()
    {
        parse_str($this->getParsedUrl()['query'], $query);
        return $query;
    }

    protected function getParseRedirectUri()
    {
        return parse_url($this->getQuery()['redirect_uri']);
    }
}