<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\FormData;
use Laravel\Nova\Http\Requests\NovaRequest;
use Log;

class ProductAttribute extends Resource
{
    public static $displayInNavigation = false;

    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return __('Фильтры');
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __('Фильтр');
    }

    /**
     * Get the displayable singular label of the resource in Genitive case.
     *
     * @return string
     */
    public static function genitiveLabel()
    {
        return __('Фильтра');
    }
    
    /**
     * Get the displayable singular label of the resource in Accusative case.
     *
     * @return string
     */
    public static function accusativeLabel()
    {
        return __('Фильтр');
    }
    
    /**
     * Get the displayable plural label of the resource in Genitive case.
     *
     * @return string
     */
    public static function genitivePluralLabel()
    {
        return __('Фильтров');
    }
    
    /**
     * Get the text for the create resource button.
     *
     * @return string|null
     */
    public static function createButtonLabel()
    {
        return __('Создать фильтр');
    }

    /**
     * Get the text for the update resource button.
     *
     * @return string|null
     */
    public static function updateButtonLabel()
    {
        return __('Обновить фильтр');
    }

    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\ProductAttribute>
     */
    public static $model = \App\Models\ProductAttribute::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
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

            BelongsTo::make(__('Объявление'), 'product', Product::class)->searchable(),

            BelongsTo::make(__('Группа фильтров'), 'filterGroup', FilterGroup::class)->searchable(),

            Select::make(__('Значение фильтра'), 'filter_id')
                ->searchable()
                ->hide()
                ->dependsOn(
                    ['filterGroup'],
                    function (Select $field, NovaRequest $request, FormData $formData) {
                        if ($formData->filterGroup) {
                            $filters = \App\Models\Filter::where('filter_group_id', $formData->filterGroup)
                                ->get()
                                /*->mapWithKeys(fn ($filter) => [
                                    $filter->id => \App\Models\Filter::make($filter)->value()
                                ])*/;
                            $filtersArray = [];
                            foreach ($filters as $filter) {
                                $filtersArray[$filter->id] = $filter->value;
                            }
                            $field->options($filtersArray)->show();
                        } else {
                            $field->options([])->hide();
                        }
                    }
                )
                ->displayUsingLabels()
                ->onlyOnForms(),

            BelongsTo::make(__('Значение фильтра'), 'filter', Filter::class)->exceptOnForms(),

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
