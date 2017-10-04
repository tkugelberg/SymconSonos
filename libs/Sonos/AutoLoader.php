<?php
namespace Sonos;


/**
 * A PHP PSR-0 Autoloader Class.
 * @link http://www.php-fig.org/psr/psr-0/
 */
class AutoLoader
{

    /**
     * @var array Base directories to be searched for classes.
     * @see AutoLoader::AddBaseDir()
     */
    protected static $baseDirs;

    /**
     * Registers the PHP PSR-0 autoloader and adds the specified base directory to the search list.
     * @param string $baseDir Absolute path to the base directory to be added to the search list.
     * @see AutoLoader::$baseDirs
     */
    public static function Register($baseDir)
    {
        // Remember the given base directory
        self::AddBaseDir($baseDir);

        // Register the PHP SPL Autoloader
        spl_autoload_register(__NAMESPACE__ . '\AutoLoader::load');
    }

    /**
     * Adds a base directory to the search list.
     * @param string $baseDir Absolute path to the base directory to be added to the search list.
     */
    public static function AddBaseDir($baseDir)
    {
        // Add the base directory
        self::$baseDirs[] = $baseDir;
    }

    /**
     * Iterates through the search list and tries to load the class file until the class was found.
     * This method is registered as PHP autoload method and automatically called by the interpreter.
     * @param string $className FQCN of the class to be loaded.
     * @return bool True if the class was found and false if not.
     */
    public static function Load($className)
    {
        // Assume the loader did not find the class
        $success = false;

        // Loop through all base directories
        foreach (self::$baseDirs as $baseDir) {
            // Try to load the class from the directory structure
            $success = self::LoadFromPath($baseDir, $className);
            // Break if the class was found
            if ($success) break;
        }

        // Return the result
        return $success;
    }

    /**
     * Tries to include the class file in the given base directory using the PSR-0 naming conventions.
     * @param string $baseDir Absolute path to the base directory to be added to the search list.
     * @param string $className FQCN of the class to be loaded.
     * @return bool True if the class exists after including the file or false if the file was not found or did not contain the class.
     */
    protected static function LoadFromPath($baseDir, $className)
    {
        // Generate the file name according to the PSR-0 specification
        $fileName = $baseDir . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $className) . '.php';

        // Return false if the file doesn't exist
        if (! file_exists($fileName)) {
            return false;
        }

        // Require the file
        /** @noinspection PhpIncludeInspection */
        @require_once($fileName);

        // Check whether the class now exists and return the result
        return class_exists($className);
    }

}