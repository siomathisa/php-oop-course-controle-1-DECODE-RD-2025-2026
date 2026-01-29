<?php

namespace App\Controllers;

use App\Lib\Controllers\AbstractController;
use App\Lib\Http\Request;
use App\Lib\Http\Response;

class GetContactController extends AbstractController
{
    public function process(Request $request): Response
    {
        // Vérifier que la méthode est GET
        if ($request->checkMethod('GET') === false) {
            return new Response(json_encode(['error' => 'Method Not Allowed']), 405, ['Content-Type' => 'application/json']);
        }

        // Récupérer l'email depuis le path parameter
        $emailToFind = $request->getPathParam('email');

        // Vérifier que le paramètre email est présent
        if (empty($emailToFind)) {
            return new Response(json_encode(['error' => 'Email parameter is required']), 400, ['Content-Type' => 'application/json']);
        }

        // Chemin du répertoire des contacts
        $dir = __DIR__ . '/../../var/contacts/';

        // Vérifier si le répertoire existe
        if (!is_dir($dir)) {
            return new Response(json_encode(['error' => 'Contact not found']), 404, ['Content-Type' => 'application/json']);
        }

        // Récupérer tous les fichiers JSON du répertoire
        $files = glob($dir . '*.json');

        // Chercher le contact avec cet email
        foreach ($files as $file) {
            $content = file_get_contents($file);
            $contactData = json_decode($content, true);

            if ($contactData && $contactData['email'] === $emailToFind) {
                // Contact trouvé, le retourner avec le format demandé
                $contact = [
                    'email' => $contactData['email'],
                    'subject' => $contactData['subject'],
                    'message' => $contactData['message'],
                    'dateOfCreation' => $contactData['CreationDate'],
                    'dateOfLastUpdate' => $contactData['LastUpdateDate']
                ];
                return new Response(json_encode($contact), 200, ['Content-Type' => 'application/json']);
            }
        }

        // Contact non trouvé
        return new Response(json_encode(['error' => 'Contact not found']), 404, ['Content-Type' => 'application/json']);
    }
}
