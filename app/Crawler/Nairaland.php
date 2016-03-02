<?php
/**
 * Created by PhpStorm.
 * User: olajide
 * Date: 3/1/16
 * Time: 12:05 PM
 */

namespace Olajide\Crawler;


use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Olajide\Post;
use Symfony\Component\DomCrawler\Crawler;

class Nairaland extends Base
{
    protected $post;

    public function __construct()
    {
        $this->post = new Post();
        $this->work();
    }

    public function work($page = 0)
    {
        $programming = new Crawler(file_get_contents('http://nairaland.com/programming/'.$page));
        $topics = $programming->filter('th')->parents()->siblings();
        if($topics->count() < 1) {
            // done with this section
            Log::info('Done with the Nairaland Section');
            // todo: notify me
            sleep(3600); // sleep for 1 hour
            // restart
            $this->work();
        }

        for($i = 0; $i < $topics->count(); $i++) {
            $topic = $topics->eq($i);
            $title = $topic->filter('b a');
            $link = $title->attr('href');
            $title_text = $title->text();

            $info = $topic->filter('.s')->text();
            if(preg_match('/(\d+) posts \& (\d+) views/', $info, $matches)) {
                $data = ['posts' => $matches[1], 'views' => $matches[2]];
            }

            $id = $topic->filter('a')->attr('name');

            // ensure multiple bots doesn't work on a topic
            if(Cache::has('n_'.$id)) {
                continue;
            } else {
                //store
                Cache::put('n_'.$id, 'working', 1); // cache for 1 min
            }

            // check if the topic has already been processed
            $exist = $this->post->nairaland()->where('c_id', $id)->first();

            if($exist) {
                // count all the posts under the topic
                $posts = $this->post->nairaland()->where('parent_id', $id)->count();
                if($posts && $data['posts'] > $posts+1) {
                    // check if another bot is currently not working on the topic
                    $last_post = $this->post->nairaland()->where('parent_id', $id)->oldest()->first();
                    if($last_post) {
                        if($last_post->created_at->diffInMinutes() < 60) { // another bot might still be working on the topic
                            continue; // skip
                        }
                    }
                }
            }

            $this->processPosts($link, ['title' => $title_text, 'id' => $id, 'data' => $data]);
        }

        // move to the next page
        $this->work($page+1);
    }

    public function processPosts($link, $topic = false) {

        $posts_content = new Crawler(file_get_contents('http://nairaland.com'.$link));
        $posts = $posts_content->filter('.bold.l.pu');

        preg_match('/(\d+)\/.+/', $link, $match);

        $topic_id = $match[1];

        for($i = 0; $i < $posts->count(); $i++) {

            $post = $posts->eq($i);

            $links = $post->filter('a');

            $id = $links->eq(0)->attr('name');

            $author = $links->last()->text();

            $date = $post->filter('.s')->text();
            $date = preg_replace('/on /i', '', $date);
            // convert to an instance of carbon
            $date = Carbon::parse($date);

            $post_content = $posts_content->filter('#pb'.$id);

            $data = [];

            // check if the post has likes or shares
            $info = $post_content->filter('.s');
            if($info->count()) {
                $text = $info->text();

                // likes
                preg_match('/(\d+) like/i', $text, $likes);
                if($likes) {
                    $data['likes'] = $likes[1];
                }

                // shares
                preg_match('/(\d+) share/i', $text, $shares);
                if($shares) {
                    $data['shares'] = $shares[1];
                }
            }

            $exists = $this->post->nairaland()->where('c_id', $id)->first();

            if($exists) {
                // skip
                Log::info('Met a post that has already been saved');
                continue;
            }


            $save = [
                'c_id' => $id,
                'link' => 'http://nairaland.com'.$links->eq(3)->attr('href'),
                'author' => ['name' => $author, 'link' => 'http://nairaland.com/'.strtolower($author)],
                'date' => $date,
                'content' => stripNl($post_content->filter('.narrow')->html()),
                'data' => $data,
                'title' => $links->eq(3)->text()
            ];

            if($i == 0 && $topic) { // first post
                $save['views'] = $topic['data']['views'];
                $save['c_id'] = $topic['id'];
                $save['data'] = array_merge($save['data'], array_except($topic['data'], ['views']));
            } else {
                $save['parent_id'] = $topic_id;
            }

            $save['type'] = 'nairaland';

            $this->post->create($save);

        }

//        dd('all done');

        // still on the same page
        // start follow the next links
        $next = $posts_content->filter('.nocopy p')->first()->filter('b')->siblings()->eq(1);
        if(!$next->count()) {
            $next = $posts_content->filter('.nocopy p')->first()->filter('b')->siblings()->first();
        }

        $next = $next->attr('href');

        $last = $posts_content->filter('.nocopy p')->first()->filter('b')->siblings()->last()->attr('href');

        if($next != $last) {
            // last is usually the reply link or it means the current page is the last page
            $this->processPosts($next);
        }
    }
}
