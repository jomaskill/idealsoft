<?php

declare(strict_types=1);

arch('Shopify domain uses strict types')
    ->expect('App\Domains\Shopify')
    ->toUseStrictTypes();

arch('DTOs are readonly')
    ->expect('App\Domains\Shopify\DTOs')
    ->toBeReadonly();

arch('no debugging functions in domain code')
    ->expect('App\Domains')
    ->not->toUse(['dd', 'dump', 'ray']);

arch('controllers are invokable or have standard methods')
    ->expect('App\Domains\Shopify\Http\Controllers')
    ->toHaveSuffix('Controller');

arch('models extend Eloquent Model')
    ->expect('App\Domains\Shopify\Models')
    ->toExtend('Illuminate\Database\Eloquent\Model');

arch('jobs implement ShouldQueue')
    ->expect('App\Domains\Shopify\Jobs')
    ->toImplement('Illuminate\Contracts\Queue\ShouldQueue');
