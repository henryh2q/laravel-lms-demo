<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasTranslations;

    protected $fillable = ['course_id', 'position', 'prompt', 'options', 'correct_option'];

    protected $casts = ['prompt' => 'array', 'options' => 'array', 'correct_option' => 'integer'];

    /** @return list<string> */
    public function translatedOptions(?string $locale = null): array
    {
        $locale ??= app()->getLocale();

        return array_map(fn (array $o) => $o[$locale] ?? $o['en'] ?? '', $this->options);
    }
}
