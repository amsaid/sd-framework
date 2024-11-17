<?php

declare(strict_types=1);

namespace App\Controllers;

use SdFramework\Http\Request;
use SdFramework\Http\Response;
use SdFramework\View\View;
use SdFramework\Validation\Validator;
use SdFramework\Validation\RuleRegistry;

class ContactController
{
    private RuleRegistry $ruleRegistry;

    public function __construct(RuleRegistry $ruleRegistry)
    {
        $this->ruleRegistry = $ruleRegistry;
    }

    public function show(Request $request): Response
    {
        $view = View::make('contact.php', [
            'title' => 'Contact Us',
            'errors' => []
        ], 'layouts/main.php');

        return new Response($view->render());
    }

    public function submit(Request $request): Response
    {
        $data = $request->all();
        
        // Create validator instance
        $validator = new Validator($data, [
            'name' => 'required|min:2',
            'email' => 'required|email',
            'message' => 'required|min:10',
        ], $this->ruleRegistry);

        // Validate the request
        if (!$validator->validate()) {
            return $this->showWithErrors($validator->getErrors());
        }

        // Process the valid contact form
        // Here you would typically send an email or save to database
        
        return Response::redirect('/contact/success');
    }

    public function success(Request $request): Response
    {
        $view = View::make('contact/success.php', [
            'title' => 'Message Sent',
            'message' => 'Thank you for contacting us!'
        ], 'layouts/main.php');

        return new Response($view->render());
    }

    private function showWithErrors(array $errors): Response
    {
        $view = View::make('contact.php', [
            'title' => 'Contact Us',
            'errors' => $errors
        ], 'layouts/main.php');

        return new Response($view->render(), 422);
    }
}
