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

        $posts = Post::take(10)->orderBy('date', 'desc');

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

        $posts = $posts->get();

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
                'date' => $post->date,
                'author' => $post->author
            ];
        }
        $data = collect($data);
        $total = $data->count();
        // dividing into two columns
        if($total) {
            $each = ceil($total / 2);
            $column1 = $data->take($each);

            $column2 = $data->take($each - $total); // negative will pick from the back

            return ['col1' => $column1, 'col2' => $column2];
        }
        return ['col1' => [], 'col2' => []];
    }
}
