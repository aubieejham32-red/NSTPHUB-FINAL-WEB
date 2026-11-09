<?php

namespace Tests\Unit;

use App\Support\PublicMediaUrl;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;

class PublicMediaUrlTest extends TestCase
{
    public function test_it_uses_the_incoming_https_host_instead_of_app_url(): void
    {
        $request = Request::create('https://nstphub.example/api/mobile/homepage');

        $this->assertSame(
            'https://nstphub.example/storage/student-profile-photos/3/photo.jpg',
            PublicMediaUrl::forRequest($request, 'student-profile-photos/3/photo.jpg')
        );
    }

    public function test_it_normalizes_common_public_storage_prefixes(): void
    {
        $request = Request::create('https://nstphub.example/api/mobile/digital-student-id');

        foreach ([
            '/storage/universities/logo.png',
            'public/storage/universities/logo.png',
            'storage/app/public/universities/logo.png',
        ] as $path) {
            $this->assertSame(
                'https://nstphub.example/storage/universities/logo.png',
                PublicMediaUrl::forRequest($request, $path)
            );
        }
    }

    public function test_it_does_not_rewrite_external_urls_or_allow_traversal(): void
    {
        $request = Request::create('https://nstphub.example/api/mobile/me');

        $this->assertSame(
            'https://cdn.example/image.png',
            PublicMediaUrl::forRequest($request, 'https://cdn.example/image.png')
        );
        $this->assertNull(PublicMediaUrl::forRequest($request, 'storage/../.env'));
        $this->assertNull(PublicMediaUrl::forRequest($request, ''));
    }
}
