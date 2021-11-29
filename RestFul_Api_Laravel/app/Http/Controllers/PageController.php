<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{
    /**
     * Create a new PageController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['read', 'read_all']]);
    }

    /**
     * @OA\Get(
     *     tags={"page"},
     *     path="/api/page/{slug}",
     *     summary="Get Page",
     *
     *     @OA\Response(
     *          response=200,
     *          description="response",
     *          @OA\JsonContent(ref="#/components/schemas/Page")
     *      )
     * )
     *
     * Page Update
     * @return \Illuminate\Http\JsonResponse
     */
    public function read()
    {
        $page = new Page();
        $page = $page->where('slug', request('slug'))->first();

        if(!$page){
            return response()->json(['error' => 'Page not exist']);
        }

        return response()->json($page);
    }

    /**
     * @OA\Get(
     *     tags={"page"},
     *     path="/api/page",
     *     summary="get pages",
     *
     *     @OA\RequestBody(
     *          description="pagination & count per page",
     *          @OA\JsonContent(
     *              @OA\Property(property="page", type="string", format="page", example="1"),
     *              @OA\Property(property="per_page", type="string", format="per_page", example="10"),
     *          ),
     *      ),
     *
     *     @OA\Response(
     *          response=200,
     *          description="response",
     *          @OA\JsonContent(
     *              @OA\Property(property="current_page", type="string", format="current_page", example="1"),
     *              @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Page")),
     *              @OA\Property(property="first_page_url", type="string", format="first_page_url", example="http://api-public.timepal.local/api/page?page=1"),
     *              @OA\Property(property="from", type="string", format="from", example="1"),
     *              @OA\Property(property="last_page", type="string", format="last_page", example="2"),
     *              @OA\Property(property="last_page_url", type="string", format="last_page_url", example="http://api-public.timepal.local/api/page?page=2"),
     *              @OA\Property(property="links", type="array",
     *                  example={
     *                  {
     *                      "url": null,
     *                      "label": "&laquo; Previous",
     *                      "active": false
     *                  },
     *                  {
     *                      "url": "http://api-public.timepal.local/api/page?page=1",
     *                      "label": "1",
     *                      "active": true
     *                  },
     *                  {
     *                      "url": "http://api-public.timepal.local/api/page?page=2",
     *                      "label": "2",
     *                      "active": false
     *                  },
     *                  {
     *                      "url": "http://api-public.timepal.local/api/page?page=2",
     *                      "label": "Next &raquo;",
     *                      "active": false
     *                  }},
     *                  @OA\Items(
     *                      @OA\Property(property="url", type="string", format="url"),
     *                      @OA\Property(property="label", type="string", format="label"),
     *                      @OA\Property(property="active", type="string", format="active")
     *                  ),
     *              ),
     *              @OA\Property(property="next_page_url", type="string", format="next_page_url", example="http://api-public.timepal.local/api/page?page=2"),
     *              @OA\Property(property="path", type="string", format="path", example="http://api-public.timepal.local/api/page"),
     *              @OA\Property(property="per_page", type="string", format="per_page", example="10"),
     *              @OA\Property(property="prev_page_url", type="string", format="prev_page_url", example="null"),
     *              @OA\Property(property="to", type="string", format="to", example="10"),
     *              @OA\Property(property="total", type="string", format="total", example="12"),
     *          )
     *      )
     * )
     *
     * Get all Pages
     * @return \Illuminate\Http\JsonResponse
     */
    public function read_all()
    {
        $per_page = request('per_page') ?: 10;
        return Page::paginate($per_page);
    }
}
