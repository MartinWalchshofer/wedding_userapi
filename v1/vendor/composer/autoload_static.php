<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInita3b4aa1c13404a5040d9b7863c42fe2e
{
    public static $prefixLengthsPsr4 = array (
        'F' => 
        array (
            'Firebase\\JWT\\' => 13,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Firebase\\JWT\\' => 
        array (
            0 => __DIR__ . '/..' . '/firebase/php-jwt/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInita3b4aa1c13404a5040d9b7863c42fe2e::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInita3b4aa1c13404a5040d9b7863c42fe2e::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInita3b4aa1c13404a5040d9b7863c42fe2e::$classMap;

        }, null, ClassLoader::class);
    }
}