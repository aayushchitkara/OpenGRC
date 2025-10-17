<?php

namespace App\Filament\Admin\Resources;

use Aliziodev\LaravelTaxonomy\Models\Taxonomy;
use App\Filament\Admin\Resources\TaxonomyResource\Pages;
use App\Filament\Admin\Resources\TaxonomyResource\RelationManagers;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TaxonomyResource extends Resource
{
    protected static ?string $model = Taxonomy::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationLabel = 'Taxonomy Types';

    protected static ?string $navigationGroup = 'System';

    protected static ?int $navigationSort = 30;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->unique('taxonomies', 'name', ignoreRecord: true)
                    ->maxLength(255)
                    ->label('Taxonomy Name')
                    ->helperText('e.g., Department, Scope, Risk Level')
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Forms\Set $set, ?string $state) {
                        if ($state) {
                            // Auto-set type to lowercase, underscored version of name
                            $set('type', strtolower(str_replace(' ', '_', $state)));
                        }
                    }),
                Forms\Components\Hidden::make('type'),
                Forms\Components\TextInput::make('slug')
                    ->unique('taxonomies', 'slug', ignoreRecord: true)
                    ->maxLength(255)
                    ->label('Slug')
                    ->helperText('URL-friendly version of the name (auto-generated if empty)'),
                Forms\Components\Textarea::make('description')
                    ->maxLength(1000)
                    ->columnSpanFull()
                    ->label('Description')
                    ->helperText('Optional description of this taxonomy'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Taxonomy Name'),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->sortable()
                    ->label('Slug'),
                Tables\Columns\TextColumn::make('children_count')
                    ->counts('children')
                    ->label('Terms Count')
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();

                        return strlen($state) > 50 ? $state : null;
                    })
                    ->label('Description'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\TaxonomyChildrenRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTaxonomies::route('/'),
            'create' => Pages\CreateTaxonomy::route('/create'),
            // 'view' => Pages\ViewTaxonomy::route('/{record}'),
            'edit' => Pages\EditTaxonomy::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereNull('parent_id'); // Only show root taxonomies
    }
}
