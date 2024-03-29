<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Banner;
use App\Models\Feedback;
use App\Models\Admin;
use App\Models\BlogCategory;
use App\Models\Article;
use App\Models\News;
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

    public function getBlogCategories()
    {
        try {
            $categories = BlogCategory::all();

            return response()->json(['success' => true, 'categories' => $categories]);
        } catch (\ErrorException $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function getLastBlogArticles()
    {
        try {
            $lastArticles = Article::select('id', 'type', 'title', 'url', 'image', 'date', 'views')->where('type', 'blog')->orderBy('date', 'desc')->limit(10)->get();

            return response()->json(['success' => true, 'lastArticles' => $lastArticles]);
        } catch (\ErrorException $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function getLastNewsArticles()
    {
        try {
            $lastArticles = News::select('id', 'type', 'title', 'url', 'image', 'date', 'views')->where('type', 'news')->orderBy('date', 'desc')->limit(10)->get();

            return response()->json(['success' => true, 'lastArticles' => $lastArticles]);
        } catch (\ErrorException $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function getBlogArticles(Request $request)
    {
        try {
            $limit = 20;
            $offset = ($request->page - 1) * $limit;

            $query = Article::where('type', 'blog');

            if ($request->search) {
                $query->where('title', 'like', '%' . $request->search . '%');
            }

            if ($request->category_id) {
                $query->where('category_id', $request->category_id);
            }

            $total = $query->count();
            $articles = $query->orderBy('date', 'desc')->skip($offset)->take($limit)->get();

            $pages = ceil($total / $limit);

            $lastArticles = Article::where('type', 'blog')->orderBy('date', 'desc')->limit(5)->get();

            return response()->json(['success' => true, 'articles' => $articles, 'lastArticles' => $lastArticles, 'pages' => $pages]);
        } catch (\ErrorException $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function getNewsArticles(Request $request)
    {
        try {
            $limit = 20;
            $offset = ($request->page - 1) * $limit;

            $query = News::where('type', 'news');

            if ($request->search) {
                $query->where('title', 'like', '%' . $request->search . '%');
            }

            $total = $query->count();
            $articles = $query->orderBy('date', 'desc')->skip($offset)->take($limit)->get();

            $pages = ceil($total / $limit);

            $lastArticles = News::where('type', 'news')->orderBy('date', 'desc')->limit(5)->get();

            return response()->json(['success' => true, 'articles' => $articles, 'lastArticles' => $lastArticles, 'pages' => $pages]);
        } catch (\ErrorException $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function getBlogArticle($url)
    {
        try {
            if (!$url) {
                return response()->json(['success' => false, 'error' => 'Article URL not specified']);
            }

            $article = Article::where('url', $url)->where('type', 'blog')->first();

            if (!$article) {
                return response()->json(['success' => false, 'error' => 'Article not found']);
            }

            return response()->json(['success' => true, 'article' => $article]);
        } catch (\ErrorException $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function getNewsArticle($url)
    {
        try {
            if (!$url) {
                return response()->json(['success' => false, 'error' => 'News URL not specified']);
            }

            $article = News::where('url', $url)->where('type', 'news')->first();

            if (!$article) {
                return response()->json(['success' => false, 'error' => 'News not found']);
            }

            return response()->json(['success' => true, 'article' => $article]);
        } catch (\ErrorException $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function increaseBlogArticleViews(Request $request)
    {
        try {
            $article = Article::find($request->article_id);

            if (!$article) {
                return response()->json(['success' => false, 'error' => 'Article not found']);
            }

            $views = $article->views;
            $article->update(['views' => $views + 1]);

            return response()->json(['success' => true]);
        } catch (\ErrorException $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function increaseNewsArticleViews(Request $request)
    {
        try {
            $article = News::find($request->article_id);

            if (!$article) {
                return response()->json(['success' => false, 'error' => 'News not found']);
            }

            $views = $article->views;
            $article->update(['views' => $views + 1]);

            return response()->json(['success' => true]);
        } catch (\ErrorException $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
