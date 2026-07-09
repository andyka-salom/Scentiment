<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Model;

use App\Services\FieldTypeRegistry;
use App\FieldTypes\TextFieldType;
use App\FieldTypes\EmailFieldType;
use App\FieldTypes\NumberFieldType;
use App\FieldTypes\PhoneFieldType;
use App\FieldTypes\RadioFieldType;
use App\FieldTypes\DropdownFieldType;
use App\FieldTypes\CheckboxFieldType;
use App\FieldTypes\ScaleFieldType;
use App\FieldTypes\RatingFieldType;
use App\FieldTypes\DateFieldType;
use App\FieldTypes\TimeFieldType;
use App\FieldTypes\FileFieldType;
use App\FieldTypes\MatrixFieldType;
use App\FieldTypes\SignatureFieldType;
use App\FieldTypes\SectionFieldType;
use App\FieldTypes\StatementFieldType;
use App\FieldTypes\ButtonFieldType;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(FieldTypeRegistry::class, function ($app) {
            $registry = new FieldTypeRegistry();
            
            // Register field types
            $registry->register('short_text', new TextFieldType());
            $registry->register('long_text', new TextFieldType());
            $registry->register('email', new EmailFieldType());
            $registry->register('number', new NumberFieldType());
            $registry->register('phone', new PhoneFieldType());
            $registry->register('radio', new RadioFieldType());
            $registry->register('dropdown', new DropdownFieldType());
            $registry->register('checkbox', new CheckboxFieldType());
            $registry->register('scale', new ScaleFieldType());
            $registry->register('rating', new RatingFieldType());
            $registry->register('date', new DateFieldType());
            $registry->register('time', new TimeFieldType());
            $registry->register('file', new FileFieldType());
            $registry->register('matrix', new MatrixFieldType());
            $registry->register('signature', new SignatureFieldType());
            $registry->register('section', new SectionFieldType());
            $registry->register('statement', new StatementFieldType());
            $registry->register('button', new ButtonFieldType());

            return $registry;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(! app()->isProduction());
    }
}
