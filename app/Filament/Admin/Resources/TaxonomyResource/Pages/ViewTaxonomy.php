<?php

namespace App\Filament\Admin\Resources\TaxonomyResource\Pages;

use App\Filament\Admin\Resources\TaxonomyResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewTaxonomy extends ViewRecord
{
    protected static string $resource = TaxonomyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
