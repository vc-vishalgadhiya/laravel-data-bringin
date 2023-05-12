<?php

namespace Vcian\LaravelDataBringin\Models;

use Illuminate\Database\Eloquent\Model;

class ImportLog extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'import_logs';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'extra_data' => 'array',
    ];
}
