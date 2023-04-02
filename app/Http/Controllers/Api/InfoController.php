<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Banner;
use App\Models\Feedback;
use App\Models\Admin;
use App\Models\Article;
use Laravel\Nova\Notifications\NovaNotification;
use Laravel\Nova\URL;
use App\Mail\FeedbackMail;
use Mail;

class InfoController extends Controller
{
    public function getBanner($code)
    {
        try {
            $banner = Banner::select('id', 'code', 'autoplay', 'duration')->where('code', $code)->first();
            $banner->images = $banner->getActiveImages();

            return response()->json(['success' => true, 'banner' => $banner]);
        } catch (\ErrorException $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function sendFeedback(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'subject' => 'required',
                'email' => 'required|email',
                'message' => 'required',
            ]);

            if ($validator->fails()) {
                return ['success' => false, 'error' => $validator->messages()->first()];
            }

            $feedback = Feedback::create($request->all());

            Mail::to('info@agroprice.kz')->send(new FeedbackMail($feedback));
            Mail::to('wiktor8555@gmail.com')->send(new FeedbackMail($feedback));

            foreach (Admin::all() as $admin) {
                $admin->notify(
                    NovaNotification::make()
                        ->message('Получено новое обращение')
                        ->action('Перейти', URL::remote(config('app.url') . '/nova/resources/feedback/' . $feedback->id))
                        // ->icon('download')
                        ->type('info')
                );
            }

            return response()->json(['success' => true]);
        } catch (\ErrorException $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function getBlogArticles(Request $request)
    {
        try {
            $limit = 12;
            $offset = ($request->page - 1) * $limit;

            $query = Article::where('type', 'blog');

            if ($request->search) {
                $query->where('title', 'like', '%' . $request->search . '%');
            }

            $total = $query->count();
            $articles = $query->skip($offset)->take($limit)->get();

            $lastArticles = Article::where('type', 'blog')->orderBy('created_at', 'desc')->limit(4)->get();

            return response()->json(['success' => true, 'articles' => $articles, 'lastArticles' => $lastArticles, 'total' => $total]);
        } catch (\ErrorException $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
