<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Admin;
use App\Mail\VerifyMail;
use App\Mail\ForgotMail;
use Mail;
use Auth;
use JWTAuth;
use Str;
use Carbon\Carbon;
use DB;
use Laravel\Nova\Notifications\NovaNotification;
use Laravel\Nova\URL;

class AuthController extends Controller
{
    public function login(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return ['success' => false, 'error' => $validator->messages()->first()];
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['success' => false, 'error' => 'Пользователь не найден']);
        }

        if (!$token = JWTAuth::attempt($validator->validated())) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 401);
        }
        return $this->createNewToken($token);
    }

    public function register(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => $validator->messages()->first()]);
        }

        $user = User::create(array_merge(
            $validator->validated(),
            [
                'password' => bcrypt($request->password),
                'email_verify_token' => Str::random(16),
                'status' => 'incomplete',
            ]
        ));

        UserProfile::create(['user_id' => $user->id]);

        if (!$token = JWTAuth::attempt($request->only('email','password'))) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 401);
        }

        Mail::to($request->email)->send(new VerifyMail($user->email_verify_token));

        $this->notifyAdmins($user->id, 'Зарегистрирован новый пользователь');

        return $this->createNewToken($token);  
    }

    public function complete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|confirmed|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => $validator->messages()->first()]);
        }

        UserProfile::updateOrCreate([
            'user_id' => $request->user_id,
        ], [
            'user_id' => $request->user_id,
            'fullname' => $request->profile['fullname'],
            'type' => $request->profile['type'],
            'phone' => $request->profile['phone'],
            'whatsapp' => $request->profile['whatsapp'],
        ]);

        $user = User::where('id', $request->user_id)->first();
        $prev_status = $user->status;
        $user->update(['password' => bcrypt($request->password), 'status' => 'active']);

        if ($prev_status == 'incomplete') {
            $this->notifyAdmins($user->id, 'Пользователь ' . $user->name . ' завершил регистрацию');
        }

        return response()->json(['success' => true]);
    }

    public function notifyAdmins($user_id, $message)
    {
        foreach (Admin::all() as $admin) {
            $admin->notify(
                NovaNotification::make()
                    ->message($message)
                    ->action('Перейти', URL::remote(config('app.url') . '/nova/resources/users/' . $user_id))
                    // ->icon('download')
                    ->type('info')
            );
        }
    }

    protected function createNewToken($token){
        return response()->json([
            'success' => true,
            'access_token' => $token,
            'token_type' => 'bearer',
            'user' => auth()->user()
        ]);
    }

    public function verifyEmail($token)
    {
        $user = User::where('email_verify_token', $token)->first();
        if (!$user) {
            return abort(404);
        }

        if (!$user->email_verified_at) {
            $user->update(['email_verified_at' => date('Y-m-d H:i:s')]);
            return redirect()->intended(config('app.spa_url') . '/#/profile/complete-register');
        }
    }

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()){
            return response()->json(['success' => false, 'error' => $validator->messages()->first()]);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['success' => false, 'error' => 'Пользователь не найден']);
        }

        $token = $this->getForgotToken($user->email);
        Mail::to($request->email)->send(new ForgotMail($token));

        return response()->json(['success' => true]);
    }

    public function getForgotToken($email)
    {
        $oldToken = DB::table('password_resets')->where('email', $email)->first();

        if ($oldToken) {
            return $oldToken->token;
        }

        $token = Str::random(40);
        $this->saveToken($token, $email);
        return $token;
    }

    private function saveToken($token, $email)
    {
        DB::table('password_resets')->insert([
            'email' => $email,
            'token' => $token,
            'created_at' => Carbon::now()
        ]);
    }

    private function checkToken($token)
    {
        return DB::table('password_resets')->where('token', $token)->first();
    }

    private function deleteToken($token)
    {
        DB::table('password_resets')->where('token', $token)->delete();
    }

    public function resetPassword($token)
    {
        $existToken = $this->checkToken($token);
        if (!$existToken) {
            return abort(404);
        }

        $user = User::where('email', $existToken->email)->first();

        if (!$user) {
            return abort(404);               
        }

        return redirect()->intended(config('app.spa_url') . '/#/reset-password/' . $token);
    }

    public function checkPasswordResetToken($token)
    {
        return response()->json(['existToken' => $this->checkToken($token)]);        
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
            'token' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => $validator->messages()->first()]);
        }

        $existToken = $this->checkToken($request->token);
        if (!$existToken) {
            return response()->json(['success' => false, 'error' => 'Ключ для восстановления пароля не найден']);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['success' => false, 'error' => 'Пользователь не найден']);
        }

        if ($existToken->email != $user->email) {
            return response()->json(['success' => false, 'error' => 'Не найдено соответствие ключа указанному пользователю']);
        }

        $user->update(['password' => bcrypt($request->password)]);

        $this->deleteToken($request->token);

        return response()->json(['success' => true]);        
    }

    public function getUser(Request $request)
    {
        return response()->json($request->user());
    }

    public function refresh() {
        return $this->createNewToken(auth()->refresh());
    }

    public function logout()
    {
        auth()->logout();

        return response()->json(['success' => true], 200);
    }

}