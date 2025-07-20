<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class CommentAnalyzer
{

    protected string $apiUrl = 'https://router.huggingface.co/hf-inference/models/distilbert/distilbert-base-uncased-finetuned-sst-2-english';
    protected string $api2Url = 'https://router.huggingface.co/hf-inference/models/unitary/toxic-bert';
    protected string $apiKey = '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'; // استبدل بمفتاحك إذا تغير

    public function analyze(string $comment): string
    {
        if ($this->isToxic($comment)) {
            return 'Bad';
        }

        $sentiment = $this->analyzeSentiment($comment);

        return $sentiment; // "positive" أو "negative"
    }

    protected function isToxic(string $comment): bool
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post($this->api2Url, [
            'inputs' => $comment,
        ]);

        if (!$response->successful()) {
            return false; // إذا ما اشتغل الـ API منخلي التعليق يمر
        }

        $data = $response->json();

        // بنختار أي label فيه احتمال أعلى من 0.5
        $toxicLabels = collect($data[0] ?? [])
            ->filter(fn($item) => $item['score'] >= 0.5)
            ->pluck('label')
            ->map(fn($label) => strtolower($label));

        // إذا في أي تصنيف مسيء، منرفض التعليق
        return $toxicLabels->contains(function ($label) {
            return in_array($label, [
                'toxic',
                'severe_toxic',
                'obscene',
                'threat',
                'insult',
                'identity_hate',
            ]);
        });
    }

    protected function analyzeSentiment(string $comment): string
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post($this->apiUrl, [
            'inputs' => $comment,
        ]);

        if (!$response->successful()) {
            return 'unknown';
        }

        $data = $response->json();
        $topResult = collect($data[0])->sortByDesc('score')->first();

        return strtolower($topResult['label']); // "positive" أو "negative"
    }
}
