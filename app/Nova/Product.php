<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\MultiSelect;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Http\Requests\NovaRequest;
use Log;

class Product extends Resource
{
    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return __('Объявления');
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __('Объявление');
    }

    /**
     * Get the displayable singular label of the resource in Genitive case.
     *
     * @return string
     */
    public static function genitiveLabel()
    {
        return __('Объявления');
    }
    
    /**
     * Get the displayable singular label of the resource in Accusative case.
     *
     * @return string
     */
    public static function accusativeLabel()
    {
        return __('Объявление');
    }
    
    /**
     * Get the displayable plural label of the resource in Genitive case.
     *
     * @return string
     */
    public static function genitivePluralLabel()
    {
        return __('Объявлений');
    }
    
    /**
     * Get the text for the create resource button.
     *
     * @return string|null
     */
    public static function createButtonLabel()
    {
        return __('Создать объявление');
    }

    /**
     * Get the text for the update resource button.
     *
     * @return string|null
     */
    public static function updateButtonLabel()
    {
        return __('Обновить объявление');
    }

    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Product>
     */
    public static $model = \App\Models\Product::class;

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
        'name', 
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

            /*Select::make(__('Пользователь'), 'user_id')
                ->options($this->getUsers())
                ->displayUsingLabels()
                ->rules('required'),*/

            BelongsTo::make(__('Пользователь'), 'user', User::class)->searchable(),

            Text::make(__('Название'), 'name')
                ->sortable()
                ->rules('required', 'max:255'),

            BelongsToMany::make(__('Категории'), 'categories', Category::class)->searchable(),

            Textarea::make(__('Описание'), 'description')
                ->rules('required')
                ->hideFromIndex(),

            Number::make(__('Цена'), 'price')
                ->rules('required'),

            Boolean::make(__('Договорная'), 'price_negotiable')->default(false),

            Select::make(__('Местоположение'), 'location_id')
                ->searchable()
                ->options($this->getLocations())
                ->displayUsingLabels()
                ->rules('required')
                ->hideFromIndex(),

            Text::make(__('Контактное лицо'), 'person')
                ->hideFromIndex(),

            Text::make(__('Email-адрес'), 'email')
                ->hideFromIndex()
                ->rules('email'),

            Text::make(__('Номер телефона'), 'phone')
                ->hideFromIndex(),

            HasMany::make(__('Изображения'), 'productImages', ProductImage::class),

        ];
    }

    private function getUsers()
    {
        $users = \App\Models\User::all();
        foreach ($users as $user) {
            $usersArray[$user->id] = $user->profile->fullname;
        }
        return $usersArray;
    }

    private function getCategories()
    {
        $categories = \App\Models\Category::all();
        $categoriesArray[0] = 'Нет';
        foreach ($categories as $category) {
            $categoriesArray[$category->id] = implode(' > ', array_reverse($this->getPath($category->id)));
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

    private function getLocations()
    {
        $locations = \App\Models\Location::orderBy('city', 'asc')->get();
        $locationsArray[0] = 'Нет';
        foreach ($locations as $location) {
            $locationsArray[$location->id] = $location->city;
        }
        return $locationsArray;
    }

    protected static function fillFields(NovaRequest $request, $model, $fields)
    {
        if (isset($request['person']) || isset($request['email']) || isset($request['phone'])) {
            $productContact = $request->only(['person', 'email', 'phone']);

            $request->request->remove('person');
            $request->request->remove('email');
            $request->request->remove('phone');

            $result = parent::fillFields($request, $model, $fields);

            $result[1][] = function () use ($productContact, $model) {
                $model->contact()->updateOrCreate(
                    [],
                    $productContact
                );
            };
        }
        else {
            $result = parent::fillFields($request, $model, $fields);
        }

        return $result;
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
