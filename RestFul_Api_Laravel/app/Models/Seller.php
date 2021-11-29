<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * @OA\Schema(
 *     title="Seller",
 *     @OA\Property(property="id", type="string", format="id", example="1"),
 *     @OA\Property(property="user_id", type="string", format="user_id", example="1"),
 *     @OA\Property(property="name", type="string", format="name", example="name"),
 *     @OA\Property(property="description", type="string", format="description", example="description"),
 *     @OA\Property(property="price", type="string", format="price", example="3"),
 *     @OA\Property(property="payment_system", type="string", format="payment_system", example="PayPal"),
 *     @OA\Property(property="credentials", type="json", format="credentials", example=""),
 *     @OA\Property(property="meetings_count", type="string", format="meetings_count", example="1"),
 *     @OA\Property(property="participants_count", type="string", format="participants_count", example="1"),
 *     @OA\Property(property="is_private", type="string", format="is_private", example="0"),
 *     @OA\Property(property="created_at", type="string", format="created_at", example="2021-03-18T09:49:02.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="updated_at", example="2021-03-18T09:49:02.000000Z"),
 * )
 */
class Seller extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'credentials',
        'payment_system',
        'is_private',
        'rate'
    ];

    protected $casts = [
        'credentials' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function review_list()
    {
        return $this->hasMany(SellerReview::class, 'seller_id','user_id');
    }

    public function rate_list()
    {
        return $this->hasMany(SellerRate::class, 'seller_id','user_id');
    }

    public function calculate_avg_rate()
    {
        $this->rate = $this->rate_list->avg('rate');
        $this->save();
        return $this->rate;
    }

    public function calculate_meetings_count()
    {
        $this->meetings_count = $this->user->meetings->whereNull('declined_at')->count();
        $this->save();
        return $this->meetings_count;
    }

    public function calculate_participants_count()
    {
        $this->participants_count = DB::table('meetings')
            ->rightJoin('meeting_participants', 'meetings.id', '=', 'meeting_participants.meeting_id')
            ->whereNull('meetings.declined_at')
            ->where('meetings.user_id', $this->user_id)
            ->count();
        $this->save();
        return $this->participants_count;
    }

    public function calculate_meetings_participants_count()
    {
        $this->meetings_count = count($this->user->meetings->whereNull('declined_at'));
        $this->participants_count = DB::table('meetings')
            ->rightJoin('meeting_participants', 'meetings.id', '=', 'meeting_participants.meeting_id')
            ->whereNull('meetings.declined_at')
            ->where('meetings.user_id', $this->user_id)
            ->count();
        $this->save();
    }


}
