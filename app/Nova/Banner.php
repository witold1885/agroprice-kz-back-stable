<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Http\Requests\NovaRequest;

class Banner extends Resource
{
    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return __('Баннеры');
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __('Баннер');
    }

    /**
     * Get the displayable singular label of the resource in Genitive case.
     *
     * @return string
     */
    public static function genitiveLabel()
    {
        return __('Баннера');
    }
    
    /**
     * Get the displayable singular label of the resource in Accusative case.
     *
     * @return string
     */
    public static function accusativeLabel()
    {
        return __('Баннер');
    }
    
    /**
     * Get the displayable plural label of the resource in Genitive case.
     *
     * @return string
     */
    public static function genitivePluralLabel()
    {
        return __('Баннеров');
    }
    
    /**
     * Get the text for the create resource button.
     *
     * @return string|null
     */
    public static function createButtonLabel()
    {
        return __('Создать баннер');
    }

    /**
     * Get the text for the update resource button.
     *
     * @return string|null
     */
    public static function updateButtonLabel()
    {
        return __('Обновить баннер');
    }

    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Banner>
     */
    public static $model = \App\Models\Banner::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'name', 'code',
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            // ID::make()->sortable(),

            Text::make(__('Название'), 'name')
                ->rules('required'),

            Text::make(__('Code'), 'code')
                ->rules('required')
                ->onlyOnForms(),

            Boolean::make(__('Автослайдинг'), 'autoplay')->default(false),

            Number::make(__('Скорость прокрутки'), 'duration')
                ->min(0)->max(10)->step(1)->default(0),

            HasMany::make('BannerImages')

        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function cards(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function filters(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function lenses(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function actions(NovaRequest $request)
    {
        return [];
    }
}
