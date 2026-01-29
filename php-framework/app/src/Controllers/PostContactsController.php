<?php

namespace App\Controllers;

use App\Lib\Controllers\AbstractController;
use App\Lib\Http\Request;
use App\Lib\Http\Response;
use App\Entities\Contact;

class PostContactsController extends AbstractController
{

    public function process(Request $request): Response
    {
        // Champs autorisés
        $allowedFields = ['email', 'subject', 'message'];

        // Vérifier que la méthode est POST
        if ($request->checkMethod('POST') === false) {
            return new Response(json_encode(['error' => 'Method Not Allowed']), 405, ['Content-Type' => 'application/json']);
        }

        // Vérifier que la requête n'est pas vide
        $bodyRaw = $request->getBody();
        if (empty($bodyRaw)) {
            return new Response(json_encode(['error' => 'Empty request body']), 400, ['Content-Type' => 'application/json']);
        }

        // Vérifier le format JSON
        if (!Request::isJson($bodyRaw)) {
            return new Response(json_encode(['error' => 'Invalid JSON format']), 400, ['Content-Type' => 'application/json']);
        }

        // Décoder le JSON
        $body = json_decode($bodyRaw, true);

        //  Vérifier que les champs sont valides
        if (count(array_diff(array_keys($body), $allowedFields)) > 0) {
            // Si champs invalides -> 400
            return new Response(json_encode(['error' => 'Invalid fields in request body']), 400, ['Content-Type' => 'application/json']);
        }

        // Vérifier que tous les champs sont présents
        if (count(array_diff($allowedFields, array_keys($body))) > 0) {
            // Si champs manquants -> 400
            return new Response(json_encode(['error' => 'Missing fields in request body']), 400, ['Content-Type' => 'application/json']);
        }

        // Construction de l'entité Contact
        $timestamp = time();
        $contact = new Contact(
            $body['email'],
            $body['subject'],
            $body['message']
        );

        // Préparer chemin de stockage
        $filename = "{$timestamp}_{$body['email']}.json";
        $dir = __DIR__ . '/../../var/contacts/';
        // Crée le répertoire si besoin
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $filepath = $dir . $filename;

        // Sauvegarde du contact
        $contact->saveToFile($filepath);

        // Nom du fichier à afficher
        $displayFilename = date('Y-m-d_H-i-s', $timestamp) . "_{$body['email']}.json";
        return new Response(json_encode(["file" => $displayFilename]), 201, ['Content-Type' => 'application/json']);
    }
}