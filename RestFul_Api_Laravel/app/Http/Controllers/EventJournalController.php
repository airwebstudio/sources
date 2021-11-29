<?php

namespace App\Http\Controllers;

use App\Journal\Journal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class EventJournalController extends Controller
{
    /**
     * @OA\Get(
     *     tags={"events"},
     *     path="/api/events/me",
     *     summary="Get All My Events History",
     *
     *     @OA\RequestBody(
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="page", type="string", example="1"),
     *              @OA\Property(property="per_page", type="string", format="per_page", example="1"),
     *          ),
     *      ),
     *
     *     @OA\Response(
     *          response=200,
     *          description="response",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Successfully uploadet!"),
     *          )
     *      )
     * )
     */
    public function me(Request $request)
    {
        $per_page = $request->per_page ?: 10;
        $page = $request->page ?: 1;
        $user = auth()->user();

        $Journal = new Journal;
        $response = $Journal->get_all_events([
            'primary_id'=> $user->id,
            'per_page'=> $per_page,
            'page'=> $page
        ]);

        return response()->json($response);
    }
}
