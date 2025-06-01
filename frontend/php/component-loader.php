<?php
/**
 * PHP Component Loader
 * Server-side component loading and caching system
 */
class ComponentLoader {
    private $rel_path;
    private $cache;
    private $base_path;

    public function __construct($rel_path = '../components/') {
        $this->rel_path = $rel_path;
        $this->cache = [];
        // Set base path to the directory containing this script
        $this->base_path = dirname(__FILE__) . '/';
    }

    /**
     * Get the full URL/path for a component
     */
    public function getComponentUrl($name) {
        return $this->rel_path . $name . '.html';
    }

    /**
     * Get the full file path for a component
     */
    public function getComponentPath($name) {
        return $this->base_path . $this->rel_path . $name . '.html';
    }

    /**
     * Load a component from file system
     */
    public function loadComponent($url) {
        // Check cache first
        if (isset($this->cache[$url])) {
            return $this->cache[$url];
        }

        // Convert URL to file path
        $file_path = $this->base_path . $url;
        
        try {
            if (!file_exists($file_path)) {
                throw new Exception("Component file not found: $file_path");
            }

            if (!is_readable($file_path)) {
                throw new Exception("Component file not readable: $file_path");
            }

            $html = file_get_contents($file_path);
            
            if ($html === false) {
                throw new Exception("Failed to read component file: $file_path");
            }

            // Cache the result
            $this->cache[$url] = $html;
            return $html;
        } catch (Exception $e) {
            error_log("Error loading component: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Load component by name
     */
    public function loadComponentByName($name) {
        $url = $this->getComponentUrl($name);
        return $this->loadComponent($url);
    }

    /**
     * Get component HTML with error handling
     */
    public function getComponent($name) {
        $html = $this->loadComponentByName($name);
        if ($html === null) {
            error_log("Component not found: $name");
            return "<!-- Component '$name' not found -->";
        }
        return $html;
    }

    /**
     * Render component directly to output
     */
    public function renderComponent($name) {
        echo $this->getComponent($name);
    }

    /**
     * Load multiple components and return as array
     */
    public function batchLoadComponents($componentNames) {
        $components = [];
        
        foreach ($componentNames as $name) {
            $html = $this->loadComponentByName($name);
            
            if ($html !== null) {
                $components[$name] = $html;
            } else {
                error_log("Component not found: $name");
                $components[$name] = "<!-- Component '$name' not found -->";
            }
        }
        
        return $components;
    }

    /**
     * Render multiple components in sequence
     */
    public function batchRenderComponents($componentNames) {
        foreach ($componentNames as $name) {
            $this->renderComponent($name);
        }
    }

    /**
     * Get all cached components
     */
    public function getCache() {
        return $this->cache;
    }

    /**
     * Clear the cache
     */
    public function clearCache() {
        $this->cache = [];
    }

    /**
     * Clear specific component from cache
     */
    public function clearComponentCache($name) {
        $url = $this->getComponentUrl($name);
        unset($this->cache[$url]);
    }

    /**
     * Check if component exists
     */
    public function componentExists($name) {
        $file_path = $this->getComponentPath($name);
        return file_exists($file_path) && is_readable($file_path);
    }

    /**
     * Get component with variables replaced
     * Usage: getComponentWithVars('header', ['title' => 'My Page', 'user' => 'John'])
     */
    public function getComponentWithVars($name, $variables = []) {
        $html = $this->getComponent($name);
        
        if (!empty($variables)) {
            foreach ($variables as $key => $value) {
                // Replace {{variable}} syntax
                $html = str_replace('{{' . $key . '}}', htmlspecialchars($value), $html);
                // Replace {variable} syntax as well
                $html = str_replace('{' . $key . '}', htmlspecialchars($value), $html);
            }
        }
        
        return $html;
    }

    /**
     * Render component with variables
     */
    public function renderComponentWithVars($name, $variables = []) {
        echo $this->getComponentWithVars($name, $variables);
    }
}

// Helper function for quick component loading
function loadComponent($name, $rel_path = '../components/') {
    static $loader = null;
    if ($loader === null) {
        $loader = new ComponentLoader($rel_path);
    }
    return $loader->getComponent($name);
}

// Helper function for quick component rendering
function renderComponent($name, $rel_path = '../components/') {
    echo loadComponent($name, $rel_path);
}

// Helper function for component with variables
function renderComponentWithVars($name, $variables = [], $rel_path = '../components/') {
    static $loader = null;
    if ($loader === null) {
        $loader = new ComponentLoader($rel_path);
    }
    echo $loader->getComponentWithVars($name, $variables);
}
?>
