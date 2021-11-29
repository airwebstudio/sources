<?php

namespace App\Http\Controllers;

use App\Models\MeetingReport;
use App\Models\Meeting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeetingReportController extends Controller
{
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function send_report(Request $request, $hash): JsonResponse
    {
        $user = auth()->user();

        if(!$user) {
            return response()->json(['error' => 'Current user not found'], 404);
        }

        $report = MeetingReport::create([
            'meeting_id' => Meeting::where('hash', '=', $hash)->first()->id,
            'user_id' => $user->id,
            'type' => $request->input('type'),
            'names' => $request->input('names'),
            'description' => $request->input('description'),
        ]);

        return response()->json([
            'message' => 'Successfully created!',
        ]);
    }

}
