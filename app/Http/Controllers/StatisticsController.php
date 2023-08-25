<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StatisticsController extends Controller
{
    public function get(Request $request) {
        $user = auth()->user();

        $teams = $user->teams()->with('project.document.typeDocument', 'project.team.members')->get();       
        $projects = $teams->pluck('project')->filter();

        

        $ongoing = $projects->filter(function($item) { return in_array($item->status,  ['new', 'In Progress', 'Internal Approval']); })->count();
        $pending = $projects->filter(function($item) { return in_array($item->status,  ['New Version Sent', 'New Version Recived']); })->count();
        $complete = $projects->filter(function($item) { return in_array($item->status,  ['Completed', 'Overdue']); })->count();

        return response()->json([
            'on-going' => $ongoing,
            'pending' => $pending,
            'complete' => $complete,
        ]);
    }
}
