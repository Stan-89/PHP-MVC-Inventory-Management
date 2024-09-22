<?php

declare(strict_types=1);

namespace App;

class Utility
{
    //Load the .env file
    public static function loadEnv(string $filepath): void
    {
        $lines = file($filepath, FILE_IGNORE_NEW_LINES);

        //For each line. List assigns variables in one operation from an array (from explode)
        //Since the .env file must be structured as such, ex : DB_HOST=localhost
        foreach ($lines as $line)
        {
            list($name, $value) = explode("=", $line, 2);

            $_ENV[$name] = $value;
        }
    }


    //Error handling - make it an exception
    public static function handleError(int $error_number, string $error_string, string $error_file, int $error_line ): bool
    {
        throw new ErrorException($error_string, 0, $error_number, $error_file, $error_line);
    }

    //Exception handling
    public static function handleException(Throwable $exception): void
    {
        echo 'an error occured!';
        print_r($exception);
        error_log($exception);
    }

}
