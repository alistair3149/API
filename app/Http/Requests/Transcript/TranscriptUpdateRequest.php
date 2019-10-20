<?php

declare(strict_types=1);

namespace App\Http\Requests\Transcript;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TranscriptUpdateRequest extends FormRequest
{
    private const WIKI_ID = 'wiki_id';

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->can('web.user.transcripts.update');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|min:1|max:255',
            'youtube_url' => 'nullable|url|min:15|max:255',
            'published_at' => 'required|date',
            'format' => 'required|string|exists:video_formats,id',
            self::WIKI_ID => [
                'required',
                'integer',
                'min:1',
                'max:100000',
                Rule::unique('transcripts')->ignore($this->request->get(self::WIKI_ID), self::WIKI_ID),
            ],
        ];
    }
}
