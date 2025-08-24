<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DevSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void {
    $admins = \App\Models\User::factory()->count(2)->state(['role'=>'admin'])->create();
    $users  = \App\Models\User::factory()->count(8)->state(['role'=>'colaborador'])->create();

    \App\Models\Project::factory()->count(5)->create()->each(function ($project) use ($admins,$users) {
        $project->users()->attach(
        $users->random(rand(2,5))->pluck('id')->unique()
        );
        
        \App\Models\Task::factory()->count(8)->create([
        'project_id'=>$project->id,
        'assigned_to'=>$project->users()->inRandomOrder()->first()?->id,
        ]);
    });
    }
}
