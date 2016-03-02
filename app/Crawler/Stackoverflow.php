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

class Stackoverflow extends Base
{
    protected $post;
    protected $post_link;

    public function __construct()
    {
        $this->post = new Post();
        $this->work();
    }

    public function work($page = 1)
    {
        $stack = new Crawler(file_get_contents('http://stackoverflow.com/questions?page='.$page));
        $topics = $stack->filter('.question-summary');
        if($topics->count() < 1) {
            // done with this section
            Log::info('Done with the Stackoverflow');
            // todo: notify me
            sleep(3600); // sleep for 1 hour
            // restart
            $this->work();
        }

        for($i = 0; $i < $topics->count(); $i++) {
            $topic = $topics->eq($i);

            $data = [
                'answers' => (int) $topic->filter('.status')->text(),
                'views' => (int) $topic->filter('.views')->text(),
                'tags' => $topic->filter('.post-tag')->each(function (Crawler $node, $i) {
                            return $node->text();
                        })
            ];

            $title = $topic->filter('h3 a');
            $link = $title->attr('href');
            $title_text = $title->text();

            $id = preg_replace('/\D+/', '', $topic->attr('id'));

            // ensure multiple bots doesn't work on a topic
            if(\Cache::has('s_'.$id)) {
                continue;
            } else {
                //store
                \Cache::put('s_'.$id, 'working', 1); // cache for 1 min
            }

            // check if the topic has already been processed
            $exist = $this->post->stackoverflow()->where('c_id', $id)->first();

            if($exist) {
                // count all the posts under the topic
                $posts = $this->post->stackoverflow()->where('parent_id', $id)->count();
                if($posts && $data['answers'] > $posts+1) {
                    // check if another bot is currently not working on the topic
                    $last_post = $this->post->stackoverflow()->where('parent_id', $id)->oldest()->first();
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

    public function getPost(Crawler $question) {
        $data = [
            'votes' => (int) $question->filter('.vote-count-post')->text()
        ];

        $content = $question->filter('.post-text')->text();

        $date = $question->filter('.user-action-time')->last()->filter('span')->attr('title');
        $date = Carbon::parse($date);

        $author = [
            'name' => $question->filter('.user-details a')->first()->text(),
            'link' => a_link('stackoverflow').$question->filter('.user-details a')->first()->attr('href')
        ];

        return [
            'data' => $data,
            'content' => stripNl($content),
            'date' => $date,
            'author' => $author,
            'link' => $this->post_link.'#'.$question->attr('id')
        ];
    }

    public function processPosts($link, $topic) {

        $this->post_link = 'http://stackoverflow.com'.$link;

        $posts_content = new Crawler(file_get_contents($this->post_link));

        $question = $posts_content->filter('#question');

        $to_process = [$question];

        // find and process the answers too
        $answers = $posts_content->filter('.answer');

        for($i = 0; $i < $answers->count(); $i++) {
            $to_process[] = $answers->eq($i);
        }

        foreach($to_process as $key => $post) {

            // get the question/answer data
            $q = $this->getPost($post);

            if($key == 0) { // the first post
                $q['c_id'] = $topic['id'];
                $q['views'] = $topic['data']['views'];
                $q['data'] = array_merge($q['data'], array_except($topic['data'], ['views']));
                $q['title'] = $topic['title'];
            } else {
                $q['c_id'] = preg_replace('/\D+/', '', $question->attr('id'));
                $q['parent_id'] = $topic['id'];
                $q['title'] = 'Ans: '.$topic['title'];
            }

            $q['type'] = 'stackoverflow';

            $exists = $this->post->stackoverflow()->where('c_id', $q['c_id'])->first();

            if($exists) {
                // skip
                Log::info('Met a Stackoverflow question/answer that has already been saved');
                // update
                $exists->update($q);
            } else {
                $this->post->create($q);
            }
        }

//        dd('all done');
    }
}
