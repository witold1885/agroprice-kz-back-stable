<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Gravatar;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Password;
use Laravel\Nova\Fields\HasOne;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Http\Requests\NovaRequest;
// use Dniccum\PhoneNumber\PhoneNumber;
// use Bissolli\NovaPhoneField\PhoneNumber;
// use Wemersonrv\InputMask\InputMask;

class User extends Resource
{
    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return __('Пользователи');
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __('Пользователь');
    }

    /**
     * Get the displayable singular label of the resource in Genitive case.
     *
     * @return string
     */
    public static function genitiveLabel()
    {
        return __('Пользователя');
    }
    
    /**
     * Get the displayable singular label of the resource in Accusative case.
     *
     * @return string
     */
    public static function accusativeLabel()
    {
        return __('Пользователя');
    }
    
    /**
     * Get the displayable plural label of the resource in Genitive case.
     *
     * @return string
     */
    public static function genitivePluralLabel()
    {
        return __('Пользователей');
    }
    
    /**
     * Get the text for the create resource button.
     *
     * @return string|null
     */
    public static function createButtonLabel()
    {
        return __('Создать пользователя');
    }

    /**
     * Get the text for the update resource button.
     *
     * @return string|null
     */
    public static function updateButtonLabel()
    {
        return __('Обновить пользователя');
    }

    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\User>
     */
    public static $model = \App\Models\User::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'profile.fullname';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'name', 'email', 'profile.fullname',
    ];

    public function title()
    {
        return $this->profile->fullname . ' (ID ' . $this->name . ')';
    }

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

            // Gravatar::make()->maxWidth(50),

            Text::make('ID', 'name')
                ->sortable()
                ->rules('required', 'max:255')
                ->withMeta(['extraAttributes' => [
                    'readonly' => true
                ]])
                ->default(function ($request) {
                    return $this->getRandomID();
                }),

            Text::make(__('Имя/Название'), 'fullname')
                ->rules('required'),

            Select::make(__('Тип профиля'), 'type')
                ->options([
                    'private' => 'Частное лицо',
                    'company' => 'Организация',
                ])->rules('required')->displayUsingLabels(),

            Text::make(__('Номер телефона'), 'phone'),

            Text::make(__('Whatsapp'), 'whatsapp'),
            // InputMask::make(__('Номер телефона'), 'phone')
            //     ->mask('###.###.###-##')  // 111.222.333.44
            //     ->raw(),

            Text::make(__('E-mail'), 'email')
                ->sortable()
                ->rules('required', 'email', 'max:254')
                ->creationRules('unique:users,email')
                ->updateRules('unique:users,email,{{resourceId}}'),

            Password::make(__('Пароль'), 'password')
                ->onlyOnForms()
                ->creationRules('required', Rules\Password::defaults())
                ->updateRules('nullable', Rules\Password::defaults()),

            HasMany::make(__('Объявления'), 'products', Product::class),

        ];
    }

    private function getProfileType($type)
    {
        $profile_types = [
            'private' => 'Частное лицо',
            'company' => 'Организация',
        ];
        return $profile_types[$type];
    }

    private function getRandomID()
    {
        $randomID = rand(100001, 999999);
        $checkExists = \App\Models\User::where('name', $randomID)->first();
        if (!$checkExists) {
            return $randomID;
        }
        else {
            return $this->getRandomID();
        }
    }

    protected static function fillFields(NovaRequest $request, $model, $fields)
    {
        // Get all of our user details data
        $userProfile = $request->only(['fullname', 'type']);

        // Remove them from the request
        $request->request->remove('fullname');
        $request->request->remove('type');

        $result = parent::fillFields($request, $model, $fields);

        // Insert them in the details object after model has been saved.
        $result[1][] = function () use ($userProfile, $model){
            $model->profile()->updateOrCreate(
                [],
                $userProfile
            );
        };

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
