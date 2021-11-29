<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Journal\Journal;

class IsCorrectRefer {

    protected $journal;

    public function __construct(Journal $journal) {

        $this->journal = $journal;
    }
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $ip = $request->ip();
        foreach (explode(',', config('app.access_ips')) as $aip) {
            if ($ip == $aip) {
                $this->journal->add(1, 1, 'API_REQUEST', 'API_REQUEST', date('Y-m-d H:i:s'));
                return $next($request);
            }
        }

        exit('Invalid refer ip: '.$ip);
    }
}
