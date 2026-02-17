<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainingVideo extends Model
{
    use HasFactory;
	protected $table="training_videos";
    protected $fillable = [
        'video_title',
		'video_path',
		'total_video_time_length',
		'thumbnail',
        'is_active',
        'is_delete',
    ];
	
	public static function filterTrainingVideo(string $searchTerm = "", int $limit = 0, int $skip = 0)
    {
        $query = self::select('training_videos.*')
            ->where('training_videos.is_delete', 0);

        if ($searchTerm) {
            $query->where(function ($query) use ($searchTerm) {
                $query->orWhere('training_videos.id', 'like', "%{$searchTerm}%");
				$query->orWhere('training_videos.total_video_time_length', 'like', "%{$searchTerm}%");
                $query->orWhere('training_videos.video_title', 'like', "%{$searchTerm}%");
            });
        }

        $total = $query->count();

        if ($limit && $limit > 0 && $skip && $skip > 0) {
            $query->limit($limit)->offset($skip);
        }
        $result = $query->orderBy('training_videos.id', 'DESC')->get();

        return ["totalRecords" => $total, "result" => $result];
    }

}
