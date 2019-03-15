<?php namespace App\Services\Files;

use App\Reply;
use App\Services\Mail\ParsedEmail;
use Carbon\Carbon;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Filesystem\FilesystemManager;

class EmailStore {

    /**
     * Laravel storage service instance.
     *
     * @var FilesystemAdapter
     */
    private $storage;

    /**
     * EmailStore constructor.
     *
     * @param FilesystemManager $storage
     */
    public function __construct(FilesystemManager $storage)
    {
        $this->storage = $storage;
    }

    /**
     * Store specified email on disk.
     *
     * @param ParsedEmail $parsedEmail
     * @param Reply $reply
     */
    public function storeEmail($parsedEmail, Reply $reply = null)
    {
        //if email was matched to existing ticket, we will use reply ID
        //as file name so we can later match it to corresponding reply
        if ($reply) {
            $path = $this->makeMatchedEmailPath($reply);

        //otherwise we will store email into "unmatched" directory
        } else {
            $path = $this->makeUnmatchedEmailPath();
        }

        $this->storage->put($path, $parsedEmail->toJson());
    }

    /**
     * Get original email for specified reply.
     *
     * @param Reply $reply
     *
     * @return array
     */
    public function getEmailForReply(Reply $reply)
    {
        $path = $this->makeMatchedEmailPath($reply);

        if ( ! $this->storage->exists($path)) return null;

        return json_decode($this->storage->get($path), true);
    }

    /**
     * Make path for storing specified reply's email.
     *
     * @param Reply $reply
     * @return string
     */
    private function makeMatchedEmailPath(Reply $reply)
    {
        $date = $reply->created_at;
        return "emails/matched/{$date->year}/{$date->month}/{$date->day}/$reply->uuid.txt";
    }

    /**
     * Make path for storing unmatched replies email.
     *
     * @return string
     */
    private function makeUnmatchedEmailPath()
    {
        $date = Carbon::now(); $name = str_random(30);
        return "emails/unmatched/{$date->year}/{$date->month}/{$date->day}/{$name}.txt";
    }

    /**
     * Get all stored emails that were not matched to tickets.
     *
     * @return array
     */
    public function getUnmatchedEmails()
    {
        return $this->storage->allFiles('emails/unmatched');
    }
}