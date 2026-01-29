<?php

namespace App\Controllers;

use App\Lib\Controllers\AbstractController;
use App\Lib\Http\Request;
use App\Lib\Http\Response;

class GetContactsController extends AbstractController
{
    public function process(Request $request): Response
    {
        // Vérifier que la méthode est GET
        if ($request->checkMethod('GET') === false) {
            return new Response(json_encode(['error' => 'Method Not Allowed']), 405, ['Content-Type' => 'application/json']);
        }

        // Chemin du répertoire des contacts
        $dir = __DIR__ . '/../../var/contacts/';

        // Vérifier si le répertoire existe
        if (!is_dir($dir)) {
            // Retourner un tableau vide si aucun contact existe
            return new Response(json_encode([]), 200, ['Content-Type' => 'application/json']);
        }

        // Récupérer tous les fichiers JSON du répertoire
        $files = glob($dir . '*.json');

        // Tableau pour stocker les contacts
        $contacts = [];

        // Parcourir tous les fichiers et récupérer leur contenu
        foreach ($files as $file) {
            $content = file_get_contents($file);
            $contactData = json_decode($content, true);

            // Reformater les données selon le template demandé
            if ($contactData) {
                $contacts[] = [
                    'email' => $contactData['email'],
                    'subject' => $contactData['subject'],
                    'message' => $contactData['message'],
                    'creationDate' => $contactData['CreationDate'],
                    'lastUpdateDate' => $contactData['LastUpdateDate']
                ];
            }
        }

        // Retourner la liste des contacts au format JSON
        return new Response(json_encode($contacts), 200, ['Content-Type' => 'application/json']);
    }
}
