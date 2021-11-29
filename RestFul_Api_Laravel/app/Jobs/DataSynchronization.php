<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;
use Log;

use App\Models\Smile;
use App\Models\Meeting;
use App\Models\SystemMessage;
use App\Models\Message;
use App\Models\Question;

class DataSynchronization implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $meeting_hash;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($meeting_hash)
    {
        $this->meeting_hash = $meeting_hash;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $messages = Redis::lrange('room:'.$this->meeting_hash.':messages', 0, -1);
        foreach($messages as $message){
            $objectMessage = json_decode($message);
            if(isset($objectMessage->systemWithUser) && isset($objectMessage->content)){
                info($objectMessage->systemWithUser->id);
                $message = SystemMessage::create([
                    'meeting_id' => Meeting::where('hash', '=', $this->meeting_hash)->first()->id,
                    'system_with_user_id' => $objectMessage->systemWithUser->id,
                    'content' => $objectMessage->content,
                    'writed_at' => $objectMessage->created_at,
                ]);
            };

            if(isset($objectMessage->user) && isset($objectMessage->content)){
                $message = Message::create([
                    'meeting_id' => Meeting::where('hash', '=', $this->meeting_hash)->first()->id,
                    'user_id' => $objectMessage->user->id,
                    'content' => $objectMessage->content,
                    'writed_at' => $objectMessage->created_at,
                ]);
            };
        }
        info("Messages ".$this->meeting_hash." synchronized.");


//         $files = Redis::lrange('room:'.$this->meeting_hash.':files', 0, -1);
//         $objectFiles = [];
//         foreach($files as $file){
//             $objectFiles[] = json_decode($file);
//         }
//         info($objectFiles);
//
//
        $questionsKeys = Redis::keys('room:'.$this->meeting_hash.':question:*:info');
        foreach($questionsKeys as $questionKey){
            $question = Redis::hgetall($questionKey);
            $questionsVotes = Redis::smembers('room:'.$this->meeting_hash.':question:'.$question['id'].':votes');
            $Q = Question::create([
                'meeting_id' => Meeting::where('hash', '=', $this->meeting_hash)->first()->id,
                'user_id' => json_decode($question['user'])->id,
                'text' => json_decode($question['question'])->text,
                'status' => json_decode($question['question'])->status,
                'uid' => $question['id'],
                'started_at' => $question['started_at'],
                'finished_at' => $question['finished_at'],
            ]);
            $Q->votes()->attach($questionsVotes);
        }
        info("Questions ".$this->meeting_hash." synchronized.");
        info("Votes ".$this->meeting_hash." synchronized.");


        $smiles = Redis::lrange('room:'.$this->meeting_hash.':smiles', 0, -1);
        foreach($smiles as $smile){
            $smile = Smile::create([
                'meeting_id' => Meeting::where('hash', '=', $this->meeting_hash)->first()->id,
                'user_id' => json_decode($smile)->userID,
                'smile' => json_decode($smile)->smile,
            ]);
        }
        info("Smiles ".$this->meeting_hash." synchronized.");

    }
}
