<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Project;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReminderProject;

class SendReminderEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'project:reminder-notify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send emails for reminder';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $projects = Project::whereDate('due_date', '>=', now())->whereDate('reminder', now())->get();
        $projects->each(function($project) {
            $members = collect($project->team ? $project->team->members : []);
            if ($project->members) {
                $members = $members->merge($project->members);
            }

            collect($members)->each(function($member) use ($project) {
                $team = ! empty($project->team->name) ? $project->team->name : '-';
                $type = ! empty($project->document->type) ? $project->document->type : '-';
    
                Mail::to($member->email)
                    ->send(new ReminderProject($project->name, $project->due_date, $type, $team));
            });
        });

        echo 'Successfully completed!';

        return 0;
    }
}
