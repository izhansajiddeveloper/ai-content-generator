<?php
function generateAIContent($prompt)
{
    $api_key = "AIzaSyDqydxkg4SJmyzV0Qro0rytZeOZrgIuWt0"; // Your Gemini API Key

    $data = [
        "model" => "gemini-2.5-flash", // Gemini model
        "messages" => [
            ["role" => "system", "content" => "You are a helpful AI assistant."],
            ["role" => "user", "content" => $prompt]
        ]
    ];

    $ch = curl_init('https://generativelanguage.googleapis.com/v1beta/openai/chat/completions');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $api_key
    ]);
    curl_setopt($ch, CURLOPT_POST, 1); // crul is the client url function use to send the request of HTTPS to other servers
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));//send the data to server in json format
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);
    return $result['choices'][0]['message']['content'] ?? "No content returned by AI.";
}
