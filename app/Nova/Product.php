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
use DB;
use Illuminate\Support\Facades\Cache;
use Ganyicz\NovaCallbacks\HasCallbacks;
use App\Models\Helper;

class Product extends Resource
{
    use HasCallbacks;
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
                /*->default(function ($request) {
                    return $this->getUserData()['person'];
                })*/
                ->hideFromIndex()
                ->rules('required'),

            Text::make(__('Email-адрес'), 'email')
                /*->default(function ($request) {
                    return $this->getUserData()['email'];
                })*/
                ->hideFromIndex(),

            Text::make(__('Номер телефона'), 'phone')
                /*->default(function ($request) {
                    return $this->getUserData()['phone'];
                })*/
                ->hideFromIndex()
                ->hideWhenCreating()
                ->rules('required'),

            Text::make(__('Номер телефона'), 'phone')
                ->default('+77')
                ->hideFromIndex()
                ->hideFromDetail()
                ->hideWhenUpdating()
                ->rules('required', 'min:12', 'max:12'),

            Select::make(__('Статус'), 'status')
                ->options([
                    'draft' => 'Черновик',
                    'published' => 'Опубликовано',
                    'moderating' => 'На модерации',
                    'accepted' => 'Одобрено',
                    'declined' => 'Отклонено',
                ])
                ->default('accepted')
                ->displayUsingLabels(),

            HasMany::make(__('Изображения'), 'productImages', ProductImage::class),

        ];
    }

    private function getUserData()
    {
        if ($_GET['viaResource'] == 'users' && $_GET['viaResourceId']) {
            $user = \App\Models\User::find($_GET['viaResourceId']);
            if ($user) {
                return [
                    'person' => $user->profile->fullname,
                    'email' => $user->email,
                    'phone' => $user->profile->phone,
                ];
            }
        }
        return [
            'person' => null,
            'email' => null,
            'phone' => null,
        ];
    }

    private function getUsers()
    {
        if (Cache::store('redis')->has('users')) {
            $usersArray = Cache::store('redis')->get('users');
        }
        else {
            $users = \App\Models\User::all();
            foreach ($users as $user) {
                $usersArray[$user->id] = $user->profile->fullname;
            }
            asort($usersArray);
            Cache::store('redis')->put('users', $usersArray, 3600);
        }
        return $usersArray;
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

    private function getLocations()
    {
        if (Cache::store('redis')->has('locations')) {
            $locationsArray = Cache::store('redis')->get('locations');
        }
        else {
            $locations = \App\Models\Location::orderBy('city', 'asc')->get();
            foreach ($locations as $location) {
                $locationsArray[$location->id] = $location->city;
            }
            asort($locationsArray);
            $locationsArray[0] = 'Нет';
            Cache::store('redis')->put('locations', $locationsArray, 3600);
        }
        return $locationsArray;
    }

    private static function updateProductCategories($product_id, $id)
    {
        $category = \App\Models\Category::find($id);
        DB::table('product_categories')->updateOrInsert([
            'category_id' => $id,
            'product_id' => $product_id, 
        ], [
            'category_id' => $id,
            'product_id' => $product_id, 
        ], ['timestamps' => false]);
        if ($category->parent_id) {
            return self::updateProductCategories($product_id, $category->parent_id);
        }
        return true;
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
        elseif ($request['viaRelationship'] == 'categories' && $request['editMode'] == 'attach') {
            $category = \App\Models\Category::find($model->category_id);
            if ($category->parent_id) {
                self::updateProductCategories($model->product_id, $category->parent_id);
            }
            $result = parent::fillFields($request, $model, $fields);
        }
        else {
            $result = parent::fillFields($request, $model, $fields);
        }

        return $result;
    }

    public static function afterCreate(Request $request, $model)
    {
        $productUrl = Helper::transliterate($model->name, 'ru') . '-' . $model->id;
        $model->update(['url' => $productUrl]);
    }

    public static function afterUpdate(Request $request, $model)
    {
        // Log::info('Product updated:');
        // Log::info($request);
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
