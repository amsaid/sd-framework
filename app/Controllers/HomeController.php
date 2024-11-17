<?php

declare(strict_types=1);

namespace App\Controllers;

use SdFramework\Http\Request;
use SdFramework\Http\Response;
use SdFramework\View\View;
use SdFramework\Validation\Validator;
use SdFramework\Validation\RuleRegistry;

class HomeController
{
    private RuleRegistry $ruleRegistry;

    public function __construct(RuleRegistry $ruleRegistry)
    {
        $this->ruleRegistry = $ruleRegistry;
    }

    public function index(Request $request): Response
    {
        $view = View::make('home.php', [
            'title' => 'Welcome to SdFramework',
            'content' => 'A modern PHP MVC Framework with powerful validation'
        ], 'layouts/main.php');

        return new Response($view->render());
    }

    public function about(Request $request): Response
    {
        return Response::json([
            'framework' => 'SdFramework',
            'version' => '1.0.0',
            'author' => 'Your Name',
            'features' => [
                'Modular Architecture',
                'Advanced Validation',
                'Event System',
                'Database Integration'
            ]
        ]);
    }

    public function search(Request $request): Response
    {
        $data = $request->all();
        
        // Example of using validation in API endpoint
        $validator = new Validator($data, [
            'q' => 'required|min:3',
            'type' => 'required|alpha',
            'limit' => 'numeric|min:1|max:100'
        ], $this->ruleRegistry);

        if (!$validator->validate()) {
            return Response::json([
                'error' => 'Validation failed',
                'errors' => $validator->getErrors()
            ], 422);
        }

        // Process valid search request
        return Response::json([
            'query' => $data['q'],
            'type' => $data['type'],
            'limit' => $data['limit'] ?? 10,
            'results' => [] // Your search results here
        ]);
    }
}
