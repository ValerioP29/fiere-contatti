<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Support\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCurrentTenant
{
    public function __construct(private readonly TenantContext $tenantContext)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return $next($request);
        }

        $currentTenant = $this->tenantContext->resolveForUser($user, $request->session());

        if ($currentTenant === null) {
            if ($request->routeIs('tenant.missing')) {
                return $next($request);
            }

            if ($request->expectsJson() || ! $request->acceptsHtml()) {
                abort(403, 'No tenant associated with the authenticated user.');
            }

            return redirect()->route('tenant.missing');
        }

        $request->attributes->set('currentTenant', $currentTenant);

        return $next($request);
    }
}
