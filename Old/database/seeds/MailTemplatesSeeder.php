<?php

use Illuminate\Database\Seeder;
use App\Services\Mail\MailTemplates;

class MailTemplatesSeeder extends Seeder
{
    /**
     * @var MailTemplates
     */
    private $mailTemplates;

    /**
     * List of subjects for mail templates.
     * @var array
     */
    private $subjects = [
        'email_confirmation' => 'Confirm your {{SITE_NAME}} account',
        'share' => "{{DISPLAY_NAME}} shared '{{ITEM_NAME}}' with you",
        'generic' => '{{EMAIL_SUBJECT}}',
    ];

    /**
     * MailTemplatesTableSeeder constructor.
     *
     * @param MailTemplates $mailTemplates
     */
    public function __construct(MailTemplates $mailTemplates)
    {
        $this->mailTemplates = $mailTemplates;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->mailTemplates->getDefault()->each(function($config) {
            //user friendly template name
            $config['display_name'] = str_replace('-', ' ', title_case($config['name']));

            //for what action template will be used
            $config['action'] = str_replace('-', '_', $config['name']);

            //set template subject
            $config['subject'] = $this->subjects[$config['action']];

            //set template file name
            $config['file_name'] = $config['name'].'.blade.php';

            //mail template already exists, bail
            if ($this->mailTemplates->getByAction($config['action'])) return;

            try {
                $this->mailTemplates->create($config);
            } catch(\Exception $e) {
                //
            }
        });
    }
}
