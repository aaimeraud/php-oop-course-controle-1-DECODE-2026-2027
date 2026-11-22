<?php

namespace App\Services;

class ContactStorage
{
    private const string DIRECTORY = __DIR__ . "/../../var/contacts/";


    public function isValidName(string $name): bool
    {
        return str_contains($name, "..") === false
            && str_contains($name, "/") === false
            && str_contains($name, "\\") === false;
    }

    public function exists(string $name): bool
    {
        return file_exists($this->pathFor($name));
    }

    public function read(string $name): array
    {
        return json_decode(file_get_contents($this->pathFor($name)), true, 512, JSON_THROW_ON_ERROR);
    }

    public function save(string $name, array $contact): bool
    {
        $content = json_encode($contact, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);

        return file_put_contents($this->pathFor($name), $content, LOCK_EX) !== false;
    }

    private function pathFor(string $name): string
    {
        return self::DIRECTORY . $name . ".json";
    }
}
