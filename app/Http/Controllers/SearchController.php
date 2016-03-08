<?php

namespace Olajide\Http\Controllers;

use Illuminate\Http\Request;

use Olajide\Http\Requests;
use Olajide\Post;

class SearchController extends Controller
{
    public function index(Request $request) {
        $q = $request->get('q');
        $nl = $request->get('nl');
        $stack = $request->get('stack');

        $posts = Post::orderBy('date', 'desc');

        $in = [];
        if($nl == 'true') {
            $in[] = 'nairaland';
        }

        if($stack == 'true') {
            $in[] = 'stackoverflow';
        }

        if($in) {
            $posts = $posts->whereIn('type', $in);
        }

        if($q) {
            $posts = $posts->where('content', 'LIKE', '%'. $q . '%');
        } else {
            $posts = $posts->whereNull('parent_id'); // only get the parent post
        }

        $posts = $posts->paginate(10);
        $data = [];
        foreach($posts as $post) {
            $data[] = [
                'title' => $post->title,
                'id' => $post->id,
                'data' => array_except($post->data, ['tags']),
                'content' => limitTo($post->content, $q),
                'link' => $post->link,
                'tags' => $post->tags,
                'views' => $post->views,
                'type' => $post->type,
                'date' => $post->date_to,
                'author' => $post->author,
                'parent' => $post->parent_id == null ? 'yes' : 'no'
            ];
        }
        $posts = $posts->toArray();

        $info = [
            'total' => $posts['total'],
            "per_page" => $posts['per_page'],
            "current_page" => $posts['current_page'],
            "last_page" => $posts['last_page'],
            "next_page_url" => $posts['next_page_url'],
            "prev_page_url" => $posts['prev_page_url'],
            "from" => $posts['from'],
            "to" => $posts['to']
        ];

        $data = collect($data);
        $total = $data->count();
        // dividing into two columns
        if($total) {
            $each = ceil($total / 2);
            $column1 = $data->take($each);

            $column2 = $data->take($each - $total); // negative will pick from the back

            return ['col1' => $column1, 'col2' => $column2, 'info' => $info];
        }
        return ['col1' => [], 'col2' => [], 'info' => $info, 'error' => 1];
    }
}
