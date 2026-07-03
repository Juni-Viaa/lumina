<?php

namespace Tests\Unit;

use App\Http\Middleware\RoleMiddleware;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_allows_request_when_user_has_matching_role(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $middleware = new RoleMiddleware();
        $request    = Request::create('/upload', 'GET');

        $response = $middleware->handle($request, function ($req) {
            return response('OK', 200);
        }, 'admin');

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_aborts_403_when_user_role_does_not_match(): void
    {
        $mahasiswa = User::factory()->mahasiswa()->create();
        $this->actingAs($mahasiswa);

        $middleware = new RoleMiddleware();
        $request    = Request::create('/upload', 'GET');

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        $middleware->handle($request, function ($req) {
            return response('OK', 200);
        }, 'admin');
    }

    public function test_aborts_403_when_no_user_is_authenticated(): void
    {
        $middleware = new RoleMiddleware();
        $request    = Request::create('/upload', 'GET');

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        $middleware->handle($request, function ($req) {
            return response('OK', 200);
        }, 'admin');
    }
}
