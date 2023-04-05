<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Image;
use Emilianotisato\NovaTinyMCE\NovaTinyMCE;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;
use App\Models\Helper;
use Carbon\Carbon;

class Article extends Resource
{
    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return __('Статьи');
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __('Статья');
    }

    /**
     * Get the displayable singular label of the resource in Genitive case.
     *
     * @return string
     */
    public static function genitiveLabel()
    {
        return __('Статьи');
    }
    
    /**
     * Get the displayable singular label of the resource in Accusative case.
     *
     * @return string
     */
    public static function accusativeLabel()
    {
        return __('Статью');
    }
    
    /**
     * Get the displayable plural label of the resource in Genitive case.
     *
     * @return string
     */
    public static function genitivePluralLabel()
    {
        return __('Статей');
    }
    
    /**
     * Get the text for the create resource button.
     *
     * @return string|null
     */
    public static function createButtonLabel()
    {
        return __('Создать статью');
    }

    /**
     * Get the text for the update resource button.
     *
     * @return string|null
     */
    public static function updateButtonLabel()
    {
        return __('Обновить статью');
    }

    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Article>
     */
    public static $model = \App\Models\Article::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'title';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'title',
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

            BelongsTo::make(__('Категория блога'), 'blogCategory', BlogCategory::class)->searchable()->nullable(),

            Text::make(__('Название'), 'title')
                ->sortable()
                ->rules('required', 'max:255'),

            Text::make(__('URL'), 'url')
                ->hideFromIndex(),

            Image::make('Изображение', 'image')
                ->disk('public')
                ->path('blog')
                ->hideFromIndex(),

            NovaTinyMCE::make(__('Содержание'), 'content')
                ->options([
                    'use_lfm' => true,
                    'lfm_url' => 'laravel-filemanager'
                ])
                ->hideFromIndex(),

            Date::make(__('Дата'), 'date')
                ->withMeta(['value' => Carbon::now()])
                ->sortable()->nullable(),

            Textarea::make(__('Meta Description'), 'meta_description')
                ->hideFromIndex(),

            Textarea::make(__('Meta Keywords'), 'meta_keywords')
                ->hideFromIndex(),

        ];
    }

    protected static function fillFields(NovaRequest $request, $model, $fields)
    {
        $fillFields = parent::fillFields($request, $model, $fields);

        $modelObject = $fillFields[0];
        $modelObject->type = 'blog';
        if (!$modelObject->url) {
            $modelObject->url = Helper::transliterate($modelObject->title, 'ru');
        }

        return $fillFields;
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
