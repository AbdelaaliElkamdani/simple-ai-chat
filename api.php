<?php
session_start(); // ➔ Obligatoire pour utiliser $_SESSION

function chatBot($ClientPrompt)
{
    $ApiToken = "your_gemini_api_token"; // Remplacez par votre token d'API Gemini
    $UrlApi = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent?key={$ApiToken}";
    
    $data = array(
        "contents" => [
            [
                "parts" => [
                    [
                        "text" => "Réponds de façon claire, courte et précise en une seule phrase : " . $ClientPrompt
                    ]
                ]
            ]
        ],
        "generationConfig" => [
            "temperature" => 0.4,
            "topK" => 1,
            "topP" => 1,
            "maxOutputTokens" => 300
        ]
    );

    $header = array(
        "Content-Type: application/json",
    );

    $chemin = curl_init();
    curl_setopt($chemin, CURLOPT_URL, $UrlApi);
    curl_setopt($chemin, CURLOPT_POST, true);
    curl_setopt($chemin, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($chemin, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($chemin, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($chemin, CURLOPT_HTTPHEADER, $header);
    $response = curl_exec($chemin);
    curl_close($chemin);
    return $response;
}

function cleanResponse($text)
{
    $text = preg_replace('/\*\*(.*?)\*\*/', '$1', $text);
    $text = preg_replace('/__(.*?)__/', '$1', $text);
    $text = preg_replace('/\s+/', ' ', $text);
    return trim($text);
}

if (!isset($_SESSION['messages'])) {
    $_SESSION['messages'] = []; // Initialiser l'historique si vide
}

if (isset($_POST['prompt']) and isset($_POST['ok'])) {
    $promptC = $_POST['prompt'];
    $result = chatBot($promptC);
    $data = json_decode($result);

    if (isset($data->candidates[0]->content->parts[0]->text)) {
        $botReply = cleanResponse($data->candidates[0]->content->parts[0]->text);
        
        // Ajouter le message de l'utilisateur et la réponse du bot dans l'historique
        $_SESSION['messages'][] = [
            'user' => htmlspecialchars($promptC),
            'bot' => htmlspecialchars($botReply)
        ];
    } else {
        $_SESSION['messages'][] = [
            'user' => htmlspecialchars($promptC),
            'bot' => "Erreur : aucune réponse obtenue."
        ];
    }
}
?>

<!-----------------------Code HTML------------------------->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Memo AI assistant</title>
    <link rel="stylesheet" href="api.css">
</head>

<body>
    <header>
        <i><img src="gamer.png" alt="logo"></i>
        <h1>Disscuter avec Memo</h1>
    </header>
    <section class="messages">
    <?php
    if (isset($_SESSION['messages'])) {
        foreach ($_SESSION['messages'] as $msg) {
            echo "<p class='user-message'><strong>Vous :</strong> {$msg['user']}</p>";
            echo "<p class='bot-message'><strong>Memo :</strong> {$msg['bot']}</p>";
        }
    }
    ?>
    </section>
    <section class="form-zone">
        <form action="" method="post" class="form">
            <input type="text" name="prompt" placeholder="writing zone">
            <button type="submit" name="ok"><img src="send.png" alt="button"></button>
        </form>
    </section>
</body>

</html>