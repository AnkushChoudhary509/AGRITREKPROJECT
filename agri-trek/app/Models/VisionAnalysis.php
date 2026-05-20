<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class VisionAnalysis extends Model {
    protected $fillable = ['mode','object_count','healthy_pct','affected_pct','result_json','image_path'];
    protected $casts    = ['result_json' => 'array'];
}
