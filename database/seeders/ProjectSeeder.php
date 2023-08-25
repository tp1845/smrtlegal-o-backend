<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [
                'name' => 'Project #1',
                'due_date' => now(),
                'summary' => 'lorem ipsum',
                'status' => 'new',
                'team' => [
                    'name' => 'Team #1',
                    'members' => [
                        'member1@test.com',
                        'member2@test.com',
                    ],
                ]

            ],
            [
                'name' => 'Project #2',
                'due_date' => now(),
                'summary' => 'lorem ipsum',
                'status' => 'new',
                'team' => [
                    'name' => 'Team #2',
                    'members' => [
                        'member2@test.com',
                        'member3@test.com',
                    ],
                ]

            ],
        ];

        Project::truncate();
        Team::truncate();
        TeamMember::truncate();

        collect($data)->each(function($row) {
            $project = Project::create([
                'name' => $row['name'],
                'due_date' => $row['due_date'],
                'summary' => $row['summary'],
                'status' => $row['status'],
                'document_id' => '0',
                'reminder_id' => '0',
                'team_id' => '0'
            ]);

            $team = Team::create([
                'name' => $row['team']['name']
            ]);

            $project->update(['team_id' => $team->id]);

            collect($row['team']['members'])->each(function($member, $index) use ($team) {
                $user = User::where('email', $member)->first();
                if ( ! $user) {
                    $user = User::create([
                        'email' => $member,
                        'password' => bcrypt('123456'),
                        'name' => null,
                        'email_verified_at' => now(),
                    ]);
                }

                $team->members()->attach($user, [
                    'user_id' => $user->id, 
                    'role_id' => $index + 1,
                    'name' => 'test' . $index + 1, 
                    'email' => $user->email
                ]);
            });

        });
    }
}
