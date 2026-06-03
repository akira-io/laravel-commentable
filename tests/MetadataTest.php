<?php

declare(strict_types=1);

it('does not reference the retired repository URL', function (): void {
    $retiredRepositoryUrl = implode('/', ['github.com/akira-io', 'commentable']);

    $paths = [
        '.github',
        'README.md',
        'composer.json',
        'docs',
    ];

    $matches = [];

    foreach ($paths as $path) {
        if (is_file($path)) {
            if (str_contains((string) file_get_contents($path), $retiredRepositoryUrl)) {
                $matches[] = $path;
            }

            continue;
        }

        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));

        foreach ($files as $file) {
            if (! $file->isFile()) {
                continue;
            }

            if (str_contains((string) file_get_contents($file->getPathname()), $retiredRepositoryUrl)) {
                $matches[] = $file->getPathname();
            }
        }
    }

    expect($matches)->toBeEmpty();
});
