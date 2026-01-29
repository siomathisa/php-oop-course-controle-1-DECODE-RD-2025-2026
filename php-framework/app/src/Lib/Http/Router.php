<?php

namespace App\Lib\Http;

use App\Lib\Controllers\AbstractController;


class Router {

    const string CONTROLLER_NAMESPACE_PREFIX = "App\\Controllers\\";
    const string ROUTE_CONFIG_PATH = __DIR__ . '/../../../config/routes.json';
    

    public static function route(Request $request): Response {
        $config = self::getConfig();

        foreach($config as $route) {
            if(self::checkMethod($request, $route) === false) {
                continue;
            }
            
            $pathParams = self::checkUri($request, $route);
            if($pathParams === false) {
                continue;
            }
            
            // Stocker les paramètres du path dans la requête
            $request->setPathParams($pathParams);

            $controller = self::getControllerInstance($route['controller']);
            return $controller->process($request);
        }

        throw new \Exception('Route not found', 404);
    }
    
    private static function getConfig(): array {
        $routesConfigContent = file_get_contents(self::ROUTE_CONFIG_PATH);
        $routesConfig = json_decode($routesConfigContent, true);

        return $routesConfig;
    }


    private static function checkMethod(Request $request, array $route): bool {
        return $request->getMethod() === $route['method'];
    }

    private static function checkUri(Request $request, array $route): array|false {
        // Nettoyer l'URI de la requête (enlever les query params)
        $requestUri = parse_url($request->getUri(), PHP_URL_PATH);
        $routePath = $route['path'];
        
        // Si pas de paramètres dans la route, comparaison simple
        if (strpos($routePath, '{') === false) {
            return $requestUri === $routePath ? [] : false;
        }
        
        // Convertir le pattern de route en regex
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';
        
        // Tester si l'URI correspond au pattern
        if (preg_match($pattern, $requestUri, $matches)) {
            // Extraire seulement les paramètres nommés
            $params = [];
            foreach ($matches as $key => $value) {
                if (is_string($key)) {
                    $params[$key] = $value;
                }
            }
            return $params;
        }
        
        return false;
    }
    
    private static function getControllerInstance(string $controller): AbstractController {
        $controllerClass = self::CONTROLLER_NAMESPACE_PREFIX . $controller;

        if(class_exists($controllerClass) === false) {
            throw new \Exception('Route not found', 404);
        }

        $controllerInstance = new $controllerClass();

        if(is_subclass_of($controllerInstance, AbstractController::class)=== false){
            throw new \Exception('Route not found', 404);
        }
        
        return $controllerInstance;
    }

}
