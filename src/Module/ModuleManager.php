<?php

declare(strict_types=1);

namespace SdFramework\Module;

use SdFramework\Config\Config;
use SdFramework\Event\EventDispatcher;
use SdFramework\Database\Connection;

class ModuleManager
{
    private array $modules = [];
    private array $loadOrder = [];
    private Connection $db;
    private Config $config;
    private EventDispatcher $events;

    public function __construct(
        Connection $db,
        Config $config,
        EventDispatcher $events
    ) {
        $this->db = $db;
        $this->config = $config;
        $this->events = $events;
    }

    public function register(string $moduleClass): void
    {
        if (!is_subclass_of($moduleClass, Module::class)) {
            throw new \InvalidArgumentException("$moduleClass must extend " . Module::class);
        }

        $module = new $moduleClass($this->config, $this->events);
        $this->modules[$module->getName()] = $module;

        // Save to database if not exists
        if (!$this->db->table('modules')->where('name', '=', $module->getName())->exists()) {
            $this->db->table('modules')->insert([
                'name' => $module->getName(),
                'version' => $module->getVersion(),
                'description' => $module->getDescription(),
                'dependencies' => json_encode($module->getDependencies()),
                'installed_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        $this->calculateLoadOrder();
    }

    public function boot(): void
    {
        foreach ($this->loadOrder as $moduleName) {
            $this->modules[$moduleName]->boot();
        }
    }

    public function getModule(string $name): ?Module
    {
        return $this->modules[$name] ?? null;
    }

    public function isEnabled(string $name): bool
    {
        return $this->db->table('modules')
            ->where('name', '=', $name)
            ->where('is_active', '=', true)
            ->exists();
    }

    public function enable(string $name): void
    {
        $this->db->table('modules')
            ->where('name', '=', $name)
            ->update(['is_active' => true]);

        if (isset($this->modules[$name])) {
            $this->modules[$name]->enable();
        }
    }

    public function disable(string $name): void
    {
        $this->db->table('modules')
            ->where('name', '=', $name)
            ->update(['is_active' => false]);

        if (isset($this->modules[$name])) {
            $this->modules[$name]->disable();
        }
    }

    private function calculateLoadOrder(): void
    {
        // Reset load order
        $this->loadOrder = [];
        $visited = [];
        $visiting = [];

        foreach ($this->modules as $module) {
            $this->visitModule($module->getName(), $visited, $visiting);
        }
    }

    private function visitModule(string $name, array &$visited, array &$visiting): void
    {
        if (isset($visited[$name])) {
            return;
        }

        if (isset($visiting[$name])) {
            throw new \RuntimeException("Circular dependency detected in module: $name");
        }

        $visiting[$name] = true;
        $module = $this->modules[$name];

        foreach ($module->getDependencies() as $dependency) {
            if (!isset($this->modules[$dependency])) {
                throw new \RuntimeException("Missing dependency: $dependency for module: $name");
            }
            $this->visitModule($dependency, $visited, $visiting);
        }

        unset($visiting[$name]);
        $visited[$name] = true;
        $this->loadOrder[] = $name;
    }
}
