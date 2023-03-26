<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Textarea;
use Emilianotisato\NovaTinyMCE\NovaTinyMCE;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Image;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Http\Requests\NovaRequest;
use App\Models\Helper;
use Log;
use Illuminate\Support\Facades\Cache;
use Ganyicz\NovaCallbacks\HasCallbacks;

class Category extends Resource
{
    use HasCallbacks;
    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return __('Категории');
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __('Категория');
    }

    /**
     * Get the displayable singular label of the resource in Genitive case.
     *
     * @return string
     */
    public static function genitiveLabel()
    {
        return __('Категории');
    }
    
    /**
     * Get the displayable singular label of the resource in Accusative case.
     *
     * @return string
     */
    public static function accusativeLabel()
    {
        return __('Категорию');
    }
    
    /**
     * Get the displayable plural label of the resource in Genitive case.
     *
     * @return string
     */
    public static function genitivePluralLabel()
    {
        return __('Категорий');
    }
    
    /**
     * Get the text for the create resource button.
     *
     * @return string|null
     */
    public static function createButtonLabel()
    {
        return __('Создать категорию');
    }

    /**
     * Get the text for the update resource button.
     *
     * @return string|null
     */
    public static function updateButtonLabel()
    {
        return __('Обновить категорию');
    }

    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Category>
     */
    public static $model = \App\Models\Category::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'path';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'name'
    ];

    /*public function title()
    {
        return implode(' > ', array_reverse($this->getPath($this->id)));
    }*/

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
                ->sortable()
                ->rules('required', 'max:255'),

            Number::make(__('Порядок отображения'), 'order')
                ->default(1)
                ->rules('required'),

            Select::make(__('Родительская категория'), 'parent_id')
                ->searchable()
                ->options($this->getCategories())
                ->default(0)
                ->displayUsingLabels()
                ->onlyOnForms(),

            /*Text::make(__('Путь'), function () {
                    return implode(' > ', array_reverse($this->getPath($this->id))); 
                })->sortable()->onlyOnIndex(),*/

            Text::make(__('Путь'), 'path')
                ->sortable()
                ->onlyOnIndex(),

            Image::make('Изображение', 'image')
                ->disk('public')
                ->path('category')
                ->hideFromIndex(),

            Text::make(__('URL'), 'url')
                ->hideFromIndex(),

            NovaTinyMCE::make(__('Описание'), 'description')
                ->hideFromIndex(),

            Text::make(__('H1'), 'meta_heading')
                ->hideFromIndex(),

            Text::make(__('Meta Title'), 'meta_title')
                ->hideFromIndex(),

            Textarea::make(__('Meta Description'), 'meta_description')
                ->hideFromIndex(),

            Textarea::make(__('Meta Keywords'), 'meta_keywords')
                ->hideFromIndex(),

            HasMany::make('FilterGroups')
        ];
    }

    private function getCategories()
    {
        if (Cache::store('redis')->has('categories')) {
            $categoriesArray = Cache::store('redis')->get('categories');
        }
        else {
            $categories = \App\Models\Category::all();
            foreach ($categories as $category) {
                // $categoriesArray[$category->id] = implode(' > ', array_reverse($this->getPath($category->id)));
                $categoriesArray[$category->id] = $category->path;
            }
            asort($categoriesArray);
            $categoriesArray[0] = 'Нет';
            Cache::store('redis')->put('categories', $categoriesArray, 3600);
        }
        return $categoriesArray;
    }

    private function getPath($id, $path = [])
    {
        $category = \App\Models\Category::find($id);
        $path[] = $category->name;
        if ($category->parent_id) {
            return $this->getPath($category->parent_id, $path);
        }
        return $path;
    }

    private function getSelfPath($id, $path = [])
    {
        $category = \App\Models\Category::find($id);
        $path[] = $category->name;
        if ($category->parent_id) {
            return self::getSelfPath($category->parent_id, $path);
        }
        return $path;
    }

    protected static function fillFields(NovaRequest $request, $model, $fields)
    {
        $fillFields = parent::fillFields($request, $model, $fields);

        // first element should be model object
        $modelObject = $fillFields[0];

        // add extra attribute
        if (!$modelObject->url) {
            $modelObject->url = Helper::transliterate($modelObject->name, 'ru');
        }

        if (!$modelObject->meta_heading) {
            $modelObject->meta_heading = $modelObject->name;
        }

        if (!$modelObject->meta_title) {
            $modelObject->meta_title = $modelObject->name;
        }

        // $modelObject->path = implode(' > ', array_reverse(self::getSelfPath($modelObject->id)));

        return $fillFields;
    }

    public static function afterCreate(Request $request, $model)
    {
        $path = implode(' > ', array_reverse(self::getSelfPath($model->id)));
        $model->update(['path' => $path]);
        if (Cache::store('redis')->has('categories')) {
            $categoriesArray = Cache::store('redis')->get('categories');
            // $categoriesArray[$model->id] = implode(' > ', array_reverse(self::getSelfPath($model->id)));
            $categoriesArray[$model->id] = $path;
            asort($categoriesArray);
            Cache::store('redis')->put('categories', $categoriesArray, 3600);
        }
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
