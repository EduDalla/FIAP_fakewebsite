<?php

/**
 * @param string $secret
 * @return void
 */
function google_recapcha(): void {
    $loader = new \Twig\Loader\FilesystemLoader('view');
    $twig = new \Twig\Environment($loader, [
    ]);

    global $secret;
    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => 'https://www.google.com/recaptcha/api/siteverify',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => [
            'secret' => $secret,
            'response' => $_POST['g-recaptcha-response'] ?? '',
        ]
    ]);

// executa a requisição
    $response = curl_exec($curl);
// fecha conexão curl
    curl_close($curl);

// response em array
    $response_array = json_decode($response, true);

// sucesso so recaptcha
    $sucesso = $response_array['success'] ?? false;

    if (!$sucesso) {
        $status = "Preencha seus dados";
        echo $twig->render('registrar.twig', ['status' => $status]);
        die();
    }
}
