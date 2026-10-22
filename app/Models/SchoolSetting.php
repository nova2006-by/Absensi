<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolSetting extends Model
{
    protected $table = 'school_settings';

    protected $fillable = [
        'latitude',
        'longitude',
        'radius',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'radius' => 'integer',
        ];
    }

    public static function currentOrDefault(): self
    {
        return self::query()->find(1) ?? new self([
            'latitude' => config('absensi.lokasi_latitude'),
            'longitude' => config('absensi.lokasi_longitude'),
            'radius' => config('absensi.lokasi_radius', 100),
        ]);
    }
}
