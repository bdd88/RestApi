<?php
namespace bdd88\AutoLoader;

/**
 * A lazy autoloader that follows PSR-4 specifications.
 * 
 * @version 1.0.1
 * @link https://github.com/bdd88/AutoLoader
 * @link https://www.php-fig.org/psr/psr-4/
 */
class AutoLoader
{
    private array $namespaces;

    /**
     * Set a catch all directory.
     *
     * @param string|null $defaultDirectory Defaults to the current working directory if not specified.
     */
    public function __construct(?string $defaultDirectory = NULL)
    {
        $dir = $defaultDirectory ?? getcwd();
        $this->register('', $dir);
    }

    /** Check for and remove a leading slash from namespace string for consistency. */
    private function validateNamespace(string $namespace): string
    {
        if (strpos($namespace, '\\') === 0) {
            $namespace = substr($namespace, 1);
        }
        return $namespace;
    }

    /**
     * Register a namespace to a directory.
     * Sub namespace directories will be determined automatically using the base namespace directory (per the PSR-4 spec), unless they are manually registered to a different directory.
     */
    public function register(string $namespace, string $path): void
    {
        $namespace = $this->validateNamespace($namespace);
        $this->namespaces[$namespace] = $path;
    }

    /**
     * Load a class file from a registered namespace.
     *
     * @param string $class Should include the fully qualified namespace.
     * @return boolean TRUE if file loaded, or FALSE if file wasn't found.
     */
    public function load(string $class): void
    {
        $class = $this->validateNamespace($class);

        // Find a base registered namespace by checking for the most specific to the least specific sub namespaces.
        $baseNamespace = explode('\\', $class);
        while (sizeof($baseNamespace) > 0) {
            array_pop($baseNamespace);
            $baseNamespaceString = implode('\\', $baseNamespace);
            if (isset($this->namespaces[$baseNamespaceString])) {
                break;
            }
        }

        // Construct a path to the class file by combining the registered base namespace directory with the expected sub namespace directories.
        $path = $this->namespaces[$baseNamespaceString];
        $subNamespace = array_diff(explode('\\', $class), $baseNamespace);
        foreach ($subNamespace as $dir) {
            $path .= DIRECTORY_SEPARATOR . $dir;
        }
        $path .= '.php';
        if (file_exists($path)) {
            require $path;
        }
    }

}

?>