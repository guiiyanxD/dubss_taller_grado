<?php

return [
    // Tema y apariencia
    'theme' => 'default',

    // Título de la documentación
    'title' => 'DUBSS API Documentation',

    // Descripción del proyecto
    'description' => 'API REST para el Sistema de Seguimiento de Trámites de Becas Socioeconómicas (DUBSS). Proporciona endpoints para gestión de becas, postulaciones, trámites, formularios socioeconómicos y Business Intelligence.',

    // URL base de la API
    'base_url' => env('APP_URL', 'http://localhost:8000'),

    // Rutas a documentar
    'routes' => [
        [
            'match' => [
                'prefixes' => ['api/*'],
                'domains' => ['*'],
            ],
            'include' => [],
            'exclude' => [],
            'apply' => [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
            ],
        ],
    ],

    // Tipo de documentación
    'type' => 'laravel',

    // Información de autenticación
    'auth' => [
        'enabled' => true,
        'default' => false,
        'in' => 'bearer',
        'name' => 'Authorization',
        'use_value' => env('SCRIBE_AUTH_KEY'),
        'placeholder' => '{YOUR_AUTH_KEY}',
        'extra_info' => 'Puedes obtener tu token de autenticación haciendo login en <code>POST /api/auth/login</code>. Luego incluye el token en el header: <code>Authorization: Bearer {token}</code>',
    ],

    // Configuración de ejemplos
    'examples' => [
        'faker_seed' => 1234,
        'models_source' => ['factoryCreate', 'factoryMake', 'databaseFirst'],
    ],

    // Configuración de respuestas
    'responses' => [
        'calls' => [
            'methods' => ['GET'],
            'config' => [
                'app.env' => 'documentation',
            ],
            'queryParams' => [],
            'bodyParams' => [],
            'fileParams' => [],
        ],
        'files' => [
            [
                'status' => 200,
                'content' => '{"message": "Success"}',
            ],
            [
                'status' => 401,
                'content' => '{"message": "Unauthenticated."}',
            ],
            [
                'status' => 403,
                'content' => '{"message": "This action is unauthorized."}',
            ],
            [
                'status' => 404,
                'content' => '{"message": "Resource not found."}',
            ],
            [
                'status' => 422,
                'content' => '{"message": "The given data was invalid.", "errors": {}}',
            ],
        ],
    ],

    // Logo
    'logo' => false,

    // Postman collection
    'postman' => [
        'enabled' => true,
        'overrides' => [
            'info.name' => 'DUBSS API',
            'info.description' => 'Colección de Postman para la API de DUBSS',
        ],
    ],

    // OpenAPI spec
    'openapi' => [
        'enabled' => true,
        'overrides' => [
            'info.title' => 'DUBSS API',
            'info.description' => 'Especificación OpenAPI para la API de DUBSS',
            'info.version' => '1.0.0',
        ],
    ],

    // Estrategias de extracción de metadata (CORREGIDAS)
    'strategies' => [
        'metadata' => [
            \Knuckles\Scribe\Extracting\Strategies\Metadata\GetFromDocBlocks::class,
        ],
        'urlParameters' => [
            \Knuckles\Scribe\Extracting\Strategies\UrlParameters\GetFromLaravelAPI::class,
            \Knuckles\Scribe\Extracting\Strategies\UrlParameters\GetFromUrlParamAttribute::class,
        ],
        'queryParameters' => [
            \Knuckles\Scribe\Extracting\Strategies\QueryParameters\GetFromFormRequest::class,
            \Knuckles\Scribe\Extracting\Strategies\QueryParameters\GetFromInlineValidator::class,
        ],
        'headers' => [
            \Knuckles\Scribe\Extracting\Strategies\Headers\GetFromHeaderAttribute::class,
        ],
        'bodyParameters' => [
            \Knuckles\Scribe\Extracting\Strategies\BodyParameters\GetFromFormRequest::class,
            \Knuckles\Scribe\Extracting\Strategies\BodyParameters\GetFromInlineValidator::class,
            \Knuckles\Scribe\Extracting\Strategies\BodyParameters\GetFromBodyParamAttribute::class,
        ],
        'responses' => [
            \Knuckles\Scribe\Extracting\Strategies\Responses\UseResponseAttributes::class,
            \Knuckles\Scribe\Extracting\Strategies\Responses\UseApiResourceTags::class,
            \Knuckles\Scribe\Extracting\Strategies\Responses\UseTransformerTags::class,
            \Knuckles\Scribe\Extracting\Strategies\Responses\UseResponseTag::class,
            \Knuckles\Scribe\Extracting\Strategies\Responses\UseResponseFileTag::class,
            \Knuckles\Scribe\Extracting\Strategies\Responses\ResponseCalls::class,
        ],
        'responseFields' => [
            \Knuckles\Scribe\Extracting\Strategies\ResponseFields\GetFromResponseFieldAttribute::class,
        ],
    ],

    // Directorio de salida
    'static' => [
        'output_path' => 'public/docs',
    ],

    'laravel' => [
        'add_routes' => true,
        'docs_url' => '/docs',
        'middleware' => [],
    ],

    // Idioma
    'example_languages' => [
        'bash',
        'javascript',
        'php',
        'python',
    ],

    'fractal' => [
        'serializer' => null,
    ],

    'routeMatcher' => \Knuckles\Scribe\Matching\RouteMatcher::class,
];
