<?php

declare(strict_types=1);

namespace SdFramework\View;

class View
{
    private string $template;
    private array $data;
    private string $layoutFile;
    private array $sections = [];
    private ?string $currentSection = null;

    public function __construct(string $template, array $data = [], string $layoutFile = '')
    {
        $this->template = $template;
        $this->data = $data;
        $this->layoutFile = $layoutFile;
    }

    public function render(): string
    {
        $templatePath = $this->findTemplate($this->template);
        
        if (!file_exists($templatePath)) {
            throw new \RuntimeException("Template file not found: {$this->template}");
        }

        extract($this->data, EXTR_SKIP);
        ob_start();
        include $templatePath;
        $content = ob_get_clean();

        if ($this->layoutFile) {
            $layoutPath = $this->findTemplate($this->layoutFile);
            if (!file_exists($layoutPath)) {
                throw new \RuntimeException("Layout file not found: {$this->layoutFile}");
            }
            ob_start();
            include $layoutPath;
            $content = ob_get_clean();
        }

        return $content;
    }

    private function findTemplate(string $template): string
    {
        $viewsPath = dirname(__DIR__, 2) . '/app/Views/';
        return $viewsPath . ltrim($template, '/');
    }

    public function section(string $name): void
    {
        $this->currentSection = $name;
        ob_start();
    }

    public function endSection(): void
    {
        if ($this->currentSection === null) {
            throw new \RuntimeException('No section started');
        }

        $this->sections[$this->currentSection] = ob_get_clean();
        $this->currentSection = null;
    }

    public function getSection(string $name): string
    {
        return $this->sections[$name] ?? '';
    }

    public function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    public static function make(string $template, array $data = [], string $layoutFile = ''): self
    {
        return new self($template, $data, $layoutFile);
    }
}
